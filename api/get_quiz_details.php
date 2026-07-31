<?php
include '../middleware.php';
include '../db_connection.php';
require_api_auth();

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
$quiz_details = ['error' => 'Quiz not found'];

if ($quiz_id > 0) {
    $stmt = $conn->prepare("SELECT quiz_id, title, description, duration_minutes, total_questions, passing_score FROM quizzes WHERE quiz_id = ?");
    if ($stmt) {
        $stmt->execute([$quiz_id]);
        if ($row = $stmt->fetch()) {
            $quiz_details = $row;
        }
    }
}
$conn = null;
echo json_encode($quiz_details);
?>