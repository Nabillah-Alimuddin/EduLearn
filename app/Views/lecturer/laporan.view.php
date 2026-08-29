<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Nilai - EduLearn</title>
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
            <h2><i class="fas fa-file-excel me-2"></i>Laporan Rekapitulasi Nilai</h2>
        </div>
    </div>

    <div class="container">
        <div class="card card-custom p-4">
            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <label class="form-label">Pilih Mata Kuliah</label>
                    <select class="form-select" onchange="location = this.value;">
                        <?php foreach ($courses as $c): ?>
                            <option value="index.php?url=lecturer/laporan&course_id=<?= $c['course_id']; ?>" <?= ($selectedCourseId == $c['course_id']) ? 'selected' : ''; ?>>
                                <?= html_escape($c['course_name']); ?> (<?= html_escape($c['course_code']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <?php if ($selectedCourseId): ?>
                        <a href="index.php?url=grade/exportExcel&course_id=<?= $selectedCourseId; ?>" class="btn btn-success rounded-pill px-4">
                            <i class="fas fa-file-excel me-1"></i> Ekspor ke Excel (.csv)
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <p class="text-muted small">Silakan pilih mata kuliah di atas lalu klik tombol <strong>Ekspor ke Excel</strong> untuk mengunduh file laporan rekap nilai mahasiswa secara otomatis.</p>
        </div>
    </div>
</body>
</html>
