<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input & Review Nilai - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #63A3F1, #4F8A9E); color: white; padding: 2rem 0; border-radius: 0 0 20px 20px; }
        .card-custom { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .grade-input { width: 85px; text-align: center; font-weight: 600; }
        .final-score { font-weight: bold; font-size: 1.1rem; }
    </style>
</head>
<body>
    <div class="header mb-4">
        <div class="container">
            <a href="index.php?url=lecturer/dashboard" class="btn btn-light btn-sm rounded-pill mb-2"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            <h2><i class="fas fa-pen-fancy me-2"></i>Review Pekerjaan & Input Nilai</h2>
            <p class="mb-0 text-white-50">Periksa hasil pengumpulan mahasiswa, berikan masukan/feedback, dan input nilai komponen akhir.</p>
        </div>
    </div>

    <div class="container">
        <!-- Filter Card -->
        <div class="card card-custom p-4 mb-4">
            <form method="GET" action="index.php">
                <input type="hidden" name="url" value="lecturer/inputNilai">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label font-weight-bold"><i class="fas fa-book me-1 text-primary"></i>Pilih Mata Kuliah</label>
                        <select class="form-select" name="course_id" onchange="this.form.submit()">
                            <option value="">-- Pilih Mata Kuliah --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['course_id']; ?>" <?= ($selectedCourseId == $c['course_id']) ? 'selected' : ''; ?>>
                                    <?= html_escape($c['course_name']); ?> (<?= html_escape($c['course_code']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($selectedCourseId): ?>
                        <div class="col-md-5">
                            <label class="form-label font-weight-bold"><i class="fas fa-tasks me-1 text-primary"></i>Pilih Tugas Periksa</label>
                            <select class="form-select" name="assignment_id" onchange="this.form.submit()">
                                <option value="">-- Pilih Tugas --</option>
                                <?php foreach ($assignments as $a): ?>
                                    <option value="<?= $a['assignment_id']; ?>" <?= ($selectedAssignmentId == $a['assignment_id']) ? 'selected' : ''; ?>>
                                        <?= html_escape($a['title']); ?> (Tenggat: <?= formatDateDisplay($a['due_date']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php if (!$selectedCourseId): ?>
            <div class="card card-custom p-5 text-center text-muted">
                <i class="fas fa-hand-pointer fa-3x mb-3 text-primary"></i>
                <h5>Silakan pilih Mata Kuliah di atas</h5>
                <p>Pilih mata kuliah terlebih dahulu untuk menampilkan daftar mahasiswa dan menginput nilai.</p>
            </div>
        <?php elseif (empty($students)): ?>
            <div class="card card-custom p-5 text-center text-muted">
                <i class="fas fa-user-slash fa-3x mb-3 text-warning"></i>
                <h5>Belum Ada Mahasiswa Terdaftar</h5>
                <p>Tidak ada mahasiswa yang mengambil mata kuliah ini.</p>
            </div>
        <?php else: ?>
            <!-- Main Grading Form -->
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-table me-2 text-primary"></i>Tabel Review Pekerjaan & Penilaian Mahasiswa</h5>
                    <button type="button" onclick="submitGradesForm()" class="btn btn-success rounded-pill px-4"><i class="fas fa-save me-1"></i> Simpan Semua Nilai</button>
                </div>

                <div class="alert alert-info py-2 small mb-3">
                    <i class="fas fa-info-circle me-1"></i> Klik tombol <strong>Review Pekerjaan</strong> untuk melihat catatan teks / berkas tugas mahasiswa. Formulasi Nilai Akhir: <strong>Tugas (20%) + UTS (30%) + UAS (40%) + Partisipasi (10%)</strong>.
                </div>

                <form id="gradingForm" action="index.php?url=grade/saveCrud" method="POST">
                    <input type="hidden" name="course_id" value="<?= $selectedCourseId; ?>">
                    <input type="hidden" name="assignment_id" value="<?= $selectedAssignmentId ?? 0; ?>">

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light text-center">
                                <tr>
                                    <th style="width: 40px;">No</th>
                                    <th>NIM & Nama Mahasiswa</th>
                                    <th style="width: 170px;">Review Berkas Tugas</th>
                                    <th style="width: 110px;">Nilai Tugas (20%)</th>
                                    <th style="width: 110px;">UTS (30%)</th>
                                    <th style="width: 110px;">UAS (40%)</th>
                                    <th style="width: 110px;">Partisipasi (10%)</th>
                                    <th style="width: 100px;">Nilai Akhir</th>
                                    <th style="width: 80px;">Grade</th>
                                    <th>Feedback Dosen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($students as $s): 
                                    $sId = $s['user_id'];
                                    
                                    // Fetch existing grade values
                                    $valTugas = $gradesMap[$sId]['Assignment'][$selectedAssignmentId ?? 0]['value'] 
                                                ?? $gradesMap[$sId]['Assignment'][0]['value'] ?? '';
                                    $fbTugas  = $gradesMap[$sId]['Assignment'][$selectedAssignmentId ?? 0]['feedback'] 
                                                ?? $gradesMap[$sId]['Assignment'][0]['feedback'] ?? '';

                                    $valUTS   = $gradesMap[$sId]['UTS'][$selectedCourseId]['value'] 
                                                ?? $gradesMap[$sId]['UTS'][0]['value'] ?? '';
                                    $valUAS   = $gradesMap[$sId]['UAS'][$selectedCourseId]['value'] 
                                                ?? $gradesMap[$sId]['UAS'][0]['value'] ?? '';
                                    $valPart  = $gradesMap[$sId]['Partisipasi'][$selectedCourseId]['value'] 
                                                ?? $gradesMap[$sId]['Partisipasi'][0]['value'] ?? '';

                                    // Check submission
                                    $sub = $submissionsMap[$sId] ?? null;
                                ?>
                                    <tr data-student-id="<?= $sId; ?>">
                                        <td class="text-center fw-bold"><?= $no++; ?></td>
                                        <td>
                                            <strong><?= html_escape($s['full_name']); ?></strong>
                                            <div class="text-muted small">NIM: <?= html_escape($s['nim'] ?? '-'); ?></div>
                                        </td>
                                        <td class="text-center small">
                                            <?php if ($sub): ?>
                                                <span class="badge bg-success mb-1"><i class="fas fa-check me-1"></i> Terkumpul</span>
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill w-100 mt-1 py-1"
                                                        onclick="viewSubmissionDetail('<?= html_escape(addslashes($s['full_name'])); ?>', '<?= html_escape($s['nim'] ?? '-'); ?>', '<?= formatDateDisplay($sub['submitted_at']); ?>', '<?= html_escape(addslashes($sub['submission_text'] ?? 'Tidak ada catatan teks.')); ?>', '<?= html_escape($sub['submission_file_path'] ?? ''); ?>', <?= $sId; ?>)">
                                                    <i class="fas fa-eye me-1"></i> Review Pekerjaan
                                                </button>
                                            <?php else: ?>
                                                <span class="badge bg-secondary mb-1">Belum Mengumpulkan</span>
                                                <div class="text-muted small">-</div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Nilai Tugas -->
                                        <td class="text-center">
                                            <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm grade-input mx-auto input-tugas" 
                                                   id="input_tugas_<?= $sId; ?>" name="grades[<?= $sId; ?>][Assignment]" value="<?= $valTugas; ?>" oninput="calculateRowGrade(<?= $sId; ?>)">
                                            <input type="hidden" name="grades[<?= $sId; ?>][Assignment_item_id]" value="<?= $selectedAssignmentId ?? 0; ?>">
                                        </td>

                                        <!-- Nilai UTS -->
                                        <td class="text-center">
                                            <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm grade-input mx-auto input-uts" 
                                                   name="grades[<?= $sId; ?>][UTS]" value="<?= $valUTS; ?>" oninput="calculateRowGrade(<?= $sId; ?>)">
                                        </td>

                                        <!-- Nilai UAS -->
                                        <td class="text-center">
                                            <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm grade-input mx-auto input-uas" 
                                                   name="grades[<?= $sId; ?>][UAS]" value="<?= $valUAS; ?>" oninput="calculateRowGrade(<?= $sId; ?>)">
                                        </td>

                                        <!-- Nilai Partisipasi -->
                                        <td class="text-center">
                                            <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm grade-input mx-auto input-part" 
                                                   name="grades[<?= $sId; ?>][Partisipasi]" value="<?= $valPart; ?>" oninput="calculateRowGrade(<?= $sId; ?>)">
                                        </td>

                                        <!-- Calculated Final Score -->
                                        <td class="text-center final-score text-primary" id="final_<?= $sId; ?>">-</td>

                                        <!-- Calculated Letter Grade -->
                                        <td class="text-center font-weight-bold" id="letter_<?= $sId; ?>">-</td>

                                        <!-- Feedback Input -->
                                        <td>
                                            <input type="text" class="form-control form-control-sm" id="input_fb_<?= $sId; ?>" name="grades[<?= $sId; ?>][feedback]" value="<?= html_escape($fbTugas); ?>" placeholder="Catatan/feedback dosen...">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-3">
                        <button type="button" onclick="submitGradesForm()" class="btn btn-success rounded-pill px-4"><i class="fas fa-save me-1"></i> Simpan Semua Nilai</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Review Pekerjaan Mahasiswa -->
    <div class="modal fade" id="submissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title" id="subModalStudentName"><i class="fas fa-user-graduate me-2"></i>Review Pekerjaan Mahasiswa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <strong>NIM:</strong> <span id="subModalNim">-</span>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <strong>Waktu Pengumpulan:</strong> <span id="subModalTime">-</span>
                        </div>
                    </div>

                    <div class="card bg-light border-0 p-3 mb-3">
                        <h6 class="fw-bold text-primary mb-2"><i class="fas fa-align-left me-1"></i>Catatan Teks Submission:</h6>
                        <p class="mb-0 text-dark" id="subModalText" style="white-space: pre-wrap;">-</p>
                    </div>

                    <div class="mb-3" id="subModalFileBox">
                        <h6 class="fw-bold text-primary mb-2"><i class="fas fa-paperclip me-1"></i>Berkas Tugas Terlampir:</h6>
                        <a href="#" id="subModalFileLink" target="_blank" class="btn btn-outline-primary rounded-pill">
                            <i class="fas fa-download me-1"></i> Unduh Berkas Tugas Mahasiswa
                        </a>
                    </div>

                    <hr>

                    <h6 class="fw-bold text-primary mb-2"><i class="fas fa-star me-1"></i>Input Langsung Nilai & Feedback:</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nilai Tugas (0-100)</label>
                            <input type="number" step="0.01" class="form-control" id="modalGradeInput">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Catatan Feedback Dosen</label>
                            <input type="text" class="form-control" id="modalFeedbackInput" placeholder="Masukan untuk perbaikan mahasiswa...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" onclick="applyModalGrade()" class="btn btn-success rounded-pill px-4"><i class="fas fa-check me-1"></i> Terapkan Nilai & Feedback</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentModalStudentId = null;
        let subModalObj = null;

        document.addEventListener('DOMContentLoaded', function() {
            subModalObj = new bootstrap.Modal(document.getElementById('submissionModal'));
            
            // Initial calculation for all rows
            document.querySelectorAll('tr[data-student-id]').forEach(tr => {
                const sId = tr.getAttribute('data-student-id');
                calculateRowGrade(sId);
            });
        });

        function calculateRowGrade(studentId) {
            const tr = document.querySelector(`tr[data-student-id="${studentId}"]`);
            if (!tr) return;

            const t = parseFloat(tr.querySelector('.input-tugas')?.value) || 0;
            const u = parseFloat(tr.querySelector('.input-uts')?.value) || 0;
            const ua = parseFloat(tr.querySelector('.input-uas')?.value) || 0;
            const p = parseFloat(tr.querySelector('.input-part')?.value) || 0;

            const hasInput = (tr.querySelector('.input-tugas')?.value !== '') ||
                             (tr.querySelector('.input-uts')?.value !== '') ||
                             (tr.querySelector('.input-uas')?.value !== '') ||
                             (tr.querySelector('.input-part')?.value !== '');

            if (!hasInput) {
                document.getElementById(`final_${studentId}`).textContent = '-';
                document.getElementById(`letter_${studentId}`).textContent = '-';
                return;
            }

            const finalScore = (t * 0.20) + (u * 0.30) + (ua * 0.40) + (p * 0.10);
            document.getElementById(`final_${studentId}`).textContent = finalScore.toFixed(2);

            let letter = 'E';
            if (finalScore >= 85) letter = 'A';
            else if (finalScore >= 75) letter = 'B';
            else if (finalScore >= 65) letter = 'C';
            else if (finalScore >= 50) letter = 'D';

            const letterEl = document.getElementById(`letter_${studentId}`);
            letterEl.textContent = letter;
            letterEl.className = 'text-center font-weight-bold ' + 
                (letter === 'A' ? 'text-success' : (letter === 'B' ? 'text-primary' : (letter === 'C' ? 'text-warning' : 'text-danger')));
        }

        function viewSubmissionDetail(name, nim, timeStr, textStr, filePath, studentId) {
            currentModalStudentId = studentId;
            document.getElementById('subModalStudentName').innerHTML = `<i class="fas fa-user-graduate me-2"></i>Review Pekerjaan: ${name}`;
            document.getElementById('subModalNim').textContent = nim;
            document.getElementById('subModalTime').textContent = timeStr;
            document.getElementById('subModalText').textContent = textStr || 'Tidak ada catatan teks.';

            const fileBox = document.getElementById('subModalFileBox');
            if (filePath && filePath.trim() !== '') {
                fileBox.style.display = 'block';
                document.getElementById('subModalFileLink').href = filePath;
            } else {
                fileBox.style.display = 'none';
            }

            document.getElementById('modalGradeInput').value = document.getElementById(`input_tugas_${studentId}`).value;
            document.getElementById('modalFeedbackInput').value = document.getElementById(`input_fb_${studentId}`).value;

            subModalObj.show();
        }

        function applyModalGrade() {
            if (!currentModalStudentId) return;

            const val = document.getElementById('modalGradeInput').value;
            const fb = document.getElementById('modalFeedbackInput').value;

            document.getElementById(`input_tugas_${currentModalStudentId}`).value = val;
            document.getElementById(`input_fb_${currentModalStudentId}`).value = fb;

            calculateRowGrade(currentModalStudentId);
            subModalObj.hide();
        }

        function submitGradesForm() {
            const form = document.getElementById('gradingForm');
            const formData = new FormData(form);

            fetch('index.php?url=grade/saveCrud', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.error) {
                    alert('Gagal: ' + res.error);
                } else {
                    alert(res.message || 'Semua nilai berhasil disimpan!');
                    location.reload();
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan koneksi saat menyimpan nilai.');
            });
        }
    </script>
</body>
</html>
