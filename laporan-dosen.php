<?php
include 'middleware.php';
include 'db_connection.php';
include 'helpers.php';
require_role('lecturer');

$current_lecturer_id = $_SESSION['user_id'];

// Inisialisasi variabel filter dari GET request
$selected_course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;

$courses_for_filter_dropdown = [];
$students_final_grades = [];
$summary_stats = [
    'total_mahasiswa' => 0,
    'sudah_dinilai' => 0,
    'rata_rata_ips' => 0.00,
    'rata_rata_ipk' => 0.00
];
$grade_distribution_php = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];

$dosen_name_for_header = "Dosen Pengajar";
$selected_course_name_display = "Pilih Mata Kuliah";
$selected_course_code_display = "";

// --- Ambil Nama Dosen untuk Header ---
$sql_lecturer_header = "SELECT full_name, gelar FROM users WHERE user_id = ? AND role = 'lecturer'";
$stmt_lecturer_header = $conn->prepare($sql_lecturer_header);
if ($stmt_lecturer_header) {
    $stmt_lecturer_header->execute([$current_lecturer_id]);
    if ($row_lecturer = $stmt_lecturer_header->fetch()) {
        $dosen_name_for_header = htmlspecialchars($row_lecturer['full_name'] . ($row_lecturer['gelar'] ? ', ' . $row_lecturer['gelar'] : ''));
    }
} else {
    error_log("Error preparing lecturer query.");
}

// --- Ambil Mata Kuliah yang Diampu Dosen Ini ---
$sql_courses = "SELECT course_id, course_name, course_code, credits FROM courses WHERE lecturer_id = ? ORDER BY course_name ASC";
$stmt_courses = $conn->prepare($sql_courses);
if ($stmt_courses) {
    $stmt_courses->execute([$current_lecturer_id]);
    while ($row = $stmt_courses->fetch()) {
        $courses_for_filter_dropdown[] = $row;
    }
} else {
    error_log("Error preparing courses query.");
}


