<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;

class GradeController extends Controller {

    public function __construct() {
        Middleware::requireApiAuth('lecturer');
    }

    public function save(): void {
        $lecturerId = Middleware::currentUserId();
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['course_id']) || !isset($data['grades'])) {
            Middleware::jsonError("Invalid data provided.");
        }

        $courseId = (int)$data['course_id'];
        $gradesToSave = $data['grades'];

        /** @var \App\Models\Grade $gradeModel */
        $gradeModel = $this->model('Grade');

        $gradeTypeMap = [
            'tugas' => 'Assignment',
            'uts'   => 'UTS',
            'uas'   => 'UAS',
            'partisipasi' => 'Partisipasi'
        ];

        foreach ($gradesToSave as $studentGrade) {
            $studentId = (int)$studentGrade['user_id'];
            foreach (['tugas', 'uts', 'uas', 'partisipasi'] as $typeKey) {
                if (isset($studentGrade[$typeKey])) {
                    $val = (float)$studentGrade[$typeKey];
                    $dbType = $gradeTypeMap[$typeKey];
                    $gradeModel->saveGradeItem($studentId, $courseId, null, $dbType, $val, null, $lecturerId);
                }
            }
        }

        Middleware::jsonSuccess("Nilai berhasil disimpan.");
    }

    public function saveLaporan(): void {
        $lecturerId = Middleware::currentUserId();
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        $courseId = $data['course_id'] ?? null;
        $gradesData = $data['grades'] ?? [];

        if (!$courseId || empty($gradesData)) {
            Middleware::jsonError("Missing course_id or grades data.");
        }

        /** @var \App\Models\Grade $gradeModel */
        $gradeModel = $this->model('Grade');

        $typeMap = [
            'tugas'       => 'Assignment',
            'uts'         => 'UTS',
            'uas'         => 'UAS',
            'partisipasi' => 'Partisipasi'
        ];

        foreach ($gradesData as $studentGrade) {
            $studentId = (int)($studentGrade['user_id'] ?? 0);
            foreach ($typeMap as $key => $typeStr) {
                if (isset($studentGrade[$key]) && is_numeric($studentGrade[$key])) {
                    $val = (float)$studentGrade[$key];
                    $gradeModel->saveGradeItem($studentId, (int)$courseId, null, $typeStr, $val, null, $lecturerId);
                }
            }
        }

        Middleware::jsonSuccess("Nilai komponen berhasil disimpan!");
    }

    public function saveCrud(): void {
        $lecturerId = Middleware::currentUserId();

        $courseId = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
        $assignmentId = isset($_POST['assignment_id']) ? (int)$_POST['assignment_id'] : 0;
        $gradesData = $_POST['grades'] ?? [];

        if ($courseId === 0 || empty($gradesData)) {
            Middleware::jsonError("Mata kuliah atau data nilai tidak valid.");
        }

        /** @var \App\Models\Grade $gradeModel */
        $gradeModel = $this->model('Grade');
        /** @var \App\Models\Submission $submissionModel */
        $submissionModel = $this->model('Submission');

        foreach ($gradesData as $studentId => $components) {
            $studentId = (int)$studentId;
            if (isset($components['Assignment']) && $components['Assignment'] !== '') {
                $val = (float)$components['Assignment'];
                $fb = $components['feedback'] ?? null;
                $itemId = (int)($components['Assignment_item_id'] ?? $assignmentId);
                $gradeModel->saveGradeItem($studentId, $courseId, $itemId, 'Assignment', $val, $fb, $lecturerId);
                
                if ($itemId > 0) {
                    $submissionModel->updateGradeAndFeedback($itemId, $studentId, $val, $fb);
                }
            }
            if (isset($components['UTS']) && $components['UTS'] !== '') {
                $val = (float)$components['UTS'];
                $itemId = (int)($components['UTS_item_id'] ?? $courseId);
                $gradeModel->saveGradeItem($studentId, $courseId, $itemId, 'UTS', $val, null, $lecturerId);
            }
            if (isset($components['UAS']) && $components['UAS'] !== '') {
                $val = (float)$components['UAS'];
                $itemId = (int)($components['UAS_item_id'] ?? $courseId);
                $gradeModel->saveGradeItem($studentId, $courseId, $itemId, 'UAS', $val, null, $lecturerId);
            }
            if (isset($components['Partisipasi']) && $components['Partisipasi'] !== '') {
                $val = (float)$components['Partisipasi'];
                $itemId = (int)($components['Partisipasi_item_id'] ?? $courseId);
                $gradeModel->saveGradeItem($studentId, $courseId, $itemId, 'Partisipasi', $val, null, $lecturerId);
            }
        }

        Middleware::jsonSuccess("Nilai berhasil disimpan.");
    }

    public function exportExcel(): void {
        Middleware::requireRole('lecturer');
        $lecturerId = Middleware::currentUserId();

        $courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
        if ($courseId === 0) {
            die("Error: ID Mata Kuliah tidak ditentukan.");
        }

        /** @var \App\Models\Course $courseModel */
        $courseModel = $this->model('Course');
        $courseInfo = $courseModel->getCourseDetails($courseId);
        $students = $courseModel->getStudentsEnrolled($courseId);

        /** @var \App\Models\Grade $gradeModel */
        $gradeModel = $this->model('Grade');

        $filename = "Rekap_Nilai_" . preg_replace('/[^A-Za-z0-9_\-]/', '_', $courseInfo['course_name'] ?? 'Course') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        fputcsv($output, ['No', 'NIM', 'Nama Mahasiswa', 'Program Studi', 'Tugas (20%)', 'UTS (30%)', 'UAS (40%)', 'Partisipasi (10%)', 'Nilai Akhir', 'Grade']);

        $no = 1;
        foreach ($students as $s) {
            $sGrades = $gradeModel->getGradesByCourseAndStudent($courseId, $s['user_id']);
            $gMap = [];
            foreach ($sGrades as $g) {
                $gMap[$g['grade_type']] = $g['grade_value'];
            }

            $t = $gMap['Assignment'] ?? 0;
            $u = $gMap['UTS'] ?? 0;
            $ua = $gMap['UAS'] ?? 0;
            $p = $gMap['Partisipasi'] ?? 0;

            $final = ($t * 0.2) + ($u * 0.3) + ($ua * 0.4) + ($p * 0.1);
            $letter = getGradeLetterPHP($final);

            fputcsv($output, [
                $no++,
                $s['nim'],
                $s['full_name'],
                $s['study_program'],
                $t, $u, $ua, $p,
                round($final, 2),
                $letter
            ]);
        }

        fclose($output);
        exit();
    }
}
