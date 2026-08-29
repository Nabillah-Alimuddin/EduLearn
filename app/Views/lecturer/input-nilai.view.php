<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai - EduLearn</title>
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
            <h2><i class="fas fa-pen-fancy me-2"></i>Input & Edit Nilai Mahasiswa</h2>
        </div>
    </div>

    <div class="container">
        <div class="card card-custom p-4">
            <form method="GET" action="index.php">
                <input type="hidden" name="url" value="lecturer/inputNilai">
                <div class="row g-3 align-items-end mb-4">
                    <div class="col-md-5">
                        <label class="form-label">Mata Kuliah</label>
                        <select class="form-select" name="course_id" onchange="this.form.submit()">
                            <option value="">-- Pilih Mata Kuliah --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['course_id']; ?>" <?= ($selectedCourseId == $c['course_id']) ? 'selected' : ''; ?>><?= html_escape($c['course_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>

            <?php if (!$selectedCourseId): ?>
                <p class="text-muted text-center py-4">Silakan pilih mata kuliah di atas untuk menginput nilai.</p>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-1"></i> Penginputan nilai komponen tugas, UTS, UAS, dan partisipasi dilakukan secara terintegrasi.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
