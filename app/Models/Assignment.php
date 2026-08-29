<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Assignment {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getByCourse(int $courseId): array {
        $sql = "SELECT assignment_id, title, description, due_date, max_grade, created_at, file_path, file_type FROM assignments WHERE course_id = ? ORDER BY due_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function getPendingCountForLecturer(int $lecturerId): int {
        $sql = "
            SELECT COUNT(a.assignment_id) 
            FROM assignments a 
            JOIN courses c ON a.course_id = c.course_id 
            WHERE a.due_date > NOW() AND c.lecturer_id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lecturerId]);
        return (int)$stmt->fetchColumn();
    }

    public function create(array $data): bool {
        $sql = "INSERT INTO assignments (course_id, title, description, due_date, max_grade, file_path, file_type) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['course_id'], $data['title'], $data['description'] ?? null,
            $data['due_date'], $data['max_grade'] ?? 100.00, $data['file_path'] ?? null, $data['file_type'] ?? null
        ]);
    }

    public function getDeadlinesForStudent(int $studentId): array {
        $sql = "
            SELECT 
                a.assignment_id, a.title, a.description, a.due_date, a.max_grade,
                c.course_name, c.course_code,
                s.submission_id, s.submitted_at, s.grade, s.feedback
            FROM assignments a
            JOIN courses c ON a.course_id = c.course_id
            JOIN course_enrollments ce ON c.course_id = ce.course_id
            LEFT JOIN submissions s ON a.assignment_id = s.assignment_id AND s.student_id = ?
            WHERE ce.student_id = ?
            ORDER BY a.due_date ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $studentId]);
        return $stmt->fetchAll();
    }
}
