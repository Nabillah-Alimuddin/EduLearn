<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;

class LecturerController extends Controller {

    public function __construct() {
        Middleware::requireRole('lecturer');
    }

    public function dashboard(): void {
        $lecturerId = Middleware::currentUserId();

        /** @var \App\Models\User $userModel */
        $userModel = $this->model('User');
        $lecturerInfo = $userModel->getLecturerInfo($lecturerId);

        /** @var \App\Models\Course $courseModel */
        $courseModel = $this->model('Course');
        $kelasAktif = $courseModel->countActiveCoursesForLecturer($lecturerId);
        $totalMahasiswa = $courseModel->countTotalStudentsForLecturer($lecturerId);

        /** @var \App\Models\Assignment $assignmentModel */
        $assignmentModel = $this->model('Assignment');
        $tugasPending = $assignmentModel->getPendingCountForLecturer($lecturerId);

        /** @var \App\Models\Quiz $quizModel */
        $quizModel = $this->model('Quiz');
        $quizAktif = $quizModel->getActiveCountForLecturer($lecturerId);

        $this->view('lecturer/dashboard', [
            'lecturerInfo'   => $lecturerInfo,
            'kelasAktif'     => $kelasAktif,
            'totalMahasiswa' => $totalMahasiswa,
            'tugasPending'   => $tugasPending,
            'quizAktif'      => $quizAktif
        ]);
    }

    public function profile(): void {
        $lecturerId = Middleware::currentUserId();

        /** @var \App\Models\User $userModel */
        $userModel = $this->model('User');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ttlInput = $_POST['ttl'] ?? '';
            $ttlParts = explode(', ', $ttlInput, 2);
            $placeOfBirth = $ttlParts[0] ?? null;
            $dateOfBirthStr = $ttlParts[1] ?? null;
            $dateOfBirth = null;
            if ($dateOfBirthStr) {
                $dateObj = \DateTime::createFromFormat('d F Y', $dateOfBirthStr);
                if ($dateObj) {
                    $dateOfBirth = $dateObj->format('Y-m-d');
                }
            }

            $data = [
                'full_name'          => $_POST['nama'] ?? '',
                'gelar'              => $_POST['gelar'] ?? '',
                'nik'                => $_POST['nip'] ?? '',
                'study_program'      => $_POST['prodi'] ?? '',
                'jabatan_akademik'   => $_POST['jabatan'] ?? '',
                'bidang_keahlian'    => $_POST['bidang'] ?? '',
                'status_kepegawaian' => $_POST['status'] ?? '',
                'email'              => $_POST['email'] ?? '',
                'phone_number'       => $_POST['telepon'] ?? '',
                'ruang_kerja'        => $_POST['ruang'] ?? '',
                'jam_konsultasi'     => $_POST['jamKonsul'] ?? '',
                'place_of_birth'     => $placeOfBirth,
                'date_of_birth'      => $dateOfBirth
            ];

            $userModel->updateLecturerProfile($lecturerId, $data);

            echo "<script>alert('Profil berhasil diperbarui!'); window.location.href='index.php?url=lecturer/profile';</script>";
            exit();
        }

        $profileData = $userModel->findById($lecturerId);