// --- Tentukan Mata Kuliah yang Dipilih untuk Tampilan ---
if (!empty($courses_for_filter_dropdown)) {
    if ($selected_course_id === null || !in_array($selected_course_id, array_column($courses_for_filter_dropdown, 'course_id'))) {
        $selected_course_id = $courses_for_filter_dropdown[0]['course_id'];
    }

    // Cari detail mata kuliah yang dipilih
    $selected_course_credits = 0; // Inisialisasi SKS
    foreach ($courses_for_filter_dropdown as $course) {
        if ($course['course_id'] == $selected_course_id) {
            $selected_course_name_display = htmlspecialchars($course['course_name']);
            $selected_course_code_display = htmlspecialchars($course['course_code']);
            $selected_course_credits = $course['credits']; // Ambil SKS mata kuliah
            break;
        }
    }

    // --- Fetch Data Laporan ---
    $sql_students_in_course = "
        SELECT u.user_id, u.full_name, u.nim, u.study_program
        FROM users u
        JOIN course_enrollments ce ON u.user_id = ce.student_id
        WHERE ce.course_id = ? AND u.role = 'student'
        ORDER BY u.full_name ASC;
    ";
    $stmt_students_in_course = $conn->prepare($sql_students_in_course);
    $all_students_for_report = [];
    if ($stmt_students_in_course) {
        $stmt_students_in_course->execute([$selected_course_id]);
        $all_students_for_report = $stmt_students_in_course->fetchAll();
    } else {
        error_log("Error preparing students data for report query.");
    }

    // Perbaikan: Ambil semua nilai, TIDAK HANYA yang ada semester/academic_year
    $grades_map_by_student_type_item = []; // [student_id => [grade_type => [item_id => [value, feedback]]]]
    if (!empty($all_students_for_report)) {
        $student_ids_array = array_column($all_students_for_report, 'user_id');
        $placeholders = implode(',', array_fill(0, count($student_ids_array), '?'));

        // Mengambil semua grade yang relevan dalam satu query
        $sql_all_grades_for_course = "
            SELECT student_id, grade_type, grade_value, feedback, item_id, semester, academic_year
            FROM grades
            WHERE student_id IN ({$placeholders}) AND course_id = ?
        ";
        $stmt_all_grades_for_course = $conn->prepare($sql_all_grades_for_course);
        if ($stmt_all_grades_for_course) {
            $bind_params = array_merge($student_ids_array, [$selected_course_id]);
            $stmt_all_grades_for_course->execute($bind_params);
            while($row_grade = $stmt_all_grades_for_course->fetch()) {
                // Perbaikan: Gunakan item_id untuk Tugas, dan course_id untuk UTS/UAS/Partisipasi
                // karena di input_nilai_dosen_crud.php, item_id diisi sesuai dengan grade_type
                if ($row_grade['grade_type'] == 'Assignment') {
                    $grades_map_by_student_type_item[$row_grade['student_id']][$row_grade['grade_type']][] = [
                        'value' => $row_grade['grade_value'],
                        'feedback' => $row_grade['feedback']
                    ];
                } else {
                     $grades_map_by_student_type_item[$row_grade['student_id']][$row_grade['grade_type']][$row_grade['item_id']] = [
                        'value' => $row_grade['grade_value'],
                        'feedback' => $row_grade['feedback']
                    ];
                }
            }
        } else {
            error_log("Error preparing all grades for course query.");
        }
    }

    // --- Ambil Semua Nilai untuk Perhitungan IPK (dari semua mata kuliah dan semester) ---
    $all_grades_for_ipk_calc = []; // [student_id => [semester_key => [course_id => ['nilai_akhir' => X, 'grade_points' => Y, 'sks' => Z]]]]
    if (!empty($all_students_for_report)) {
        $student_ids_array = array_column($all_students_for_report, 'user_id');
        $placeholders = implode(',', array_fill(0, count($student_ids_array), '?'));

        $sql_all_grades_for_ipk = "
            SELECT g.student_id, g.course_id, g.grade_type, g.grade_value, c.credits, g.semester, g.academic_year
            FROM grades g
            JOIN courses c ON g.course_id = c.course_id
            WHERE g.student_id IN ({$placeholders}) AND g.grade_type IN ('Assignment', 'UTS', 'UAS', 'Partisipasi') AND g.grade_value IS NOT NULL
        ";
        $stmt_all_grades_for_ipk = $conn->prepare($sql_all_grades_for_ipk);
        if ($stmt_all_grades_for_ipk) {
            $stmt_all_grades_for_ipk->execute($student_ids_array);

            // Peta untuk menyimpan nilai komponen per mahasiswa per mata kuliah per semester
            $temp_course_grades_for_ipk = []; // [student_id => [course_id => [semester_academic_year_key => [grade_type => value, 'credits' => X, 'has_relevant_grade' => bool]]]]

            while($row_grade = $stmt_all_grades_for_ipk->fetch()) {
                $student_id = $row_grade['student_id'];
                $course_id = $row_grade['course_id'];
                $grade_type = $row_grade['grade_type'];
                $grade_value = (float)$row_grade['grade_value'];
                $semester_key_db = ($row_grade['semester'] ?? 'unknown') . '-' . ($row_grade['academic_year'] ?? 'unknown');
                $credits = (int)$row_grade['credits'];

                if (!isset($temp_course_grades_for_ipk[$student_id][$course_id][$semester_key_db])) {
                    $temp_course_grades_for_ipk[$student_id][$course_id][$semester_key_db] = [
                        'Assignment' => [], 'UTS' => 0, 'UAS' => 0, 'Partisipasi' => 0,
                        'credits' => $credits,
                        'has_relevant_grade' => false
                    ];
                }
                
                if ($grade_type == 'Assignment') {
                    $temp_course_grades_for_ipk[$student_id][$course_id][$semester_key_db]['Assignment'][] = $grade_value;
                } else {
                    $temp_course_grades_for_ipk[$student_id][$course_id][$semester_key_db][$grade_type] = $grade_value;
                }
                
                if ($grade_value !== null && $grade_value > 0) {
                    $temp_course_grades_for_ipk[$student_id][$course_id][$semester_key_db]['has_relevant_grade'] = true;
                }
            }

            // Hitung nilai akhir untuk setiap mata kuliah per mahasiswa per semester (untuk IPK)
            $grade_weights = [
                'Assignment' => 0.20,
                'UTS' => 0.30,
                'UAS' => 0.40,
                'Partisipasi' => 0.10,
            ];

            foreach ($temp_course_grades_for_ipk as $student_id => $courses) {
                foreach ($courses as $course_id => $semesters) {
                    foreach ($semesters as $semester_key_db => $grade_components) {
                        if ($grade_components['has_relevant_grade']) {
                            $tugas_val_avg = !empty($grade_components['Assignment']) ? array_sum($grade_components['Assignment']) / count($grade_components['Assignment']) : 0;
                            $uts_val = $grade_components['UTS'];
                            $uas_val = $grade_components['UAS'];
                            $partisipasi_val = $grade_components['Partisipasi'];
                            $credits = $grade_components['credits'];

                            $nilai_akhir_course = ($tugas_val_avg * 0.20) + ($uts_val * 0.30) + ($uas_val * 0.40) + ($partisipasi_val * 0.10);
                            $grade_letter_course = getGradeLetterPHP($nilai_akhir_course);
                            $grade_points_course = getGradePointsPHP($grade_letter_course);

                            $all_grades_for_ipk_calc[$student_id][] = [
                                'nilai_akhir' => $nilai_akhir_course,
                                'grade_letter' => $grade_letter_course,
                                'grade_points' => $grade_points_course,
                                'credits' => $credits,
                                'semester_key' => $semester_key_db
                            ];
                        }
                    }
                }
            }
        } else {
            error_log("Error preparing all grades for IPK query: " . $conn->error);
        }
    }


    // Gabungkan data mahasiswa dengan nilai-nilai mereka
    $total_ips_sum_for_avg = 0;
    $count_ips_for_avg = 0;

    $total_ipk_sum_for_avg = 0;
    $count_ipk_for_avg = 0;

    foreach ($all_students_for_report as $student) {
        $student_id = $student['user_id'];
        $has_any_grade_this_course_semester = false;
        
        $tugas_val_for_calc = 0;
        $uts_val_for_calc = 0;
        $uas_val_for_calc = 0;
        $partisipasi_val_for_calc = 0;
        
        // Ambil nilai dari map yang sudah dibuat
        $grades_in_course = $grades_map_by_student_type_item[$student_id] ?? [];
        
        if (isset($grades_in_course['Assignment']) && !empty($grades_in_course['Assignment'])) {
            $tugas_val_for_calc = array_sum(array_column($grades_in_course['Assignment'], 'value')) / count($grades_in_course['Assignment']);
            $has_any_grade_this_course_semester = true;
        }

        if (isset($grades_in_course['UTS'][$selected_course_id])) {
            $uts_val_for_calc = (float) $grades_in_course['UTS'][$selected_course_id]['value'];
            $has_any_grade_this_course_semester = true;
        }
        
        if (isset($grades_in_course['UAS'][$selected_course_id])) {
            $uas_val_for_calc = (float) $grades_in_course['UAS'][$selected_course_id]['value'];
            $has_any_grade_this_course_semester = true;
        }
        
        if (isset($grades_in_course['Partisipasi'][$selected_course_id])) {
            $partisipasi_val_for_calc = (float) $grades_in_course['Partisipasi'][$selected_course_id]['value'];
            $has_any_grade_this_course_semester = true;
        }
        
        $tugas_val_display = $tugas_val_for_calc > 0 ? number_format($tugas_val_for_calc, 2) : '-';
        $uts_val_display = $uts_val_for_calc > 0 ? number_format($uts_val_for_calc, 2) : '-';
        $uas_val_display = $uas_val_for_calc > 0 ? number_format($uas_val_for_calc, 2) : '-';
        $partisipasi_val_display = $partisipasi_val_for_calc > 0 ? number_format($partisipasi_val_for_calc, 2) : '-';

        // Hitung nilai akhir
        $final_score_this_course_semester = ($tugas_val_for_calc * 0.20) + ($uts_val_for_calc * 0.30) + ($uas_val_for_calc * 0.40) + ($partisipasi_val_for_calc * 0.10);
        $grade_letter_this_course_semester = getGradeLetterPHP($final_score_this_course_semester);
        $ips_this_course = getGradePointsPHP($grade_letter_this_course_semester);

        // Perhitungan IPK
        $total_mutu_all_courses_all_semesters = 0;
        $total_sks_all_courses_all_semesters = 0;
        $ipk = 0.00;

        if (isset($all_grades_for_ipk_calc[$student_id])) {
            foreach ($all_grades_for_ipk_calc[$student_id] as $course_grade_data) {
                $total_mutu_all_courses_all_semesters += ($course_grade_data['grade_points'] * $course_grade_data['credits']);
                $total_sks_all_courses_all_semesters += $course_grade_data['credits'];
            }
            if ($total_sks_all_courses_all_semesters > 0) {
                $ipk = $total_mutu_all_courses_all_semesters / $total_sks_all_courses_all_semesters;
            }
        }

        $students_final_grades[] = [
            'user_id' => $student_id,
            'nim' => $student['nim'],
            'full_name' => $student['full_name'],
            'tugas' => $tugas_val_display,
            'uts' => $uts_val_display,
            'uas' => $uas_val_display,
            'partisipasi' => $partisipasi_val_display,
            'nilai_akhir' => number_format($final_score_this_course_semester, 2),
            'grade_letter' => $grade_letter_this_course_semester,
            'ips' => number_format($ips_this_course, 2),
            'ipk' => number_format($ipk, 2),
        ];

        // Update summary stats
        $summary_stats['total_mahasiswa']++;
        if ($has_any_grade_this_course_semester) {
            $summary_stats['sudah_dinilai']++;
            $total_ips_sum_for_avg += $ips_this_course;
            $total_ipk_sum_for_avg += (float)number_format($ipk, 2);
            $grade_distribution_php[$grade_letter_this_course_semester]++;
        }
    }

    if ($summary_stats['sudah_dinilai'] > 0) {
        $summary_stats['rata_rata_ips'] = number_format($total_ips_sum_for_avg / $summary_stats['sudah_dinilai'], 2);
        $summary_stats['rata_rata_ipk'] = number_format($total_ipk_sum_for_avg / $summary_stats['sudah_dinilai'], 2);
    } else {
        $summary_stats['rata_rata_ips'] = number_format(0.00, 2);
        $summary_stats['rata_rata_ipk'] = number_format(0.00, 2);
    }
} else {
    $selected_course_name_display = "Tidak Ada Mata Kuliah Diampu";
    $selected_course_code_display = "";
}

