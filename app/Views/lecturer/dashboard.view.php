<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen - EduLearn</title>
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

        .nav-btn {
            background: rgba(255, 255, 255, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: var(--white);
            border-radius: 25px;
            padding: 0.45rem 1.2rem;
            text-decoration: none;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 600;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
        }

        .nav-btn:hover {
            background: var(--white);
            color: var(--dark-teal);
            transform: translateY(-2px);
        }

        .stat-card {
            background: var(--white);
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.25rem;
            transition: transform 0.25s ease;
            border: none;
        }

        .stat-card:hover { transform: translateY(-4px); }

        .stat-icon {
            font-size: 2.2rem;
            color: var(--dark-teal);
            margin-bottom: 0.5rem;
        }

        .menu-card {
            background: var(--white);
            border-radius: 16px;
            padding: 1.5rem 1.25rem;
            text-align: center;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.25rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            text-decoration: none;
            color: var(--text-dark);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: none;
            height: 160px;
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
            color: var(--dark-teal);
        }

        .menu-icon-wrapper {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 0.75rem;
            transition: transform 0.3s ease;
        }

        .menu-card:hover .menu-icon-wrapper {
            transform: scale(1.1);
        }

        .icon-kelas { background: #EBF3FA; color: var(--dark-teal); }
        .icon-materi { background: #E8F5E9; color: #2E7D32; }
        .icon-kuis { background: #F3E5F5; color: #7B1FA2; }
        .icon-pengumuman { background: #FFEBEE; color: #C62828; }
        .icon-nilai { background: #E0F2F1; color: #00695C; }
        .icon-laporan { background: #FFF8E1; color: #F57F17; }

        .section-title {
            color: var(--dark-teal);
            font-weight: 700;
            margin-bottom: 1.25rem;
            font-size: 1.25rem;
        }
    </style>
</head>
<body>
    <!-- Unified Header Dosen -->
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h3 class="fw-bold mb-1"><i class="fas fa-chalkboard-teacher me-2"></i>Selamat Datang, Dosen 👋</h3>
                    <p class="mb-0 fs-6 opacity-90"><i class="fas fa-user-circle me-1"></i><?= html_escape(($lecturerInfo['full_name'] ?? 'Dosen') . ($lecturerInfo['gelar'] ? ', ' . $lecturerInfo['gelar'] : '')); ?></p>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <a href="index.php?url=lecturer/profile" class="nav-btn me-2"><i class="fas fa-user"></i> Profil</a>
                    <a href="index.php?url=auth/logout" class="nav-btn bg-danger border-0" onclick="return confirm('Apakah Anda yakin ingin keluar?')"><i class="fas fa-sign-out-alt"></i> Keluar</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <!-- Quick Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-6">
                <div class="stat-card text-center">
                    <div class="stat-icon"><i class="fas fa-book-reader"></i></div>
                    <h3 class="fw-bold text-dark mb-1"><?= $kelasAktif; ?></h3>
                    <p class="text-muted small mb-0 fw-semibold">Kelas Aktif</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card text-center">
                    <div class="stat-icon text-success"><i class="fas fa-users"></i></div>
                    <h3 class="fw-bold text-dark mb-1"><?= $totalMahasiswa; ?></h3>
                    <p class="text-muted small mb-0 fw-semibold">Mahasiswa Diampu</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card text-center">
                    <div class="stat-icon text-warning"><i class="fas fa-tasks"></i></div>
                    <h3 class="fw-bold text-dark mb-1"><?= $tugasPending; ?></h3>
                    <p class="text-muted small mb-0 fw-semibold">Tugas Pending</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card text-center">
                    <div class="stat-icon text-info"><i class="fas fa-question-circle"></i></div>
                    <h3 class="fw-bold text-dark mb-1"><?= $quizAktif; ?></h3>
                    <p class="text-muted small mb-0 fw-semibold">Quiz Aktif</p>
                </div>
            </div>
        </div>

        <!-- Dashboard Menu Grid Dosen -->
        <h4 class="section-title"><i class="fas fa-th-large me-2"></i>Menu Manajemen Akademik Dosen</h4>
        <div class="row">
            <div class="col-md-4 col-sm-6">
                <a href="index.php?url=lecturer/kelas" class="menu-card">
                    <div class="menu-icon-wrapper icon-kelas"><i class="fas fa-chalkboard"></i></div>
                    <h5 class="fw-bold fs-6 mb-1">Kelola Kelas</h5>
                    <p class="small text-muted mb-0">Daftar kelas dan daftar mahasiswa</p>
                </a>
            </div>
            <div class="col-md-4 col-sm-6">
                <a href="index.php?url=lecturer/materitugas" class="menu-card">
                    <div class="menu-icon-wrapper icon-materi"><i class="fas fa-folder-open"></i></div>
                    <h5 class="fw-bold fs-6 mb-1">Materi & Tugas</h5>
                    <p class="small text-muted mb-0">Upload materi dan buat tugas baru</p>
                </a>
            </div>
            <div class="col-md-4 col-sm-6">
                <a href="index.php?url=quiz/manage" class="menu-card">
                    <div class="menu-icon-wrapper icon-kuis"><i class="fas fa-edit"></i></div>
                    <h5 class="fw-bold fs-6 mb-1">Kelola Kuis</h5>
                    <p class="small text-muted mb-0">Buat dan kelola bank soal kuis</p>
                </a>
            </div>
            <div class="col-md-4 col-sm-6">
                <a href="index.php?url=lecturer/pengumuman" class="menu-card">
                    <div class="menu-icon-wrapper icon-pengumuman"><i class="fas fa-bullhorn"></i></div>
                    <h5 class="fw-bold fs-6 mb-1">Pengumuman</h5>
                    <p class="small text-muted mb-0">Buat pengumuman untuk kelas</p>
                </a>
            </div>
            <div class="col-md-4 col-sm-6">
                <a href="index.php?url=lecturer/inputNilai" class="menu-card">
                    <div class="menu-icon-wrapper icon-nilai"><i class="fas fa-pen-fancy"></i></div>
                    <h5 class="fw-bold fs-6 mb-1">Input Nilai</h5>
                    <p class="small text-muted mb-0">Input dan edit nilai tugas/UTS/UAS</p>
                </a>
            </div>
            <div class="col-md-4 col-sm-6">
                <a href="index.php?url=lecturer/laporan" class="menu-card">
                    <div class="menu-icon-wrapper icon-laporan"><i class="fas fa-file-excel"></i></div>
                    <h5 class="fw-bold fs-6 mb-1">Laporan Nilai</h5>
                    <p class="small text-muted mb-0">Rekap nilai dan ekspor ke Excel</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
