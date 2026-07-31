<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Izinkan akses dari semua domain (untuk pengembangan)
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include '../middleware.php';
include '../db_connection.php';
require_api_auth('student');

$student_id = $_SESSION['user_id'];

// Query untuk mengambil semua tugas yang terkait dengan mata kuliah yang diikuti oleh student_id ini.
$sql = "SELECT 
            a.assignment_id, 
            a.course_id,
            a.title, 
            a.description, 
            a.due_date, 
            a.max_grade, 
            a.created_at,
            a.file_path AS assignment_file_path, -- Tambahkan file_path untuk tugas
            a.file_type AS assignment_file_type, -- Tambahkan file_type untuk tugas
            c.course_name
        FROM assignments a
        JOIN courses c ON a.course_id = c.course_id
        JOIN course_enrollments ce ON c.course_id = ce.course_id
        WHERE ce.student_id = ?
        ORDER BY a.due_date ASC";

$stmt = $conn->prepare($sql);
$stmt->execute([$student_id]);

$assignments = [];
while ($row = $stmt->fetch()) {
    $assignments[] = $row;
}

echo json_encode($assignments);

$conn = null;
?>