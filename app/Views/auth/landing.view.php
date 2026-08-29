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
            --light-green: #FAFFEE;
            --dark-blue: #4F8A9E;
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

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) { top: 10%; left: 10%; width: 80px; height: 80px; background: var(--white); border-radius: 50%; animation-delay: -1s; }
        .shape:nth-child(2) { top: 20%; right: 10%; width: 60px; height: 60px; background: var(--light-green); border-radius: 10px; animation-delay: -3s; }
        .shape:nth-child(3) { bottom: 20%; left: 5%; width: 100px; height: 100px; background: var(--dark-blue); border-radius: 50%; animation-delay: -2s; }
        .shape:nth-child(4) { bottom: 30%; right: 20%; width: 70px; height: 70px; background: var(--white); transform: rotate(45deg); animation-delay: -4s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .hero-content { position: relative; z-index: 2; }
        .hero-title { font-size: 3.5rem; font-weight: 700; color: var(--white); margin-bottom: 1.5rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
        .hero-subtitle { font-size: 1.3rem; color: var(--light-green); margin-bottom: 2rem; font-weight: 300; }
        .hero-description { font-size: 1.1rem; color: var(--white); margin-bottom: 3rem; opacity: 0.9; line-height: 1.6; }

        .cta-button {
            background: linear-gradient(45deg, var(--dark-blue), var(--secondary-blue));
            border: none;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--white);
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(79, 138, 158, 0.3);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(79, 138, 158, 0.4);
        }

        .features-grid { position: relative; z-index: 2; }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .feature-icon { font-size: 3rem; color: var(--light-green); margin-bottom: 1rem; }
        .feature-title { font-size: 1.3rem; font-weight: 600; color: var(--white); margin-bottom: 1rem; }
        .feature-text { color: var(--white); opacity: 0.9; line-height: 1.5; }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
        }

        .navbar-brand { font-size: 1.8rem; font-weight: 700; color: var(--white) !important; }
        .navbar-nav .nav-link { color: var(--white) !important; font-weight: 500; margin: 0 1rem; transition: all 0.3s ease; }
        .navbar-nav .nav-link:hover { color: var(--light-green) !important; transform: translateY(-2px); }

        .scroll-indicator { position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); color: var(--white); animation: bounce 2s infinite; }
        @keyframes bounce { 0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); } 40% { transform: translateX(-50%) translateY(-10px); } 60% { transform: translateX(-50%) translateY(-5px); } }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php?url=auth/landing">
                <i class="fas fa-graduation-cap me-2"></i>EduLearn
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#beranda">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tentang">Tentang</a></li>
                    <li class="nav-item"><a class="nav-link" href="#fitur">Fitur</a></li>
                    <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
                    <li class="nav-item"><a class="btn btn-outline-light rounded-pill px-4 ms-3" href="index.php?url=auth/login">Masuk</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section" id="beranda">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
        
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">Selamat Datang di <span style="color: var(--light-green);">EduLearn</span></h1>
                        <p class="hero-subtitle">Platform E-Learning Modern untuk Perguruan Tinggi</p>
                        <p class="hero-description">Bergabunglah dengan revolusi pendidikan digital. Akses ribuan materi pembelajaran, berinteraksi dengan dosen dan mahasiswa, dan raih prestasi akademik terbaik Anda melalui platform pembelajaran online yang inovatif dan user-friendly.</p>
                        <button class="cta-button pulse-animation" onclick="redirectToLogin()">
                            <i class="fas fa-sign-in-alt me-2"></i> Masuk ke Platform
                        </button>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="features-grid">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="feature-card">
                                    <div class="feature-icon"><i class="fas fa-book-open"></i></div>
                                    <h3 class="feature-title">Materi Lengkap</h3>
                                    <p class="feature-text">Akses ribuan materi pembelajaran dari berbagai jurusan dan mata kuliah</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="feature-card">
                                    <div class="feature-icon"><i class="fas fa-users"></i></div>
                                    <h3 class="feature-title">Kolaborasi</h3>
                                    <p class="feature-text">Berinteraksi dengan dosen dan mahasiswa melalui forum diskusi</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="feature-card">
                                    <div class="feature-icon"><i class="fas fa-clock"></i></div>
                                    <h3 class="feature-title">Fleksibel</h3>
                                    <p class="feature-text">Belajar kapan saja dan di mana saja sesuai dengan jadwal Anda</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="feature-card">
                                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                                    <h3 class="feature-title">Progress Tracking</h3>
                                    <p class="feature-text">Pantau perkembangan belajar dengan analitik yang mendalam</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="scroll-indicator">
            <i class="fas fa-chevron-down fa-2x"></i>
        </div>
    </section>

    <section id="tentang" class="py-5" style="background: var(--light-green);">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="section-title mb-4" style="color: var(--dark-blue); font-size: 2.5rem; font-weight: 700;">Tentang EduLearn</h2>
                    <p class="lead mb-4" style="color: var(--dark-blue); font-size: 1.2rem;">EduLearn adalah platform e-learning terdepan yang dirancang khusus untuk perguruan tinggi modern.</p>
                    <p style="color: var(--dark-blue); line-height: 1.8; margin-bottom: 1.5rem;">Kami menyediakan solusi pembelajaran digital yang komprehensif, memungkinkan dosen dan mahasiswa berinteraksi dalam lingkungan pembelajaran yang dinamis dan efektif.</p>
                </div>
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 text-center" style="background: var(--white); border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <i class="fas fa-laptop-code fa-2x mb-2" style="color: var(--secondary-blue);"></i>
                                <h6 style="color: var(--dark-blue);">Digital Learning</h6>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 text-center" style="background: var(--white); border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <i class="fas fa-mobile-alt fa-2x mb-2" style="color: var(--secondary-blue);"></i>
                                <h6 style="color: var(--dark-blue);">Mobile Friendly</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="kontak" class="py-5" style="background: var(--white);">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="text-center mb-5">
                        <h2 class="section-title" style="color: var(--dark-blue); font-size: 2.5rem; font-weight: 700;">Hubungi Kami</h2>
                        <p class="lead" style="color: var(--dark-blue); font-size: 1.2rem; opacity: 0.8;">Ada pertanyaan? Tim support kami siap membantu Anda</p>
                    </div>

                    <div class="contact-form" style="background: rgba(99, 163, 241, 0.1); border-radius: 20px; padding: 2.5rem; border: 1px solid rgba(99, 163, 241, 0.2);">
                        <form method="POST" action="index.php?url=feedback/submit">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" style="color: var(--dark-blue); font-weight: 600;">Nama Lengkap</label>
                                    <input type="text" class="form-control" name="name" required style="border: 2px solid var(--primary-blue); border-radius: 10px; padding: 12px;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="color: var(--dark-blue); font-weight: 600;">Email</label>
                                    <input type="email" class="form-control" name="email" required style="border: 2px solid var(--primary-blue); border-radius: 10px; padding: 12px;">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" style="color: var(--dark-blue); font-weight: 600;">Subjek</label>
                                    <input type="text" class="form-control" name="subject" required style="border: 2px solid var(--primary-blue); border-radius: 10px; padding: 12px;">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" style="color: var(--dark-blue); font-weight: 600;">Pesan</label>
                                    <textarea class="form-control" rows="4" name="message" required style="border: 2px solid var(--primary-blue); border-radius: 10px; padding: 12px;"></textarea>
                                </div>
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn" style="background: linear-gradient(45deg, var(--dark-blue), var(--secondary-blue)); color: var(--white); border: none; padding: 15px 40px; border-radius: 25px; font-weight: 600; font-size: 1.1rem;">
                                        <i class="fas fa-paper-plane me-2"></i>Kirim Pesan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-4 text-center" style="background: var(--dark-blue); color: white;">
        <p class="mb-0">&copy; 2026 EduLearn. All rights reserved.</p>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function redirectToLogin() {
            window.location.href = "index.php?url=auth/login";
        }
    </script>
</body>
</html>
