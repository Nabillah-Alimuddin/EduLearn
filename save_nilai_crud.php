<?php
// save_nilai_crud.php
ob_start();
include 'middleware.php';
include 'db_connection.php';
require_api_auth('lecturer');

// Pastikan request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error("Invalid request method.", 405);
}

$current_lecturer_id = $_SESSION['user_id'];

$course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : null;
$assignment_id = isset($_POST['assignment_id']) ? (int)$_POST['assignment_id'] : null;
$grades_data = $_POST['grades'] ?? [];

if (!$course_id || !$assignment_id || empty($grades_data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data submitted. Missing course_id, assignment_id, or grades data.']);
    exit();
}

$conn->beginTransaction();

try {
    // PostgreSQL uses ON CONFLICT instead of ON DUPLICATE KEY UPDATE
    // Assumes a unique constraint on (student_id, course_id, item_id, grade_type)
    $stmt = $conn->prepare("
        INSERT INTO grades (student_id, course_id, item_id, grade_type, grade_value, feedback, graded_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?) 
        ON CONFLICT (student_id, course_id, item_id, grade_type) DO UPDATE SET
            grade_value = EXCLUDED.grade_value, 
            feedback = EXCLUDED.feedback, 
            graded_by = EXCLUDED.graded_by
    ");

    if (!$stmt) {
        throw new Exception("Failed to prepare statement.");
    }
    
    $null_feedback = null;

    foreach ($grades_data as $student_id => $grade_components) {
        $student_id = (int)$student_id;
        
        $assignment_grade_value = isset($grade_components['Assignment']) && $grade_components['Assignment'] !== '' ? (float)$grade_components['Assignment'] : null;
        $assignment_feedback = $grade_components['feedback'] ?? '';
        $assignment_item_id = $grade_components['Assignment_item_id'] ?? $assignment_id;

        if ($assignment_grade_value !== null) {
            $grade_type = 'Assignment';
            $stmt->execute([$student_id, $course_id, $assignment_item_id, $grade_type, $assignment_grade_value, $assignment_feedback, $current_lecturer_id]);
        }

        $uts_grade_value = isset($grade_components['UTS']) && $grade_components['UTS'] !== '' ? (float)$grade_components['UTS'] : null;
        $uts_item_id = $grade_components['UTS_item_id'] ?? $course_id;
        if ($uts_grade_value !== null) {
            $grade_type = 'UTS';
            $stmt->execute([$student_id, $course_id, $uts_item_id, $grade_type, $uts_grade_value, $null_feedback, $current_lecturer_id]);
        }
        
        $uas_grade_value = isset($grade_components['UAS']) && $grade_components['UAS'] !== '' ? (float)$grade_components['UAS'] : null;
        $uas_item_id = $grade_components['UAS_item_id'] ?? $course_id;
        if ($uas_grade_value !== null) {
            $grade_type = 'UAS';
            $stmt->execute([$student_id, $course_id, $uas_item_id, $grade_type, $uas_grade_value, $null_feedback, $current_lecturer_id]);
        }
        
        $partisipasi_grade_value = isset($grade_components['Partisipasi']) && $grade_components['Partisipasi'] !== '' ? (float)$grade_components['Partisipasi'] : null;
        $partisipasi_item_id = $grade_components['Partisipasi_item_id'] ?? $course_id;
        if ($partisipasi_grade_value !== null) {
            $grade_type = 'Partisipasi';
            $stmt->execute([$student_id, $course_id, $partisipasi_item_id, $grade_type, $partisipasi_grade_value, $null_feedback, $current_lecturer_id]);
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Nilai berhasil disimpan.']);

} catch (Exception $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan nilai: ' . $e->getMessage()]);
    error_log("Error saving grades: " . $e->getMessage());
}

$conn = null;
ob_end_flush();