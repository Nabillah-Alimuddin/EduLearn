<?php
// input_nilai_dosen_crud.php
ob_start();
include 'middleware.php';
include 'db_connection.php';
include 'helpers.php';
require_role('lecturer');
$current_lecturer_id = $_SESSION['user_id'];

// --- Inisialisasi Filter & Data Tampilan ---
$selected_course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;
$selected_assignment_id = isset($_GET['assignment_id']) ? (int)$_GET['assignment_id'] : null;
$selected_semester = isset($_GET['semester']) ? $_GET['semester'] : 'genap';
$selected_academic_year = isset($_GET['academic_year']) ? $_GET['academic_year'] : '2025/2026';


$courses_for_dropdown = [];
$assignments_for_dropdown = [];
$students_grades_data = []; // Data utama untuk tabel

// --- Fetch Mata Kuliah yang Diampu Dosen ---
$sql_courses = "SELECT course_id, course_name FROM courses WHERE lecturer_id = ? ORDER BY course_name ASC";
$stmt_courses = $conn->prepare($sql_courses);
if ($stmt_courses) {
    $stmt_courses->execute([$current_lecturer_id]);
    $courses_for_dropdown = $stmt_courses->fetchAll();
}

// --- Fetch Tugas untuk Mata Kuliah yang Dipilih ---
if ($selected_course_id) {
    $sql_assignments = "SELECT assignment_id, title, due_date FROM assignments WHERE course_id = ? ORDER BY title ASC"; // Menambahkan due_date
    $stmt_assignments = $conn->prepare($sql_assignments);
    if ($stmt_assignments) {
        $stmt_assignments->execute([$selected_course_id]);
        $assignments_for_dropdown = $stmt_assignments->fetchAll();
    }
}

