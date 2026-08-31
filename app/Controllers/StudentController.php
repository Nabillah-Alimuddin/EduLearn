<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;
use App\Helpers\StorageHelper;

class StudentController extends Controller {

    public function index(): void {
        $this->dashboard();
    }

    public function __construct() {
        Middleware::requireRole('student');
    }

    public function dashboard(): void {
        $studentId = Middleware::currentUserId();

        /** @var \App\Models\User $userModel */
        $userModel = $this->model('User');
        $studentInfo = $userModel->getStudentInfo($studentId);

        /** @var \App\Models\Course $courseModel */
        $courseModel = $this->model('Course');
        $enrolledCourses = $courseModel->getEnrolledCoursesForStudent($studentId);

        $this->view('student/dashboard', [
            'studentInfo' => $studentInfo,
            'enrolledCourses' => $enrolledCourses,
            'totalEnrolledCourses' => count($enrolledCourses)
        ]);
    }

    public function profile(): void {
        $studentId = Middleware::currentUserId();

        /** @var \App\Models\User $userModel */
        $userModel = $this->model('User');
        $profileData = $userModel->findById($studentId);

        $this->view('student/profile', [
            'profileData' => $profileData
        ]);
    }

    public function updateProfile(): void {
        $studentId = Middleware::currentUserId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Middleware::jsonError("Invalid request method.");
        }

        /** @var \App\Models\User $userModel */
        $userModel = $this->model('User');

        $data = [
            'full_name'       => $_POST['full_name'] ?? null,
            'nim'             => $_POST['nim'] ?? null,
            'nik'             => $_POST['nik'] ?? null,
            'gender'          => $_POST['gender'] ?? null,
            'study_program'   => $_POST['study_program'] ?? null,
            'religion'        => $_POST['religion'] ?? null,
            'nationality'     => $_POST['nationality'] ?? null,
            'place_of_birth'  => $_POST['place_of_birth'] ?? null,
            'date_of_birth'   => $_POST['date_of_birth'] ?? null,
            'phone_number'    => $_POST['phone_number'] ?? null,
            'previous_school' => $_POST['previous_school'] ?? null,
            'nisn'            => $_POST['nisn'] ?? null,
            'school_city'     => $_POST['school_city'] ?? null,
        ];

        $success = $userModel->updateStudentProfile($studentId, $data);

