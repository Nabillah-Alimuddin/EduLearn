<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include '../middleware.php';
include '../db_connection.php';
require_api_auth('student');

$student_id = $_SESSION['user_id'];

$sql = "SELECT 
            g.grade_id, 
            g.student_id, 
            g.course_id, 
            g.item_id, 
            g.grade_value, 
            g.grade_letter, 
            g.grade_points, 
            g.grade_type, 
            g.graded_at,
            c.course_name,
            c.credits
        FROM grades g
        LEFT JOIN courses c ON g.course_id = c.course_id
        WHERE g.student_id = ?
        ORDER BY g.course_id, g.graded_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([$student_id]);

$grades_data = [];
while ($row = $stmt->fetch()) {
    $grades_data[] = $row;
}

echo json_encode($grades_data);

$conn = null;
?>