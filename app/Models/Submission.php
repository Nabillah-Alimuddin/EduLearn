<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Submission {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getSubmission(int $assignmentId, int $studentId): ?array {
        $sql = "SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$assignmentId, $studentId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function submit(int $assignmentId, int $studentId, ?string $filePath, ?string $submissionText): bool {
        $existing = $this->getSubmission($assignmentId, $studentId);
        if ($existing) {
            $sql = "UPDATE submissions SET submission_file_path = COALESCE(?, submission_file_path), submission_text = COALESCE(?, submission_text), submitted_at = NOW() WHERE assignment_id = ? AND student_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$filePath, $submissionText, $assignmentId, $studentId]);
        } else {
            $sql = "INSERT INTO submissions (assignment_id, student_id, submission_file_path, submission_text, submitted_at) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$assignmentId, $studentId, $filePath, $submissionText]);
        }
    }

    public function delete(int $assignmentId, int $studentId): ?string {
        $existing = $this->getSubmission($assignmentId, $studentId);
        if (!$existing) {
            return null;
        }
        $filePath = $existing['submission_file_path'];

        $sql = "DELETE FROM submissions WHERE assignment_id = ? AND student_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$assignmentId, $studentId]);

        return $filePath;
    }

    public function getSubmissionsForAssignment(int $assignmentId): array {
        $sql = "
            SELECT s.*, u.full_name, u.nim
            FROM submissions s
            JOIN users u ON s.student_id = u.user_id
            WHERE s.assignment_id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$assignmentId]);
        return $stmt->fetchAll();
    }

    public function getSubmissionsForCourse(int $courseId): array {
        $sql = "
            SELECT s.*, a.title AS assignment_title, a.due_date, u.full_name, u.nim
            FROM submissions s
            JOIN assignments a ON s.assignment_id = a.assignment_id
            JOIN users u ON s.student_id = u.user_id
            WHERE a.course_id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function updateGradeAndFeedback(int $assignmentId, int $studentId, float $grade, ?string $feedback): bool {
        $sql = "UPDATE submissions SET grade = ?, feedback = ? WHERE assignment_id = ? AND student_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$grade, $feedback, $assignmentId, $studentId]);
    }
}
