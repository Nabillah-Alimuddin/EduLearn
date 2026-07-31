<?php
include 'middleware.php';
include 'db_connection.php';
require_role('student');

// Ambil user_id mahasiswa dari sesi
$current_student_id = $_SESSION['user_id']; 

$student_name = $_SESSION['full_name'] ?? 'Mahasiswa';
$student_nim = 'Tidak diketahui';
$student_study_program = 'Tidak diketahui';
$profile_picture_url = 'default_profile.svg'; // Default value
$enrolled_courses = []; 
$total_enrolled_courses = 0;

// Ambil NIM, program studi, dan URL foto profil dari database
$sql_student_info = "SELECT nim, study_program, full_name, profile_picture_url FROM users WHERE user_id = ? AND role = 'student'";
$stmt_student_info = $conn->prepare($sql_student_info);
if ($stmt_student_info) {
    $stmt_student_info->execute([$current_student_id]);
    if ($row_student_info = $stmt_student_info->fetch()) {
        $student_name = htmlspecialchars($row_student_info['full_name']);
        $student_nim = htmlspecialchars($row_student_info['nim']);
        $student_study_program = htmlspecialchars($row_student_info['study_program']);
        if (!empty($row_student_info['profile_picture_url']) && $row_student_info['profile_picture_url'] !== 'default_profile.svg') {
            $profile_picture_url = htmlspecialchars($row_student_info['profile_picture_url']);
        }
    }
}

// Ambil daftar mata kuliah yang diikuti mahasiswa, HAPUS informasi dosen dan mata kuliah Pendidikan Agama
$sql_enrolled_courses = "
    SELECT ce.course_id, c.course_name, c.course_code
    FROM course_enrollments ce
    JOIN courses c ON ce.course_id = c.course_id
    WHERE ce.student_id = ? 
    AND c.course_name NOT IN ('Pendidikan Agama', 'Pendidikan Kewarganegaraan', 'Fotografi', 'Basis Data', 'Multimedia')
    ORDER BY c.course_name ASC
";
$stmt_enrolled_courses = $conn->prepare($sql_enrolled_courses);
if ($stmt_enrolled_courses) {
    $stmt_enrolled_courses->execute([$current_student_id]);
    while ($row = $stmt_enrolled_courses->fetch()) {
        $enrolled_courses[] = $row;
    }
}

