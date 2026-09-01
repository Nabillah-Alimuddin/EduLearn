<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #7FB3D3;
            --secondary-blue: #63A3F1;
            --dark-teal: #4F8A9E;
            --light-bg: #F0F4F8;
            --white: #FFFFFF;
            --text-dark: #2C3E50;
            --shadow-sm: 0 4px 12px rgba(79, 138, 158, 0.12);
        }

        body {
            background: #F0F4F8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
            min-height: 100vh;
        }

        /* Unified Header Specification */
        .dashboard-header, .header {
            background: linear-gradient(135deg, var(--secondary-blue) 0%, var(--dark-teal) 100%);
            color: var(--white);
            padding: 2.2rem 0;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 6px 20px rgba(79, 138, 158, 0.2);
            margin-bottom: 2rem;
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .profile-pic {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2.5px solid var(--white);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .profile-pic-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: var(--dark-teal);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .btn-header-action {
            background: rgba(255, 255, 255, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: var(--white);
            border-radius: 25px;
            padding: 0.45rem 1.2rem;
            text-decoration: none;
            transition: all 0.25s ease;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .btn-header-action:hover {
            background: var(--white);
            color: var(--dark-teal);
            transform: translateY(-2px);
        }

        .menu-card {
            background: var(--white);
            border-radius: 16px;
            padding: 1.4rem 1rem;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            border: none;
            height: 145px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-bottom: 1.25rem;
            position: relative;
        }

        .menu-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-blue), var(--dark-teal));
            border-radius: 16px 16px 0 0;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(79, 138, 158, 0.25);
        }

        .menu-icon-wrapper {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
            transition: transform 0.3s ease;
        }

        .menu-card:hover .menu-icon-wrapper {
            transform: scale(1.1);
        }

        .icon-krs { background: #EBF3FA; color: var(--dark-teal); }
        .icon-jadwal { background: #E8F5E9; color: #2E7D32; }
        .icon-tugas { background: #FFF8E1; color: #F57F17; }
        .icon-quiz { background: #F3E5F5; color: #7B1FA2; }
        .icon-pengumuman { background: #FFEBEE; color: #C62828; }
        .icon-nilai { background: #E0F2F1; color: #00695C; }
        .icon-ujian { background: #FCE4EC; color: #AD1457; }
        .icon-profil { background: #E1F5FE; color: #0277BD; }

        .menu-title {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 0.92rem;
            margin: 0;
        }

        .course-card {
            background: var(--white);
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: var(--shadow-sm);
            border: none;
            transition: all 0.3s ease;
            cursor: pointer;
            height: 130px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin-bottom: 1.25rem;
        }

        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(79, 138, 158, 0.25);
        }

        .section-heading {
            color: var(--dark-teal);
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 1.25rem;
        }
    </style>
</head>
<body>
    <!-- Unified Header Dashboard -->
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-7 mb-3 mb-md-0">
                    <div class="profile-card">
                        <div class="d-flex align-items-center">
                            <?php if (!empty($studentInfo['profile_picture_url']) && $studentInfo['profile_picture_url'] !== 'default_profile.svg'): ?>
                                <img src="<?= html_escape($studentInfo['profile_picture_url']); ?>" alt="Profile Picture" class="profile-pic me-3">
                            <?php else: ?>
                                <div class="profile-pic-icon me-3">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h5 class="fw-bold mb-1 fs-5">Halo, <?= html_escape($studentInfo['full_name'] ?? 'Mahasiswa'); ?> 👋</h5>
                                <p class="mb-0 opacity-90 small"><i class="fas fa-id-card me-1"></i>NIM: <?= html_escape($studentInfo['nim'] ?? '-'); ?></p>
                                <p class="mb-0 opacity-90 small"><i class="fas fa-university me-1"></i>Prodi: <?= html_escape($studentInfo['study_program'] ?? '-'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 text-md-end">
                    <a href="index.php?url=student/krs" class="btn-header-action me-2 mb-2">
                        <i class="fas fa-list-check"></i> Kelola KRS
                    </a>
                    <a href="index.php?url=student/profile" class="btn-header-action me-2 mb-2">
                        <i class="fas fa-user-circle"></i> Profil
                    </a>
                    <a href="index.php?url=auth/logout" class="btn-header-action bg-danger border-0 mb-2" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                        <i class="fas fa-sign-out-alt"></i> Keluar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="container pb-5">
        <!-- Menu Utama Grid -->
        <div class="row">
            <div class="col-12">
                <h4 class="section-heading">
                    <i class="fas fa-th-large me-2"></i>Menu Utama Mahasiswa
                </h4>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="menu-card feature-item" data-feature="KRS">
                    <div class="menu-icon-wrapper icon-krs"><i class="fas fa-list-check"></i></div>
                    <h6 class="menu-title">KRS & Ambil Kelas</h6>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="menu-card feature-item" data-feature="Jadwal Mata Kuliah">
                    <div class="menu-icon-wrapper icon-jadwal"><i class="fas fa-calendar-alt"></i></div>
                    <h6 class="menu-title">Jadwal Perkuliahan</h6>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="menu-card feature-item" data-feature="Deadline Tugas">
                    <div class="menu-icon-wrapper icon-tugas"><i class="fas fa-clock"></i></div>
                    <h6 class="menu-title">Deadline Tugas</h6>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="menu-card feature-item" data-feature="Quiz">
                    <div class="menu-icon-wrapper icon-quiz"><i class="fas fa-question-circle"></i></div>
                    <h6 class="menu-title">Kuis & Evaluasi</h6>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="menu-card feature-item" data-feature="Pengumuman">
                    <div class="menu-icon-wrapper icon-pengumuman"><i class="fas fa-bullhorn"></i></div>
                    <h6 class="menu-title">Pengumuman Kampus</h6>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="menu-card feature-item" data-feature="Nilai">
                    <div class="menu-icon-wrapper icon-nilai"><i class="fas fa-file-alt"></i></div>
                    <h6 class="menu-title">Nilai & KHS</h6>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="menu-card feature-item" data-feature="Ujian">
                    <div class="menu-icon-wrapper icon-ujian"><i class="fas fa-clipboard-check"></i></div>
                    <h6 class="menu-title">Ujian Akhir</h6>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="menu-card feature-item" data-feature="Profil">
                    <div class="menu-icon-wrapper icon-profil"><i class="fas fa-user-cog"></i></div>
                    <h6 class="menu-title">Profil Saya</h6>
                </div>
            </div>
        </div>

        <!-- Mata Kuliah Terdaftar -->
        <div class="row mt-4">
            <div class="col-12">
                <h4 class="section-heading">
                    <i class="fas fa-graduation-cap me-2"></i>Mata Kuliah Terdaftar (<?= $totalEnrolledCourses; ?>)
                </h4>
            </div>
            
            <div id="courseList" class="row">
                <?php if (empty($enrolledCourses)): ?>
                    <div class="col-12 text-center text-muted py-4">
                        <div class="border-0 rounded-4 p-5 bg-white shadow-sm">
                            <i class="fas fa-inbox fa-3x mb-3 text-secondary"></i>
                            <h5 class="fw-bold text-dark">Belum Ada Mata Kuliah Terdaftar</h5>
                            <p class="text-secondary mb-3">Silakan ambil mata kuliah terlebih dahulu melalui menu KRS & Ambil Kelas.</p>
                            <a href="index.php?url=student/krs" class="btn text-white rounded-pill px-4 fw-bold" style="background: linear-gradient(135deg, var(--secondary-blue), var(--dark-teal));">
                                <i class="fas fa-plus me-1"></i> Kelola KRS Sekarang
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($enrolledCourses as $course): ?>
                        <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="course-card" onclick="goToKelas(<?= (int)$course['course_id']; ?>)">
                                <div class="d-flex align-items-center">
                                    <div class="menu-icon-wrapper icon-krs me-3 mb-0" style="width:45px; height:45px; font-size:1.2rem;">
                                        <i class="<?= getCourseIcon($course['course_name']); ?>"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1 fs-6"><?= html_escape($course['course_name']); ?></h6>
                                        <span class="badge bg-light text-secondary border"><?= html_escape($course['course_code']); ?></span>
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
        function goToKelas(courseId) {
            window.location.href = `index.php?url=student/kelas&course_id=${courseId}`;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const pages = {
                'KRS': 'index.php?url=student/krs',
                'Jadwal Mata Kuliah': 'index.php?url=student/jadwal',
                'Deadline Tugas': 'index.php?url=student/deadline',
                'Quiz': 'index.php?url=quiz/list',
                'Pengumuman': 'index.php?url=student/pengumuman',
                'Nilai': 'index.php?url=student/nilai',
                'Ujian': 'index.php?url=exam/studentExam',
                'Profil': 'index.php?url=student/profile'
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
