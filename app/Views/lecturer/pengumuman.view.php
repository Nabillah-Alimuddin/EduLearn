<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengumuman - EduLearn</title>
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
            <h2><i class="fas fa-bullhorn me-2"></i>Kelola Pengumuman Dosen</h2>
        </div>
    </div>

    <div class="container">
        <div class="card card-custom p-4">
            <h5 class="mb-3">Buat Pengumuman Baru</h5>
            <form action="index.php?url=lecturer/pengumuman" method="POST">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Judul Pengumuman</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mata Kuliah (Opsional)</label>
                        <select class="form-select" name="subject">
                            <option value="">-- Pengumuman Umum --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['course_id']; ?>"><?= html_escape($c['course_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Isi Pengumuman</label>
                        <textarea class="form-control" name="content" rows="4" required></textarea>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-paper-plane me-1"></i> Publisikan</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card card-custom p-4">
            <h5 class="mb-3">Daftar Pengumuman Terkirim</h5>
            <?php if (empty($announcements)): ?>
                <p class="text-muted mb-0">Belum ada pengumuman yang dibuat.</p>
            <?php else: ?>
                <?php foreach ($announcements as $a): ?>
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="text-primary mb-1"><?= html_escape($a['title']); ?></h6>
                            <small class="text-muted"><?= formatDateDisplay($a['published_at']); ?></small>
                        </div>
                        <p class="small text-secondary mb-1"><?= !empty($a['course_name']) ? 'Mata Kuliah: ' . html_escape($a['course_name']) : 'Umum'; ?></p>
                        <p class="mb-0 small text-dark"><?= nl2br(html_escape($a['content'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
