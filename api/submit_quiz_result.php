<?php
include '../middleware.php';
include '../db_connection.php';
require_api_auth('student');

$student_id = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);

$quiz_id = $input['quiz_id'] ?? null;
$score = $input['score'] ?? null;
$correct_answers_count = $input['correct_answers_count'] ?? null;
$total_questions_answered = $input['total_questions_answered'] ?? null;
$total_questions = $input['total_questions'] ?? null;

if ($quiz_id === null || $score === null) {
    json_error('Data input tidak valid.');
}

$response = ['success' => false, 'message' => ''];

$stmt_check = $conn->prepare("SELECT attempt_id FROM quiz_attempts WHERE quiz_id = ? AND student_id = ?");
if ($stmt_check) {
    $stmt_check->execute([$quiz_id, $student_id]);
    if ($stmt_check->fetch()) {
        json_error('Anda sudah mengerjakan kuis ini sebelumnya.');
    }
}

$stmt = $conn->prepare("INSERT INTO quiz_attempts (quiz_id, student_id, start_time, end_time, score, is_completed) VALUES (?, ?, NOW(), NOW(), ?, TRUE)");
if ($stmt) {
    if ($stmt->execute([$quiz_id, $student_id, $score])) {
        json_success('Quiz result saved successfully.');
    } else {
        json_error('Failed to save quiz attempt.');
    }
} else {
    json_error('Failed to prepare statement.');
}

$conn = null;
?>