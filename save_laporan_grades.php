<?php
// save_laporan_grades.php
include 'middleware.php';
include 'db_connection.php';
require_api_auth('lecturer');

$current_lecturer_id = $_SESSION['user_id'];

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$course_id = $data['course_id'] ?? null;
$semester = $data['semester'] ?? null;
$academic_year = $data['academic_year'] ?? null;
$grades_data = $data['grades'] ?? [];

if (!$course_id || !is_array($grades_data) || empty($grades_data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided.', 'error_detail' => 'Missing course_id or grades data.']);
    exit();
}

$conn->beginTransaction();

try {
    // Fungsi helper untuk mendapatkan grade letter
    function getGradeLetterForSave($final_score) {
        if ($final_score === null) return '-';
        if ($final_score >= 85) return 'A';
        if ($final_score >= 70) return 'B';
        if ($final_score >= 55) return 'C';
        if ($final_score >= 40) return 'D';
        return 'E';
    }

    function getGradePointsForSave($grade_letter) {
        switch ($grade_letter) {
            case 'A': return 4.00;
            case 'B': return 3.00;
            case 'C': return 2.00;
            case 'D': return 1.00;
            case 'E': return 0.00;
            default: return 0.00;
        }
    }


    foreach ($grades_data as $student_grade) {
        $student_id = (int)($student_grade['user_id'] ?? 0);
        $tugas = $student_grade['tugas'] ?? null;
        $uts = $student_grade['uts'] ?? null;
        $uas = $student_grade['uas'] ?? null;
        $partisipasi = $student_grade['partisipasi'] ?? null;

        $grade_components = [
            'Assignment' => $tugas,
            'UTS' => $uts,
            'UAS' => $uas,
            'Partisipasi' => $partisipasi
        ];

        foreach ($grade_components as $type => $value) {
            if ($value !== null && is_numeric($value)) {
                $item_id_for_grade = null;

                // Cek apakah nilai untuk komponen ini sudah ada di tabel grades
                $sql_check = "SELECT grade_id FROM grades WHERE student_id = ? AND course_id = ? AND grade_type = ?";
                $stmt_check = $conn->prepare($sql_check);
                if (!$stmt_check) throw new Exception("Prepare check grade failed.");
                $stmt_check->execute([$student_id, $course_id, $type]);

                $grade_letter = getGradeLetterForSave($value);
                $grade_points = getGradePointsForSave($grade_letter);

                $row = $stmt_check->fetch();
                if ($row) {
                    $grade_id = $row['grade_id'];
                    $sql_update = "UPDATE grades SET grade_value = ?, grade_letter = ?, grade_points = ?, graded_at = NOW() WHERE grade_id = ?";
                    $stmt_update = $conn->prepare($sql_update);
                    if (!$stmt_update) throw new Exception("Prepare update grade failed.");
                    $stmt_update->execute([$value, $grade_letter, $grade_points, $grade_id]);
                } else {
                    $sql_insert = "INSERT INTO grades (student_id, course_id, item_id, grade_value, grade_letter, grade_points, grade_type, graded_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                    $stmt_insert = $conn->prepare($sql_insert);
                    if (!$stmt_insert) throw new Exception("Prepare insert grade failed.");
                    $stmt_insert->execute([$student_id, $course_id, $item_id_for_grade, $value, $grade_letter, $grade_points, $type]);
                }
            }
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Nilai komponen berhasil disimpan!']);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Failed to save grades.', 'error_detail' => $e->getMessage()]);
}

$conn = null;
?>