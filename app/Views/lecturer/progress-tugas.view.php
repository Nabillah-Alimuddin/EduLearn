<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Tugas - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #63A3F1, #4F8A9E); color: white; padding: 2rem 0; border-radius: 0 0 20px 20px; }
        .card-custom { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="header mb-4">
        <div class="container">
            <a href="index.php?url=lecturer/dashboard" class="btn btn-light btn-sm rounded-pill mb-2"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            <h2><i class="fas fa-tasks me-2"></i>Progress Pengumpulan Tugas</h2>
        </div>
    </div>

    <div class="container">
        <div class="card card-custom p-4">
            <h5 class="mb-3">Mata Kuliah Diampu</h5>
            <?php if (empty($courses)): ?>
                <p class="text-muted mb-0">Belum ada kelas yang diampu.</p>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($courses as $c): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6><?= html_escape($c['course_name']); ?> (<?= html_escape($c['course_code']); ?>)</h6>
                            </div>
                            <a href="index.php?url=lecturer/inputNilai&course_id=<?= $c['course_id']; ?>" class="btn btn-sm btn-primary rounded-pill">Periksa Pengumpulan</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
