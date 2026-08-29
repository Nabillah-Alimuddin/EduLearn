<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Exam {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getForStudent(int $studentId): array {
        $sql = "
            SELECT
                e.exam_id, e.title, e.exam_type, e.exam_date, e.start_time, e.end_time,
                e.room, e.is_online, e.online_link, e.duration_minutes, e.quiz_id,
                c.course_name, q.total_questions,
                (SELECT COUNT(*) FROM exam_attempts ea WHERE ea.exam_id = e.exam_id AND ea.student_id = ? AND ea.is_completed = TRUE) AS student_completed_exam
            FROM exams e
            JOIN courses c ON e.course_id = c.course_id
            LEFT JOIN quizzes q ON e.quiz_id = q.quiz_id
            JOIN course_enrollments ce ON c.course_id = ce.course_id
            WHERE ce.student_id = ?
            ORDER BY e.exam_date ASC, e.start_time ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $studentId]);
        return $stmt->fetchAll();
    }

    public function getForLecturer(int $lecturerId, string $examType = 'UTS'): array {
        $sql = "
            SELECT
                e.exam_id, e.title, e.exam_type, e.exam_date, e.start_time, e.end_time,
                e.room, e.is_online, e.online_link, c.course_name, c.course_code
            FROM exams e
            JOIN courses c ON e.course_id = c.course_id
            WHERE c.lecturer_id = ? AND e.exam_type = ?
            ORDER BY e.exam_date ASC, e.start_time ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lecturerId, $examType]);
        return $stmt->fetchAll();
    }

    public function recordSubmission(int $examId, int $studentId, ?string $filePath, ?string $submissionText): bool {
        $sqlInsert = "INSERT INTO submissions (assignment_id, student_id, submission_file_path, submission_text) VALUES (?, ?, ?, ?)";
        $stmtInsert = $this->db->prepare($sqlInsert);
        $stmtInsert->execute([$examId, $studentId, $filePath, $submissionText]);

        $sqlUpdate = "UPDATE exam_attempts SET is_completed = TRUE WHERE exam_id = ? AND student_id = ?";
        $stmtUpdate = $this->db->prepare($sqlUpdate);
        return $stmtUpdate->execute([$examId, $studentId]);
    }
}
