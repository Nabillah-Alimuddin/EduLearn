<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Mengajar - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #63A3F1, #4F8A9E); color: white; padding: 2rem 0; border-radius: 0 0 20px 20px; }
        .schedule-card { background: white; border-radius: 15px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 1rem; border-left: 5px solid #63A3F1; }
    </style>
</head>
<body>
    <div class="header mb-4">
        <div class="container">
            <a href="index.php?url=lecturer/dashboard" class="btn btn-light btn-sm rounded-pill mb-2"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            <h2><i class="fas fa-calendar-alt me-2"></i>Jadwal Mengajar</h2>
        </div>
    </div>

    <div class="container">
        <?php if (empty($schedules)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                <p>Belum ada jadwal mengajar.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($schedules as $s): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="schedule-card">
                            <span class="badge bg-primary mb-2"><?= html_escape($s['day_of_week']); ?></span>
                            <h5 class="card-title"><?= html_escape($s['course_name']); ?></h5>
                            <p class="text-muted mb-1"><i class="far fa-clock me-1"></i><?= html_escape(substr($s['start_time'], 0, 5)); ?> - <?= html_escape(substr($s['end_time'], 0, 5)); ?></p>
                            <p class="text-muted mb-0"><i class="fas fa-map-marker-alt me-1"></i>Ruang: <?= html_escape($s['room'] ?? 'TBA'); ?> (<?= html_escape($s['class_type']); ?>)</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
