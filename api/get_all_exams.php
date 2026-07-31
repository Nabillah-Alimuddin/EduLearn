<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Izinkan akses dari semua domain (untuk pengembangan)
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include '../middleware.php';
include '../db_connection.php';
require_api_auth();

// Query untuk mengambil semua ujian.
// Join dengan tabel `courses` untuk mendapatkan nama mata kuliah.
$sql = "SELECT 
            e.exam_id, 
            e.course_id, 
            e.title, 
            e.exam_type, 
            e.exam_date, 
            e.start_time, 
            e.end_time, 
            e.room, 
            e.is_online, 
            e.online_link, 
            e.duration_minutes, 
            e.total_questions, 
            e.exam_status, 
            c.course_name,
            e.description
        FROM exams e
        LEFT JOIN courses c ON e.course_id = c.course_id
        ORDER BY e.exam_date ASC, e.start_time ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();

$exams_data = [];
while ($row = $stmt->fetch()) {
    $exams_data[] = $row;
}

echo json_encode($exams_data);

$conn = null;
?>