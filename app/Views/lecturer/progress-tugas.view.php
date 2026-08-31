<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Pengumpulan Tugas - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #63A3F1, #4F8A9E); color: white; padding: 2rem 0; border-radius: 0 0 20px 20px; }
        .card-custom { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .stat-card { border-radius: 12px; padding: 1.25rem; color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .stat-card h3 { font-size: 2rem; font-weight: 700; margin-bottom: 0; }
        .accordion-button:not(.collapsed) { background-color: #eaf3ff; color: #3068b5; }
    </style>
</head>
<body>
    <div class="header mb-4">
        <div class="container">
            <a href="index.php?url=lecturer/dashboard" class="btn btn-light btn-sm rounded-pill mb-2"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            <h2><i class="fas fa-tasks me-2"></i>Progress Pengumpulan Tugas</h2>
            <p class="mb-0 text-white-50">Monitoring status pengumpulan tugas mahasiswa per kelas secara real-time.</p>
        </div>
    </div>

    <div class="container">
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card bg-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50">Total Kelas</small>
                            <h3><?= $summary['total_classes']; ?></h3>
                        </div>
                        <i class="fas fa-chalkboard-user fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50">Total Mahasiswa</small>
                            <h3><?= $summary['total_students']; ?></h3>
                        </div>
                        <i class="fas fa-user-graduate fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-dark-50">Total Tugas</small>
                            <h3><?= $summary['total_assignments']; ?></h3>
                        </div>
                        <i class="fas fa-file-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50">Completion Rate</small>
                            <h3><?= $summary['overall_completion_rate']; ?>%</h3>
                        </div>
                        <i class="fas fa-chart-line fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Progress Accordion -->
        <div class="card card-custom p-4">
            <h5 class="mb-3"><i class="fas fa-list-check me-2 text-primary"></i>Detail Pengumpulan per Kelas</h5>
            
            <?php if (empty($coursesProgress)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-folder-open fa-3x mb-3"></i>
                    <p>Belum ada mata kuliah yang diampu.</p>
                </div>
            <?php else: ?>
                <div class="accordion" id="courseProgressAccordion">
                    <?php foreach ($coursesProgress as $idx => $cp): ?>
                        <div class="accordion-item mb-3 border rounded shadow-sm overflow-hidden">
                            <h2 class="accordion-header" id="heading_<?= $cp['course_id']; ?>">
                                <button class="accordion-button <?= $idx !== 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_<?= $cp['course_id']; ?>">
                                    <div class="w-100 d-flex justify-content-between align-items-center me-3">
                                        <div>
                                            <strong><?= html_escape($cp['course_name']); ?></strong> (<?= html_escape($cp['course_code']); ?>)
                                            <span class="badge bg-secondary ms-2"><?= count($cp['students']); ?> Mahasiswa</span>
                                        </div>
                                        <span class="badge bg-primary rounded-pill"><?= count($cp['assignments']); ?> Tugas</span>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse_<?= $cp['course_id']; ?>" class="accordion-collapse collapse <?= $idx === 0 ? 'show' : ''; ?>" data-bs-parent="#courseProgressAccordion">
                                <div class="accordion-body">
                                    <?php if (empty($cp['assignments'])): ?>
                                        <p class="text-muted small mb-0">Belum ada tugas untuk mata kuliah ini.</p>
                                    <?php else: ?>
                                        <?php foreach ($cp['assignments'] as $a): 
                                            $totalSt = count($cp['students']);
                                            $compPct = $totalSt > 0 ? Math.round(($a['completed'] / $totalSt) * 100) : 0;
                                        ?>
                                            <div class="card border mb-4">
                                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-primary"><?= html_escape($a['title']); ?></h6>
                                                        <small class="text-muted"><i class="far fa-clock me-1"></i>Tenggat: <?= formatDateDisplay($a['due_date']); ?></small>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-success me-1"><i class="fas fa-check me-1"></i> Tepat Waktu: <?= $a['completed']; ?></span>
                                                        <span class="badge bg-warning text-dark me-1"><i class="fas fa-exclamation-triangle me-1"></i> Terlambat: <?= $a['late']; ?></span>
                                                        <span class="badge bg-danger me-2"><i class="fas fa-times me-1"></i> Belum: <?= $a['not_submitted']; ?></span>
                                                        <a href="index.php?url=lecturer/inputNilai&course_id=<?= $cp['course_id']; ?>&assignment_id=<?= $a['assignment_id']; ?>" class="btn btn-sm btn-primary rounded-pill px-3 py-1">
                                                            <i class="fas fa-edit me-1"></i> Periksa & Beri Nilai
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-hover align-middle mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th style="width: 40px;">No</th>
                                                                    <th>NIM</th>
                                                                    <th>Nama Mahasiswa</th>
                                                                    <th>Status</th>
                                                                    <th>Waktu Pengumpulan</th>
                                                                    <th class="text-center">Berkas Submission</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $sNo = 1; foreach ($a['students'] as $st): ?>
                                                                    <tr>
                                                                        <td class="text-center"><?= $sNo++; ?></td>
                                                                        <td><?= html_escape($st['nim'] ?? '-'); ?></td>
                                                                        <td><strong><?= html_escape($st['full_name']); ?></strong></td>
                                                                        <td>
                                                                            <?php if ($st['status'] === 'completed'): ?>
                                                                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Tepat Waktu</span>
                                                                            <?php elseif ($st['status'] === 'late'): ?>
                                                                                <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i> Terlambat</span>
                                                                            <?php else: ?>
                                                                                <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Belum Mengumpulkan</span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                        <td class="small text-muted">
                                                                            <?= $st['submitted_at'] ? formatDateDisplay($st['submitted_at']) : '-'; ?>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <?php if (!empty($st['file_path'])): ?>
                                                                                <a href="<?= html_escape($st['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                                                                                    <i class="fas fa-download me-1"></i> Unduh File
                                                                                </a>
                                                                            <?php else: ?>
                                                                                <span class="text-muted small">-</span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
