<?php
session_start();
include 'db_connection.php';

// Periksa apakah pengguna sudah login dan memiliki peran 'student'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.html");
    exit();
}

$current_student_id = $_SESSION['user_id'];

// Ambil data pengumuman yang relevan untuk mahasiswa
$announcements_data = [];

$sql = "
    SELECT
        a.announcement_id,
        a.title,
        a.content,
        a.published_at,
        a.lecturer_id,
        a.course_id,
        c.course_name,
        u.full_name AS lecturer_full_name
    FROM
        announcements a
    LEFT JOIN
        courses c ON a.course_id = c.course_id
    LEFT JOIN
        users u ON a.lecturer_id = u.user_id
    WHERE
        (a.course_id IN (
            SELECT course_id FROM course_enrollments WHERE student_id = ?
        ) OR a.course_id IS NULL)
    ORDER BY
        a.published_at DESC;
";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->execute([$current_student_id]);
    while ($row = $stmt->fetch()) {
        $announcements_data[] = $row;
    }
}

$conn = null;

// --- Fungsi Helper untuk PHP ---
function formatDate($dateString) {
    return date('d F Y H:i', strtotime($dateString));
}

function escapeHtml($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function nl2brReplace($str) {
    return str_replace(["\r\n", "\r", "\n"], "<br>", escapeHtml($str));
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman - Dashboard Mahasiswa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS yang sudah ada */
        :root {
            --primary-color: #7FB3D3;
            --secondary-color: #B8D4E3;
            --accent-color: #E8F4F8;
            --white: #FFFFFF;
            --light-gray: #F8F9FA;
            --text-dark: #2C3E50;
            --shadow: 0 4px 12px rgba(127, 179, 211, 0.15);
            --danger: #E74C3C;
            --warning: #F39C12;
            --success: #27AE60;
        }

        body {
            background: linear-gradient(135deg, #E8F4F8 0%, #B8D4E3 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem 0;
            box-shadow: var(--shadow);
            border-radius: 0 0 20px 20px;
            margin-bottom: 2rem;
        }

        .profile-section {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .profile-pic {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary-color);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
        }

        .announcement-card {
            background: var(--white);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .announcement-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(127, 179, 211, 0.25);
        }

        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .mata-kuliah-badge {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .announcement-title {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .announcement-description {
            color: #6C757D;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .announcement-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .announcement-date {
            display: flex;
            align-items: center;
            font-weight: 600;
            color: var(--text-dark);
        }

        .announcement-date i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .announcement-dosen {
            display: flex;
            align-items: center;
            font-weight: 600;
            color: var(--text-dark);
        }

        .announcement-dosen i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        
        /* Menghapus CSS Filter */
        .filter-section, .filter-title, .filter-buttons, .filter-btn {
            display: none;
        }

        .section-title {
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6C757D;
            display: none; /* Akan diatur oleh PHP */
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                margin-bottom: 1rem;
            }
            
            .announcement-info {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="dashboard-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="profile-section">
                            <div class="d-flex align-items-center">
                                <div class="profile-pic me-3">
                                    <i class="fas fa-bullhorn"></i>
                                </div>
                                <div>
                                    <h5 class="welcome-text m-0">Pengumuman</h5>
                                    <p class="student-id m-0">Informasi terbaru dari dosen</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="dash-mahasiswa.php" class="back-btn">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h3 class="section-title fade-in">
                        <i class="fas fa-bullhorn me-2"></i>Daftar Pengumuman
                    </h3>
                </div>
            </div>

            <div id="announcementContainer" class="row">
                <?php if (empty($announcements_data)): ?>
                    <div class="col-12">
                        <div id="emptyState" class="empty-state" style="display: block;">
                            <i class="fas fa-clipboard-list"></i>
                            <h4>Tidak ada pengumuman ditemukan</h4>
                            <p>Tidak ada pengumuman yang sesuai dengan filter yang dipilih.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($announcements_data as $announcement): ?>
                        <div class="col-12 fade-in">
                            <div class="announcement-card">
                                <div class="announcement-header">
                                    <span class="mata-kuliah-badge"><?php echo htmlspecialchars($announcement['course_name'] ?? 'Umum'); ?></span>
                                </div>
                                <h4 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h4>
                                <p class="announcement-description"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                                <div class="announcement-info">
                                    <div class="announcement-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Diumumkan: <?php echo date('d F Y H:i', strtotime($announcement['published_at'])); ?></span>
                                    </div>
                                    <div class="announcement-dosen">
                                        <i class="fas fa-user"></i>
                                        <span>Dosen: <?php echo htmlspecialchars($announcement['lecturer_full_name'] ?? 'Tidak Diketahui'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inisialisasi halaman
        document.addEventListener('DOMContentLoaded', function() {
            const fadeElements = document.querySelectorAll('.fade-in');
            fadeElements.forEach((element, index) => {
                element.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>