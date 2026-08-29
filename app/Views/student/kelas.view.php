<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Mata Kuliah - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #4A90E2, #7FB3D3); color: white; padding: 2rem 0; border-radius: 0 0 20px 20px; }
        .card-custom { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="header mb-4">
        <div class="container">
            <a href="index.php?url=student/dashboard" class="btn btn-light btn-sm rounded-pill mb-2"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            <h2><i class="<?= getCourseIcon($courseDetails['course_name'] ?? ''); ?> me-2"></i><?= html_escape($courseDetails['course_name'] ?? 'Mata Kuliah'); ?></h2>
            <p class="mb-0">Kode: <?= html_escape($courseDetails['course_code'] ?? '-'); ?> | Dosen: <?= html_escape($courseDetails['lecturer_name'] ?? 'Dosen'); ?><?= !empty($courseDetails['gelar']) ? ', ' . html_escape($courseDetails['gelar']) : ''; ?></p>
        </div>
    </div>

    <div class="container">
        <div class="card card-custom p-4">
            <h4 class="mb-3"><i class="fas fa-folder-open me-2 text-primary"></i>Materi Pembelajaran</h4>
            <?php if (empty($materialsData)): ?>
                <p class="text-muted">Belum ada materi pembelajaran yang diunggah untuk mata kuliah ini.</p>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($materialsData as $material): ?>
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><i class="<?= getFileIcon($material['file_type'] ?? ''); ?> me-2 text-primary"></i><?= html_escape($material['title']); ?></h6>
                                <small class="text-muted">Diunggah: <?= formatDateDisplay($material['uploaded_at']); ?></small>
                            </div>
                            <?php if (!empty($material['file_path'])): ?>
                                <a href="<?= html_escape($material['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fas fa-download me-1"></i> Unduh</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
