<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kuis - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #4b89dc, #3068b5); color: white; padding: 2rem 0; border-radius: 0 0 20px 20px; }
        .quiz-card { background: white; border-radius: 15px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 1.5rem; transition: transform 0.2s; }
        .quiz-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>
    <div class="header mb-4">
        <div class="container">
            <a href="index.php?url=student/dashboard" class="btn btn-light btn-sm rounded-pill mb-2"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            <h2><i class="fas fa-question-circle me-2"></i>Kuis Pembelajaran</h2>
        </div>
    </div>

    <div class="container">
        <?php if (empty($availableQuizzes)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-check-double fa-3x mb-3 text-success"></i>
                <p>Tidak ada kuis yang perlu dikerjakan saat ini.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($availableQuizzes as $q): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="quiz-card">
                            <h5 class="text-primary mb-1"><?= html_escape($q['title']); ?></h5>
                            <p class="text-muted small mb-2"><i class="fas fa-book me-1"></i><?= html_escape($q['course_name']); ?></p>
                            <p class="small text-secondary mb-3"><?= html_escape($q['description'] ?? 'Kuis evaluasi materi.'); ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3 text-muted small">
                                <span><i class="far fa-clock me-1"></i><?= $q['duration_minutes']; ?> Menit</span>
                                <span><i class="fas fa-list-ol me-1"></i><?= $q['total_questions']; ?> Soal</span>
                            </div>

                            <a href="index.php?url=quiz/take&quiz_id=<?= $q['quiz_id']; ?>" class="btn btn-primary w-100 rounded-pill"><i class="fas fa-play me-1"></i> Mulai Kuis</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