        $this->view('lecturer/profile', [
            'profileData' => $profileData
        ]);
    }

    public function kelas(): void {
        $lecturerId = Middleware::currentUserId();

        /** @var \App\Models\Course $courseModel */
        $courseModel = $this->model('Course');
        $courses = $courseModel->getCoursesByLecturer($lecturerId);

        $this->view('lecturer/kelas', [
            'courses' => $courses
        ]);
    }

    public function jadwal(): void {
        $lecturerId = Middleware::currentUserId();
        $selectedCourseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;

        /** @var \App\Models\Course $courseModel */
        $courseModel = $this->model('Course');
        $courses = $courseModel->getCoursesByLecturer($lecturerId);

        /** @var \App\Models\Schedule $scheduleModel */
        $scheduleModel = $this->model('Schedule');
        $schedules = $scheduleModel->getForLecturer($lecturerId, $selectedCourseId);

        $this->view('lecturer/jadwal', [
            'courses'          => $courses,
            'schedules'        => $schedules,
            'selectedCourseId' => $selectedCourseId
        ]);
    }

    public function materitugas(): void {
        $lecturerId = Middleware::currentUserId();

        /** @var \App\Models\Course $courseModel */
        $courseModel = $this->model('Course');
        $courses = $courseModel->getCoursesByLecturer($lecturerId);

        $currentCourseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : ($courses[0]['course_id'] ?? null);

        /** @var \App\Models\Assignment $assignmentModel */
        $assignmentModel = $this->model('Assignment');
        $materialsAndAssignments = $currentCourseId ? $assignmentModel->getByCourse($currentCourseId) : [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_type'])) {
            $uploadType = $_POST['upload_type'];
            $courseId = (int)$_POST['course_id_upload'];

            if (isset($_FILES['uploaded_file']) && !empty($_FILES['uploaded_file']['name'][0])) {
                $targetDir = "uploads/";
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                foreach ($_FILES['uploaded_file']['name'] as $key => $uploadedFileName) {
                    if ($_FILES['uploaded_file']['error'][$key] === UPLOAD_ERR_OK) {
                        $uniqueFileName = uniqid() . '_' . basename($uploadedFileName);
                        $targetFilePath = $targetDir . $uniqueFileName;
                        move_uploaded_file($_FILES['uploaded_file']['tmp_name'][$key], $targetFilePath);

                        $ext = strtolower(pathinfo($uploadedFileName, PATHINFO_EXTENSION));

                        $assignmentModel->create([
                            'course_id'   => $courseId,
                            'title'       => pathinfo($uploadedFileName, PATHINFO_FILENAME),
                            'description' => 'Materi/Tugas baru diunggah.',
                            'due_date'    => date('Y-m-d H:i:s', strtotime('+7 days')),
                            'file_path'   => $targetFilePath,
                            'file_type'   => $ext
                        ]);
                    }
                }
            }

            $this->redirect("index.php?url=lecturer/materitugas&course_id={$courseId}");
        }

        $this->view('lecturer/materitugas', [
            'courses'                 => $courses,
            'currentCourseId'         => $currentCourseId,
            'materialsAndAssignments' => $materialsAndAssignments
        ]);
    }

    public function pengumuman(): void {
        $lecturerId = Middleware::currentUserId();

        /** @var \App\Models\Announcement $announcementModel */
        $announcementModel = $this->model('Announcement');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $courseId = !empty($_POST['subject']) ? (int)$_POST['subject'] : null;
            $announcementId = $_POST['announcementId'] ?? null;

            if (!empty($title) && !empty($content)) {
                if (!empty($announcementId)) {
                    $announcementModel->update((int)$announcementId, $lecturerId, [
                        'title'     => $title,
                        'content'   => $content,
                        'course_id' => $courseId
                    ]);
                } else {
                    $announcementModel->create([
                        'title'       => $title,
                        'content'     => $content,
                        'lecturer_id' => $lecturerId,
                        'course_id'   => $courseId
                    ]);
                }
            }
            $this->redirect('index.php?url=lecturer/pengumuman');
        }

        $announcements = $announcementModel->getForLecturer($lecturerId);

        /** @var \App\Models\Course $courseModel */
        $courseModel = $this->model('Course');
        $courses = $courseModel->getCoursesByLecturer($lecturerId);

        $this->view('lecturer/pengumuman', [
            'announcements' => $announcements,
            'courses'       => $courses
        ]);
    }

    public function progressTugas(): void {
        $lecturerId = Middleware::currentUserId();

        /** @var \App\Models\Course $courseModel */
        $courseModel = $this->model('Course');
        $courses = $courseModel->getCoursesByLecturer($lecturerId);

        $this->view('lecturer/progress-tugas', [
            'courses' => $courses
        ]);
    }

    public function laporan(): void {
        $lecturerId = Middleware::currentUserId();

        /** @var \App\Models\Course $courseModel */
        $courseModel = $this->model('Course');
        $courses = $courseModel->getCoursesByLecturer($lecturerId);

        $selectedCourseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : ($courses[0]['course_id'] ?? null);

        $this->view('lecturer/laporan', [
            'courses'          => $courses,
            'selectedCourseId' => $selectedCourseId
        ]);
    }

    public function inputNilai(): void {
        $lecturerId = Middleware::currentUserId();

        /** @var \App\Models\Course $courseModel */
        $courseModel = $this->model('Course');
        $courses = $courseModel->getCoursesByLecturer($lecturerId);

        $selectedCourseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;

        /** @var \App\Models\Assignment $assignmentModel */
        $assignmentModel = $this->model('Assignment');
        $assignments = $selectedCourseId ? $assignmentModel->getByCourse($selectedCourseId) : [];

        $this->view('lecturer/input-nilai', [
            'courses'          => $courses,
            'assignments'      => $assignments,
            'selectedCourseId' => $selectedCourseId
        ]);
    }
}
