<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Grade {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getGradedCoursesForStudent(int $studentId): array {
        $sql = "
            SELECT DISTINCT c.course_id, c.course_name, c.credits 
            FROM grades g
            JOIN courses c ON g.course_id = c.course_id
            WHERE g.student_id = ?
            ORDER BY c.course_name ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function getGradesForStudent(int $studentId): array {
        $sql = "
            SELECT g.grade_value, g.grade_type, g.course_id, g.graded_at
            FROM grades g
            WHERE g.student_id = ? AND g.grade_type IN ('Assignment', 'UTS', 'UAS', 'Partisipasi')
            ORDER BY g.graded_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function getGradesByCourseAndStudent(int $courseId, int $studentId): array {
        $sql = "SELECT * FROM grades WHERE course_id = ? AND student_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId, $studentId]);
        return $stmt->fetchAll();
    }

    public function saveGradeItem(int $studentId, int $courseId, ?int $itemId, string $gradeType, float $gradeValue, ?string $feedback, int $gradedBy): bool {
        $gradeLetter = getGradeLetterPHP($gradeValue);
        $gradePoints = getGradePointsPHP($gradeLetter);

        $sql = "
            INSERT INTO grades (student_id, course_id, item_id, grade_type, grade_value, grade_letter, grade_points, feedback, graded_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) 
            ON CONFLICT (student_id, course_id, item_id, grade_type) DO UPDATE SET
                grade_value = EXCLUDED.grade_value, 
                grade_letter = EXCLUDED.grade_letter,
                grade_points = EXCLUDED.grade_points,
                feedback = EXCLUDED.feedback, 
                graded_by = EXCLUDED.graded_by,
                graded_at = NOW()
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$studentId, $courseId, $itemId, $gradeType, $gradeValue, $gradeLetter, $gradePoints, $feedback, $gradedBy]);
    }
}
