<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

include '../middleware.php';
include '../db_connection.php';
require_api_auth('student');

$user_id = $_SESSION['user_id'];

$sql = "SELECT user_id, full_name, email, nim, gender, study_program, profile_picture_url 
        FROM users 
        WHERE user_id = ? AND role = 'student'";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);

if ($row = $stmt->fetch()) {
    echo json_encode($row);
} else {
    echo json_encode(["message" => "Student not found."]);
}

$conn = null;
?>