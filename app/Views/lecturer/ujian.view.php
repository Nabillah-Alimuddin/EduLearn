<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Ujian Dosen - EduLearn</title>
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
            <h2><i class="fas fa-clipboard-check me-2"></i>Jadwal Ujian (<?= html_escape($examType); ?>)</h2>
        </div>
    </div>

    <div class="container">
        <div class="card card-custom p-4">
            <div class="mb-3">
                <a href="index.php?url=exam/lecturerExam&exam_type=UTS" class="btn btn-<?= $examType === 'UTS' ? 'primary' : 'outline-primary'; ?> rounded-pill px-3 me-2">UTS</a>
                <a href="index.php?url=exam/lecturerExam&exam_type=UAS" class="btn btn-<?= $examType === 'UAS' ? 'primary' : 'outline-primary'; ?> rounded-pill px-3">UAS</a>
            </div>

            <?php if (empty($exams)): ?>
                <p class="text-muted mb-0">Belum ada jadwal ujian <?= html_escape($examType); ?>.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Mata Kuliah</th>
                                <th>Judul Ujian</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Ruang</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($exams as $e): ?>
                                <tr>
                                    <td><?= html_escape($e['course_name']); ?></td>
                                    <td><strong><?= html_escape($e['title']); ?></strong></td>
                                    <td><?= formatDateDisplay($e['exam_date']); ?></td>
                                    <td><?= html_escape(substr($e['start_time'], 0, 5)); ?> - <?= html_escape(substr($e['end_time'], 0, 5)); ?></td>
                                    <td><?= html_escape($e['room'] ?? 'Online'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
