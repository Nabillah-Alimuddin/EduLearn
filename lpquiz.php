<?php
ob_start(); // Start output buffering
include 'middleware.php';
include 'db_connection.php';
require_role('student');

$current_student_id = $_SESSION['user_id'];
$available_quizzes = [];

// Fetch quizzes available for the student (e.g., in courses they are enrolled in)
$sql_available_quizzes = "
    SELECT
        q.quiz_id,
        q.title,
        q.description,
        q.duration_minutes,
        q.total_questions,
        q.passing_score,
        q.start_date,
        q.end_date,
        c.course_name
    FROM
        quizzes q
    JOIN
        courses c ON q.course_id = c.course_id
    JOIN
        course_enrollments ce ON q.course_id = ce.course_id
    WHERE
        ce.student_id = ?
    ORDER BY
        q.end_date ASC";

$stmt_available_quizzes = $conn->prepare($sql_available_quizzes);
if ($stmt_available_quizzes) {
    try {
        $stmt_available_quizzes->execute([$current_student_id]);
        while ($row = $stmt_available_quizzes->fetch()) {
            $available_quizzes[] = $row;
        }
    } catch (PDOException $e) {
        error_log("Failed to execute available quizzes statement: " . $e->getMessage());
    }
} else {
    error_log("Failed to prepare available quizzes statement.");
}

