<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #63A3F1 0%, #4F8A9E 100%); color: white; padding: 2.2rem 0; border-radius: 0 0 20px 20px; box-shadow: 0 6px 20px rgba(79, 138, 158, 0.2); margin-bottom: 2rem; }
        .announcement-card { background: white; border-radius: 15px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="header mb-4">
        <div class="container">
            <a href="index.php?url=student/dashboard" class="btn btn-light btn-sm rounded-pill mb-2"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            <h2><i class="fas fa-bullhorn me-2"></i>Pengumuman Akademia</h2>
        </div>
    </div>

    <div class="container">
        <?php if (empty($announcements)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-bell-slash fa-3x mb-3"></i>
                <p>Belum ada pengumuman terbaru.</p>
            </div>
        <?php else: ?>
            <?php foreach ($announcements as $a): ?>
                <div class="announcement-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0 text-primary"><?= html_escape($a['title']); ?></h5>
                        <small class="text-muted"><i class="far fa-clock me-1"></i><?= formatDateDisplay($a['published_at']); ?></small>
                    </div>
                    <p class="text-muted small mb-2"><i class="fas fa-user me-1"></i>Dosen: <?= html_escape($a['lecturer_full_name'] ?? 'Umum'); ?> <?= !empty($a['course_name']) ? ' (' . html_escape($a['course_name']) . ')' : ''; ?></p>
                    <p class="card-text text-dark"><?= nl2br(html_escape($a['content'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
