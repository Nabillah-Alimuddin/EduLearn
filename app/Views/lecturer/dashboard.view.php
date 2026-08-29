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
            --primary-blue: #B6D0EF;
            --secondary-blue: #63A3F1;
            --light-green: #FAFFEE;
            --dark-teal: #4F8A9E;
            --white: #FFFFFF;
        }

        body {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--secondary-blue), var(--dark-teal));
            color: var(--white);
            padding: 2rem 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 0 0 25px 25px;
            margin-bottom: 2rem;
        }

        .nav-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: var(--white);
            border-radius: 25px;
            padding: 0.5rem 1.2rem;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            margin: 0.2rem;
        }

        .nav-btn:hover {
            background: var(--white);
            color: var(--dark-teal);
            transform: translateY(-2px);
        }

        .stat-card {
            background: var(--white);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        .stat-card:hover { transform: translateY(-5px); }

        .stat-icon {
            font-size: 2.5rem;
            color: var(--secondary-blue);
            margin-bottom: 1rem;
        }

        .menu-card {
            background: var(--white);
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: var(--dark-teal);
            display: block;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(99, 163, 241, 0.3);
            color: var(--secondary-blue);
        }

        .menu-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--secondary-blue);
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h2><i class="fas fa-chalkboard-teacher me-2"></i>Selamat Datang, Dosen</h2>
                    <p class="mb-0"><?= html_escape(($lecturerInfo['full_name'] ?? 'Dosen') . ($lecturerInfo['gelar'] ? ', ' . $lecturerInfo['gelar'] : '')); ?></p>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <a href="index.php?url=lecturer/profile" class="nav-btn"><i class="fas fa-user me-1"></i> Profil</a>
                    <a href="index.php?url=auth/logout" class="nav-btn" onclick="return confirm('Yakin ingin logout?')"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3 col-6">
                <div class="stat-card text-center">
                    <div class="stat-icon"><i class="fas fa-book-reader"></i></div>
                    <h3><?= $kelasAktif; ?></h3>
                    <p class="text-muted mb-0">Kelas Aktif</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card text-center">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <h3><?= $totalMahasiswa; ?></h3>
                    <p class="text-muted mb-0">Mahasiswa Diampu</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card text-center">
                    <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                    <h3><?= $tugasPending; ?></h3>
                    <p class="text-muted mb-0">Tugas Pending</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card text-center">
                    <div class="stat-icon"><i class="fas fa-question-circle"></i></div>
                    <h3><?= $quizAktif; ?></h3>
                    <p class="text-muted mb-0">Quiz Aktif</p>
                </div>
            </div>
        </div>

        <!-- Dashboard Menu Grid -->
        <h4 class="text-white mb-3"><i class="fas fa-th-large me-2"></i>Menu Manajemen Akademik</h4>
        <div class="row">
            <div class="col-md-4 col-sm-6">
                <a href="index.php?url=lecturer/kelas" class="menu-card">
                    <div class="menu-icon"><i class="fas fa-chalkboard"></i></div>
                    <h5>Kelola Kelas</h5>
                    <p class="small text-muted mb-0">Daftar kelas dan daftar mahasiswa</p>
                </a>
            </div>
            <div class="col-md-4 col-sm-6">
                <a href="index.php?url=lecturer/materitugas" class="menu-card">
                    <div class="menu-icon"><i class="fas fa-folder-open"></i></div>
                    <h5>Materi & Tugas</h5>
                    <p class="small text-muted mb-0">Upload materi dan buat tugas baru</p>
                </a>
            </div>
            <div class="col-md-4 col-sm-6">
                <a href="index.php?url=quiz/manage" class="menu-card">
                    <div class="menu-icon"><i class="fas fa-edit"></i></div>
                    <h5>Kelola Kuis</h5>
                    <p class="small text-muted mb-0">Buat dan kelola bank soal kuis</p>
                </a>
            </div>
            <div class="col-md-4 col-sm-6">
                <a href="index.php?url=lecturer/pengumuman" class="menu-card">
                    <div class="menu-icon"><i class="fas fa-bullhorn"></i></div>
                    <h5>Pengumuman</h5>
                    <p class="small text-muted mb-0">Buat pengumuman untuk kelas</p>
                </a>
            </div>
            <div class="col-md-4 col-sm-6">
                <a href="index.php?url=lecturer/inputNilai" class="menu-card">
                    <div class="menu-icon"><i class="fas fa-pen-fancy"></i></div>
                    <h5>Input Nilai</h5>
                    <p class="small text-muted mb-0">Input dan edit nilai tugas/UTS/UAS</p>
                </a>
            </div>
            <div class="col-md-4 col-sm-6">
                <a href="index.php?url=lecturer/laporan" class="menu-card">
                    <div class="menu-icon"><i class="fas fa-file-excel"></i></div>
                    <h5>Laporan Nilai</h5>
                    <p class="small text-muted mb-0">Rekap nilai dan ekspor ke Excel</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
