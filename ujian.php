<?php
include 'middleware.php';
include 'db_connection.php';
require_role('student');

// --- Handle File Submission (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_type']) && $_POST['submit_type'] === 'file_submission') {
    $exam_id = (int)$_POST['exam_id'] ?? null;
    $student_id = (int)$_POST['student_id'] ?? null;
    $submission_text = $_POST['submission_text'] ?? null; // untuk jawaban esai jika ada
    
    if (!$exam_id || !$student_id) {
        echo "<script>alert('Gagal menyimpan: Data ujian tidak lengkap.');</script>";
        header("Location: ujian.php");
        exit();
    }
    
    $file_path = null;
    $has_file = isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK;
    
    if ($has_file) {
        $target_dir = "uploads/submissions/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES['submission_file']['name'], PATHINFO_EXTENSION);
        $unique_file_name = uniqid($student_id . '_' . $exam_id . '_', true) . '.' . $file_extension;
        $target_file = $target_dir . $unique_file_name;
        
        if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $target_file)) {
            $file_path = $target_file;
        } else {
            echo "<script>alert('Gagal mengunggah file.');</script>";
            header("Location: ujian.php");
            exit();
        }
    }
    
    // Simpan data pengumpulan ke tabel 'submissions'
    $sql_insert_submission = "INSERT INTO submissions (assignment_id, student_id, submission_file_path, submission_text) VALUES (?, ?, ?, ?)";
    $stmt_insert_submission = $conn->prepare($sql_insert_submission);
    if ($stmt_insert_submission) {
        if ($stmt_insert_submission->execute([$exam_id, $student_id, $file_path, $submission_text])) {
            echo "<script>alert('Pengumpulan berhasil!');</script>";
        } else {
            echo "<script>alert('Gagal menyimpan pengumpulan ke database.');</script>";
        }
    }

    // Update status ujian di tabel `exam_attempts`
    $sql_update_attempt = "UPDATE exam_attempts SET is_completed = TRUE WHERE exam_id = ? AND student_id = ?";
    $stmt_update_attempt = $conn->prepare($sql_update_attempt);
    if ($stmt_update_attempt) {
        $stmt_update_attempt->execute([$exam_id, $student_id]);
    }

    header("Location: ujian.php");
    exit();
}

