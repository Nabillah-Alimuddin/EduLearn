<?php
session_start();
include 'db_connection.php';

// Cek apakah pengguna sudah login dan memiliki peran 'student'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}
$current_student_id = $_SESSION['user_id'];

// Ambil course_id dari URL
$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    header("Location: dash-mahasiswa.php"); // Kembali ke dashboard jika tidak ada course_id
    exit();
}

// Ambil detail mata kuliah dan dosen
$course_details = [];
$sql_course = "
    SELECT c.course_name, c.course_code, u.full_name AS lecturer_name, u.gelar
    FROM courses c
    LEFT JOIN users u ON c.lecturer_id = u.user_id
    WHERE c.course_id = ?
";
$stmt_course = $conn->prepare($sql_course);
if ($stmt_course) {
    $stmt_course->execute([$course_id]);
    $course_details = $stmt_course->fetch();
}

// Ambil data materi untuk mata kuliah ini
$materials_data = [];
$sql_materials = "
    SELECT material_id, title, file_path, uploaded_at
    FROM materials
    WHERE course_id = ?
    ORDER BY uploaded_at DESC
";
$stmt_materials = $conn->prepare($sql_materials);
if ($stmt_materials) {
    $stmt_materials->execute([$course_id]);
    while ($row = $stmt_materials->fetch()) {
        $materials_data[] = $row;
    }
}

$conn = null;

function getCourseIcon($courseName) {
    if (strpos($courseName, 'Aljabar Linear') !== false) return 'fas fa-square-root-variable';
    if (strpos($courseName, 'Pemrograman Web') !== false) return 'fas fa-globe';
    if (strpos($courseName, 'Analisis Desain') !== false) return 'fas fa-drafting-compass';
    if (strpos($courseName, 'Multimedia') !== false) return 'fas fa-photo-video';
    if (strpos($courseName, 'Big Data') !== false) return 'fas fa-database';
    if (strpos($courseName, 'Kecerdasan Buatan') !== false) return 'fas fa-brain';
    if (strpos($courseName, 'Basis Data') !== false) return 'fas fa-server';
    if (strpos($courseName, 'Mikrokontroler') !== false) return 'fas fa-microchip';
    if (strpos($courseName, 'Pemrograman Berbasis Objek') !== false) return 'fas fa-laptop-code';
    if (strpos($courseName, 'Jaringan Komputer') !== false) return 'fas fa-network-wired';
    if (strpos($courseName, 'Pengembangan Aplikasi Mobile') !== false) return 'fas fa-mobile-alt';
    return 'fas fa-book';
}

function formatDate($dateString) {
    return date('d F Y H:i', strtotime($dateString));
}

function escapeHtml($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelas Mahasiswa - <?php echo escapeHtml($course_details['course_name'] ?? ''); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
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

        .info-card {
            background: var(--white);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }
        
        .section-title {
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }

        .material-card {
            background: var(--white);
            border-radius: 15px;
            padding: 1rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            margin-bottom: 1rem;
            position: relative;
            overflow: hidden;
        }
        
        .material-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(127, 179, 211, 0.25);
        }

        .material-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .material-title {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .material-date {
            display: flex;
            align-items: center;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.9rem;
        }

        .material-date i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
        }

        .btn-primary-custom:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
            color: white;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            margin-left: 10px;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6C757D;
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
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
                                <div class="me-3">
                                    <i class="<?php echo getCourseIcon($course_details['course_name'] ?? ''); ?>" style="font-size: 3rem; color: white;"></i>
                                </div>
                                <div>
                                    <h4 class="welcome-text fw-bold"><?php echo escapeHtml($course_details['course_name'] ?? 'Mata Kuliah'); ?></h4>
                                    <p class="student-id mb-0"><?php echo escapeHtml($course_details['course_code'] ?? '-'); ?></p>
                                    <p class="student-id">Dosen: <?php echo escapeHtml($course_details['lecturer_name'] . ($course_details['gelar'] ? ', ' . $course_details['gelar'] : '') ?? 'Tidak Diketahui'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="dash-mahasiswa.php" class="btn-back">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="info-card fade-in">
                <h4 class="section-title">
                    <i class="fas fa-file-pdf me-2"></i>Materi Kuliah
                </h4>
                <div id="materials-container">
                    <?php if (empty($materials_data)): ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <h4>Tidak ada materi saat ini</h4>
                            <p>Dosen belum mengunggah materi untuk mata kuliah ini.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($materials_data as $material): ?>
                            <div class="material-card fade-in">
                                <h5 class="material-title"><?php echo escapeHtml($material['title']); ?></h5>
                                <div class="material-info mt-3">
                                    <div class="material-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Diunggah: <?php echo formatDate($material['uploaded_at']); ?></span>
                                    </div>
                                    <div class="material-actions">
                                        <a href="<?php echo escapeHtml($material['file_path']); ?>" target="_blank" class="btn btn-sm btn-primary-custom">
                                            <i class="fas fa-download me-1"></i>Lihat/Unduh
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fadeElements = document.querySelectorAll('.fade-in');
            fadeElements.forEach((element, index) => {
                element.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>