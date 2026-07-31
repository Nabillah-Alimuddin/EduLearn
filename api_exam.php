<?php
// api_exam.php
ob_start();
include 'middleware.php';
include 'db_connection.php';
require_api_auth();

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$student_id = $_SESSION['user_id'];

function send_json_error($message) {
    echo json_encode(['error' => $message]);
    exit();
}

switch ($action) {
    case 'get_all_exams':
        // Ambil semua jadwal ujian untuk mahasiswa yang terdaftar di mata kuliah terkait
        $sql = "
            SELECT
                e.exam_id, e.title, e.exam_type, e.exam_date, e.start_time, e.end_time,
                e.room, e.is_online, e.online_link, e.duration_minutes, e.quiz_id,
                c.course_name, q.total_questions,
                (SELECT COUNT(*) FROM exam_attempts ea WHERE ea.exam_id = e.exam_id AND ea.student_id = ? AND ea.is_completed = TRUE) AS student_completed_exam
            FROM exams e
            JOIN courses c ON e.course_id = c.course_id
            LEFT JOIN quizzes q ON e.quiz_id = q.quiz_id
            JOIN course_enrollments ce ON c.course_id = ce.course_id
            WHERE ce.student_id = ?
            ORDER BY e.exam_date ASC, e.start_time ASC
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            send_json_error("Failed to prepare statement.");
        }
        $stmt->execute([$student_id, $student_id]);
        $exams = $stmt->fetchAll();
        echo json_encode(['exams' => $exams]);
        break;

    case 'get_exam_questions':
        $exam_id = $_GET['exam_id'] ?? null;
        if (!$exam_id) {
            send_json_error('Exam ID is required.');
        }

        // Ambil data quiz_id dari exam_id yang diberikan
        $sql_exam = "SELECT quiz_id FROM exams WHERE exam_id = ?";
        $stmt_exam = $conn->prepare($sql_exam);
        $stmt_exam->execute([$exam_id]);
        $exam = $stmt_exam->fetch();

        if (!$exam || !$exam['quiz_id']) {
            echo json_encode(['questions' => []]);
            exit();
        }
        $quiz_id = $exam['quiz_id'];

        // Ambil semua pertanyaan untuk quiz_id ini
        $sql_questions = "SELECT question_id, question_text, question_formula, question_type FROM quiz_questions WHERE quiz_id = ?";
        $stmt_questions = $conn->prepare($sql_questions);
        $stmt_questions->execute([$quiz_id]);
        $questions = $stmt_questions->fetchAll();

        // Ambil opsi jawaban untuk setiap pertanyaan pilihan ganda
        foreach ($questions as &$q) {
            if ($q['question_type'] === 'multiple_choice') {
                $sql_options = "SELECT option_id, option_text FROM question_options WHERE question_id = ?";
                $stmt_options = $conn->prepare($sql_options);
                $stmt_options->execute([$q['question_id']]);
                $q['options'] = $stmt_options->fetchAll();
            } else {
                $q['options'] = null;
            }
        }
        echo json_encode(['questions' => $questions]);
        break;

    case 'save_exam_attempt':
        $input = json_decode(file_get_contents('php://input'), true);
        $exam_id = $input['exam_id'] ?? null;
        $student_id_post = $input['student_id'] ?? null;
        $is_completed = ($input['is_completed'] ?? 0) ? TRUE : FALSE;
        $answers = $input['answers'] ?? [];

        if (!$exam_id || !$student_id_post || empty($answers)) {
            send_json_error('Invalid or incomplete data.');
        }

        // Pastikan student_id dari sesi sama dengan yang dikirim dari POST
        if ((int)$student_id_post !== (int)$student_id) {
            send_json_error('Session mismatch.');
        }

        $conn->beginTransaction();
        try {
            // Update atau insert ke tabel exam_attempts
            $sql_attempt_check = "SELECT attempt_id FROM exam_attempts WHERE exam_id = ? AND student_id = ?";
            $stmt_attempt_check = $conn->prepare($sql_attempt_check);
            $stmt_attempt_check->execute([$exam_id, $student_id]);
            $attempt = $stmt_attempt_check->fetch();

            if ($attempt) {
                $attempt_id = $attempt['attempt_id'];
                $sql_attempt_update = "UPDATE exam_attempts SET is_completed = ?, end_time = NOW() WHERE attempt_id = ?";
                $stmt_attempt_update = $conn->prepare($sql_attempt_update);
                $stmt_attempt_update->execute([$is_completed ? 1 : 0, $attempt_id]); // PostgreSQL handles boolean or int
            } else {
                $sql_attempt_insert = "INSERT INTO exam_attempts (exam_id, student_id, is_completed, end_time) VALUES (?, ?, ?, NOW())";
                $stmt_attempt_insert = $conn->prepare($sql_attempt_insert);
                $stmt_attempt_insert->execute([$exam_id, $student_id, $is_completed ? 1 : 0]);
                $attempt_id = $conn->lastInsertId();
            }

            // Simpan jawaban
            foreach ($answers as $answer) {
                $question_id = $answer['question_id'];
                $selected_option_id = $answer['selected_option_id'] ?? null;
                $essay_answer = $answer['essay_answer'] ?? null;

                // Cek apakah jawaban sudah ada, untuk update
                $sql_answer_check = "SELECT answer_id FROM quiz_answers WHERE attempt_id = ? AND question_id = ?";
                $stmt_answer_check = $conn->prepare($sql_answer_check);
                $stmt_answer_check->execute([$attempt_id, $question_id]);
                $existing_answer = $stmt_answer_check->fetch();

                // Dapatkan status 'is_correct' jika pertanyaan pilihan ganda
                $is_correct = null;
                if ($selected_option_id !== null) {
                    $sql_correct = "SELECT is_correct FROM question_options WHERE option_id = ?";
                    $stmt_correct = $conn->prepare($sql_correct);
                    $stmt_correct->execute([$selected_option_id]);
                    if ($row_correct = $stmt_correct->fetch()) {
                        $is_correct = $row_correct['is_correct'] ? 1 : 0;
                    }
                }

                if ($existing_answer) {
                    // Update jawaban
                    $sql_answer_update = "UPDATE quiz_answers SET selected_option_id = ?, essay_answer = ?, is_correct = ? WHERE answer_id = ?";
                    $stmt_answer_update = $conn->prepare($sql_answer_update);
                    $stmt_answer_update->execute([$selected_option_id, $essay_answer, $is_correct, $existing_answer['answer_id']]);
                } else {
                    // Insert jawaban baru
                    $sql_answer_insert = "INSERT INTO quiz_answers (attempt_id, question_id, selected_option_id, essay_answer, is_correct) VALUES (?, ?, ?, ?, ?)";
                    $stmt_answer_insert = $conn->prepare($sql_answer_insert);
                    $stmt_answer_insert->execute([$attempt_id, $question_id, $selected_option_id, $essay_answer, $is_correct]);
                }
            }

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Jawaban berhasil disimpan.']);
        } catch (Exception $e) {
            $conn->rollBack();
            send_json_error("Failed to save answers: " . $e->getMessage());
        }
        break;

    default:
        send_json_error('Invalid action.');
        break;
}

ob_end_flush();
?>