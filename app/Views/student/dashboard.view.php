<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
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
            margin-left: 10px;
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
            top: 0; left: 0; right: 0;
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

        .welcome-text { font-size: 1.1rem; margin: 0; }
        .student-id { font-size: 0.9rem; opacity: 0.9; margin: 0; }
        .section-title { color: var(--text-dark); font-weight: 700; margin-bottom: 1.5rem; font-size: 1.3rem; }

        .fade-in { animation: fadeIn 0.6s ease-in; }
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
                                <?php if (!empty($studentInfo['profile_picture_url']) && $studentInfo['profile_picture_url'] !== 'default_profile.svg'): ?>
                                    <img src="<?= html_escape($studentInfo['profile_picture_url']); ?>" alt="Profile Picture" class="profile-pic me-3">
                                <?php else: ?>
                                    <div class="profile-pic-icon me-3">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h5 class="welcome-text">Hi, <?= html_escape($studentInfo['full_name'] ?? 'Mahasiswa'); ?></h5>
                                    <p class="student-id"><?= html_escape($studentInfo['nim'] ?? '-'); ?></p>
                                    <p class="student-id"><?= html_escape($studentInfo['study_program'] ?? '-'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="index.php?url=student/profile" class="header-btn">
                            <i class="fas fa-user-circle me-2"></i>Profile
                        </a>
                        <a href="index.php?url=auth/logout" class="header-btn" onclick="return confirm('Apakah Anda yakin ingin logout?')">
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
                        <div class="mata-kuliah-icon"><i class="fas fa-calendar-alt"></i></div>
                        <h6 class="mata-kuliah-title">Jadwal Mata Kuliah</h6>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6 fade-in">
                    <div class="mata-kuliah-card feature-item" data-feature="Deadline Tugas">
                        <div class="mata-kuliah-icon"><i class="fas fa-clock"></i></div>
                        <h6 class="mata-kuliah-title">Deadline Tugas</h6>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6 fade-in">
                    <div class="mata-kuliah-card feature-item" data-feature="Quiz">
                        <div class="mata-kuliah-icon"><i class="fas fa-question-circle"></i></div>
                        <h6 class="mata-kuliah-title">Quiz</h6>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6 fade-in">
                    <div class="mata-kuliah-card feature-item" data-feature="Pengumuman">
                        <div class="mata-kuliah-icon"><i class="fas fa-bullhorn"></i></div>
                        <h6 class="mata-kuliah-title">Pengumuman</h6>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6 fade-in">
                    <div class="mata-kuliah-card feature-item" data-feature="Nilai">
                        <div class="mata-kuliah-icon"><i class="fas fa-file-alt"></i></div>
                        <h6 class="mata-kuliah-title">Nilai</h6>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6 fade-in">
                    <div class="mata-kuliah-card feature-item" data-feature="Ujian">
                        <div class="mata-kuliah-icon"><i class="fas fa-clipboard-check"></i></div>
                        <h6 class="mata-kuliah-title">Ujian</h6>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <h3 class="section-title fade-in">
                        <i class="fas fa-book me-2"></i>Mata Kuliah Diampu (<?= $totalEnrolledCourses; ?>)
                    </h3>
                </div>
                
                <div id="courseList" class="row">
                    <?php if (empty($enrolledCourses)): ?>
                        <div class="col-12 text-center text-muted py-4">
                            <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                            <p>Tidak ada mata kuliah yang terdaftar untuk Anda saat ini.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($enrolledCourses as $course): ?>
                            <div class="col-lg-3 col-md-6 col-sm-6 fade-in">
                                <div class="mata-kuliah-card" onclick="goToKelas(<?= (int)$course['course_id']; ?>)">
                                    <div class="mata-kuliah-icon">
                                        <i class="<?= getCourseIcon($course['course_name']); ?>"></i>
                                    </div>
                                    <h6 class="mata-kuliah-title"><?= html_escape($course['course_name']); ?></h6>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        function goToKelas(courseId) {
            window.location.href = `index.php?url=student/kelas&course_id=${courseId}`;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const pages = {
                'Jadwal Mata Kuliah': 'index.php?url=student/jadwal',
                'Deadline Tugas': 'index.php?url=student/deadline',
                'Quiz': 'index.php?url=quiz/list',
                'Pengumuman': 'index.php?url=student/pengumuman',
                'Nilai': 'index.php?url=student/nilai',
                'Ujian': 'index.php?url=exam/studentExam'
            };

            const featureItems = document.querySelectorAll('.feature-item');
            featureItems.forEach(item => {
                item.addEventListener('click', function () {
                    const feature = this.getAttribute('data-feature');
                    const page = pages[feature];
                    if (page) {
                        window.location.href = page;
                    }
                });
            });
        });
    </script>
</body>
</html>
