<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;

class QuizController extends Controller {

    public function list(): void {
        Middleware::requireRole('student');
        $studentId = Middleware::currentUserId();

        /** @var \App\Models\Quiz $quizModel */
        $quizModel = $this->model('Quiz');
        $availableQuizzes = $quizModel->getAvailableForStudent($studentId);

        $this->view('student/quiz-list', [
            'availableQuizzes' => $availableQuizzes
        ]);
    }

    public function take(): void {
        Middleware::requireRole('student');
        $studentId = Middleware::currentUserId();
        $quizId = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : null;

        if (!$quizId) {
            $this->redirect('index.php?url=quiz/list');
        }

        /** @var \App\Models\Quiz $quizModel */
        $quizModel = $this->model('Quiz');
        $quiz = $quizModel->getById($quizId);
        $questions = $quizModel->getQuestions($quizId);

        $this->view('student/quiz-take', [
            'quiz'      => $quiz,
            'questions' => $questions
        ]);
    }

    public function manage(): void {
        Middleware::requireRole('lecturer');
        $lecturerId = Middleware::currentUserId();

        /** @var \App\Models\Quiz $quizModel */
        $quizModel = $this->model('Quiz');
        $quizzes = $quizModel->getByLecturer($lecturerId);

        /** @var \App\Models\Course $courseModel */
        $courseModel = $this->model('Course');
        $courses = $courseModel->getCoursesByLecturer($lecturerId);

        $this->view('lecturer/quiz-crud', [
            'quizzes' => $quizzes,
            'courses' => $courses
        ]);
    }

    public function submitResult(): void {
        Middleware::requireRole('student');
        $studentId = Middleware::currentUserId();

        $input = json_decode(file_get_contents('php://input'), true);
        $quizId = $input['quiz_id'] ?? null;
        $score = $input['score'] ?? 0;

        if (!$quizId) {
            Middleware::jsonError("Quiz ID required.");
        }

        /** @var \App\Models\Quiz $quizModel */
        $quizModel = $this->model('Quiz');
        $attemptId = $quizModel->saveAttempt((int)$quizId, $studentId, (float)$score);

        if ($attemptId) {
            Middleware::jsonSuccess("Hasil kuis berhasil disimpan.", ['attempt_id' => $attemptId]);
        } else {
            Middleware::jsonError("Gagal menyimpan hasil kuis.");
        }
    }
}
