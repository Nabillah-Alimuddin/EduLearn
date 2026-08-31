<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Quiz {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAvailableForStudent(int $studentId): array {
        $sql = "
            SELECT
                q.quiz_id, q.title, q.description, q.duration_minutes,
                q.total_questions, q.passing_score, q.start_date, q.end_date,
                c.course_name
            FROM quizzes q
            JOIN courses c ON q.course_id = c.course_id
            JOIN course_enrollments ce ON q.course_id = ce.course_id
            WHERE ce.student_id = ?
            ORDER BY q.end_date ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function getById(int $quizId): ?array {
        $sql = "
            SELECT q.*, c.course_name 
            FROM quizzes q 
            JOIN courses c ON q.course_id = c.course_id 
            WHERE q.quiz_id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quizId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getQuestions(int $quizId): array {
        $sql = "SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY question_id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quizId]);
        $questions = $stmt->fetchAll();

        foreach ($questions as &$q) {
            $q['options'] = $this->getOptions($q['question_id']);
        }
        return $questions;
    }

    public function getOptions(int $questionId): array {
        $sql = "SELECT option_id, option_text, is_correct FROM question_options WHERE question_id = ? ORDER BY option_id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$questionId]);
        return $stmt->fetchAll();
    }

    public function getQuestionsWithCorrectAnswer(int $quizId): array {
        $sql = "
            SELECT
                qq.question_id, qq.quiz_id, qq.question_text, qq.question_formula,
                qq.question_type, qq.explanation,
                qo.option_id, qo.option_text, qo.is_correct,
                (SELECT option_id FROM question_options WHERE question_id = qq.question_id AND is_correct = TRUE LIMIT 1) AS correct_option_id
            FROM quiz_questions qq
            LEFT JOIN question_options qo ON qq.question_id = qo.question_id
            WHERE qq.quiz_id = ?
            ORDER BY qq.question_id, qo.option_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quizId]);
        return $stmt->fetchAll();
    }

    public function getActiveCountForLecturer(int $lecturerId): int {
        $sql = "
            SELECT COUNT(q.quiz_id) 
            FROM quizzes q 
            JOIN courses c ON q.course_id = c.course_id 
            WHERE (q.end_date IS NULL OR q.end_date > NOW()) AND c.lecturer_id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lecturerId]);
        return (int)$stmt->fetchColumn();
    }

    public function getByLecturer(int $lecturerId): array {
        $sql = "
            SELECT q.*, c.course_name 
            FROM quizzes q 
            JOIN courses c ON q.course_id = c.course_id 
            WHERE c.lecturer_id = ? 
            ORDER BY q.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lecturerId]);
        return $stmt->fetchAll();
    }

    public function createQuiz(array $data): int {
        $sql = "INSERT INTO quizzes (course_id, title, description, duration_minutes, total_questions, passing_score, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?) RETURNING quiz_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['course_id'], $data['title'], $data['description'] ?? null,
            $data['duration_minutes'], $data['total_questions'], $data['passing_score'] ?? 70.00,
            $data['start_date'] ?? null, $data['end_date'] ?? null
        ]);
        return (int)$stmt->fetchColumn();
    }

    public function updateQuiz(int $quizId, array $data): bool {
        $sql = "UPDATE quizzes SET title = ?, description = ?, duration_minutes = ?, total_questions = ?, passing_score = ?, start_date = ?, end_date = ? WHERE quiz_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['title'], $data['description'] ?? null, $data['duration_minutes'],
            $data['total_questions'], $data['passing_score'] ?? 70.00,
            $data['start_date'] ?? null, $data['end_date'] ?? null, $quizId
        ]);
    }

    public function deleteQuiz(int $quizId): bool {
        $stmt = $this->db->prepare("DELETE FROM quizzes WHERE quiz_id = ?");
        return $stmt->execute([$quizId]);
    }

    public function saveAttempt(int $quizId, int $studentId, float $score, bool $isCompleted = true): int {
        $sql = "INSERT INTO quiz_attempts (quiz_id, student_id, start_time, end_time, score, is_completed) VALUES (?, ?, NOW(), NOW(), ?, ?) RETURNING attempt_id";
        $stmt = $this->db->prepare($sql);
        $boolVal = $isCompleted ? 'true' : 'false';
        $stmt->execute([$quizId, $studentId, $score, $boolVal]);
        return (int)$stmt->fetchColumn();
    }

    public function hasStudentAttempted(int $quizId, int $studentId): bool {
        $sql = "SELECT attempt_id FROM quiz_attempts WHERE quiz_id = ? AND student_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quizId, $studentId]);
        return (bool)$stmt->fetch();
    }

    public function getStudentAttempt(int $quizId, int $studentId): ?array {
        $sql = "SELECT * FROM quiz_attempts WHERE quiz_id = ? AND student_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quizId, $studentId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function createQuestion(int $quizId, string $questionText, ?string $formula, ?string $explanation, string $questionType = 'multiple_choice'): int {
        $sql = "INSERT INTO quiz_questions (quiz_id, question_text, question_formula, explanation, question_type) VALUES (?, ?, ?, ?, ?) RETURNING question_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quizId, $questionText, $formula, $explanation, $questionType]);
        return (int)$stmt->fetchColumn();
    }

    public function createOption(int $questionId, string $optionText, bool $isCorrect): int {
        $sql = "INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, ?, ?) RETURNING option_id";
        $stmt = $this->db->prepare($sql);
        $boolVal = $isCorrect ? 'true' : 'false';
        $stmt->execute([$questionId, $optionText, $boolVal]);
        return (int)$stmt->fetchColumn();
    }

    public function deleteQuestion(int $questionId): bool {
        $stmt1 = $this->db->prepare("DELETE FROM question_options WHERE question_id = ?");
        $stmt1->execute([$questionId]);
        $stmt2 = $this->db->prepare("DELETE FROM quiz_questions WHERE question_id = ?");
        return $stmt2->execute([$questionId]);
    }

    public function deleteQuizCascade(int $quizId): bool {
        $this->db->prepare("DELETE FROM question_options WHERE question_id IN (SELECT question_id FROM quiz_questions WHERE quiz_id = ?)")->execute([$quizId]);
        $this->db->prepare("DELETE FROM quiz_questions WHERE quiz_id = ?")->execute([$quizId]);
        $this->db->prepare("DELETE FROM quiz_attempts WHERE quiz_id = ?")->execute([$quizId]);
        $stmt = $this->db->prepare("DELETE FROM quizzes WHERE quiz_id = ?");
        return $stmt->execute([$quizId]);
    }

    public function getRankings(int $quizId): array {
        $sql = "
            SELECT qa.attempt_id, qa.student_id, qa.score, qa.start_time, qa.end_time,
                   u.full_name, u.nim
            FROM quiz_attempts qa
            JOIN users u ON qa.student_id = u.user_id
            WHERE qa.quiz_id = ?
            ORDER BY qa.score DESC, qa.end_time ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quizId]);
        return $stmt->fetchAll();
    }
}