$current_student_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Jadwal Ujian - Universitas</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS yang sudah ada */
        :root {
            --primary-blue: #B6D0EF;
            --secondary-blue: #63A3F1;
            --light-green: #FAFFEE;
            --dark-teal: #4F8A9E;
            --white: #FFFFFF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            color: var(--dark-teal) !important;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .container-fluid {
            padding: 0;
        }

        .main-content {
            min-height: calc(100vh - 70px);
            padding: 2rem 0;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .card-header {
            background: var(--dark-teal);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .schedule-table {
            background: var(--white);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: var(--light-green);
            color: var(--dark-teal);
            border: none;
            font-weight: 600;
            text-align: center;
            padding: 1rem;
        }

        .table tbody td {
            padding: 1rem;
            text-align: center;
            vertical-align: middle;
            border-color: rgba(79, 138, 158, 0.1);
        }

        .online-badge {
            background: var(--secondary-blue);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
            margin-top: 0.5rem;
        }

        .online-badge:hover {
            background: var(--dark-teal);
            transform: scale(1.05);
        }

        .subject-name {
            font-weight: 600;
            color: var(--dark-teal);
            margin-bottom: 0.5rem;
        }

        .exam-time-display { /* Ubah nama class untuk menghindari konflik */
            color: #666;
            font-size: 0.9rem;
        }

        .dashboard-view { /* Ubah nama class untuk menghindari konflik */
            background: var(--white);
            border-radius: 15px;
            display: none;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dashboard-header-inner { /* Ubah nama class untuk menghindari konflik */
            background: linear-gradient(135deg, var(--secondary-blue), var(--dark-teal));
            color: white;
            padding: 2rem;
            border-radius: 15px 15px 0 0;
        }

        .question-card {
            background: var(--light-green);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--secondary-blue);
        }

        .question-number {
            background: var(--secondary-blue);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .form-check-input:checked {
            background-color: var(--secondary-blue);
            border-color: var(--secondary-blue);
        }

        .btn-primary {
            background: var(--secondary-blue);
            border-color: var(--secondary-blue);
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--dark-teal);
            border-color: var(--dark-teal);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--dark-teal);
            border-color: var(--dark-teal);
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: var(--secondary-blue);
            border-color: var(--secondary-blue);
            transform: translateY(-2px);
        }

        .progress {
            height: 10px;
            background: rgba(182, 208, 239, 0.3);
            border-radius: 5px;
        }

        .progress-bar {
            background: var(--secondary-blue);
            border-radius: 5-px;
        }

        .exam-info {
            background: rgba(99, 163, 241, 0.1);
            border: 1px solid var(--secondary-blue);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .timer {
            background: var(--dark-teal);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
        }

        .nav-tabs .nav-link {
            border: none;
            color: var(--dark-teal);
            font-weight: 600;
            padding: 1rem 2rem;
            margin-right: 0.5rem;
            border-radius: 25px 25px 0 0;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link.active {
            background: var(--secondary-blue);
            color: white;
        }

        .nav-tabs .nav-link:hover {
            background: var(--primary-blue);
            color: var(--dark-teal);
        }

        .alert-success {
            background: var(--light-green);
            border-color: var(--secondary-blue);
            color: var(--dark-teal);
            border-radius: 10px;
        }

        .footer-info {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1rem;
            border-radius: 10px;
            margin-top: 2rem;
            text-align: center;
            color: var(--dark-teal);
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6C757D;
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--primary-blue);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap me-2"></i>
                Sistem Ujian Universitas
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="#" onclick="showSchedule()">
                    <i class="fas fa-calendar-alt me-1"></i>
                    Jadwal Ujian
                </a>
                <a class="nav-link" href="#" id="userInfo">
                    <i class="fas fa-user me-1"></i>
                    Mahasiswa
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid main-content" style="margin-top: 70px;">
        <div id="scheduleView" class="container">
            <a href="dash-mahasiswa.php" class="btn btn-primary mb-3">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
            </a>
            <div class="row justify-content-center">
                <div class="col-12">
                    <h1 class="text-center mb-4" style="color: var(--dark-teal); font-weight: bold;">
                        <i class="fas fa-calendar-check me-3"></i>
                        Jadwal Ujian Tengah Semester & Ujian Akhir Semester
                    </h1>
                </div>
            </div>

            <ul class="nav nav-tabs justify-content-center mb-4">
                <li class="nav-item">
                    <button class="nav-link active" data-exam-type="UTS">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Ujian Tengah Semester (UTS)
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-exam-type="UAS">
                        <i class="fas fa-file-alt me-2"></i>
                        Ujian Akhir Semester (UAS)
                    </button>
                </li>
            </ul>
            
            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" id="showAllExamsToggle" onchange="displayExams(document.querySelector('.nav-tabs .nav-link.active').dataset.examType)">
                <label class="form-check-label" for="showAllExamsToggle" style="color: var(--dark-teal);">Tampilkan Semua Jadwal (termasuk yang sudah lewat)</label>
            </div>

            <div id="utsScheduleContainer" class="row">
                <div class="col-12">
                    <div class="card schedule-table">
                        <div class="card-header">
                            <i class="fas fa-calendar-week me-2"></i>
                            Jadwal Ujian Tengah Semester - <span id="utsSemesterInfo"></span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Hari & Tanggal</th>
                                            <th>Mata Kuliah</th>
                                            <th>Waktu</th>
                                            <th>Ruangan/Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="utsTableBody">
                                        <tr><td colspan="4" class="text-center text-muted"><i class="fas fa-spinner fa-spin me-2"></i> Memuat jadwal...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="emptyUtsState" class="empty-state" style="display:none;">
                        <i class="fas fa-calendar-times"></i>
                        <h4>Tidak ada jadwal UTS ditemukan</h4>
                        <p>Belum ada ujian tengah semester yang tersedia.</p>
                    </div>
                </div>
            </div>

            <div id="uasScheduleContainer" class="row" style="display: none;">
                <div class="col-12">
                    <div class="card schedule-table">
                        <div class="card-header">
                            <i class="fas fa-calendar-week me-2"></i>
                            Jadwal Ujian Akhir Semester - <span id="uasSemesterInfo"></span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Hari & Tanggal</th>
                                            <th>Mata Kuliah</th>
                                            <th>Waktu</th>
                                            <th>Ruangan/Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="uasTableBody">
                                        <tr><td colspan="4" class="text-center text-muted"><i class="fas fa-spinner fa-spin me-2"></i> Memuat jadwal...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="emptyUasState" class="empty-state" style="display:none;">
                        <i class="fas fa-calendar-times"></i>
                        <h4>Tidak ada jadwal UAS ditemukan</h4>
                        <p>Belum ada ujian akhir semester yang tersedia.</p>
                    </div>
                </div>
            </div>

            
        </div>

        <div id="examDashboard" class="container dashboard-view">
            <div class="row">
                <div class="col-12">
                    <div class="dashboard-header-inner">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 id="examTitle">Dashboard Ujian Online</h2>
                                <p id="examSubject" class="mb-0">Mata Kuliah: -</p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <a href="dash-mahasiswa.php" class="btn btn-light">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="exam-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5><i class="fas fa-info-circle me-2"></i>Informasi Ujian</h5>
                                    <p><strong>Durasi:</strong> <span id="examDuration"></span></p>
                                    <p><strong>Jumlah Soal:</strong> <span id="totalQuestions"></span></p>
                                    <p><strong>Tipe:</strong> <span id="examType"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <div class="timer">
                                        <div><i class="fas fa-clock me-2"></i>Sisa Waktu</div>
                                        <div id="countdown">00:00:00</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6>Progress Pengerjaan</h6>
                                <span id="progressText">0/0 soal</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                            </div>
                        </div>

                        <form id="examForm" method="POST" action="ujian.php" enctype="multipart/form-data">
                             <input type="hidden" name="exam_id" id="examIdInput">
                             <input type="hidden" name="student_id" id="studentIdInput">
                             <input type="hidden" name="submit_type" value="file_submission">
                             
                            <div id="questionsContainer">
                                <div class="alert alert-info text-center" role="alert">
                                    <i class="fas fa-spinner fa-spin me-2"></i> Memuat soal ujian...
                                </div>
                            </div>

                            <hr>
                            <h5><i class="fas fa-upload me-2"></i>Unggah Hasil Ujian</h5>
                            <p class="text-muted">Untuk ujian esai atau kode, silakan unggah file jawaban Anda di sini.</p>
                            <div class="mb-3">
                                <label for="submissionFile" class="form-label">Pilih File</label>
                                <input class="form-control" type="file" id="submissionFile" name="submission_file">
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Ujian
                                </button>
                            </div>
                        </form>

                        <div id="submitResult" class="alert alert-success mt-4" style="display: none;">
                            <h5><i class="fas fa-check-circle me-2"></i>Ujian Berhasil Disubmit!</h5>
                            <p>Terima kasih telah mengerjakan ujian. Hasil akan diumumkan setelah periode koreksi selesai.</p>
                            <p><strong>Detail Submission:</strong></p>
                            <ul>
                                <li>Waktu Submit: <span id="submitTime"></span></li>
                                <li>Total Soal Terjawab: <span id="answeredCount">0</span>/0</li>
                                <li>Status: Berhasil Tersimpan</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        const currentStudentId = <?php echo json_encode($current_student_id); ?>;
        const API_BASE_URL = 'api_exam.php';
        let allExams = [];
        let examTimer;
        let timeRemaining = 0;
        let currentExamDetails = null;
        let currentQuestions = [];
        let examIntervals = {};

        // --- Fetching Data ---
        async function fetchAllExams() {
            try {
                const response = await fetch(`${API_BASE_URL}?action=get_all_exams`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                if (data.error) {
                    console.error("API Error:", data.error);
                    return [];
                }
                // Perbaikan: Ambil array 'exams' dari objek JSON
                return data.exams;
            } catch (error) {
                console.error("Error fetching exams:", error);
                return [];
            }
        }

        async function displayExams(examType) {
            const utsTableBody = document.getElementById('utsTableBody');
            const uasTableBody = document.getElementById('uasTableBody');
            const emptyUtsState = document.getElementById('emptyUtsState');
            const emptyUasState = document.getElementById('emptyUasState');
            const showAllExams = document.getElementById('showAllExamsToggle').checked;
            
            emptyUtsState.style.display = 'none';
            emptyUasState.style.display = 'none';
            
            let tableBodyToFill = examType === 'UTS' ? utsTableBody : uasTableBody;
            tableBodyToFill.innerHTML = '<tr><td colspan="4" class="text-center text-muted"><i class="fas fa-spinner fa-spin me-2"></i> Memuat jadwal...</td></tr>';
            
            if (allExams.length === 0) {
                allExams = await fetchAllExams();
            }

            const now = new Date();
            const filteredExams = allExams.filter(exam => {
                const examEndDateTime = new Date(`${exam.exam_date}T${exam.end_time}`);
                // Tampilkan semua jika toggle aktif, jika tidak, hanya yang belum selesai
                return exam.exam_type === examType && (showAllExams || examEndDateTime >= now);
            });

            // Hentikan interval lama jika ada
            if (examIntervals[examType]) {
                clearInterval(examIntervals[examType]);
            }
            
            tableBodyToFill.innerHTML = '';
            
            if (filteredExams.length > 0) {
                const renderRows = () => {
                    tableBodyToFill.innerHTML = '';
                    filteredExams.forEach(exam => {
                        const row = document.createElement('tr');
                        const examDate = new Date(exam.exam_date);
                        const dayName = examDate.toLocaleDateString('id-ID', { weekday: 'long' });
                        const dateFormatted = examDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    
                        const currentDateTime = new Date();
                        const examStartDateTime = new Date(`${exam.exam_date}T${exam.start_time}`);
                        const examEndDateTime = new Date(`${exam.exam_date}T${exam.end_time}`);
    
                        let actionButtonHtml = '';
                        let onlineBadgeHtml = '';
    
                        const isOnlineAndHasQuestions = exam.is_online == 1 && exam.quiz_id !== null;
                        const isCompletedByStudent = exam.student_completed_exam == 1;
    
                        if (isOnlineAndHasQuestions) {
                            onlineBadgeHtml = `<span class="online-badge"><i class="fas fa-laptop me-1"></i>Ujian Online</span>`;
                            if (isCompletedByStudent) {
                                actionButtonHtml = `<button class="btn btn-sm btn-success" disabled title="Anda sudah menyelesaikan ujian ini"><i class="fas fa-check me-1"></i>Selesai</button>`;
                            } else if (currentDateTime >= examStartDateTime && currentDateTime <= examEndDateTime) {
                                actionButtonHtml = `<button class="btn btn-sm btn-primary" onclick="openExamDashboard(${exam.exam_id}, '${escapeHtml(exam.title)}', '${escapeHtml(exam.course_name)}', '${escapeHtml(exam.exam_type)}', ${exam.duration_minutes}, ${exam.total_questions || 0}, '${escapeHtml(exam.online_link || '')}')"><i class="fas fa-play me-1"></i>Mulai Ujian</button>`;
                            } else if (currentDateTime < examStartDateTime) {
                                actionButtonHtml = `<button class="btn btn-sm btn-info" disabled title="Ujian belum dimulai"><i class="fas fa-hourglass-start me-1"></i>Akan Datang</button>`;
                            } else {
                                actionButtonHtml = `<button class="btn btn-sm btn-danger" disabled title="Ujian sudah berakhir">Terlewat</button>`;
                            }
                        } else {
                            actionButtonHtml = `<button class="btn btn-sm btn-info" disabled title="Ujian di Ruangan"><i class="fas fa-door-open me-1"></i>${exam.room || 'Offline'}</button>`;
                        }
                    
                        row.innerHTML = `
                            <td><strong>${dayName}</strong><br>${dateFormatted}</td>
                            <td>
                                <div class="subject-name">${escapeHtml(exam.course_name)}</div>
                                <div class="exam-time-display">${escapeHtml(exam.title || '-')}</div>
                                ${onlineBadgeHtml}
                            </td>
                            <td>${exam.start_time.substring(0,5)} - ${exam.end_time.substring(0,5)}</td>
                            <td>${actionButtonHtml}</td>
                        `;
                        tableBodyToFill.appendChild(row);
                    });
                };
                
                renderRows();
                examIntervals[examType] = setInterval(renderRows, 1000); // Perbarui setiap detik
            } else {
                tableBodyToFill.innerHTML = `<tr><td colspan="4" class="text-center text-muted">Tidak ada jadwal ${examType} yang akan datang atau sedang berlangsung.</td></tr>`;
                if (examType === 'UTS') {
                    emptyUtsState.style.display = 'block';
                } else {
                    emptyUasState.style.display = 'block';
                }
            }
        }

        // --- Navigation functions ---
        function showSchedule() {
            document.getElementById('scheduleView').style.display = 'block';
            document.getElementById('examDashboard').style.display = 'none';
            if (examTimer) {
                clearInterval(examTimer);
                document.getElementById('countdown').textContent = '00:00:00';
            }
            const activeType = document.querySelector('.nav-tabs .nav-link.active').dataset.examType;
            displayExams(activeType);
        }

        function showUTS() {
            document.getElementById('utsScheduleContainer').style.display = 'block';
            document.getElementById('uasScheduleContainer').style.display = 'none';

            document.querySelector('.nav-link[data-exam-type="UTS"]').classList.add('active');
            document.querySelector('.nav-link[data-exam-type="UAS"]').classList.remove('active');

            displayExams('UTS');
        }

        function showUAS() {
            document.getElementById('utsScheduleContainer').style.display = 'none';
            document.getElementById('uasScheduleContainer').style.display = 'block';

            document.querySelector('.nav-link[data-exam-type="UAS"]').classList.add('active');
            document.querySelector('.nav-link[data-exam-type="UTS"]').classList.remove('active');

            displayExams('UAS');
        }

        // --- Exam Dashboard Functions ---
        async function openExamDashboard(examId, title, subjectName, examType, durationMinutes, totalQuestions, onlineLink) {
            currentExamDetails = {
                exam_id: examId,
                title: title,
                subject_name: subjectName,
                exam_type: examType,
                duration_minutes: durationMinutes,
                total_questions: totalQuestions,
                online_link: onlineLink
            };

            document.getElementById('examTitle').textContent = `Dashboard Ujian ${examType} Online`;
            document.getElementById('examSubject').textContent = `Mata Kuliah: ${subjectName}`;
            document.getElementById('examDuration').textContent = `${durationMinutes} menit`;
            document.getElementById('totalQuestions').textContent = `${totalQuestions || 'Belum Ditentukan'} soal`;
            document.getElementById('examType').textContent = examType;

            document.getElementById('submitResult').style.display = 'none';
            document.getElementById('scheduleView').style.display = 'none';
            document.getElementById('examDashboard').style.display = 'block';

            document.getElementById('examForm').reset();
            document.getElementById('questionsContainer').innerHTML = `<div class="alert alert-info text-center" role="alert"><i class="fas fa-spinner fa-spin me-2"></i> Memuat soal ujian...</div>`;
            document.getElementById('examIdInput').value = examId;
            document.getElementById('studentIdInput').value = currentStudentId;

            await fetchAndRenderQuestions(examId);

            timeRemaining = durationMinutes * 60;
            startExamTimer();
            updateProgress();
        }

        async function fetchAndRenderQuestions(examId) {
            const questionsContainer = document.getElementById('questionsContainer');
            questionsContainer.innerHTML = `<div class="alert alert-info text-center" role="alert"><i class="fas fa-spinner fa-spin me-2"></i> Memuat soal ujian...</div>`;

            try {
                const response = await fetch(`${API_BASE_URL}?action=get_exam_questions&exam_id=${examId}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();

                if (data.error) {
                    questionsContainer.innerHTML = `<div class="alert alert-danger text-center" role="alert"><i class="fas fa-exclamation-circle me-2"></i> Gagal memuat soal: ${data.error}</div>`;
                    currentQuestions = [];
                    document.getElementById('totalQuestions').textContent = '0 soal';
                    updateProgress();
                    return;
                }

                currentQuestions = data.questions;
                document.getElementById('totalQuestions').textContent = `${currentQuestions.length} soal`;

                questionsContainer.innerHTML = '';

                if (currentQuestions.length === 0) {
                     questionsContainer.innerHTML = `<div class="alert alert-warning text-center" role="alert"><i class="fas fa-exclamation-triangle me-2"></i> Tidak ada soal ditemukan untuk ujian ini.</div>`;
                } else {
                    currentQuestions.forEach((q, index) => {
                        const questionCard = document.createElement('div');
                        questionCard.className = 'question-card';
                        let optionsHtml = '';

                        if (q.question_type === 'multiple_choice' && q.options && q.options.length > 0) {
                            q.options.forEach(option => {
                                optionsHtml += `<div class="form-check"><input class="form-check-input" type="radio" name="question_${q.question_id}" id="option_${option.option_id}" value="${option.option_id}" onchange="updateProgress()"><label class="form-check-label" for="option_${option.option_id}">${escapeHtml(option.option_text)}</label></div>`;
                            });
                        } else if (q.question_type === 'essay') {
                            optionsHtml += `<div class="mb-3"><label for="essay_${q.question_id}" class="form-label">Jawaban Anda:</label><textarea class="form-control" id="essay_${q.question_id}" name="question_${q.question_id}" rows="5" onchange="updateProgress()"></textarea></div>`;
                        } else if (q.question_type === 'code') {
                             optionsHtml += `<div class="mb-3"><label for="code_${q.question_id}" class="form-label">Tulis kode Anda di sini:</label><textarea class="form-control font-monospace" id="code_${q.question_id}" name="question_${q.question_id}" rows="10" onchange="updateProgress()"></textarea></div>`;
                        }

                        questionCard.innerHTML = `<div class="question-number">${index + 1}</div><p><strong>${escapeHtml(q.question_text)}</strong></p>${q.question_formula ? `<pre class="bg-light p-3 rounded">${escapeHtml(q.question_formula)}</pre>` : ''}<div class="options mt-3">${optionsHtml}</div>`;
                        questionsContainer.appendChild(questionCard);
                    });
                }
                updateProgress();
            } catch (error) {
                console.error("Error fetching or rendering questions:", error);
                questionsContainer.innerHTML = `<div class="alert alert-danger text-center" role="alert"><i class="fas fa-exclamation-circle me-2"></i> Terjadi kesalahan saat memuat soal ujian.</div>`;
                currentQuestions = [];
                document.getElementById('totalQuestions').textContent = '0 soal';
                updateProgress();
            }
        }

        function startExamTimer() {
            if (examTimer) {
                clearInterval(examTimer);
            }

            const countdownElement = document.getElementById('countdown');
            examTimer = setInterval(function() {
                timeRemaining--;

                if (timeRemaining <= 0) {
                    clearInterval(examTimer);
                    autoSubmitExam();
                    return;
                }

                const hours = Math.floor(timeRemaining / 3600);
                const minutes = Math.floor((timeRemaining % 3600) / 60);
                const seconds = timeRemaining % 60;

                const timeString = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                countdownElement.textContent = timeString;

                const timerContainer = countdownElement.closest('.timer');
                if (timeRemaining <= 300) {
                    timerContainer.style.background = '#dc3545';
                } else if (timeRemaining <= 600) {
                    timerContainer.style.background = '#fd7e14';
                } else {
                    timerContainer.style.background = 'var(--dark-teal)';
                }
            }, 1000);
        }

        function updateProgress() {
            const form = document.getElementById('examForm');
            let answeredCount = 0;
            currentQuestions.forEach(q => {
                if (q.question_type === 'multiple_choice') {
                    const selectedOption = form.querySelector(`input[name="question_${q.question_id}"]:checked`);
                    if (selectedOption) { answeredCount++; }
                } else if (q.question_type === 'essay' || q.question_type === 'code') {
                    const textarea = form.querySelector(`textarea[name="question_${q.question_id}"]`);
                    if (textarea && textarea.value.trim() !== '') { answeredCount++; }
                }
            });

            const total = currentQuestions.length;
            const percentage = total > 0 ? (answeredCount / total) * 100 : 0;
            document.getElementById('progressBar').style.width = percentage + '%';
            document.getElementById('progressText').textContent = `${answeredCount}/${total} soal`;
            document.getElementById('answeredCount').textContent = answeredCount;
        }

        function collectAnswers() {
            const form = document.getElementById('examForm');
            const answers = [];
            currentQuestions.forEach(q => {
                let answer = {
                    question_id: q.question_id,
                    selected_option_id: null,
                    essay_answer: null
                };
                if (q.question_type === 'multiple_choice') {
                    const selectedOption = form.querySelector(`input[name="question_${q.question_id}"]:checked`);
                    if (selectedOption) { answer.selected_option_id = selectedOption.value; }
                } else if (q.question_type === 'essay' || q.question_type === 'code') {
                    const textarea = form.querySelector(`textarea[name="question_${q.question_id}"]`);
                    if (textarea) { answer.essay_answer = textarea.value.trim(); }
                }
                answers.push(answer);
            });
            return answers;
        }

        async function saveAnswers(isFinalSubmission = false) {
            if (!currentExamDetails) return;
            const saveButton = document.querySelector('.btn-secondary[onclick="saveAnswers()"]');
            const submitButton = document.querySelector('.btn-primary[onclick="submitExam()"]');
            const originalSaveText = saveButton.innerHTML;
            const originalSubmitText = submitButton.innerHTML;

            saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
            saveButton.disabled = true;
            submitButton.disabled = true;

            const answers = collectAnswers();
            const payload = {
                action: 'save_exam_attempt',
                exam_id: currentExamDetails.exam_id,
                student_id: currentStudentId,
                is_completed: isFinalSubmission ? 1 : 0,
                answers: answers
            };

            try {
                const response = await fetch(API_BASE_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();

                if (result.success) {
                    if (isFinalSubmission) {
                        saveButton.innerHTML = originalSaveText;
                    } else {
                        saveButton.innerHTML = '<i class="fas fa-check me-2"></i>Tersimpan!';
                        saveButton.classList.remove('btn-secondary');
                        saveButton.classList.add('btn-success');
                    }
                    console.log("Answers saved successfully!", result);
                } else {
                    alert('Gagal menyimpan jawaban: ' + result.error);
                    saveButton.innerHTML = '<i class="fas fa-times me-2"></i>Gagal Simpan';
                    saveButton.classList.remove('btn-secondary');
                    saveButton.classList.add('btn-danger');
                    console.error("Failed to save answers:", result.error);
                }
            } catch (error) {
                alert('Terjadi kesalahan jaringan atau server saat menyimpan jawaban: ' + error.message);
                saveButton.innerHTML = '<i class="fas fa-times me-2"></i>Error Simpan';
                saveButton.classList.remove('btn-secondary');
                saveButton.classList.add('btn-danger');
                console.error("Network or server error during save:", error);
            } finally {
                if (!isFinalSubmission) {
                    setTimeout(() => {
                        saveButton.innerHTML = originalSaveText;
                        saveButton.classList.remove('btn-success', 'btn-danger');
                        saveButton.classList.add('btn-secondary');
                        saveButton.disabled = false;
                    }, 2000);
                }
                submitButton.disabled = false;
            }
        }

        async function submitExam() {
            if (!confirm('Apakah Anda yakin ingin mengsubmit ujian? Jawaban tidak dapat diubah setelah disubmit.')) {
                return;
            }

            if (!currentExamDetails) {
                alert("Tidak ada ujian yang sedang berlangsung.");
                return;
            }

            if (examTimer) {
                clearInterval(examTimer);
            }

            const submitButton = event.target;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...';
            submitButton.disabled = true;
            document.querySelector('.btn-secondary[onclick="saveAnswers()"]').disabled = true;

            await saveAnswers(true);

            const resultDiv = document.getElementById('submitResult');
            const now = new Date();
            document.getElementById('submitTime').textContent = now.toLocaleString('id-ID');
            resultDiv.style.display = 'block';
            resultDiv.scrollIntoView({ behavior: 'smooth' });

            const formInputs = document.querySelectorAll('#examForm input, #examForm textarea, #examForm button');
            formInputs.forEach(input => input.disabled = true);

            submitButton.innerHTML = '<i class="fas fa-check me-2"></i>Sudah Disubmit';
            submitButton.classList.remove('btn-primary');
            submitButton.classList.add('btn-success');
            alert('Ujian berhasil disubmit!');
        }

        function autoSubmitExam() {
            alert('Waktu ujian telah habis. Ujian akan disubmit otomatis.');
            submitExam();
        }

        function escapeHtml(text) {
            const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.nav-tabs .nav-link').forEach(button => {
                button.addEventListener('click', function() {
                    const examType = this.dataset.examType;
                    if (examType === 'UTS') {
                        showUTS();
                    } else if (examType === 'UAS') {
                        showUAS();
                    }
                });
            });

            showUTS();

            window.addEventListener('beforeunload', function(e) {
                if (document.getElementById('examDashboard').style.display !== 'none' && timeRemaining > 0) {
                    e.preventDefault();
                    e.returnValue = 'Ujian sedang berlangsung. Yakin ingin meninggalkan halaman?';
                    return e.returnValue;
                }
            });

            let autoSaveTimer;
            const observer = new MutationObserver(mutations => {
                mutations.forEach(mutation => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                        if (document.getElementById('examDashboard').style.display !== 'none' && timeRemaining > 0) {
                            if (!autoSaveTimer) {
                                autoSaveTimer = setInterval(() => saveAnswers(false), 60000);
                            }
                        } else {
                            if (autoSaveTimer) {
                                clearInterval(autoSaveTimer);
                                autoSaveTimer = null;
                            }
                        }
                    }
                });
            });

            observer.observe(document.getElementById('examDashboard'), { attributes: true, attributeFilter: ['style'] });
        });
    </script>
</body>
</html>