// --- LOGIKA EXPORT EXCEL (CSV) ---
if (isset($_GET['action']) && $_GET['action'] === 'export_excel' && $selected_course_id) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="laporan_nilai_' . str_replace(' ', '_', $selected_course_name_display) . '_' . date('Ymd_His') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for UTF-8

    fputcsv($output, [
        'No',
        'NIM',
        'Nama Mahasiswa',
        'Tugas (20%)',
        'UTS (30%)',
        'UAS (40%)',
        'Partisipasi (10%)', // Partisipasi
        'Nilai Akhir',
        'Grade',
        'IPK' // IP Semester dihapus
    ], ';');

    $row_number = 1;
    foreach ($students_final_grades as $student) {
        fputcsv($output, [
            $row_number++,
            htmlspecialchars_decode($student['nim']),
            htmlspecialchars_decode($student['full_name']),
            str_replace('.', ',', $student['tugas']),
            str_replace('.', ',', $student['uts']),
            str_replace('.', ',', $student['uas']),
            str_replace('.', ',', $student['partisipasi']), // Partisipasi
            str_replace('.', ',', $student['nilai_akhir']),
            htmlspecialchars_decode($student['grade_letter']),
            str_replace('.', ',', $student['ipk']) // IP Semester dihapus
        ], ';');
    }

    fclose($output);
    exit();
}

