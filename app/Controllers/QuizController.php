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

        if (!$quiz) {
            $this->redirect('index.php?url=quiz/list');
        }

        $this->view('student/quiz-take', [
            'quiz'      => $quiz,
            'studentId' => $studentId
        ]);
    }

    public function apiQuestions(): void {
        Middleware::requireApiAuth('student');
        $quizId = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

        if ($quizId <= 0) {
            Middleware::jsonError("Quiz ID tidak valid.");
        }

        /** @var \App\Models\Quiz $quizModel */
        $quizModel = $this->model('Quiz');
        $quiz = $quizModel->getById($quizId);
        $rawQuestions = $quizModel->getQuestionsWithCorrectAnswer($quizId);

        $questionsMap = [];
        foreach ($rawQuestions as $q) {
            $qId = $q['question_id'];
            if (!isset($questionsMap[$qId])) {
                $questionsMap[$qId] = [
                    'id'                => $q['question_id'],
                    'quiz_id'           => $q['quiz_id'],
                    'question'          => $q['question_text'],
                    'formula'           => $q['question_formula'],
                    'question_type'     => $q['question_type'],
                    'explanation'       => $q['explanation'],
                    'correct_option_id' => $q['correct_option_id'],
                    'options'           => []
                ];
            }
            if (!empty($q['option_id'])) {
                $questionsMap[$qId]['options'][] = [
                    'option_id'   => $q['option_id'],
                    'option_text' => $q['option_text'],
                    'is_correct'  => (bool)$q['is_correct']
                ];
            }
        }

        Middleware::jsonResponse([
            'quiz'      => $quiz,
            'questions' => array_values($questionsMap)
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

    public function manage(): void {
        Middleware::requireRole('lecturer');
        $lecturerId = Middleware::currentUserId();

        /** @var \App\Models\Quiz $quizModel */
        $quizModel = $this->model('Quiz');
        $quizzes = $quizModel->getByLecturer($lecturerId);

        /** @var \App\Models\Course $courseModel */
        $courseModel = $this->model('Course');
        $courses = $courseModel->getCoursesByLecturer($lecturerId);

        $selectedQuizId = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : null;
        $questions = [];
        $rankings = [];

        if ($selectedQuizId) {
            $questions = $quizModel->getQuestions($selectedQuizId);
            $rankings = $quizModel->getRankings($selectedQuizId);
        }

        $this->view('lecturer/quiz-crud', [
            'quizzes'        => $quizzes,
            'courses'        => $courses,
            'selectedQuizId' => $selectedQuizId,
            'questions'      => $questions,
            'rankings'       => $rankings
        ]);
    }

    public function createQuiz(): void {
        Middleware::requireRole('lecturer');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $courseId = (int)$_POST['course_id'];
            $title = trim($_POST['title']);
            $description = trim($_POST['description'] ?? '');
            $duration = (int)$_POST['duration_minutes'];
            $totalQuestions = (int)($_POST['total_questions'] ?? 5);
            $passingScore = (float)($_POST['passing_score'] ?? 70.00);

            /** @var \App\Models\Quiz $quizModel */
            $quizModel = $this->model('Quiz');
            $quizId = $quizModel->createQuiz([
                'course_id'        => $courseId,
                'title'            => $title,
                'description'      => $description,
                'duration_minutes' => $duration,
                'total_questions'  => $totalQuestions,
                'passing_score'    => $passingScore,
                'start_date'       => date('Y-m-d H:i:s'),
                'end_date'         => date('Y-m-d H:i:s', strtotime('+30 days'))
            ]);

            $this->redirect("index.php?url=quiz/manage&quiz_id={$quizId}");
        }
    }

    public function addQuestion(): void {
        Middleware::requireRole('lecturer');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $quizId = (int)$_POST['quiz_id'];
            $questionText = trim($_POST['question_text']);
            $formula = trim($_POST['question_formula'] ?? '');
            $explanation = trim($_POST['explanation'] ?? '');
            $options = $_POST['options'] ?? [];
            $correctIndex = (int)($_POST['correct_option_index'] ?? 0);

            /** @var \App\Models\Quiz $quizModel */
            $quizModel = $this->model('Quiz');
            $qId = $quizModel->createQuestion($quizId, $questionText, $formula ?: null, $explanation ?: null);

            foreach ($options as $idx => $optText) {
                if (trim($optText) !== '') {
                    $isCorrect = ($idx === $correctIndex);
                    $quizModel->createOption($qId, trim($optText), $isCorrect);
                }
            }

            $this->redirect("index.php?url=quiz/manage&quiz_id={$quizId}");
        }
    }

    public function deleteQuestion(): void {
        Middleware::requireRole('lecturer');
        $questionId = isset($_GET['question_id']) ? (int)$_GET['question_id'] : 0;
        $quizId = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

        if ($questionId > 0) {
            /** @var \App\Models\Quiz $quizModel */
            $quizModel = $this->model('Quiz');
            $quizModel->deleteQuestion($questionId);
        }

        $this->redirect("index.php?url=quiz/manage&quiz_id={$quizId}");
    }

    public function deleteQuiz(): void {
        Middleware::requireRole('lecturer');
        $quizId = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

        if ($quizId > 0) {
            /** @var \App\Models\Quiz $quizModel */
            $quizModel = $this->model('Quiz');
            $quizModel->deleteQuizCascade($quizId);
        }

        $this->redirect("index.php?url=quiz/manage");
    }
}

