<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenggat Waktu Tugas - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #63A3F1 0%, #4F8A9E 100%); color: white; padding: 2.2rem 0; border-radius: 0 0 20px 20px; box-shadow: 0 6px 20px rgba(79, 138, 158, 0.2); margin-bottom: 2rem; }
        .card-custom { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .deadline-card { background: white; border-radius: 15px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 1.5rem; border-left: 5px solid #4F8A9E; transition: transform 0.2s; }
        .deadline-card:hover { transform: translateY(-3px); }
        .deadline-card.urgent { border-left-color: #e74c3c; }
        .deadline-card.submitted { border-left-color: #2ec4b6; }
        .stat-card { border-radius: 12px; padding: 1.25rem; color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .stat-card h3 { font-size: 1.8rem; font-weight: 700; margin-bottom: 0; }
    </style>
</head>
<body>
    <div class="header mb-4">
        <div class="container">
            <a href="index.php?url=student/dashboard" class="btn btn-light btn-sm rounded-pill mb-2"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            <h2><i class="fas fa-clock me-2"></i>Tenggat Waktu & Pengumpulan Tugas</h2>
            <p class="mb-0 text-white-50">Pantau seluruh tenggat waktu tugas dan unggah berkas pengumpulan Anda.</p>
        </div>
    </div>

    <div class="container">
        <?php
            $totalDeadlines = count($deadlines);
            $submittedCount = 0;
            $pendingCount = 0;
            $lateCount = 0;
            $now = time();

            foreach ($deadlines as $d) {
                if ($d['submission_id']) {
                    $submittedCount++;
                } else {
                    $dueTs = strtotime($d['due_date']);
                    if ($dueTs < $now) {
                        $lateCount++;
                    } else {
                        $pendingCount++;
                    }
                }
            }
        ?>

        <!-- Stat Cards Summary -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card bg-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50">Total Tugas</small>
                            <h3><?= $totalDeadlines; ?></h3>
                        </div>
                        <i class="fas fa-tasks fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50">Sudah Dikumpulkan</small>
                            <h3><?= $submittedCount; ?></h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-dark-50">Belum Dikumpulkan</small>
                            <h3><?= $pendingCount; ?></h3>
                        </div>
                        <i class="fas fa-hourglass-half fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-danger">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50">Melewati Tenggat</small>
                            <h3><?= $lateCount; ?></h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <ul class="nav nav-pills mb-4" id="deadlineFilters">
            <li class="nav-item">
                <button class="nav-link active rounded-pill me-2" onclick="filterDeadlines('all')">Semua Tugas (<?= $totalDeadlines; ?>)</button>
            </li>
            <li class="nav-item">
                <button class="nav-link rounded-pill me-2" onclick="filterDeadlines('pending')">Belum Dikumpulkan (<?= $pendingCount + $lateCount; ?>)</button>
            </li>
            <li class="nav-item">
                <button class="nav-link rounded-pill" onclick="filterDeadlines('submitted')">Sudah Dikumpulkan (<?= $submittedCount; ?>)</button>
            </li>
        </ul>

        <?php if (empty($deadlines)): ?>
            <div class="card card-custom p-5 text-center text-muted">
                <i class="fas fa-calendar-check fa-4x mb-3 text-success"></i>
                <h5>Tidak Ada Tugas</h5>
                <p>Semua tugas telah diselesaikan atau belum ada tugas baru dari dosen.</p>
            </div>
        <?php else: ?>
            <div class="row" id="deadlinesList">
                <?php foreach ($deadlines as $d): 
                    $isSubmitted = !empty($d['submission_id']);
                    $dueTs = strtotime($d['due_date']);
                    $isOverdue = (!$isSubmitted && $dueTs < $now);
                    $cardClass = $isSubmitted ? 'submitted' : ($isOverdue ? 'urgent' : '');
                    $filterCategory = $isSubmitted ? 'submitted' : 'pending';
                ?>
                    <div class="col-md-6 col-lg-6 deadline-item" data-category="<?= $filterCategory; ?>">
                        <div class="deadline-card <?= $cardClass; ?>">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge <?= $isSubmitted ? 'bg-success' : ($isOverdue ? 'bg-danger' : 'bg-primary'); ?> px-3 py-2">
                                    <i class="far fa-clock me-1"></i> Tenggat: <?= formatDateDisplay($d['due_date']); ?>
                                </span>
                                <?php if ($isSubmitted): ?>
                                    <span class="badge bg-teal text-white bg-info"><i class="fas fa-check-double me-1"></i> Terkumpul</span>
                                <?php elseif ($isOverdue): ?>
                                    <span class="badge bg-danger"><i class="fas fa-exclamation-circle me-1"></i> Terlambat</span>
                                <?php endif; ?>
                            </div>

                            <h5 class="card-title text-primary fw-bold mb-1"><?= html_escape($d['title']); ?></h5>
                            <p class="text-muted small mb-2"><i class="fas fa-book me-1"></i>Mata Kuliah: <strong><?= html_escape($d['course_name']); ?></strong> (<?= html_escape($d['course_code']); ?>)</p>
                            <p class="card-text small text-secondary mb-3"><?= html_escape($d['description'] ?? 'Tidak ada deskripsi.'); ?></p>

                            <?php if (!empty($d['file_path'])): ?>
                                <div class="mb-3">
                                    <a href="<?= html_escape($d['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill">
                                        <i class="fas fa-paperclip me-1"></i> Lampiran Soal Dosen
                                    </a>
                                </div>
                            <?php endif; ?>

                            <!-- Submission Status Box -->
                            <?php if ($isSubmitted): ?>
                                <div class="bg-light p-3 rounded mb-3 border">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-success font-weight-bold"><i class="fas fa-check me-1"></i> Dikumpulkan pada: <?= formatDateDisplay($d['submitted_at']); ?></small>
                                        <button class="btn btn-xs btn-outline-danger py-0 px-2 rounded-pill" onclick="deleteSubmission(<?= $d['assignment_id']; ?>)">
                                            <i class="fas fa-trash me-1"></i> Batalkan
                                        </button>
                                    </div>
                                    <?php if (!empty($d['grade'])): ?>
                                        <div class="mt-2 pt-2 border-top">
                                            <span class="badge bg-primary fs-6">Nilai: <?= $d['grade']; ?> / <?= $d['max_grade']; ?></span>
                                            <?php if (!empty($d['feedback'])): ?>
                                                <p class="small text-muted mb-0 mt-1"><i class="fas fa-comment me-1"></i><strong>Feedback:</strong> <?= html_escape($d['feedback']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="text-end">
                                <button class="btn btn-primary rounded-pill px-4" onclick="openSubmitModal(<?= $d['assignment_id']; ?>, '<?= html_escape(addslashes($d['title'])); ?>')">
                                    <i class="fas <?= $isSubmitted ? 'fa-edit' : 'fa-upload'; ?> me-1"></i> 
                                    <?= $isSubmitted ? 'Perbarui Pengumpulan' : 'Kumpulkan Tugas'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Upload Submission -->
    <div class="modal fade" id="submitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title" id="submitModalTitle"><i class="fas fa-upload me-2"></i>Kumpulkan Tugas</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="submitForm" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <input type="hidden" name="assignment_id" id="modalAssignmentId">
                        
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Unggah Berkas Tugas (PDF, DOCX, ZIP, dll)</label>
                            <input type="file" class="form-control" name="submission_file">
                            <small class="text-muted">Pilih berkas dari komputer Anda.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Catatan / Jawaban Teks (Opsional)</label>
                            <textarea class="form-control" name="submission_text" rows="3" placeholder="Tuliskan catatan atau link referensi tugas Anda..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4"><i class="fas fa-paper-plane me-1"></i> Kirim Tugas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        let submitModalObj;
        document.addEventListener('DOMContentLoaded', function() {
            submitModalObj = new bootstrap.Modal(document.getElementById('submitModal'));
            
            document.getElementById('submitForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch('index.php?url=student/submitAssignment', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.error) {
                        alert('Gagal: ' + res.error);
                    } else {
                        alert(res.message || 'Tugas berhasil dikumpulkan!');
                        location.reload();
                    }
                })
                .catch(err => {
                    alert('Terjadi kesalahan koneksi.');
                });
            });
        });

        function openSubmitModal(id, title) {
            document.getElementById('modalAssignmentId').value = id;
            document.getElementById('submitModalTitle').innerHTML = `<i class="fas fa-upload me-2"></i>Kumpulkan: ${title}`;
            submitModalObj.show();
        }

        function deleteSubmission(assignmentId) {
            if (confirm("Apakah Anda yakin ingin membatalkan/menghapus pengumpulan tugas ini?")) {
                fetch(`index.php?url=student/submitAssignment&action=delete&assignment_id=${assignmentId}`)
                .then(r => r.json())
                .then(res => {
                    if (res.error) {
                        alert('Gagal: ' + res.error);
                    } else {
                        alert('Pengumpulan tugas berhasil dibatalkan.');
                        location.reload();
                    }
                });
            }
        }

        function filterDeadlines(category) {
            document.querySelectorAll('#deadlineFilters .nav-link').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            document.querySelectorAll('.deadline-item').forEach(item => {
                if (category === 'all' || item.getAttribute('data-category') === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
