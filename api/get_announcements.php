<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include '../middleware.php';
include '../db_connection.php';
require_api_auth();

// Query untuk mengambil pengumuman.
$sql = "SELECT 
            a.announcement_id, 
            a.title, 
            a.content, 
            a.published_at, 
            a.course_id,
            c.course_name,
            u.full_name AS lecturer_full_name
        FROM announcements a
        LEFT JOIN courses c ON a.course_id = c.course_id
        LEFT JOIN lecturers l ON a.lecturer_id = l.lecturer_id
        LEFT JOIN users u ON l.user_id = u.user_id
        ORDER BY a.published_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();

$announcements = [];
while ($row = $stmt->fetch()) {
    $announcements[] = $row;
}

echo json_encode($announcements);

$conn = null;
?>