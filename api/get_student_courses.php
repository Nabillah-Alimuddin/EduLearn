<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include '../middleware.php';
include '../db_connection.php';
require_api_auth('student');

$user_id = $_SESSION['user_id'];

// Query untuk mengambil mata kuliah yang diikuti oleh user_id ini
$sql = "SELECT 
            c.course_id, 
            c.course_name, 
            c.course_code 
        FROM courses c
        JOIN course_enrollments ce ON c.course_id = ce.course_id
        WHERE ce.student_id = ?
        ORDER BY c.course_name ASC";

$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);

$courses = [];
while ($row = $stmt->fetch()) {
    $courses[] = $row;
}

echo json_encode($courses);

$conn = null;
?>