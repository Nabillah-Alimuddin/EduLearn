<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kuis & Bank Soal - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #63A3F1 0%, #4F8A9E 100%); color: white; padding: 2.2rem 0; border-radius: 0 0 20px 20px; box-shadow: 0 6px 20px rgba(79, 138, 158, 0.2); margin-bottom: 2rem; }
        .card-custom { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .nav-tabs .nav-link { color: #4F8A9E; font-weight: 600; }
        .nav-tabs .nav-link.active { color: #3068b5; border-bottom: 3px solid #3068b5; background-color: white; }
        .rank-badge { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; }
        .rank-1 { background: #ffd700; color: #000; }
        .rank-2 { background: #c0c0c0; color: #000; }
        .rank-3 { background: #cd7f32; color: #fff; }
    </style>
</head>
<body>
    <div class="header mb-4">
        <div class="container">
            <a href="index.php?url=lecturer/dashboard" class="btn btn-light btn-sm rounded-pill mb-2"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            <h2><i class="fas fa-edit me-2"></i>Kelola Kuis & Bank Soal Pembelajaran</h2>
            <p class="mb-0 text-white-50">Buat kuis baru, tambahkan pertanyaan pada bank soal, dan pantau peringkat nilai mahasiswa.</p>
        </div>
    </div>

    <div class="container">
        <?php
            // Find active quiz object if selected
            $activeQuizObj = null;
            if ($selectedQuizId) {
                foreach ($quizzes as $qz) {
                    if ($qz['quiz_id'] == $selectedQuizId) {
                        $activeQuizObj = $qz;
                        break;
                    }
                }
            }
        ?>

        <!-- Active Quiz Banner if selected -->
        <?php if ($activeQuizObj): ?>
            <div class="alert alert-primary d-flex justify-content-between align-items-center rounded-4 shadow-sm mb-4">
                <div>
                    <i class="fas fa-tasks fa-2x me-3 text-primary"></i>
                    <strong>Kuis Aktif Terpilih:</strong> <span class="fs-5 text-dark font-weight-bold"><?= html_escape($activeQuizObj['title']); ?></span> (<?= html_escape($activeQuizObj['course_name']); ?>)
                </div>
                <a href="index.php?url=quiz/manage" class="btn btn-sm btn-outline-secondary rounded-pill"><i class="fas fa-times me-1"></i> Tutup Kuis Aktif</a>
            </div>
        <?php endif; ?>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="quizTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link <?= !$selectedQuizId ? 'active' : ''; ?>" id="list-tab" data-bs-toggle="tab" data-bs-target="#tab-list" type="button">
                    <i class="fas fa-list me-1"></i> Daftar Kuis
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="create-tab" data-bs-toggle="tab" data-bs-target="#tab-create" type="button">
                    <i class="fas fa-plus-circle me-1"></i> Buat Kuis Baru
                </button>
            </li>
            <?php if ($selectedQuizId): ?>
                <li class="nav-item">
                    <button class="nav-link active" id="question-tab" data-bs-toggle="tab" data-bs-target="#tab-questions" type="button">
                        <i class="fas fa-question-circle me-1"></i> Bank Soal Kuis (<?= count($questions); ?> Soal)
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="ranking-tab" data-bs-toggle="tab" data-bs-target="#tab-rankings" type="button">
                        <i class="fas fa-trophy me-1"></i> Peringkat Mahasiswa
                    </button>
                </li>
            <?php endif; ?>
        </ul>

        <div class="tab-content">
            <!-- TAB 1: DAFTAR KUIS -->
            <div class="tab-pane fade <?= !$selectedQuizId ? 'show active' : ''; ?>" id="tab-list">
                <div class="card card-custom p-4">
                    <h5 class="mb-3"><i class="fas fa-book me-2 text-primary"></i>Kuis yang Diampu</h5>
                    <?php if (empty($quizzes)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Belum ada kuis yang dibuat. Silakan klik tab <strong>Buat Kuis Baru</strong> di atas.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mata Kuliah</th>
                                        <th>Judul Kuis</th>
                                        <th>Durasi</th>
                                        <th>Status Soal</th>
                                        <th>Passing Score</th>
                                        <th>Aksi Kelola</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($quizzes as $q): ?>
                                        <tr class="<?= ($selectedQuizId == $q['quiz_id']) ? 'table-active border-start border-4 border-primary' : ''; ?>">
                                            <td><?= html_escape($q['course_name']); ?></td>
                                            <td><strong><?= html_escape($q['title']); ?></strong></td>
                                            <td><span class="badge bg-info text-dark"><i class="far fa-clock me-1"></i><?= $q['duration_minutes']; ?> mnt</span></td>
                                            <td><?= $q['total_questions']; ?> target soal</td>
                                            <td><span class="badge bg-success"><?= $q['passing_score']; ?>%</span></td>
                                            <td>
                                                <a href="index.php?url=quiz/manage&quiz_id=<?= $q['quiz_id']; ?>" class="btn btn-sm btn-primary rounded-pill me-1"><i class="fas fa-edit me-1"></i> Tambah / Kelola Soal</a>
                                                <a href="index.php?url=quiz/deleteQuiz&quiz_id=<?= $q['quiz_id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus kuis ini beserta seluruh soalnya?')" class="btn btn-sm btn-outline-danger rounded-pill"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB 2: BUAT KUIS BARU -->
            <div class="tab-pane fade" id="tab-create">
                <div class="card card-custom p-4">
                    <h5 class="mb-3"><i class="fas fa-plus-circle me-2 text-primary"></i>Form Pembuatan Kuis Baru</h5>
                    <p class="text-muted small">Setelah mengisi form di bawah ini, Anda akan langsung diarahkan ke tab <strong>Bank Soal</strong> untuk menambahkan pertanyaan-pertanyaan kuis.</p>
                    
                    <form action="index.php?url=quiz/createQuiz" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Pilih Mata Kuliah</label>
                                <select class="form-select" name="course_id" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?= $c['course_id']; ?>"><?= html_escape($c['course_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Judul Kuis</label>
                                <input type="text" class="form-control" name="title" placeholder="Contoh: Kuis 1 - Pemrograman Web" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-weight-bold">Deskripsi / Petunjuk Kuis</label>
                                <textarea class="form-control" name="description" rows="2" placeholder="Petunjuk pengerjaan kuis..."></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-weight-bold">Durasi (Menit)</label>
                                <input type="number" class="form-control" name="duration_minutes" value="30" min="5" max="180" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-weight-bold">Target Jumlah Soal</label>
                                <input type="number" class="form-control" name="total_questions" value="5" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-weight-bold">Passing Score (%)</label>
                                <input type="number" step="0.01" class="form-control" name="passing_score" value="70.00" required>
                            </div>
                            <div class="col-12 text-end mt-3">
                                <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-arrow-right me-1"></i> Simpan & Lanjut Input Soal</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($selectedQuizId): ?>
                <!-- TAB 3: BANK SOAL -->
                <div class="tab-pane fade <?= $selectedQuizId ? 'show active' : ''; ?>" id="tab-questions">
                    <!-- Form Tambah Soal -->
                    <div class="card card-custom p-4 mb-4 border-start border-4 border-primary">
                        <h5 class="mb-3 text-primary"><i class="fas fa-plus-circle me-2"></i>Form Tambah Soal Baru untuk Kuis: <strong><?= html_escape($activeQuizObj['title'] ?? ''); ?></strong></h5>
                        
                        <form action="index.php?url=quiz/addQuestion" method="POST">
                            <input type="hidden" name="quiz_id" value="<?= $selectedQuizId; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Teks Pertanyaan</label>
                                <textarea class="form-control" name="question_text" rows="2" placeholder="Tuliskan pertanyaan kuis..." required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Formula / Rumus Matematika (Opsional)</label>
                                <input type="text" class="form-control" name="question_formula" placeholder="Contoh: f(x) = ax^2 + bx + c">
                            </div>

                            <label class="form-label font-weight-bold">Pilihan Jawaban (Tandai Radio untuk Jawaban Benar)</label>
                            <div class="row g-2 mb-3">
                                <?php for ($i = 0; $i < 4; $i++): $letter = chr(65 + $i); ?>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-text">
                                                <input class="form-check-input mt-0" type="radio" name="correct_option_index" value="<?= $i; ?>" <?= $i === 0 ? 'checked' : ''; ?>>
                                                <span class="ms-1 fw-bold"><?= $letter; ?></span>
                                            </div>
                                            <input type="text" class="form-control" name="options[]" placeholder="Opsi <?= $letter; ?>" required>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Penjelasan / Pembahasan (Opsional)</label>
                                <textarea class="form-control" name="explanation" rows="2" placeholder="Penjelasan mengapa jawaban tersebut benar..."></textarea>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-success rounded-pill px-4"><i class="fas fa-plus-circle me-1"></i> Tambahkan Soal Ini</button>
                            </div>
                        </form>
                    </div>

                    <!-- List Soal -->
                    <div class="card card-custom p-4">
                        <h5 class="mb-3"><i class="fas fa-list-ol me-2 text-primary"></i>Daftar Soal Tersimpan (<?= count($questions); ?> Soal)</h5>
                        <?php if (empty($questions)): ?>
                            <div class="alert alert-warning py-3 text-center">
                                <i class="fas fa-exclamation-triangle me-1"></i> Kuis ini belum memiliki soal. Gunakan form di atas untuk menambahkan pertanyaan pertama!
                            </div>
                        <?php else: ?>
                            <?php $qNo = 1; foreach ($questions as $qItem): ?>
                                <div class="border rounded p-3 mb-3 bg-white shadow-sm">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="fw-bold text-primary mb-2"><?= $qNo++; ?>. <?= html_escape($qItem['question_text']); ?></h6>
                                        <a href="index.php?url=quiz/deleteQuestion&question_id=<?= $qItem['question_id']; ?>&quiz_id=<?= $selectedQuizId; ?>" onclick="return confirm('Hapus soal ini?')" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></a>
                                    </div>
                                    <?php if (!empty($qItem['question_formula'])): ?>
                                        <div class="bg-light p-2 rounded mb-2 font-monospace small"><?= html_escape($qItem['question_formula']); ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="row g-2 mt-1">
                                        <?php if (!empty($qItem['options'])): ?>
                                            <?php foreach ($qItem['options'] as $idx => $opt): ?>
                                                <div class="col-md-6">
                                                    <div class="p-2 border rounded <?= $opt['is_correct'] ? 'bg-success text-white fw-bold' : 'bg-light'; ?>">
                                                        <?= chr(65 + $idx); ?>. <?= html_escape($opt['option_text']); ?>
                                                        <?= $opt['is_correct'] ? ' <i class="fas fa-check-circle ms-1"></i> (Jawaban Benar)' : ''; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($qItem['explanation'])): ?>
                                        <div class="mt-2 text-muted small"><i class="fas fa-info-circle me-1"></i><strong>Pembahasan:</strong> <?= html_escape($qItem['explanation']); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TAB 4: RANKINGS -->
                <div class="tab-pane fade" id="tab-rankings">
                    <div class="card card-custom p-4">
                        <h5 class="mb-3"><i class="fas fa-trophy me-2 text-warning"></i>Peringkat Nilai Mahasiswa (<?= html_escape($activeQuizObj['title'] ?? ''); ?>)</h5>
                        <?php if (empty($rankings)): ?>
                            <p class="text-muted mb-0">Belum ada mahasiswa yang mengerjakan kuis ini.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px;">Peringkat</th>
                                            <th>NIM</th>
                                            <th>Nama Mahasiswa</th>
                                            <th>Nilai / Skor</th>
                                            <th>Tanggal Selesai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $rank = 1; foreach ($rankings as $rk): ?>
                                            <tr>
                                                <td>
                                                    <span class="rank-badge <?= $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : 'bg-light text-dark')); ?>">
                                                        <?= $rank++; ?>
                                                    </span>
                                                </td>
                                                <td><?= html_escape($rk['nim'] ?? '-'); ?></td>
                                                <td><strong><?= html_escape($rk['full_name']); ?></strong></td>
                                                <td>
                                                    <span class="fs-5 fw-bold <?= $rk['score'] >= 70 ? 'text-success' : 'text-danger'; ?>">
                                                        <?= round($rk['score'], 2); ?>%
                                                    </span>
                                                </td>
                                                <td class="text-muted small"><?= formatDateDisplay($rk['end_time']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
