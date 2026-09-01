<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduLearn - Platform E-Learning Perguruan Tinggi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #B6D0EF;
            --secondary-blue: #63A3F1;
            --light-cream: #FAFFEE;
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
            overflow-x: hidden;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            min-height: 100vh;
        }

        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .hero-subtitle {
            font-size: 1.3rem;
            color: var(--light-cream);
            margin-bottom: 1.5rem;
            font-weight: 400;
        }

        .hero-description {
            font-size: 1.1rem;
            color: var(--white);
            margin-bottom: 2.5rem;
            opacity: 0.95;
            line-height: 1.6;
        }

        .cta-button {
            background: linear-gradient(135deg, var(--dark-teal), var(--secondary-blue));
            border: none;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--white);
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(79, 138, 158, 0.3);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(79, 138, 158, 0.45);
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            background: rgba(255, 255, 255, 0.22);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .feature-icon { font-size: 3rem; color: var(--light-cream); margin-bottom: 1rem; }
        .feature-title { font-size: 1.3rem; font-weight: 600; color: var(--white); margin-bottom: 0.75rem; }
        .feature-text { color: var(--white); opacity: 0.95; line-height: 1.5; font-size: 0.95rem; }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1rem 0;
        }

        .navbar-brand { font-size: 1.8rem; font-weight: 700; color: var(--white) !important; }
        .navbar-nav .nav-link { color: var(--white) !important; font-weight: 500; margin: 0 1rem; transition: all 0.3s ease; }
        .navbar-nav .nav-link:hover { color: var(--light-cream) !important; transform: translateY(-2px); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php?url=auth/landing">
                <i class="fas fa-graduation-cap me-2"></i>EduLearn
            </a>
            <button class="navbar-toggler text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#beranda">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tentang">Tentang</a></li>
                    <li class="nav-item"><a class="nav-link" href="#fitur">Fitur Utama</a></li>
                    <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
                    <li class="nav-item"><a class="btn btn-light text-primary rounded-pill px-4 ms-3 fw-bold" href="index.php?url=auth/login">Masuk</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section" id="beranda">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h1 class="hero-title">Selamat Datang di <span style="color: var(--light-cream);">EduLearn</span></h1>
                    <p class="hero-subtitle">Platform E-Learning Modern untuk Perguruan Tinggi</p>
                    <p class="hero-description">Bergabunglah dengan pengalaman pendidikan digital yang fleksibel & terstruktur. Akses materi perkuliahan, kuis evaluasi interaktif, pendaftaran KRS mandiri, dan pantau progres akademik Anda secara real-time.</p>
                    <button class="cta-button" onclick="redirectToLogin()">
                        <i class="fas fa-sign-in-alt me-2"></i> Masuk ke Platform
                    </button>
                </div>
                
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="feature-card">
                                <div class="feature-icon"><i class="fas fa-book-open"></i></div>
                                <h3 class="feature-title">Materi Lengkap</h3>
                                <p class="feature-text">Akses materi pembelajaran dan berkas ajar dari seluruh mata kuliah.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-card">
                                <div class="feature-icon"><i class="fas fa-list-check"></i></div>
                                <h3 class="feature-title">KRS Mandiri</h3>
                                <p class="feature-text">Pengambilan & pembatalan mata kuliah dengan akumulasi SKS otomatis.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-card">
                                <div class="feature-icon"><i class="fas fa-question-circle"></i></div>
                                <h3 class="feature-title">Quiz Interaktif</h3>
                                <p class="feature-text">Evaluasi pengerjaan kuis real-time lengkap dengan review & leaderboard.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-card">
                                <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                                <h3 class="feature-title">Progress Tracking</h3>
                                <p class="feature-text">Pantau progres pengumpulan tugas & transkrip nilai KHS secara transparan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Tentang -->
    <section id="tentang" class="py-5" style="background: var(--light-cream);">
        <div class="container py-3">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="fw-bold mb-3" style="color: var(--dark-teal); font-size: 2.2rem;">Tentang EduLearn</h2>
                    <p class="lead mb-4" style="color: var(--dark-teal); font-size: 1.15rem;">EduLearn adalah platform e-learning terdepan yang dirancang khusus untuk perguruan tinggi modern.</p>
                    <p style="color: var(--dark-teal); line-height: 1.8;">Kami menyediakan solusi pembelajaran digital yang komprehensif, memungkinkan dosen dan mahasiswa berinteraksi dalam lingkungan pembelajaran yang dinamis dan efektif.</p>
                </div>
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-4 text-center" style="background: var(--white); border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                                <i class="fas fa-laptop-code fa-2x mb-2" style="color: var(--secondary-blue);"></i>
                                <h6 style="color: var(--dark-teal);" class="fw-bold mb-0">Custom MVC Framework</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-4 text-center" style="background: var(--white); border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08);">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2" style="color: var(--secondary-blue);"></i>
                                <h6 style="color: var(--dark-teal);" class="fw-bold mb-0">Supabase Cloud Storage</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Kontak -->
    <section id="kontak" class="py-5" style="background: var(--white);">
        <div class="container py-3">
            <div class="row">
                <div class="col-lg-7 mx-auto">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold mb-2" style="color: var(--dark-teal);">Hubungi Kami</h2>
                        <p style="color: var(--dark-teal); opacity: 0.85;">Ada pertanyaan? Tim support kami siap membantu Anda.</p>
                    </div>

                    <div class="p-4 rounded-4 shadow-sm" style="background: var(--light-cream); border: 1px solid rgba(99, 163, 241, 0.2);">
                        <form method="POST" action="index.php?url=feedback/submit">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color: var(--dark-teal);">Nama Lengkap</label>
                                    <input type="text" class="form-control" name="name" required style="border-radius: 10px;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color: var(--dark-teal);">Email</label>
                                    <input type="email" class="form-control" name="email" required style="border-radius: 10px;">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold" style="color: var(--dark-teal);">Subjek</label>
                                    <input type="text" class="form-control" name="subject" required style="border-radius: 10px;">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold" style="color: var(--dark-teal);">Pesan</label>
                                    <textarea class="form-control" rows="4" name="message" required style="border-radius: 10px;"></textarea>
                                </div>
                                <div class="col-12 text-center mt-4">
                                    <button type="submit" class="btn text-white rounded-pill px-5 py-2 fw-bold" style="background: linear-gradient(135deg, var(--dark-teal), var(--secondary-blue)); border: none;">
                                        <i class="fas fa-paper-plane me-2"></i> Kirim Pesan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-4 text-center text-white" style="background: var(--dark-teal);">
        <p class="mb-0 small">&copy; 2026 EduLearn. All rights reserved.</p>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function redirectToLogin() {
            window.location.href = "index.php?url=auth/login";
        }
    </script>
</body>
</html>
