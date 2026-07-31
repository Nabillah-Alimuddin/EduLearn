<?php
// save_grades.php
include '../middleware.php';
include '../db_connection.php'; // Koneksi database
require_api_auth('lecturer');

$current_lecturer_id = $_SESSION['user_id'];

// Ambil data JSON dari request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$course_id = $data['course_id'] ?? null;
$assignment_id = $data['assignment_id'] ?? null; // item_id
$grades_data = $data['grades_data'] ?? [];

// Validasi data
if (!$course_id || !$assignment_id || !is_array($grades_data) || empty($grades_data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided.', 'error_detail' => 'Missing course_id, assignment_id, or grades_data.']);
    exit();
}

$conn->beginTransaction(); // Mulai transaksi

try {
    foreach ($grades_data as $student_grade) {
        $student_id = (int)($student_grade['student_id'] ?? 0);
        $grade_value_str = $student_grade['grade_value'] ?? null;
        $notes = $student_grade['notes'] ?? '';

        // Konversi nilai string ke float, set null jika kosong atau tidak valid
        $grade_value = null;
        if (is_numeric($grade_value_str) && $grade_value_str >= 0 && $grade_value_str <= 100) {
            $grade_value = (float)$grade_value_str;
        }

        // Jika nilai tidak valid (bukan angka atau di luar 0-100)
        if ($grade_value === null) {
            error_log("Invalid grade value for student_id $student_id, assignment_id $assignment_id: $grade_value_str");
            continue; // Lewati siswa ini jika nilainya tidak valid
        }

        // Hitung grade letter dan grade points
        $grade_letter = '';
        if ($grade_value >= 85) $grade_letter = 'A';
        else if ($grade_value >= 75) $grade_letter = 'B';
        else if ($grade_value >= 65) $grade_letter = 'C';
        else if ($grade_value >= 55) $grade_letter = 'D';
        else $grade_letter = 'E';

        $grade_points = 0.00;

        // Cek apakah nilai untuk student_id, course_id, dan item_id (assignment_id) sudah ada
        $sql_check_grade = "SELECT grade_id FROM grades WHERE student_id = ? AND course_id = ? AND item_id = ? AND grade_type = 'Assignment'";
        $stmt_check_grade = $conn->prepare($sql_check_grade);
        if (!$stmt_check_grade) throw new Exception("Failed to prepare check grade statement.");
        
        $stmt_check_grade->execute([$student_id, $course_id, $assignment_id]);
        $row = $stmt_check_grade->fetch();

        if ($row) {
            // Update nilai yang sudah ada
            $grade_id = $row['grade_id'];
            $sql_update_grade = "UPDATE grades SET grade_value = ?, grade_letter = ?, grade_points = ?, feedback = ?, graded_at = NOW() WHERE grade_id = ?";
            $stmt_update_grade = $conn->prepare($sql_update_grade);
            if (!$stmt_update_grade) throw new Exception("Failed to prepare update grade statement.");
            $stmt_update_grade->execute([$grade_value, $grade_letter, $grade_points, $notes, $grade_id]);
        } else {
            // Insert nilai baru
            $sql_insert_grade = "INSERT INTO grades (student_id, course_id, item_id, grade_value, grade_letter, grade_points, grade_type, feedback) VALUES (?, ?, ?, ?, ?, ?, 'Assignment', ?)";
            $stmt_insert_grade = $conn->prepare($sql_insert_grade);
            if (!$stmt_insert_grade) throw new Exception("Failed to prepare insert grade statement.");
            $stmt_insert_grade->execute([$student_id, $course_id, $assignment_id, $grade_value, $grade_letter, $grade_points, $notes]);
        }
    }

    $conn->commit(); // Commit transaksi jika semua berhasil
    echo json_encode(['success' => true, 'message' => 'Nilai berhasil disimpan!']);

} catch (Exception $e) {
    $conn->rollBack(); // Rollback transaksi jika ada error
    echo json_encode(['success' => false, 'message' => 'Failed to save grades.', 'error_detail' => $e->getMessage()]);
}

$conn = null;
?>