<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;

class AnnouncementController extends Controller {

    public function student(): void {
        Middleware::requireRole('student');
        $studentId = Middleware::currentUserId();

        /** @var \App\Models\Announcement $announcementModel */
        $announcementModel = $this->model('Announcement');
        $announcements = $announcementModel->getForStudent($studentId);

        $this->view('student/pengumuman', [
            'announcements' => $announcements
        ]);
    }

    public function lecturer(): void {
        Middleware::requireRole('lecturer');
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
            $this->redirect('index.php?url=announcement/lecturer');
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
}