// --- Fetch Data Nilai Mahasiswa (Perbaikan Utama di sini) ---
if ($selected_course_id && $selected_assignment_id) {
    // Tahap 1: Ambil data dasar mahasiswa di kelas
    $sql_students_base = "
        SELECT u.user_id, u.full_name, u.nim
        FROM users u
        JOIN course_enrollments ce ON u.user_id = ce.student_id
        WHERE ce.course_id = ? AND u.role = 'student'
        ORDER BY u.full_name ASC;
    ";
    $stmt_students_base = $conn->prepare($sql_students_base);
    $students_list = [];
    if ($stmt_students_base) {
        $stmt_students_base->execute([$selected_course_id]);
        $students_list = $stmt_students_base->fetchAll();
    } else {
        error_log("Failed to prepare students base query.");
    }

    // Tahap 2: Ambil semua nilai (grades) untuk siswa-siswa ini di mata kuliah ini
    $grades_map_by_student_component = []; // [student_id => [grade_type => [item_id => [value, feedback]]]]
    if (!empty($students_list)) {
        $student_ids_array = array_column($students_list, 'user_id');
        $placeholders = implode(',', array_fill(0, count($student_ids_array), '?'));

        // Mengambil semua grade yang relevan dalam satu query
        $sql_all_grades = "
            SELECT student_id, grade_type, grade_value, feedback, item_id
            FROM grades
            WHERE student_id IN (" . $placeholders . ") AND course_id = ?
        ";
        $stmt_all_grades = $conn->prepare($sql_all_grades);
        if ($stmt_all_grades) {
            $bind_params = array_merge($student_ids_array, [$selected_course_id]);
            $stmt_all_grades->execute($bind_params);
            while($row_grade = $stmt_all_grades->fetch()) {
                $grades_map_by_student_component[$row_grade['student_id']][$row_grade['grade_type']][$row_grade['item_id']] = [
                    'value' => $row_grade['grade_value'],
                    'feedback' => $row_grade['feedback']
                ];
            }
        } else {
            error_log("Failed to prepare all grades query.");
        }
    }

    // Tahap 3: Ambil status pengumpulan untuk tugas yang dipilih
    $submissions_map_by_student = []; // [student_id => submitted_at]
    if (!empty($students_list)) {
        $student_ids_array = array_column($students_list, 'user_id');
        $placeholders = implode(',', array_fill(0, count($student_ids_array), '?'));

        $sql_submissions = "SELECT student_id, submitted_at FROM submissions WHERE student_id IN (" . $placeholders . ") AND assignment_id = ?";
        $stmt_submissions = $conn->prepare($sql_submissions);
        if ($stmt_submissions) {
            $bind_params = array_merge($student_ids_array, [$selected_assignment_id]);
            $stmt_submissions->execute($bind_params);
            while($row_sub = $stmt_submissions->fetch()) {
                $submissions_map_by_student[$row_sub['student_id']] = $row_sub['submitted_at'];
            }
        } else {
            error_log("Failed to prepare submissions query.");
        }
    }


    // Gabungkan semua data untuk tampilan
    $assignment_due_date = null; // Default
    foreach($assignments_for_dropdown as $assign) {
        if ($assign['assignment_id'] == $selected_assignment_id) {
            $assignment_due_date = $assign['due_date'];
            break;
        }
    }
    if ($assignment_due_date === null) { // Fallback jika due_date tidak ada di DB
        $assignment_due_date = '2025-12-31 23:59:59'; 
    }

    foreach ($students_list as $student) {
        $student_id = $student['user_id'];
        
        $tugas_grade = '';
        $uts_grade = '';
        $uas_grade = '';
        $partisipasi_grade = '';
        $feedback = '';

        // Dapatkan nilai tugas spesifik (item_id = selected_assignment_id)
        $tugas_grade = $grades_map_by_student_component[$student_id]['Assignment'][$selected_assignment_id]['value'] ?? '';
        $feedback = $grades_map_by_student_component[$student_id]['Assignment'][$selected_assignment_id]['feedback'] ?? '';
        
        // Dapatkan nilai UTS (item_id = course_id)
        $uts_grade = $grades_map_by_student_component[$student_id]['UTS'][$selected_course_id]['value'] ?? '';

        // Dapatkan nilai UAS (item_id = course_id)
        $uas_grade = $grades_map_by_student_component[$student_id]['UAS'][$selected_course_id]['value'] ?? '';
        
        // Dapatkan nilai Partisipasi (item_id = course_id)
        $partisipasi_grade = $grades_map_by_student_component[$student_id]['Partisipasi'][$selected_course_id]['value'] ?? '';

        $submitted_at = $submissions_map_by_student[$student_id] ?? null;
        $submission_status_text = getSubmissionStatusTextPHP($submitted_at, $assignment_due_date);
        $submission_status_class = getStatusBadgeClassPHP($submitted_at, $assignment_due_date);

        $students_grades_data[] = [
            'user_id' => $student['user_id'],
            'full_name' => $student['full_name'],
            'nim' => $student['nim'],
            'tugas_grade' => $tugas_grade,
            'uts_grade' => $uts_grade,
            'uas_grade' => $uas_grade,
            'partisipasi_grade' => $partisipasi_grade,
            'feedback' => $feedback, // Feedback khusus tugas
            'submission_status_text' => $submission_status_text,
            'submission_status_class' => $submission_status_class
        ];
    }
}
$conn = null;
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Nilai Dosen</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 20px 0;
        }
        
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            border-radius: 0 0 20px 20px;
            position: relative; /* Menambahkan ini untuk posisi tombol kembali */
        }
        
        /* CSS untuk tombol kembali */
        .back-to-dashboard-btn {
            position: absolute;
            top: 25px;
            left: 25px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 12px;
            padding: 8px 15px;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-to-dashboard-btn:hover {
            background: rgba(255, 255, 255, 0.4);
            color: white;
            transform: translateY(-2px);
        }

        .header-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .header-subtitle {
            text-align: center;
            opacity: 0.9;
            font-size: 16px;
        }
        
        .main-container { 
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .filter-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border: none;
        }
        
        /* PERBAIKAN DI SINI: UBAH WARNA TEKS */
        .filter-title {
            color: #4A90E2; /* Warna biru agar terlihat jelas */
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-select, .form-control { 
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-select:focus, .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-label {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
        }
        
        .grades-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
        }
        
        .grades-title {
            color: #4a5568;
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 25px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .table-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .table { 
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table thead th { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 18px 12px;
            text-align: center;
            vertical-align: middle;
            font-size: 14px;
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background-color: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .table td {
            padding: 16px 12px;
            vertical-align: middle;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .grade-input { 
            width: 80px;
            text-align: center;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .grade-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }
        
        .grade-input.is-invalid { 
            border-color: #e53e3e;
            box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1);
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-graded { 
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
        }
        
        .status-pending { 
            background: linear-gradient(135deg, #ed8936, #dd6b20);
            color: white;
        }
        
        .status-not-submitted { 
            background: linear-gradient(135deg, #e53e3e, #c53030);
            color: white;
        }
        
        .save-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 15px 35px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .save-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .save-button:disabled {
            opacity: 0.7;
            transform: none;
            box-shadow: none;
        }
        
        .info-alert {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            font-size: 16px;
            margin-top: 30px;
        }
        
        .info-alert i {
            font-size: 24px;
            margin-bottom: 10px;
            display: block;
        }
        
        .student-name {
            font-weight: 600;
            color: #2d3748;
        }
        
        .student-nim {
            color: #667eea;
            font-weight: 500;
        }
        
        .feedback-input {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
            min-width: 150px;
            transition: all 0.3s ease;
        }
        
        .feedback-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }
        
        .percentage-label {
            font-size: 12px;
            color: #718096;
            font-weight: 500;
        }
        
        .row-number {
            font-weight: 600;
            color: #667eea;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="main-container">
            <a href="dash-dosen.php" class="back-to-dashboard-btn">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>

            <div class="header-title">
                <i class="fas fa-clipboard-check"></i>
                Input Nilai Mahasiswa
            </div>
            <div class="header-subtitle">
                Kelola dan input nilai mahasiswa untuk semester aktif
            </div>
        </div>
    </div>

    <div class="main-container">
        <div class="filter-card">
            <div class="filter-title">
                <i class="fas fa-filter"></i>
                Filter Data
            </div>
            
            <form id="filterForm" method="GET" action="input_nilai_dosen_crud.php">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="courseSelect" class="form-label">Mata Kuliah:</label>
                        <select class="form-select" id="courseSelect" name="course_id" onchange="this.form.submit()">
                            <option value="">-- Pilih Mata Kuliah --</option>
                            <?php foreach ($courses_for_dropdown as $course): ?>
                                <option value="<?php echo htmlspecialchars($course['course_id']); ?>" <?php echo ($selected_course_id == $course['course_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="assignmentSelect" class="form-label">Tugas:</label>
                        <select class="form-select" id="assignmentSelect" name="assignment_id" onchange="this.form.submit()" <?php echo empty($assignments_for_dropdown) ? 'disabled' : ''; ?>>
                            <option value="">-- Pilih Tugas --</option>
                            <?php foreach ($assignments_for_dropdown as $assignment): ?>
                                <option value="<?php echo htmlspecialchars($assignment['assignment_id']); ?>" <?php echo ($selected_assignment_id == $assignment['assignment_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($assignment['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="semester" value="<?php echo htmlspecialchars($selected_semester); ?>">
                        <input type="hidden" name="academic_year" value="<?php echo htmlspecialchars($selected_academic_year); ?>">
                    </div>
                </div>
            </form>
        </div>

        <?php if ($selected_course_id && $selected_assignment_id): ?>
            <div class="grades-card">
                <div class="grades-title">
                    <i class="fas fa-edit"></i>
                    <?php 
                        $current_assignment = array_filter($assignments_for_dropdown, function($a) use ($selected_assignment_id) {
                            return $a['assignment_id'] == $selected_assignment_id;
                        });
                        $assignment_title = !empty($current_assignment) ? array_values($current_assignment)[0]['title'] : 'Tugas Terpilih';
                        echo htmlspecialchars($assignment_title); 
                    ?>
                </div>
                
                <form id="gradeForm" method="POST" action="save_nilai_crud.php">
                    <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($selected_course_id); ?>">
                    <input type="hidden" name="assignment_id" value="<?php echo htmlspecialchars($selected_assignment_id); ?>">

                    <input type="hidden" name="semester" value="<?php echo htmlspecialchars($selected_semester); ?>">
                    <input type="hidden" name="academic_year" value="<?php echo htmlspecialchars($selected_academic_year); ?>">

                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="width: 5%;">No</th>
                                    <th rowspan="2" style="width: 15%;">NIM</th>
                                    <th rowspan="2" style="width: 20%;">Nama Mahasiswa</th>
                                    <th rowspan="2" style="width: 12%;">Status</th>
                                    <th style="width: 12%;">Tugas</th>
                                    <th style="width: 12%;">UTS</th>
                                    <th style="width: 12%;">UAS</th>
                                    <th style="width: 12%;">Partisipasi</th>
                                    <th rowspan="2" style="width: 20%;">Keterangan</th>
                                </tr>
                                <tr>
                                    <th><span class="percentage-label">(20%)</span></th>
                                    <th><span class="percentage-label">(30%)</span></th>
                                    <th><span class="percentage-label">(40%)</span></th>
                                    <th><span class="percentage-label">(10%)</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($students_grades_data)): ?>
                                    <tr><td colspan="9" class="text-center py-4" style="color: #718096;">Tidak ada mahasiswa untuk tugas ini atau tugas belum dipilih.</td></tr>
                                <?php else: ?>
                                    <?php $no = 1; ?>
                                    <?php foreach ($students_grades_data as $student): ?>
                                        <tr>
                                            <td><span class="row-number"><?php echo htmlspecialchars($no++); ?></span></td>
                                            <td><span class="student-nim"><?php echo htmlspecialchars($student['nim']); ?></span></td>
                                            <td><span class="student-name"><?php echo htmlspecialchars($student['full_name']); ?></span></td>
                                            <td>
                                                <span class="status-badge <?php echo htmlspecialchars($student['submission_status_class']); ?>">
                                                    <?php echo htmlspecialchars($student['submission_status_text']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control grade-input" 
                                                       name="grades[<?php echo htmlspecialchars($student['user_id']); ?>][Assignment]" 
                                                       min="0" max="100" step="0.01" 
                                                       value="<?php echo htmlspecialchars($student['tugas_grade']); ?>" 
                                                       oninput="validateGrade(this);">
                                                <input type="hidden" name="grades[<?php echo htmlspecialchars($student['user_id']); ?>][Assignment_item_id]" value="<?php echo htmlspecialchars($selected_assignment_id); ?>">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control grade-input" 
                                                       name="grades[<?php echo htmlspecialchars($student['user_id']); ?>][UTS]" 
                                                       min="0" max="100" step="0.01" 
                                                       value="<?php echo htmlspecialchars($student['uts_grade']); ?>" 
                                                       oninput="validateGrade(this);">
                                                <input type="hidden" name="grades[<?php echo htmlspecialchars($student['user_id']); ?>][UTS_item_id]" value="<?php echo htmlspecialchars($selected_course_id); ?>">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control grade-input" 
                                                       name="grades[<?php echo htmlspecialchars($student['user_id']); ?>][UAS]" 
                                                       min="0" max="100" step="0.01" 
                                                       value="<?php echo htmlspecialchars($student['uas_grade']); ?>" 
                                                       oninput="validateGrade(this);">
                                                <input type="hidden" name="grades[<?php echo htmlspecialchars($student['user_id']); ?>][UAS_item_id]" value="<?php echo htmlspecialchars($selected_course_id); ?>">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control grade-input" 
                                                       name="grades[<?php echo htmlspecialchars($student['user_id']); ?>][Partisipasi]" 
                                                       min="0" max="100" step="0.01" 
                                                       value="<?php echo htmlspecialchars($student['partisipasi_grade']); ?>" 
                                                       oninput="validateGrade(this);">
                                                <input type="hidden" name="grades[<?php echo htmlspecialchars($student['user_id']); ?>][Partisipasi_item_id]" value="<?php echo htmlspecialchars($selected_course_id); ?>">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control feedback-input" 
                                                       name="grades[<?php echo htmlspecialchars($student['user_id']); ?>][feedback]" 
                                                       value="<?php echo htmlspecialchars($student['feedback'] ?? ''); ?>"
                                                       placeholder="Opsional">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="save-button">
                            <i class="fas fa-save me-2"></i> Simpan Semua Nilai
                        </button>
                    </div>
                </form>
            </div>
        <?php elseif ($selected_course_id): ?>
            <div class="info-alert">
                <i class="fas fa-info-circle"></i>
                Pilih tugas untuk mata kuliah ini
            </div>
        <?php else: ?>
            <div class="info-alert">
                <i class="fas fa-info-circle"></i>
                Pilih mata kuliah dari dropdown di atas
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function validateGrade(inputElement) {
            const value = parseFloat(inputElement.value);
            if (inputElement.value.trim() === '') {
                inputElement.classList.remove('is-invalid');
                return; // Input kosong dianggap valid untuk disimpan sebagai NULL
            }
            if (isNaN(value) || value < 0 || value > 100) {
                inputElement.classList.add('is-invalid');
            } else {
                inputElement.classList.remove('is-invalid');
            }
        }

        document.getElementById('gradeForm')?.addEventListener('submit', function(event) {
            event.preventDefault(); // Mencegah submit form standar

            let hasInvalid = false;
            document.querySelectorAll('.grade-input').forEach(input => {
                if (input.classList.contains('is-invalid')) {
                    hasInvalid = true;
                }
            });

            if (hasInvalid) {
                alert('Terdapat nilai yang tidak valid (ditandai merah). Mohon perbaiki sebelum menyimpan.');
                return;
            }

            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';

            const formData = new FormData(this); // Mengambil semua data form
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errorData => {
                        throw new Error(errorData.message || 'Network response was not ok');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Berhasil disimpan, langsung refresh halaman tanpa alert
                    window.location.reload(); 
                } else {
                    // Jika ada error dari server, tampilkan alert
                    alert('Gagal menyimpan nilai: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan nilai: ' + error.message);
            })
            .finally(() => {
                // Kembalikan tombol ke kondisi semula
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-save me-2"></i> Simpan Semua Nilai';
            });
        });

        // Auto-save individual grades (optional enhancement)
        document.addEventListener('DOMContentLoaded', function() {
            const gradeInputs = document.querySelectorAll('.grade-input');
            gradeInputs.forEach(input => {
                input.addEventListener('blur', function() {
                    // Optional: Auto-save on blur
                    // This can be implemented if needed
                });
            });
        });

        // Keyboard navigation enhancement
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.classList.contains('grade-input')) {
                e.preventDefault();
                const inputs = Array.from(document.querySelectorAll('.grade-input'));
                const currentIndex = inputs.indexOf(e.target);
                const nextIndex = currentIndex + 1;
                
                if (nextIndex < inputs.length) {
                    inputs[nextIndex].focus();
                    inputs[nextIndex].select();
                }
            }
        });

        // Highlight row on input focus
        document.querySelectorAll('.grade-input, .feedback-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('tr').style.backgroundColor = '#f0f4ff';
            });
            
            input.addEventListener('blur', function() {
                this.closest('tr').style.backgroundColor = '';
            });
        });
    </script>
</body>
</html>