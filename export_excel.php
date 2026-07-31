<?php
// export_excel.php
include 'middleware.php';
include 'db_connection.php';
include 'helpers.php';
require_role('lecturer');

// Pastikan request adalah GET (untuk export)
if (!isset($_GET['action']) || $_GET['action'] !== 'export_excel') {
    die("Akses tidak valid atau tidak diizinkan.");
}

$current_lecturer_id = $_SESSION['user_id'];

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$semester = isset($_GET['semester']) ? $_GET['semester'] : 'Genap 2023'; // Mengubah default ke Bahasa Indonesia
$class_name = isset($_GET['class_name']) ? $_GET['class_name'] : null;
$academic_year = isset($_GET['academic_year']) ? $_GET['academic_year'] : '2024/2025';

if ($course_id === 0) {
    die("Error: ID Mata Kuliah tidak ditentukan untuk ekspor."); // Pesan error Bahasa Indonesia
}

try {
    // Ambil nama dan kode mata kuliah
    $course_name_display = "Mata_Kuliah_Tidak_Dikenal"; // Mengubah default ke Bahasa Indonesia
    $course_code_display = "";
    $stmt_course_info = $conn->prepare("SELECT course_name, course_code FROM courses WHERE course_id = ? AND lecturer_id = ?");
    if ($stmt_course_info) {
        $stmt_course_info->execute([$course_id, $current_lecturer_id]);
        if ($row_course_info = $stmt_course_info->fetch()) {
            $course_name_display = $row_course_info['course_name'];
            $course_code_display = $row_course_info['course_code'];
        }
    } else {
        throw new Exception("Gagal menyiapkan kueri info mata kuliah"); // Pesan error Bahasa Indonesia
    }

    // Ambil nama dosen
    $dosen_name_for_header = '';
    $stmt_lecturer_name_export = $conn->prepare("SELECT full_name, gelar FROM users WHERE user_id = ?");
    if($stmt_lecturer_name_export) {
        $stmt_lecturer_name_export->execute([$current_lecturer_id]);
        if($row_lecturer_export = $stmt_lecturer_name_export->fetch()) {
            $dosen_name_for_header = $row_lecturer_export['full_name'] . ($row_lecturer_export['gelar'] ? ', ' . $row_lecturer_export['gelar'] : '');
        }
    } else {
        throw new Exception("Gagal menyiapkan kueri nama dosen untuk ekspor"); // Pesan error Bahasa Indonesia
    }


    // Ambil data mahasiswa
    $all_students_for_report = [];
    $sql_students_in_course = "
        SELECT u.user_id, u.full_name, u.nim, u.study_program
        FROM users u
        JOIN course_enrollments ce ON u.user_id = ce.student_id
        WHERE ce.course_id = ? AND u.role = 'student'
        ORDER BY u.full_name ASC;
    ";
    $stmt_students = $conn->prepare($sql_students_in_course);
    if ($stmt_students) {
        $stmt_students->execute([$course_id]);
        $all_students_for_report = $stmt_students->fetchAll();
    } else {
        throw new Exception("Gagal menyiapkan kueri mahasiswa dalam mata kuliah untuk ekspor"); // Pesan error Bahasa Indonesia
    }

    // Ambil SEMUA nilai relevan untuk SEMUA mahasiswa di mata kuliah INI
    $grades_map_by_student_type_item = [];
    if (!empty($all_students_for_report)) {
        $student_ids_array = array_column($all_students_for_report, 'user_id');
        $placeholders = implode(',', array_fill(0, count($student_ids_array), '?'));

        $sql_all_grades_for_course = "
            SELECT student_id, grade_type, grade_value, feedback, item_id
            FROM grades
            WHERE student_id IN ({$placeholders}) AND course_id = ?
        ";
        $stmt_all_grades_for_course = $conn->prepare($sql_all_grades_for_course);
        if ($stmt_all_grades_for_course) {
            $bind_params = array_merge($student_ids_array, [$course_id]);
            $stmt_all_grades_for_course->execute($bind_params);
            while($row_grade = $stmt_all_grades_for_course->fetch()) {
                $grades_map_by_student_type_item[$row_grade['student_id']][$row_grade['grade_type']][$row_grade['item_id']] = [
                    'value' => $row_grade['grade_value'],
                    'feedback' => $row_grade['feedback']
                ];
            }
        } else {
            throw new Exception("Gagal menyiapkan kueri semua nilai untuk ekspor"); // Pesan error Bahasa Indonesia
        }
    }

    // Siapkan data laporan
    $report_data_rows = [];
    $row_number = 1;
    foreach ($all_students_for_report as $student) {
        $student_id = $student['user_id'];
        
        $tugas_val_display = null;
        $uts_val_display = null;
        $uas_val_display = null;
        $partisipasi_val_display = null;
        $feedback_tugas_umum_display = null;

        // Ambil nilai rata-rata Tugas
        $all_assignment_grades_for_student = [];
        if (isset($grades_map_by_student_type_item[$student_id]['Assignment'])) {
            foreach ($grades_map_by_student_type_item[$student_id]['Assignment'] as $item_id => $grade_entry) {
                if ($grade_entry['value'] !== null) {
                    $all_assignment_grades_for_student[] = (float)$grade_entry['value'];
                    if ($item_id == $course_id) { // Asumsi course_id digunakan untuk feedback tugas umum
                         $feedback_tugas_umum_display = $grade_entry['feedback'];
                    } elseif ($feedback_tugas_umum_display === null) {
                        $feedback_tugas_umum_display = $grade_entry['feedback'];
                    }
                }
            }
        }
        $tugas_val_for_calc = 0;
        if (!empty($all_assignment_grades_for_student)) {
            $tugas_val_for_calc = array_sum($all_assignment_grades_for_student) / count($all_assignment_grades_for_student);
            $tugas_val_display = number_format($tugas_val_for_calc, 2);
        }

        // Ambil nilai UTS, UAS, Partisipasi
        $uts_val_for_calc = 0;
        if (isset($grades_map_by_student_type_item[$student_id]['UTS'][$course_id])) {
            $uts_val_for_calc = (float)($grades_map_by_student_type_item[$student_id]['UTS'][$course_id]['value'] ?? 0);
            $uts_val_display = $grades_map_by_student_type_item[$student_id]['UTS'][$course_id]['value'] ?? '';
        }
        $uas_val_for_calc = 0;
        if (isset($grades_map_by_student_type_item[$student_id]['UAS'][$course_id])) {
            $uas_val_for_calc = (float)($grades_map_by_student_type_item[$student_id]['UAS'][$course_id]['value'] ?? 0);
            $uas_val_display = $grades_map_by_student_type_item[$student_id]['UAS'][$course_id]['value'] ?? '';
        }
        $partisipasi_val_for_calc = 0;
        if (isset($grades_map_by_student_type_item[$student_id]['Partisipasi'][$course_id])) {
            $partisipasi_val_for_calc = (float)($grades_map_by_student_type_item[$student_id]['Partisipasi'][$course_id]['value'] ?? 0);
            $partisipasi_val_display = $grades_map_by_student_type_item[$student_id]['Partisipasi'][$course_id]['value'] ?? '';
        }

        $final_score = ($tugas_val_for_calc * 0.20) + ($uts_val_for_calc * 0.30) + ($uas_val_for_calc * 0.40) + ($partisipasi_val_for_calc * 0.10);
        $grade_letter = getGradeLetterPHP($final_score); 
        $grade_points = getGradePointsPHP($grade_letter); 
        $ips = $grade_points; 
        $ipk = 0.00; // IPK ditetapkan 0.00 (per instruksi asli)

        $report_data_rows[] = [
            (string)$row_number++,
            (string)$student['nim'],
            (string)$student['full_name'],
            (string)($tugas_val_display ?? '-'),
            (string)($uts_val_display ?? '-'),
            (string)($uas_val_display ?? '-'),
            (string)($partisipasi_val_display ?? '-'),
            (string)number_format($final_score, 2),
            (string)$grade_letter,
            (string)number_format($ips, 2),
            (string)number_format($ipk, 2),
            (string)($feedback_tugas_umum_display ?? '')
        ];
    }

    // --- Header Output CSV ---
    // Bersihkan buffer output sebelumnya
    if (ob_get_level() > 0) {
        ob_clean(); 
    }

    // Nama file yang lebih deskriptif
    $filename = 'Laporan_Nilai_' . preg_replace("/[^a-zA-Z0-9_]/", "", $course_name_display) . "_" . preg_replace("/[^a-zA-Z0-9_]/", "", $course_code_display) . '_' . str_replace(' ', '_', $semester) . '_' . str_replace('/', '-', $academic_year) . '.csv'; // Penyesuaian nama file

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public'); // Diperlukan untuk IE
    header('Expires: 0'); // Mencegah caching

    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF"); // BOM untuk UTF-8

    // Header Laporan dalam CSV
    fputcsv($output, ['Laporan Nilai Semester']);
    fputcsv($output, ['Mata Kuliah: ' . $course_name_display . ' (' . $course_code_display . ')']);
    fputcsv($output, ['Semester: ' . $semester]);
    fputcsv($output, ['Tahun Akademik: ' . $academic_year]);
    fputcsv($output, ['Dosen Pengajar: ' . $dosen_name_for_header]);
    fputcsv($output, ['Tanggal Ekspor: ' . date('Y-m-d H:i:s')]); // Mengubah ke Bahasa Indonesia
    fputcsv($output, []); // Baris kosong

    // Kolom Header Tabel
    fputcsv($output, [
        'No', 'NIM', 'Nama Mahasiswa',
        'Tugas (20%)', 'UTS (30%)', 'UAS (40%)', 'Partisipasi (10%)',
        'Nilai Akhir', 'Grade', 'IP Semester', 'IPK', 'Keterangan'
    ]);

    // Data Mahasiswa
    foreach ($report_data_rows as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    $conn = null;
    exit();

} catch (Exception $e) {
    // Jika terjadi error selama ekspor, laporkan ke pengguna.
    // Pastikan semua output buffering dibersihkan sebelum mengirim error.
    if (ob_get_level() > 0) {
        ob_clean(); 
    }
    // Set header text/plain agar browser tidak mencoba mengunduh file yang rusak
    header('Content-Type: text/plain'); 
    die("Ekspor gagal: " . $e->getMessage() . " (Baris: " . $e->getLine() . "). Silakan periksa log error PHP untuk detail lebih lanjut."); // Pesan error Bahasa Indonesia
} finally {
    $conn = null;
}
?>