$conn = null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Mahasiswa - Portal Pembelajaran</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Desain Ulang Total */
        :root {
            --primary-color: #4F8A9E;
            --secondary-color: #63A3F1;
            --background-start: #B6D0EF;
            --background-end: #4F8A9E;
            --text-light: #f5f5f5;
            --text-dark: #2c3e50;
            --card-bg: rgba(255, 255, 255, 0.15);
            --card-border: rgba(255, 255, 255, 0.3);
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            
            /* Improved color palette */
            --accent-blue: #3B82F6;
            --accent-blue-light: #60A5FA;
            --accent-blue-dark: #1E40AF;
            --accent-teal: #0891B2;
            --accent-teal-light: #06B6D4;
            --accent-cyan: #0EA5E9;
            --accent-emerald: #10B981;
            --accent-purple: #8B5CF6;
            --accent-indigo: #6366F1;
            --success-color: #10B981;
            --warning-color: #F59E0B;
            --danger-color: #EF4444;
        }

        body, html {
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-y: auto;
        }

        .hero-section {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--background-start) 0%, var(--background-end) 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.15"/><circle cx="20" cy="80" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
            z-index: 0;
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) { width: 80px; height: 80px; top: 20%; left: 10%; animation-delay: 0s; }
        .shape:nth-child(2) { width: 120px; height: 120px; top: 60%; right: 15%; animation-delay: 2s; }
        .shape:nth-child(3) { width: 60px; height: 60px; bottom: 20%; left: 20%; animation-delay: 4s; }
        .shape:nth-child(4) { width: 100px; height: 100px; top: 10%; right: 30%; animation-delay: 1s; }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.6; }
            50% { transform: translateY(-20px) rotate(180deg); opacity: 0.8; }
            100% { transform: translateY(0) rotate(360deg); opacity: 0.6; }
        }

        .content-wrapper {
            position: relative;
            z-index: 2;
            max-width: 800px;
            width: 100%;
            height: 100%;
            text-align: center;
            color: var(--text-light);
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .main-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(79, 138, 158, 0.3);
            background: linear-gradient(45deg, #FAFFEE, #B6D0EF);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .quiz-info-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid var(--card-border);
            box-shadow: var(--shadow);
            text-align: left;
            width: 100%;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .quiz-details-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            flex-grow: 1;
            justify-content: center;
        }

        .quiz-item {
            background: rgba(255, 255, 255, 0.25);
            border-radius: 18px;
            padding: 1.2rem;
            transition: all 0.3s ease;
            display: grid;
            grid-template-areas:
                "title status"
                "course-info ."
                "details button";
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 0.7rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .quiz-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.25);
            background: rgba(255, 255, 255, 0.3);
        }

        .quiz-item h4 {
            grid-area: title;
            color: var(--text-light);
            margin: 0;
            font-weight: 600;
            font-size: 1.15rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .quiz-item .course-info {
            grid-area: course-info;
            font-size: 0.9rem;
            color: var(--text-light);
            opacity: 0.85;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .quiz-item .details-grid {
            grid-area: details;
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        /* Enhanced Badge Designs */
        .quiz-item .badge {
            font-size: 0.75rem;
            padding: 0.45em 0.85em;
            border-radius: 12px;
            font-weight: 600;
            letter-spacing: 0.3px;
            border: 1px solid transparent;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        /* Status Badge - Active */
        .quiz-item .badge.bg-success {
            background: linear-gradient(135deg, var(--success-color), var(--accent-emerald)) !important;
            color: white !important;
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* Status Badge - Inactive */
        .quiz-item .badge.bg-danger {
            background: linear-gradient(135deg, var(--danger-color), #DC2626) !important;
            color: white !important;
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* Duration Badge */
        .quiz-item .badge.badge-duration {
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-blue-light)) !important;
            color: white !important;
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* Questions Badge */
        .quiz-item .badge.badge-questions {
            background: linear-gradient(135deg, var(--accent-teal), var(--accent-teal-light)) !important;
            color: white !important;
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* Passing Score Badge */
        .quiz-item .badge.badge-score {
            background: linear-gradient(135deg, var(--warning-color), #FBBF24) !important;
            color: white !important;
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* Badge Hover Effects */
        .quiz-item .badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* Enhanced Start Button */
        .quiz-item .btn-start-single {
            grid-area: button;
            background: linear-gradient(135deg, var(--accent-blue-dark), var(--accent-blue));
            border: none;
            padding: 0.7rem 1.4rem;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 20px;
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
            transition: all 0.3s ease;
            justify-self: end;
            align-self: end;
            border: 1px solid rgba(255, 255, 255, 0.2);
            letter-spacing: 0.3px;
        }
        
        .quiz-item .btn-start-single:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-blue-light));
        }

        .quiz-item .btn-start-single:disabled {
            background: linear-gradient(135deg, #6B7280, #9CA3AF);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            cursor: not-allowed;
            opacity: 0.7;
        }

        .warning-text {
            background: rgba(182, 208, 239, 0.2);
            border: 1px solid rgba(182, 208, 239, 0.5);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1.5rem;
            color: var(--text-light);
            display: none;
        }
        
        .top-right-back-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            position: absolute;
            top: 2rem;
            right: 2rem;
            z-index: 10;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .top-right-back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        @media (max-width: 768px) {
            .main-title { font-size: 2.5rem; }
            .subtitle { font-size: 1.1rem; }
            .quiz-info-card { padding: 1rem; }
            .top-right-back-btn { top: 1rem; right: 1rem; padding: 0.4rem 1rem; font-size: 0.8rem; }
            .quiz-item {
                 grid-template-areas:
                    "title status"
                    "course-info ."
                    "details button";
                 grid-template-columns: 1fr auto;
                 padding: 1rem;
            }
            .quiz-item .btn-start-single {
                 font-size: 0.8rem;
                 padding: 0.6rem 1.1rem;
            }
            .quiz-item .details-grid {
                gap: 0.4rem;
            }
            .quiz-item .badge {
                font-size: 0.7rem;
                padding: 0.35em 0.7em;
            }
        }
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>

        <a href="dash-mahasiswa.php" class="top-right-back-btn" onclick="goToDashboard(event)">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>

        <div class="content-wrapper animate-fade-in">
            <h1 class="main-title">Daftar Kuis Tersedia</h1>
            <p class="subtitle">
                Pilih kuis yang ingin Anda kerjakan. Pastikan Anda memahami informasi kuis sebelum memulai.
            </p>

            <div class="quiz-info-card">
                <h3 class="mb-4 text-center text-secondary">
                    <i class="fas fa-list-alt me-2"></i>
                    Daftar Kuis
                </h3>

                <div class="quiz-details-container">
                    <?php if (empty($available_quizzes)): ?>
                        <div class="text-center text-muted">
                            <i class="fas fa-box-open fa-3x mb-3"></i>
                            <p>Tidak ada kuis yang tersedia saat ini.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($available_quizzes as $quiz): ?>
                            <?php
                                $quiz_id = htmlspecialchars($quiz['quiz_id']);
                                $title = htmlspecialchars($quiz['title']);
                                $description = htmlspecialchars($quiz['description']);
                                $duration_minutes = htmlspecialchars($quiz['duration_minutes']);
                                $total_questions = htmlspecialchars($quiz['total_questions']);
                                $passing_score = htmlspecialchars($quiz['passing_score']);
                                $start_date = new DateTime($quiz['start_date']);
                                $end_date = new DateTime($quiz['end_date']);
                                $course_name = htmlspecialchars($quiz['course_name']);

                                $now = new DateTime();
                                $is_active = ($now >= $start_date && $now <= $end_date);
                                $status_badge_class = $is_active ? 'bg-success' : 'bg-danger';
                                $status_text = $is_active ? 'Aktif' : 'Tidak Aktif';
                                $button_disabled = $is_active ? '' : 'disabled';
                                $button_text = $is_active ? 'Mulai Quiz' : 'Telah Berakhir / Belum Dimulai';
                            ?>
                            <div class="quiz-item animate-fade-in">
                                <h4><?php echo $title; ?></h4>
                                <span class="badge <?php echo $status_badge_class; ?>"><?php echo $status_text; ?></span>
                                <p class="course-info"><i class="fas fa-book me-1"></i>Mata Kuliah: <?php echo $course_name; ?></p>
                                <div class="details-grid">
                                    <span class="badge badge-duration"><i class="fas fa-clock me-1"></i><?php echo $duration_minutes; ?> Menit</span>
                                    <span class="badge badge-questions"><i class="fas fa-question-circle me-1"></i><?php echo $total_questions; ?> Soal</span>
                                    <span class="badge badge-score"><i class="fas fa-star me-1"></i>Lulus: <?php echo $passing_score; ?>/100</span>
                                </div>
                                <button class="btn btn-start-single" <?php echo $button_disabled; ?> onclick="startQuiz(<?php echo $quiz_id; ?>, '<?php echo $title; ?>', '<?php echo $duration_minutes; ?>', '<?php echo $total_questions; ?>')">
                                    <i class="fas fa-play me-2"></i><?php echo $button_text; ?>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-4 text-center">
                <small class="opacity-75">
                    <i class="fas fa-shield-alt me-1"></i>
                    Kuis dilindungi sistem anti-kecurangan
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Start Quiz Function
        function startQuiz(quizId, quizTitle, quizDuration, totalQuestions) {
            // Konfirmasi sebelum memulai
            const confirmStart = confirm(
                `Apakah Anda yakin ingin memulai quiz "${quizTitle}"?\n\n` +
                `⚠️ PERHATIAN:\n` +
                `• Quiz ini berdurasi ${quizDuration} menit.\n` +
                `• Terdiri dari ${totalQuestions} pertanyaan.\n` +
                `• Quiz hanya dapat dikerjakan SATU KALI.\n` +
                `• Pastikan koneksi internet stabil dan siapkan waktu yang cukup.\n` +
                `• Jangan refresh atau tutup browser selama quiz.\n\n` +
                'Klik OK untuk memulai quiz.'
            );

            if (confirmStart) {
                // Redirect ke halaman quiz.php dengan quiz_id sebagai parameter
                window.location.href = 'quiz.php?quiz_id=' + quizId;
            }
        }

        // Fungsi untuk kembali ke dashboard mahasiswa (dengan event parameter untuk preventDefault)
        function goToDashboard(event) {
            // Jika dipanggil dari <a> tag, cegah perilaku default untuk mengontrol navigasi sepenuhnya
            if (event) {
                event.preventDefault();
            }
            window.location.href = 'dash-mahasiswa.php';
        }

        // Mencegah klik kanan dan seleksi teks untuk keamanan kuis
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });

        document.addEventListener('selectstart', function(e) {
            e.preventDefault();
        });
    </script>
</body>
</html>