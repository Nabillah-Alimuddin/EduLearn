<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ujian Mahasiswa - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #4A90E2, #7FB3D3); color: white; padding: 2rem 0; border-radius: 0 0 20px 20px; }
        .exam-card { background: white; border-radius: 15px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="header mb-4">
        <div class="container">
            <a href="index.php?url=student/dashboard" class="btn btn-light btn-sm rounded-pill mb-2"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            <h2><i class="fas fa-clipboard-check me-2"></i>Jadwal Ujian (UTS & UAS)</h2>
        </div>
    </div>

    <div class="container">
        <?php if (empty($exams)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-calendar-check fa-3x mb-3 text-success"></i>
                <p>Belum ada jadwal ujian yang terdaftar.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($exams as $e): ?>
                    <div class="col-md-6">
                        <div class="exam-card">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-danger px-3 py-2"><?= html_escape($e['exam_type']); ?></span>
                                <small class="text-muted"><i class="far fa-calendar me-1"></i><?= formatDateDisplay($e['exam_date']); ?></small>
                            </div>
                            <h5 class="card-title text-primary"><?= html_escape($e['title']); ?></h5>
                            <p class="text-muted small mb-2"><i class="fas fa-book me-1"></i><?= html_escape($e['course_name']); ?></p>
                            <p class="small text-secondary mb-3"><i class="far fa-clock me-1"></i>Waktu: <?= html_escape(substr($e['start_time'], 0, 5)); ?> - <?= html_escape(substr($e['end_time'], 0, 5)); ?> (<?= $e['duration_minutes']; ?> Menit)</p>
                            
                            <?php if ($e['student_completed_exam']): ?>
                                <span class="badge bg-success px-3 py-2"><i class="fas fa-check me-1"></i> Ujian Selesai</span>
                            <?php else: ?>
                                <button class="btn btn-primary btn-sm rounded-pill" onclick="openExamUploadModal(<?= $e['exam_id']; ?>)">Unggah Lembar Jawaban</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function openExamUploadModal(id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'index.php?url=exam/studentExam';
            form.enctype = 'multipart/form-data';

            const inputType = document.createElement('input');
            inputType.type = 'hidden';
            inputType.name = 'submit_type';
            inputType.value = 'file_submission';
            form.appendChild(inputType);

            const inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'exam_id';
            inputId.value = id;
            form.appendChild(inputId);

            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.name = 'submission_file';
            fileInput.onchange = function() {
                if (fileInput.files.length > 0) {
                    document.body.appendChild(form);
                    form.submit();
                }
            };
            fileInput.click();
        }
    </script>
</body>
</html>
