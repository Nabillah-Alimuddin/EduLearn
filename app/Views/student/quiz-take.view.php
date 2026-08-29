<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengerjaan Kuis - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .quiz-card { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin: 30px auto; max-width: 800px; padding: 2rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="quiz-card">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                <h4><i class="fas fa-edit text-primary me-2"></i><?= html_escape($quiz['title'] ?? 'Pengerjaan Kuis'); ?></h4>
                <span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="far fa-clock me-1"></i> Durasi: <?= $quiz['duration_minutes'] ?? 30; ?> Menit</span>
            </div>

            <?php if (empty($questions)): ?>
                <div class="text-center py-4 text-muted">
                    <p>Soal kuis belum tersedia.</p>
                    <a href="index.php?url=quiz/list" class="btn btn-secondary rounded-pill">Kembali</a>
                </div>
            <?php else: ?>
                <form id="quizForm">
                    <?php $no = 1; foreach ($questions as $q): ?>
                        <div class="mb-4">
                            <h6 class="fw-bold"><?= $no++; ?>. <?= html_escape($q['question_text']); ?></h6>
                            <?php if (!empty($q['options'])): ?>
                                <?php foreach ($q['options'] as $opt): ?>
                                    <div class="form-check my-2">
                                        <input class="form-check-input" type="radio" name="q_<?= $q['question_id']; ?>" value="<?= $opt['option_id']; ?>" id="opt_<?= $opt['option_id']; ?>">
                                        <label class="form-check-label" for="opt_<?= $opt['option_id']; ?>">
                                            <?= html_escape($opt['option_text']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="text-end border-top pt-3">
                        <button type="button" onclick="submitQuiz()" class="btn btn-success rounded-pill px-4"><i class="fas fa-paper-plane me-1"></i> Selesaikan Kuis</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function submitQuiz() {
            if (confirm("Apakah Anda yakin ingin menyelesaikan kuis ini?")) {
                fetch('index.php?url=quiz/submitResult', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        quiz_id: <?= (int)($quiz['quiz_id'] ?? 0) ?>,
                        score: 85.00
                    })
                })
                .then(r => r.json())
                .then(res => {
                    alert(res.message || "Kuis berhasil diselesaikan!");
                    window.location.href = 'index.php?url=quiz/list';
                });
            }
        }
    </script>
</body>
</html>
