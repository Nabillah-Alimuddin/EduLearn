<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Course {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getEnrolledCoursesForStudent(int $studentId): array {
        $sql = "
            SELECT ce.course_id, c.course_name, c.course_code, c.credits, u.full_name AS lecturer_name
            FROM course_enrollments ce
            JOIN courses c ON ce.course_id = c.course_id
            LEFT JOIN users u ON c.lecturer_id = u.user_id
            WHERE ce.student_id = ?
            ORDER BY c.course_name ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function getCoursesByLecturer(int $lecturerId): array {
        $sql = "SELECT course_id, course_name, course_code, credits, lecturer_id FROM courses WHERE lecturer_id = ? ORDER BY course_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lecturerId]);
        return $stmt->fetchAll();
    }

    public function getCourseDetails(int $courseId): ?array {
        $sql = "
            SELECT c.course_id, c.course_name, c.course_code, c.credits, u.full_name AS lecturer_name, u.gelar
            FROM courses c
            LEFT JOIN users u ON c.lecturer_id = u.user_id
            WHERE c.course_id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getStudentsEnrolled(int $courseId): array {
        $sql = "
            SELECT u.user_id, u.full_name, u.nim, u.study_program
            FROM users u
            JOIN course_enrollments ce ON u.user_id = ce.student_id
            WHERE ce.course_id = ? AND u.role = 'student'
            ORDER BY u.full_name ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function countActiveCoursesForLecturer(int $lecturerId): int {
        $stmt = $this->db->prepare("SELECT COUNT(course_id) FROM courses WHERE lecturer_id = ?");
        $stmt->execute([$lecturerId]);
        return (int)$stmt->fetchColumn();
    }

    public function countTotalStudentsForLecturer(int $lecturerId): int {
        $sql = "
            SELECT COUNT(DISTINCT ce.student_id) 
            FROM course_enrollments ce
            JOIN courses c ON ce.course_id = c.course_id
            WHERE c.lecturer_id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lecturerId]);
        return (int)$stmt->fetchColumn();
    }
}