$total_enrolled_courses = count($enrolled_courses);

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
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
            object-fit: cover;
            border: 3px solid var(--white);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .profile-pic-icon {
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


        .header-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            margin-left: 10px; /* Jarak antar tombol */
        }

        .header-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
        }

        .mata-kuliah-card {
            background: var(--white);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .mata-kuliah-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .mata-kuliah-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(127, 179, 211, 0.25);
        }

        .mata-kuliah-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .mata-kuliah-title {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.9rem;
            margin: 0;
        }

        .sidebar-card {
            background: var(--white);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            border: none;
        }

        .sidebar-card h5 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .sidebar-item {
            background: var(--accent-color);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .sidebar-item:hover {
            background: var(--secondary-color);
            transform: translateX(5px);
        }

        .sidebar-item h6 {
            margin: 0 0 0.3rem 0;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .sidebar-item p {
            margin: 0;
            color: #6C757D;
            font-size: 0.8rem;
        }

        .welcome-text {
            font-size: 1.1rem;
            margin: 0;
        }

        .student-id {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }

        .section-title {
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }

        .quick-stats {
            background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.7));
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1rem;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-dark);
            margin: 0;
        }

        @media (max-width: 992px) {
            .mata-kuliah-card {
                height: 120px;
                margin-bottom: 1rem;
            }
            
            .mata-kuliah-icon {
                font-size: 1.8rem;
            }
            
            .mata-kuliah-title {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 768px) {
            .dashboard-header {
                margin-bottom: 1rem;
            }
            
            .mata-kuliah-card {
                height: 110px;
            }
            
            .mata-kuliah-icon {
                font-size: 1.5rem;
            }
            
            .mata-kuliah-title {
                font-size: 0.8rem;
            }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes ripple {
            to {
                transform: scale(2);
                opacity: 0;
            }
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
                                <?php if (!empty($profile_picture_url) && $profile_picture_url !== 'default_profile.svg'): ?>
                                    <img src="<?php echo htmlspecialchars($profile_picture_url); ?>" alt="Profile Picture" class="profile-pic me-3">
                                <?php else: ?>
                                    <div class="profile-pic-icon me-3">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h5 class="welcome-text"><span id="studentFullName">Hi, <?php echo $student_name; ?></span></h5>
                                    <p class="student-id" id="studentNim"><?php echo $student_nim; ?></p>
                                    <p class="student-id" id="studentStudyProgram"><?php echo $student_study_program; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="profile.php" class="header-btn">
                            <i class="fas fa-user-circle me-2"></i>Profile
                        </a>
                        <a href="logout.php" class="header-btn" onclick="handleLogout()">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row mb-4">
                <div class="col-12">
                    <h3 class="section-title fade-in">
                        <i class="fas fa-tachometer-alt me-2"></i>Menu Utama
                    </h3>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6 fade-in">
                    <div class="mata-kuliah-card feature-item" data-feature="Jadwal Mata Kuliah">
                        <div class="mata-kuliah-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h6 class="mata-kuliah-title">Jadwal Mata Kuliah</h6>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6 fade-in">
                    <div class="mata-kuliah-card feature-item" data-feature="Deadline Tugas">
                        <div class="mata-kuliah-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h6 class="mata-kuliah-title">Deadline Tugas</h6>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6 fade-in">
                    <div class="mata-kuliah-card feature-item" data-feature="Quiz">
                        <div class="mata-kuliah-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h6 class="mata-kuliah-title">Quiz</h6>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6 fade-in">
                    <div class="mata-kuliah-card feature-item" data-feature="Pengumuman">
                        <div class="mata-kuliah-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h6 class="mata-kuliah-title">Pengumuman</h6>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6 fade-in">
                    <div class="mata-kuliah-card feature-item" data-feature="Nilai">
                        <div class="mata-kuliah-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h6 class="mata-kuliah-title">Nilai</h6>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6 fade-in">
                    <div class="mata-kuliah-card feature-item" data-feature="Ujian">
                        <div class="mata-kuliah-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h6 class="mata-kuliah-title">Ujian</h6>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <h3 class="section-title fade-in">
                        <i class="fas fa-book me-2"></i>Mata Kuliah Diampu (<?php echo $total_enrolled_courses; ?>)
                    </h3>
                </div>
                
                <div id="courseList" class="row">
                    <?php if (empty($enrolled_courses)): ?>
                        <div class="col-12 text-center text-muted" id="loadingCourses">
                            <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                            <p>Tidak ada mata kuliah yang terdaftar untuk Anda saat ini.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($enrolled_courses as $course): ?>
                            <div class="col-lg-3 col-md-6 col-sm-6 fade-in">
                                <div class="mata-kuliah-card" onclick="goToKelas(<?php echo htmlspecialchars($course['course_id']); ?>, '<?php echo htmlspecialchars($course['course_name']); ?>')">
                                    <div class="mata-kuliah-icon">
                                        <i class="<?php echo getCourseIcon($course['course_name']); ?>"></i>
                                    </div>
                                    <h6 class="mata-kuliah-title"><?php echo htmlspecialchars($course['course_name']); ?></h6>
                                    </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.10.2/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.min.js"></script>
    <script>
        function goToKelas(courseId, courseName) {
            // Mengirim courseId melalui URL
            window.location.href = `kelas.php?course_id=${courseId}`;
        }

        function handleLogout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = 'logout.php';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const fadeElements = document.querySelectorAll('.fade-in');
            fadeElements.forEach((element, index) => {
                element.style.animationDelay = `${index * 0.1}s`;
            });

            const cards = document.querySelectorAll('.mata-kuliah-card');
            cards.forEach(card => {
                card.addEventListener('click', function(e) {
                    const ripple = document.createElement('div');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        background: rgba(127, 179, 211, 0.3);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: ripple 0.6s ease-out;
                        pointer-events: none;
                    `;
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });

            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(2);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);

            const pages = {
                'Jadwal Mata Kuliah': 'jadwal.php',
                'Deadline Tugas': 'deadline.php',
                'Quiz': 'lpquiz.php',
                'Pengumuman': 'pengumuman.php',
                'Nilai': 'nilai.php',
                'Ujian': 'ujian.php'
            };

            const featureItems = document.querySelectorAll('.feature-item');
            featureItems.forEach(item => {
                item.addEventListener('click', function () {
                    const feature = this.getAttribute('data-feature');
                    const page = pages[feature];
                    if (page) {
                        this.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            this.style.transform = 'scale(1)';
                            window.location.href = page;
                        }, 150);
                    } else {
                        alert(`Halaman untuk ${feature} belum tersedia!`);
                    }
                });
            });
        });

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>