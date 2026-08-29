<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Schedule {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getForStudent(int $studentId): array {
        $sql = "
            SELECT 
                s.schedule_id, s.day_of_week, s.start_time, s.end_time, s.room, s.class_type,
                c.course_name, c.course_code, u.full_name AS lecturer_name, u.gelar
            FROM schedules s
            JOIN courses c ON s.course_id = c.course_id
            JOIN users u ON s.lecturer_id = u.user_id
            JOIN course_enrollments ce ON c.course_id = ce.course_id
            WHERE ce.student_id = ?
            ORDER BY 
                CASE s.day_of_week
                    WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3
                    WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 WHEN 'Minggu' THEN 7
                END, s.start_time ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function getForLecturer(int $lecturerId, ?int $courseId = null): array {
        $sql = "
            SELECT 
                s.schedule_id, s.day_of_week, s.start_time, s.end_time, s.room, s.class_type,
                c.course_name, c.course_code
            FROM schedules s
            JOIN courses c ON s.course_id = c.course_id
            WHERE s.lecturer_id = ?
        ";
        $params = [$lecturerId];
        if ($courseId) {
            $sql .= " AND s.course_id = ?";
            $params[] = $courseId;
        }
        $sql .= " ORDER BY s.start_time ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