$conn = null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Nilai - Dosen</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS ini disalin PASTI dari kode yang Anda berikan, dengan penyesuaian minimal */
        /* Pastikan untuk mengacu pada gambar `image_8c50c2.jpg` */

        /* Definisi variabel warna - dari kode Anda */
        :root {
            --color-primary: #B6D0EF; /* Warna biru lebih cerah */
            --color-secondary: #63A3F1; /* Warna biru utama, lebih kuat */
            --color-accent: #FAFFEE; /* Biru muda sangat terang, untuk latar belakang ringan */
            --color-dark: #4F8A9E; /* Biru gelap untuk teks/elemen utama */
            --color-white: #FFFFFF;
            --color-success: #28a745;
            --color-info: #17a2b8;
            --color-warning: #ffc107;
            --color-danger: #dc3545;
        }

        /* Reset dan Box Sizing */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
            min-height: 100vh;
            overflow-x: hidden;
            color: #333;
        }

        .navbar {
            background: linear-gradient(90deg, var(--color-dark) 0%, var(--color-secondary) 100%);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: relative;
        }
        
        .back-btn {
            position: absolute;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 12px;
            padding: 8px 15px;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.4);
            color: white;
            transform: translateY(-50%) scale(1.05);
        }

        .navbar-brand {
            color: var(--color-white) !important;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .navbar-text {
            color: var(--color-white) !important;
            font-weight: 500;
        }

        .main-container {
            padding: 2rem;
            max-width: 100%;
        }

        .header-section {
            background: var(--color-white);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-left: 5px solid var(--color-secondary);
        }

        .header-section h2 {
            color: var(--color-dark);
        }

        .header-section p {
            color: #666;
        }

        .semester-info {
            background: var(--color-accent);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 2px solid var(--color-primary);
        }

        .semester-info label {
            color: var(--color-dark);
            margin-bottom: 0.25rem;
        }

        .form-select, .form-control {
            border: 2px solid var(--color-primary);
            border-radius: 8px;
            padding: 0.5rem;
            text-align: left;
            font-weight: bold;
        }

        .form-select:focus, .form-control:focus {
            border-color: var(--color-secondary);
            box-shadow: 0 0 0 0.2rem rgba(99, 163, 241, 0.25);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(90deg, var(--color-secondary) 0%, var(--color-primary) 100%);
            color: var(--color-white);
            border: none;
            padding: 1.5rem;
            font-weight: bold;
            font-size: 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: var(--color-primary);
            color: var(--color-dark);
            border: none;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding: 1rem 0.5rem;
            font-size: 0.9rem;
        }

        .table tbody td {
            text-align: center;
            vertical-align: middle;
            padding: 0.8rem 0.5rem;
            border-color: var(--color-primary);
        }

        /* Warna Grade */
        .grade-display {
            font-weight: bold;
            font-size: 1.1rem;
            padding: 0.5rem;
            border-radius: 8px;
            text-align: center;
        }
        .grade-A { background: #d4edda; color: #155724; }
        .grade-B { background: #d1ecf1; color: #0c5460; }
        .grade-C { background: #fff3cd; color: #856404; }
        .grade-D { background: #f8d7da; color: #721c24; }
        .grade-E { background: #f5c6cb; color: #721c24; }

        .ip-display {
            background: var(--color-accent);
            border: 2px solid var(--color-primary);
            border-radius: 10px;
            padding: 1rem;
            margin: 0.5rem 0;
        }

        .ip-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--color-dark);
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-card {
            background: var(--color-white);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-left: 5px solid var(--color-secondary);
        }

        .summary-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--color-dark);
            margin-bottom: 0.25rem;
        }

        .summary-label {
            color: var(--color-secondary);
            font-weight: 500;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            .table-responsive {
                font-size: 0.8rem;
            }
            .form-control {
                padding: 0.3rem;
                font-size: 0.8rem;
            }
        }

        .loading {
            display: none;
            text-align: center;
            margin: 2rem 0;
            flex-direction: column;
            align-items: center;
        }

        .spinner-border {
            color: var(--color-secondary);
        }

        .table-center th, .table-center td {
            text-align: center;
        }

        .container-fluid {
            padding: 1.5rem;
        }

        .class-selector {
            margin-bottom: 1.5rem;
            padding: 1.25rem;
        }
        .class-selector .row {
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .class-selector .col-md-6,
        .class-selector .col-md-12 {
            margin-bottom: 0.75rem;
        }

        .alert {
            padding: 0.75rem;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
        }

        .section-title {
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .card-header {
            padding: 1rem;
        }
        .export-btn-container {
            margin-left: auto;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            color: #fff;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .btn-success:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }
        
        /* CSS untuk merapikan header */
        .navbar .container-fluid {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-brand {
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a href="dash-dosen.php" class="back-btn">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            
            <span class="navbar-brand">
                <i class="fas fa-edit me-2"></i>
                <?php echo $selected_course_name_display . " (" . $selected_course_code_display . ")"; ?>
            </span>
            <div class="d-flex align-items-center text-white">
                <i class="fas fa-user-tie me-2"></i>
                Selamat datang, <?php echo $dosen_name_for_header; ?>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="class-selector">
            <h4 class="section-title">
                <i class="fas fa-users me-2"></i>
                Pilih Kelas
            </h4>
            <div class="row">
                <div class="col-md-6">
                    <form id="classSelectForm" method="GET" action="laporan-dosen.php">
                        <select class="form-select" id="mataKuliah" name="course_id" onchange="this.form.submit()">
                            <option value="">-- Pilih Mata Kuliah --</option>
                            <?php foreach ($courses_for_filter_dropdown as $course): ?>
                                <option value="<?php echo htmlspecialchars($course['course_id']); ?>" <?php echo ($selected_course_id == $course['course_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Pilih mata kuliah untuk melihat laporan nilai mahasiswa.
                    </div>
                </div>
            </div>
        </div>
        

        <?php if ($selected_course_id): ?>
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-number" id="totalMahasiswa"><?php echo $summary_stats['total_mahasiswa']; ?></div>
                <div class="summary-label">Total Mahasiswa</div>
            </div>
            <div class="summary-card">
                <div class="summary-number" id="rataRataIPK"><?php echo $summary_stats['rata_rata_ipk']; ?></div>
                <div class="summary-label">Rata-rata IPK Mahasiswa</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="fas fa-table me-2"></i>
                    Daftar Nilai Mahasiswa
                </span>
                <div class="export-btn-container">
                    <?php if (!empty($students_final_grades)): ?>
                        <button class="btn btn-success btn-sm" onclick="exportToExcel()">
                            <i class="fas fa-file-excel me-2"></i> Export Excel
                        </button>
                    <?php else: ?>
                        <button class="btn btn-success btn-sm" disabled title="Tidak ada data untuk diekspor">
                            <i class="fas fa-file-excel me-2"></i> Export Excel
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="loading" id="loadingSpinner">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat data mahasiswa...</p>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-center" id="nilaiTable">
                        <thead>
                            <tr>
                                <th rowspan="2">No</th>
                                <th rowspan="2">NIM</th>
                                <th rowspan="2">Nama Mahasiswa</th>
                                <th colspan="4">Nilai Komponen</th>
                                <th rowspan="2">Nilai Akhir</th>
                                <th rowspan="2">Grade</th>
                                <th rowspan="2">IPK</th>
                            </tr>
                            <tr>
                                <th>Tugas<br>(20%)</th>
                                <th>UTS<br>(30%)</th>
                                <th>UAS<br>(40%)</th>
                                <th>Partisipasi<br>(10%)</th>
                            </tr>
                        </thead>
                        <tbody id="mahasiswaTableBody">
                            <?php if (empty($students_final_grades)): ?>
                                <tr><td colspan="9" class="text-muted text-center py-3">Tidak ada data nilai untuk mata kuliah ini.</td></tr>
                            <?php else: ?>
                                <?php $row_number = 1; ?>
                                <?php foreach ($students_final_grades as $student): ?>
                                    <tr data-user-id="<?php echo htmlspecialchars($student['user_id']); ?>">
                                        <td><?php echo $row_number++; ?></td>
                                        <td><?php echo htmlspecialchars($student['nim']); ?></td>
                                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['tugas']); ?></td>
                                        <td><?php echo htmlspecialchars($student['uts']); ?></td>
                                        <td><?php echo htmlspecialchars($student['uas']); ?></td>
                                        <td><?php echo htmlspecialchars($student['partisipasi']); ?></td>
                                        <td><?php echo htmlspecialchars($student['nilai_akhir']); ?></td>
                                        <td class="grade-display grade-letter grade-<?php echo strtolower(str_replace('.', '', htmlspecialchars($student['grade_letter']))); ?>"><?php echo htmlspecialchars($student['grade_letter']); ?></td>
                                        <td><?php echo htmlspecialchars($student['ipk']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        const SELECTED_COURSE_ID_JS = <?php echo json_encode($selected_course_id); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            const mataKuliahSelect = document.getElementById('mataKuliah');
            if (mataKuliahSelect) {
                mataKuliahSelect.value = SELECTED_COURSE_ID_JS;
            }
            const loadingSpinner = document.getElementById('loadingSpinner');
            if (loadingSpinner) {
                loadingSpinner.style.display = 'none';
            }
        });

        function exportToExcel() {
            const courseId = document.getElementById('mataKuliah').value;

            if (!courseId) {
                alert('Pilih mata kuliah terlebih dahulu untuk export data.');
                return;
            }

            window.location.href = `laporan-dosen.php?action=export_excel&course_id=${courseId}`;
        }
    </script>
</body>
</html>