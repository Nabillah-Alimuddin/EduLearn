<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materi & Tugas Dosen - EduLearn</title>
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
            <h2><i class="fas fa-folder-open me-2"></i>Manajemen Materi & Tugas</h2>
        </div>
    </div>

    <div class="container">
        <div class="card card-custom p-4">
            <h5 class="mb-3">Upload Materi / Tugas Baru</h5>
            <form action="index.php?url=lecturer/materitugas" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="upload_type" value="material">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Pilih Mata Kuliah</label>
                        <select class="form-select" name="course_id_upload" required>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['course_id']; ?>" <?= ($currentCourseId == $c['course_id']) ? 'selected' : ''; ?>><?= html_escape($c['course_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pilih File (PDF, DOCX, PPTX, dll)</label>
                        <input type="file" class="form-control" name="uploaded_file[]" required multiple>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-upload me-1"></i> Unggah File</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card card-custom p-4">
            <h5 class="mb-3">Daftar Berkas Terunggah</h5>
            <?php if (empty($materialsAndAssignments)): ?>
                <p class="text-muted mb-0">Belum ada materi atau tugas yang diunggah.</p>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($materialsAndAssignments as $item): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6><i class="<?= getFileIcon($item['file_type'] ?? ''); ?> me-2 text-primary"></i><?= html_escape($item['title']); ?></h6>
                                <small class="text-muted">Jatuh Tempo: <?= formatDateDisplay($item['due_date']); ?></small>
                            </div>
                            <?php if (!empty($item['file_path'])): ?>
                                <a href="<?= html_escape($item['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">Unduh</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
