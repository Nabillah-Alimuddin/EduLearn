<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include '../middleware.php';
include '../db_connection.php';
require_api_auth();

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if ($course_id === 0) {
    echo json_encode(["error" => "Course ID not provided or invalid."]);
    $conn = null;
    exit();
}

$sql = "SELECT assignment_id, title, description, due_date, max_grade, created_at 
        FROM assignments 
        WHERE course_id = ? 
        ORDER BY due_date ASC";
$stmt = $conn->prepare($sql);
$stmt->execute([$course_id]);

$assignments = [];
while ($row = $stmt->fetch()) {
    $assignments[] = $row;
}

echo json_encode($assignments);

$conn = null;
?>