<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuis Pembelajaran - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4F8A9E 0%, #63A3F1 100%);
            --card-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        body {
            background: #f0f4f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .hero-header {
            background: linear-gradient(135deg, #63A3F1 0%, #4F8A9E 100%);
            color: white;
            padding: 2.2rem 0;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 6px 20px rgba(79, 138, 158, 0.2);
            margin-bottom: 2rem;
        }

        .quiz-card {
            background: white;
            border-radius: 18px;
            padding: 1.75rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.75rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .quiz-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(79, 138, 158, 0.18);
        }

        .quiz-icon-badge {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            background: #eaf3ff;
            color: #3068b5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }

        .quiz-meta-tag {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 0.85rem;
            color: #555;
        }

        .btn-start-quiz {
            background: linear-gradient(45deg, #4b89dc, #3068b5);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .btn-start-quiz:hover {
            background: linear-gradient(45deg, #3068b5, #1e40af);
            color: white;
            box-shadow: 0 5px 15px rgba(48, 104, 181, 0.3);
        }
    </style>
</head>
<body>
    <!-- Hero Header -->
    <div class="hero-header">
        <div class="container">
            <a href="index.php?url=student/dashboard" class="btn btn-light btn-sm rounded-pill mb-3 px-3">
                <i class="fas fa-arrow-left me-1"></i> Dashboard Mahasiswa
            </a>
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="fw-bold mb-2"><i class="fas fa-question-circle me-2"></i>Kuis Pembelajaran Interactive</h1>
                    <p class="mb-0 text-white-50 fs-6">Uji pemahaman materi perkuliahan Anda dengan mengerjakan kuis berbasis timer dan evaluasi instan.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <span class="badge bg-white text-primary px-3 py-2 fs-6 rounded-pill shadow-sm">
                        <i class="fas fa-list me-1"></i> <?= count($availableQuizzes); ?> Kuis Tersedia
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <?php if (empty($availableQuizzes)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted my-4">
                <div class="py-4">
                    <i class="fas fa-check-double fa-4x text-success mb-3"></i>
                    <h4>Semua Kuis Telah Dikerjakan!</h4>
                    <p class="mb-0">Tidak ada kuis baru yang perlu dikerjakan saat ini. Silakan periksa kembali jadwal dari dosen Anda.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($availableQuizzes as $q): ?>
                    <div class="col-md-6 col-lg-4 d-flex align-items-stretch">
                        <div class="quiz-card w-100">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="quiz-icon-badge">
                                    <i class="fas fa-pen-ruler"></i>
                                </div>
                                <span class="badge bg-success px-3 py-2 rounded-pill">
                                    Passing Score: <?= $q['passing_score'] ?? 70; ?>%
                                </span>
                            </div>

                            <span class="text-primary small fw-bold text-uppercase mb-1"><i class="fas fa-book me-1"></i><?= html_escape($q['course_name']); ?></span>
                            <h5 class="fw-bold text-dark mb-2"><?= html_escape($q['title']); ?></h5>
                            <p class="small text-secondary flex-grow-1 mb-3"><?= html_escape($q['description'] ?? 'Kuis evaluasi pemahaman materi.'); ?></p>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="quiz-meta-tag">
                                    <i class="far fa-clock text-primary me-1"></i> Durasi: <strong><?= $q['duration_minutes']; ?> Mnt</strong>
                                </div>
                                <div class="quiz-meta-tag">
                                    <i class="fas fa-list-ol text-info me-1"></i> Total: <strong><?= $q['total_questions']; ?> Soal</strong>
                                </div>
                            </div>

                            <?php if (!empty($q['end_date'])): ?>
                                <div class="small text-muted mb-3">
                                    <i class="far fa-calendar-alt me-1"></i> Batas: <?= formatDateDisplay($q['end_date']); ?>
                                </div>
                            <?php endif; ?>

                            <a href="index.php?url=quiz/take&quiz_id=<?= $q['quiz_id']; ?>" class="btn-start-quiz">
                                <i class="fas fa-play me-1"></i> Mulai Kuis Sekarang
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
