<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include '../middleware.php';
include '../db_connection.php';
require_api_auth('student');

$assignment_id = isset($_GET['assignment_id']) ? intval($_GET['assignment_id']) : 0;
$student_id = $_SESSION['user_id'];

if ($assignment_id === 0) {
    echo json_encode(["error" => "Assignment ID not provided or invalid."]);
    $conn = null;
    exit();
}

// Cek apakah ada submission untuk tugas dan mahasiswa ini
$sql = "SELECT submission_id, submission_file_path, submitted_at 
        FROM submissions 
        WHERE assignment_id = ? AND student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$assignment_id, $student_id]);

if ($submission = $stmt->fetch()) {
    echo json_encode([
        "submitted" => true,
        "submission_id" => $submission['submission_id'],
        "submission_file_path" => $submission['submission_file_path'],
        "submitted_at" => $submission['submitted_at']
    ]);
} else {
    echo json_encode(["submitted" => false, "message" => "No submission found for this assignment and student.", "submission_file_path" => null]);
}

$conn = null;
?>