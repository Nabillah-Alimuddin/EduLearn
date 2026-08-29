<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kelas - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #63A3F1, #4F8A9E); color: white; padding: 2rem 0; border-radius: 0 0 20px 20px; }
        .course-card { background: white; border-radius: 15px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="header mb-4">
        <div class="container">
            <a href="index.php?url=lecturer/dashboard" class="btn btn-light btn-sm rounded-pill mb-2"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            <h2><i class="fas fa-chalkboard me-2"></i>Mata Kuliah Diampu</h2>
        </div>
    </div>

    <div class="container">
        <?php if (empty($courses)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-info-circle fa-3x mb-3"></i>
                <p>Belum ada mata kuliah yang diampu.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($courses as $c): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="course-card">
                            <h5 class="text-primary mb-1"><?= html_escape($c['course_name']); ?></h5>
                            <p class="text-muted small mb-2">Kode: <?= html_escape($c['course_code']); ?> | SKS: <?= $c['credits']; ?></p>
                            <a href="index.php?url=lecturer/materitugas&course_id=<?= $c['course_id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill mt-2 me-1"><i class="fas fa-folder-open me-1"></i> Materi & Tugas</a>
                            <a href="index.php?url=lecturer/inputNilai&course_id=<?= $c['course_id']; ?>" class="btn btn-sm btn-outline-success rounded-pill mt-2"><i class="fas fa-pen me-1"></i> Nilai</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
