<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;
use App\Helpers\StorageHelper;

class ExamController extends Controller {

    public function studentExam(): void {
        Middleware::requireRole('student');
        $studentId = Middleware::currentUserId();

        /** @var \App\Models\Exam $examModel */
        $examModel = $this->model('Exam');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_type']) && $_POST['submit_type'] === 'file_submission') {
            $examId = (int)($_POST['exam_id'] ?? 0);
            $submissionText = $_POST['submission_text'] ?? null;

            $filePath = null;
            if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
                $filePath = StorageHelper::upload($_FILES['submission_file'], 'mahasiswa/ujian');
            }

            $examModel->recordSubmission($examId, $studentId, $filePath, $submissionText);
            $this->redirect('index.php?url=exam/studentExam');
        }

        $exams = $examModel->getForStudent($studentId);

        $this->view('student/ujian', [
            'exams' => $exams
        ]);
    }

    public function lecturerExam(): void {
        Middleware::requireRole('lecturer');
        $lecturerId = Middleware::currentUserId();
        $examType = $_GET['exam_type'] ?? 'UTS';

        /** @var \App\Models\Exam $examModel */
        $examModel = $this->model('Exam');
        $exams = $examModel->getForLecturer($lecturerId, $examType);

        $this->view('lecturer/ujian', [
            'exams'    => $exams,
            'examType' => $examType
        ]);
    }

    public function api(): void {
        Middleware::requireApiAuth();
        $studentId = Middleware::currentUserId();
        $action = $_GET['action'] ?? $_POST['action'] ?? null;

        /** @var \App\Models\Exam $examModel */
        $examModel = $this->model('Exam');

        switch ($action) {
            case 'get_all_exams':
                $exams = $examModel->getForStudent($studentId);
                Middleware::jsonResponse(['exams' => $exams]);
                break;
            default:
                Middleware::jsonError("Invalid action.");
                break;
        }
    }
}
