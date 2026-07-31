<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include '../middleware.php';
include '../db_connection.php';
require_api_auth('student');

// Ambil parameter 'day' dari request GET
$day = isset($_GET['day']) ? $_GET['day'] : '';
$student_id = $_SESSION['user_id'];

if (empty($day)) {
    echo json_encode(["error" => "Day parameter not provided or invalid."]);
    $conn = null;
    exit();
}

// Query untuk mengambil jadwal kuliah mahasiswa tertentu pada hari tertentu
$sql = "SELECT 
            s.schedule_id, 
            s.day_of_week, 
            s.start_time, 
            s.end_time, 
            s.room, 
            s.class_type,
            c.course_name,
            u.full_name AS lecturer_full_name,
            u.gelar AS lecturer_gelar
        FROM schedules s
        JOIN courses c ON s.course_id = c.course_id
        JOIN users u ON c.lecturer_id = u.user_id
        JOIN course_enrollments ce ON c.course_id = ce.course_id
        WHERE ce.student_id = ? AND s.day_of_week = ?
        ORDER BY s.start_time ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Failed to prepare statement."]);
    $conn = null;
    exit();
}

$stmt->execute([$student_id, $day]);

$schedule_data = [];
while ($row = $stmt->fetch()) {
    $schedule_data[] = $row;
}

echo json_encode($schedule_data);

$conn = null;
?>