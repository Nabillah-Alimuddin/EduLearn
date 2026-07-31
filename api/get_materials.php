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

$sql = "SELECT material_id, title, description, file_path, file_type, uploaded_at 
        FROM materials 
        WHERE course_id = ? 
        ORDER BY uploaded_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$course_id]);

$materials = [];
while ($row = $stmt->fetch()) {
    $materials[] = $row;
}

echo json_encode($materials);

$conn = null;
?>