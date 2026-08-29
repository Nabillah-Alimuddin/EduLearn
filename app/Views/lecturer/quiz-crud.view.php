<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kuis - EduLearn</title>
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
            <h2><i class="fas fa-edit me-2"></i>Kelola Kuis Pembelajaran</h2>
        </div>
    </div>

    <div class="container">
        <div class="card card-custom p-4">
            <h5 class="mb-3">Daftar Kuis Dibuat</h5>
            <?php if (empty($quizzes)): ?>
                <p class="text-muted">Belum ada kuis yang dibuat.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Mata Kuliah</th>
                                <th>Judul Kuis</th>
                                <th>Durasi</th>
                                <th>Jumlah Soal</th>
                                <th>Passing Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quizzes as $q): ?>
                                <tr>
                                    <td><?= html_escape($q['course_name']); ?></td>
                                    <td><strong><?= html_escape($q['title']); ?></strong></td>
                                    <td><?= $q['duration_minutes']; ?> Menit</td>
                                    <td><?= $q['total_questions']; ?> Soal</td>
                                    <td><?= $q['passing_score']; ?></td>
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