        // Handle Profile Picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $filePath = StorageHelper::upload($_FILES['profile_picture'], 'profiles');
            if ($filePath) {
                $userModel->updateProfilePicture($studentId, $filePath);
            }
        }

        if (Middleware::isApiRequest()) {
            if ($success) {
                Middleware::jsonSuccess('Profil berhasil diperbarui.');
            } else {
                Middleware::jsonError('Gagal memperbarui profil.');
            }
        } else {
            echo "<script>alert('Profil berhasil diperbarui!'); window.location.href='index.php?url=student/profile';</script>";
            exit();
        }
    }

    public function changePassword(): void {
        $studentId = Middleware::currentUserId();

        $input = json_decode(file_get_contents('php://input'), true);
        $oldPassword = $input['old_password'] ?? null;
        $newPassword = $input['new_password'] ?? null;

        if (!$oldPassword || !$newPassword) {
            Middleware::jsonError("Data tidak lengkap.");
        }

        /** @var \App\Models\User $userModel */
        $userModel = $this->model('User');
        $user = $userModel->findById($studentId);

        if (!$user || !password_verify($oldPassword, $user['password_hash'])) {
            Middleware::jsonError("Password lama tidak sesuai.");
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        if ($userModel->updatePassword($studentId, $newHash)) {
            Middleware::jsonSuccess('Password berhasil diubah.');
        } else {
            Middleware::jsonError('Gagal mengubah password.');
        }
    }

    public function kelas(): void {
        $studentId = Middleware::currentUserId();
        $courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;

        if (!$courseId) {
            $this->redirect('index.php?url=student/dashboard');
        }

        /** @var \App\Models\Course $courseModel */
        $courseModel = $this->model('Course');
        $courseDetails = $courseModel->getCourseDetails($courseId);

        /** @var \App\Models\Assignment $assignmentModel */
        $assignmentModel = $this->model('Assignment');
        $materialsData = $assignmentModel->getByCourse($courseId);

        $this->view('student/kelas', [
            'courseDetails' => $courseDetails,
            'materialsData' => $materialsData,
            'courseId'      => $courseId
        ]);
    }

    public function jadwal(): void {
        $studentId = Middleware::currentUserId();

        /** @var \App\Models\Schedule $scheduleModel */
        $scheduleModel = $this->model('Schedule');
        $schedules = $scheduleModel->getForStudent($studentId);

        $this->view('student/jadwal', [
            'schedules' => $schedules
        ]);
    }

    public function nilai(): void {
        $studentId = Middleware::currentUserId();

        /** @var \App\Models\User $userModel */
        $userModel = $this->model('User');
        $studentData = $userModel->getStudentInfo($studentId);

        /** @var \App\Models\Course $courseModel */
        $courseModel = $this->model('Course');
        $enrolledCourses = $courseModel->getEnrolledCoursesForStudent($studentId);

        /** @var \App\Models\Grade $gradeModel */
        $gradeModel = $this->model('Grade');
        $allGradesRaw = $gradeModel->getGradesForStudent($studentId);

        $gradedCourses = [];
        foreach ($enrolledCourses as $row) {
            $gradedCourses[$row['course_id']] = [
                'course_id'      => $row['course_id'],
                'course_name'    => $row['course_name'],
                'course_code'    => $row['course_code'],
                'credits'        => $row['credits'] ?? 3,
                'lecturer_name'  => $row['lecturer_name'] ?? '-',
                'grades_by_type' => [],
                'final_grade'    => '-',
                'grade_letter'   => '-',
                'grade_points'   => 0
            ];
        }

        foreach ($allGradesRaw as $grade) {
            $courseId = $grade['course_id'];
            $gradeType = $grade['grade_type'];
            if (isset($gradedCourses[$courseId])) {
                $gradedCourses[$courseId]['grades_by_type'][$gradeType] = $grade['grade_value'];
            }
        }

        // Calculate final grade for each course
        $totalSks = 0;
        $totalPoin = 0;

        foreach ($gradedCourses as $cId => &$cData) {
            $t = $cData['grades_by_type']['Assignment'] ?? null;
            $u = $cData['grades_by_type']['UTS'] ?? null;
            $ua = $cData['grades_by_type']['UAS'] ?? null;
            $p = $cData['grades_by_type']['Partisipasi'] ?? null;

            // If at least one component grade exists, calculate weighted score
            if ($t !== null || $u !== null || $ua !== null || $p !== null) {
                $scoreT = $t ?? 0;
                $scoreU = $u ?? 0;
                $scoreUA = $ua ?? 0;
                $scoreP = $p ?? 0;

                $finalScore = ($scoreT * 0.20) + ($scoreU * 0.30) + ($scoreUA * 0.40) + ($scoreP * 0.10);
                $cData['final_grade'] = round($finalScore, 2);

                $calc = calculate_grade_letter_and_points($finalScore);
                $cData['grade_letter'] = $calc['grade_letter'];
                $cData['grade_points'] = $calc['grade_points'];

                $sks = (int)$cData['credits'];
                $totalSks += $sks;
                $totalPoin += ($calc['grade_points'] * $sks);
            }
        }

        $ipk = ($totalSks > 0) ? round($totalPoin / $totalSks, 2) : 0.00;

        $this->view('student/nilai', [
            'studentData'   => $studentData,
            'gradedCourses' => $gradedCourses,
            'totalSks'      => $totalSks,
            'ipk'           => $ipk
        ]);
    }


    public function deadline(): void {
        $studentId = Middleware::currentUserId();

        /** @var \App\Models\Assignment $assignmentModel */
        $assignmentModel = $this->model('Assignment');
        $deadlines = $assignmentModel->getDeadlinesForStudent($studentId);

        $this->view('student/deadline', [
            'deadlines' => $deadlines
        ]);
    }

    public function submitAssignment(): void {
        $studentId = Middleware::currentUserId();

        $action = $_GET['action'] ?? null;

        /** @var \App\Models\Submission $submissionModel */
        $submissionModel = $this->model('Submission');

        if ($action === 'delete') {
            $assignmentId = isset($_GET['assignment_id']) ? (int)$_GET['assignment_id'] : 0;
            if ($assignmentId === 0) {
                Middleware::jsonError("Assignment ID invalid.");
            }

            $filePath = $submissionModel->delete($assignmentId, $studentId);
            if ($filePath && file_exists(__DIR__ . '/../../' . $filePath)) {
                @unlink(__DIR__ . '/../../' . $filePath);
            }

            Middleware::jsonSuccess("Submission successfully deleted.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $assignmentId = (int)($_POST['assignment_id'] ?? 0);
            $submissionText = $_POST['submission_text'] ?? null;

            if ($assignmentId === 0) {
                Middleware::jsonError("Assignment ID invalid.");
            }

            $filePath = null;
            if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
                $filePath = StorageHelper::upload($_FILES['submission_file'], 'submissions');
            }

            if ($submissionModel->submit($assignmentId, $studentId, $filePath, $submissionText)) {
                Middleware::jsonSuccess("Tugas berhasil dikumpulkan.");
            } else {
                Middleware::jsonError("Gagal menyimpan pengumpulan tugas.");
            }
        }
    }

    public function pengumuman(): void {
        $studentId = Middleware::currentUserId();

        /** @var \App\Models\Announcement $announcementModel */
        $announcementModel = $this->model('Announcement');
        $announcements = $announcementModel->getForStudent($studentId);

        $this->view('student/pengumuman', [
            'announcements' => $announcements
        ]);
    }
}
