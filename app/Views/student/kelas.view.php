<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= html_escape($courseDetails['course_name'] ?? 'Detail Kelas'); ?> - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #4A90E2, #7FB3D3); color: white; padding: 2.5rem 0; border-radius: 0 0 25px 25px; shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .card-custom { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .nav-tabs .nav-link { color: #4F8A9E; font-weight: 600; }
        .nav-tabs .nav-link.active { color: #3068b5; border-bottom: 3px solid #3068b5; background-color: white; }
    </style>
</head>
<body>
    <!-- Header Virtual Classroom -->
    <div class="header mb-4">
        <div class="container">
            <a href="index.php?url=student/dashboard" class="btn btn-light btn-sm rounded-pill mb-3"><i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard</a>
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-1"><i class="fas fa-graduation-cap me-2"></i><?= html_escape($courseDetails['course_name'] ?? 'Mata Kuliah'); ?></h2>
                    <p class="mb-0 text-white-50 fs-6">
                        Kode: <strong><?= html_escape($courseDetails['course_code'] ?? '-'); ?></strong> | 
                        Kredit: <strong><?= $courseDetails['credits'] ?? 3; ?> SKS</strong> | 
                        Dosen Pengampu: <strong><?= html_escape($courseDetails['lecturer_name'] ?? 'Dosen'); ?><?= !empty($courseDetails['gelar']) ? ', ' . html_escape($courseDetails['gelar']) : ''; ?></strong>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="index.php?url=student/krs" class="btn btn-outline-light rounded-pill px-3 py-2"><i class="fas fa-list me-1"></i> Kelola KRS</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Navigation Tabs for Ruang Kelas -->
        <ul class="nav nav-tabs mb-4" id="classTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="materi-tab" data-bs-toggle="tab" data-bs-target="#tab-materi" type="button">
                    <i class="fas fa-folder-open me-1"></i> Materi & Berkas Ajar (<?= count($materialsData); ?>)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="kuis-tab" data-bs-toggle="tab" data-bs-target="#tab-kuis" type="button">
                    <i class="fas fa-question-circle me-1"></i> Kuis Evaluasi (<?= count($courseQuizzes); ?>)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="pengumuman-tab" data-bs-toggle="tab" data-bs-target="#tab-pengumuman" type="button">
                    <i class="fas fa-bullhorn me-1"></i> Pengumuman Kelas (<?= count($announcements); ?>)
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- TAB 1: MATERI & BERKAS AJAR -->
            <div class="tab-pane fade show active" id="tab-materi">
                <div class="card card-custom p-4">
                    <h5 class="mb-3"><i class="fas fa-folder-open me-2 text-primary"></i>Daftar Berkas & Materi Pembelajaran</h5>
                    <?php if (empty($materialsData)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Belum ada materi pembelajaran yang diunggah dosen untuk mata kuliah ini.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($materialsData as $material): ?>
                                <div class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center rounded-3 mb-2 border shadow-sm">
                                    <div>
                                        <h6 class="mb-1 fw-bold text-primary">
                                            <i class="fas fa-file-alt me-2"></i><?= html_escape($material['title']); ?>
                                        </h6>
                                        <p class="small text-secondary mb-1"><?= html_escape($material['description'] ?? ''); ?></p>
                                        <small class="text-muted"><i class="far fa-clock me-1"></i>Tenggat: <?= formatDateDisplay($material['due_date'] ?? $material['uploaded_at']); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <?php if (!empty($material['file_path'])): ?>
                                            <a href="<?= html_escape($material['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill me-2 px-3">
                                                <i class="fas fa-download me-1"></i> Unduh
                                            </a>
                                        <?php endif; ?>
                                        <a href="index.php?url=student/deadline" class="btn btn-sm btn-primary rounded-pill px-3">
                                            <i class="fas fa-upload me-1"></i> Pengumpulan
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB 2: KUIS EVALUASI -->
            <div class="tab-pane fade" id="tab-kuis">
                <div class="card card-custom p-4">
                    <h5 class="mb-3"><i class="fas fa-question-circle me-2 text-primary"></i>Kuis & Evaluasi Pemahaman</h5>
                    <?php if (empty($courseQuizzes)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                            <p>Tidak ada kuis aktif untuk mata kuliah ini saat ini.</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($courseQuizzes as $q): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded-4 p-3 bg-white shadow-sm">
                                        <h6 class="fw-bold text-primary mb-1"><?= html_escape($q['title']); ?></h6>
                                        <p class="small text-muted mb-2"><?= html_escape($q['description'] ?? 'Kuis evaluasi pemahaman materi.'); ?></p>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="badge bg-info text-dark"><i class="far fa-clock me-1"></i><?= $q['duration_minutes']; ?> mnt</span>
                                            <span class="badge bg-success">Passing Score: <?= $q['passing_score'] ?? 70; ?>%</span>
                                        </div>
                                        <a href="index.php?url=quiz/take&quiz_id=<?= $q['quiz_id']; ?>" class="btn btn-primary rounded-pill w-100 fw-bold">
                                            <i class="fas fa-play me-1"></i> Kerjakan Kuis
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB 3: PENGUMUMAN KELAS -->
            <div class="tab-pane fade" id="tab-pengumuman">
                <div class="card card-custom p-4">
                    <h5 class="mb-3"><i class="fas fa-bullhorn me-2 text-primary"></i>Pengumuman Kelas</h5>
                    <?php if (empty($announcements)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-comment-slash fa-3x mb-3"></i>
                            <p>Belum ada pengumuman khusus untuk mata kuliah ini.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($announcements as $anc): ?>
                            <div class="border-start border-4 border-primary rounded p-3 mb-3 bg-light shadow-sm">
                                <h6 class="fw-bold text-dark mb-1"><?= html_escape($anc['title']); ?></h6>
                                <p class="mb-2 text-secondary small" style="white-space: pre-wrap;"><?= html_escape($anc['content']); ?></p>
                                <div class="text-muted small">
                                    <i class="far fa-user me-1"></i> Dosen: <?= html_escape($anc['lecturer_full_name'] ?? 'Dosen Pengampu'); ?> | 
                                    <i class="far fa-clock me-1"></i> Dipublikasikan: <?= formatDateDisplay($anc['published_at']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
