<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deadline Tugas - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #4A90E2, #7FB3D3); color: white; padding: 2rem 0; border-radius: 0 0 20px 20px; }
        .deadline-card { background: white; border-radius: 15px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 1rem; border-left: 5px solid #e74c3c; }
    </style>
</head>
<body>
    <div class="header mb-4">
        <div class="container">
            <a href="index.php?url=student/dashboard" class="btn btn-light btn-sm rounded-pill mb-2"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            <h2><i class="fas fa-clock me-2"></i>Tenggat Waktu Tugas</h2>
        </div>
    </div>

    <div class="container">
        <?php if (empty($deadlines)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                <p>Semua tugas telah diselesaikan atau belum ada tugas baru.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($deadlines as $d): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="deadline-card">
                            <span class="badge bg-danger mb-2">Tenggat: <?= formatDateDisplay($d['due_date']); ?></span>
                            <h5 class="card-title"><?= html_escape($d['title']); ?></h5>
                            <p class="text-muted small mb-2"><i class="fas fa-book me-1"></i><?= html_escape($d['course_name']); ?></p>
                            <p class="card-text small text-secondary"><?= html_escape($d['description'] ?? 'Tidak ada deskripsi.'); ?></p>
                            
                            <?php if ($d['submission_id']): ?>
                                <span class="badge bg-success px-3 py-2"><i class="fas fa-check me-1"></i> Sudah Dikumpulkan</span>
                            <?php else: ?>
                                <button class="btn btn-sm btn-primary rounded-pill mt-2" onclick="openSubmitModal(<?= $d['assignment_id']; ?>)">Kumpulkan Tugas</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function openSubmitModal(id) {
            const text = prompt("Masukkan teks pengumpulan tugas atau catatan:");
            if (text !== null) {
                const formData = new FormData();
                formData.append('assignment_id', id);
                formData.append('submission_text', text);

                fetch('index.php?url=student/submitAssignment', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    alert(res.message || res.error);
                    location.reload();
                });
            }
        }
    </script>
</body>
</html>
