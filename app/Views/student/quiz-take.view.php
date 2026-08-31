<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengerjaan Kuis - <?= html_escape($quiz['title'] ?? 'EduLearn'); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f4f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px 0;
        }

        .quiz-container {
            width: 100%;
            max-width: 900px;
            padding: 15px;
        }

        .quiz-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(46, 73, 102, 0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid #d1d9e6;
        }

        .quiz-header {
            background: linear-gradient(45deg, #4b89dc, #3068b5);
            color: white;
            padding: 25px;
            border-radius: 20px 20px 0 0;
            text-align: center;
        }

        .quiz-title-header {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .quiz-subtitle-header {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .progress-container {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 5px;
            margin-top: 15px;
        }

        .progress-bar {
            background: #28a745;
            height: 8px;
            border-radius: 20px;
            transition: width 0.5s ease;
        }

        .progress-text {
            text-align: center;
            margin-top: 8px;
            font-size: 0.9rem;
            color: #dbe4f0;
        }

        #quizHeaderTimer {
            font-size: 1.1rem;
            font-weight: bold;
            color: #ffffff;
            margin-top: 10px;
            background: rgba(0, 0, 0, 0.15);
            display: inline-block;
            padding: 6px 18px;
            border-radius: 20px;
        }

        .quiz-content {
            flex: 1;
            padding: 25px;
            display: flex;
            flex-direction: column;
            position: relative;
            min-height: 380px;
        }

        .question-number {
            color: #4b89dc;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .question-text {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .options-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }

        .option-item {
            cursor: pointer;
            padding: 15px;
            border-radius: 12px;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .option-item:hover {
            background: #eaf3ff;
            border-color: #4b89dc;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(75, 137, 220, 0.1);
        }

        .option-item.selected {
            background: linear-gradient(45deg, #4b89dc, #3068b5);
            color: white;
            border-color: #3068b5;
            box-shadow: 0 8px 20px rgba(48, 104, 181, 0.2);
        }

        .option-item.correct {
            background: #28a745 !important;
            color: white !important;
            border-color: #218838 !important;
        }

        .option-item.incorrect {
            background: #dc3545 !important;
            color: white !important;
            border-color: #c82333 !important;
        }

        .option-letter {
            font-weight: bold;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            background: rgba(0, 0, 0, 0.08);
            flex-shrink: 0;
        }

        .option-item.selected .option-letter,
        .option-item.correct .option-letter,
        .option-item.incorrect .option-letter {
            background: rgba(255, 255, 255, 0.3);
        }

        .result-icon {
            margin-left: auto;
            font-size: 1.4rem;
            opacity: 0;
            transform: scale(0);
            transition: all 0.3s ease;
        }

        .option-item.correct .result-icon,
        .option-item.incorrect .result-icon {
            opacity: 1;
            transform: scale(1);
        }

        .answer-status {
            text-align: center;
            padding: 15px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.5s ease;
            margin-bottom: 15px;
        }

        .answer-status.show {
            opacity: 1;
            transform: translateY(0);
        }

        .answer-status.correct {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .answer-status.incorrect {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .navigation-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0 0 0;
            border-top: 1px solid #e0e0e0;
            margin-top: auto;
        }

        .nav-button {
            padding: 12px 25px;
            border: none;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-previous {
            background: #6c757d;
            color: white;
        }

        .btn-next {
            background: linear-gradient(45deg, #4b89dc, #3068b5);
            color: white;
        }

        .btn-finish {
            background: #28a745;
            color: white;
        }

        .nav-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .math-formula {
            background: #f8f9fa;
            border-left: 4px solid #4b89dc;
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            overflow-x: auto;
            color: #2c3e50;
        }

        #loadingQuizContent {
            text-align: center;
            padding: 50px;
        }

        /* Results Screen */
        .results-container {
            text-align: center;
            padding: 20px;
        }

        .results-container h2 {
            font-size: 2rem;
            color: #3068b5;
            margin-bottom: 20px;
        }

        .score-box {
            background: #eaf3ff;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border: 2px solid #c8d9e8;
        }

        .score-display {
            font-size: 3.8rem;
            font-weight: 700;
            color: #28a745;
            margin-bottom: 5px;
        }

        .score-display.fail {
            color: #dc3545;
        }

        .status-text {
            font-size: 1.5rem;
            font-weight: 600;
            color: #28a745;
            text-transform: uppercase;
        }

        .status-text.fail {
            color: #dc3545;
        }

        .score-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .score-item {
            background: #f0f4f8;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            border: 1px solid #d1d9e6;
        }

        .score-item h4 {
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 5px;
        }

        .score-item .number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #4b89dc;
        }

        .review-section {
            text-align: left;
            margin-top: 30px;
            border-top: 1px solid #e0e0e0;
            padding-top: 20px;
        }

        .review-section h4 {
            font-size: 1.4rem;
            color: #3068b5;
            margin-bottom: 20px;
        }

        .review-item {
            background: #ffffff;
            border: 1px solid #d1d9e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .review-item.correct {
            border-left: 5px solid #28a745;
        }

        .review-item.incorrect {
            border-left: 5px solid #dc3545;
        }

        .btn-back-to-menu {
            background: #6c757d;
            color: white;
            border-radius: 30px;
            padding: 12px 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="quiz-container">
        <div class="quiz-card" id="quizCard">
            <div class="quiz-header">
                <h1 class="quiz-title-header" id="quizHeaderTitle"><?= html_escape($quiz['title'] ?? 'Memuat Kuis...'); ?></h1>
                <p class="quiz-subtitle-header" id="quizHeaderSubtitle"><?= html_escape($quiz['description'] ?? ''); ?></p>
                <div class="progress-container">
                    <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="progress-text" id="progressText">Memuat pertanyaan...</div>
                <div id="quizHeaderTimer"><i class="far fa-clock me-1"></i> Durasi: <?= (int)($quiz['duration_minutes'] ?? 30); ?> Menit</div>
            </div>

            <div class="quiz-content" id="quizContent">
                <div id="loadingQuizContent">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                    <p class="mt-3 text-muted">Memuat pertanyaan kuis...</p>
                </div>
                
                <div class="question-container d-none" id="questionContainer">
                    <div class="question-number" id="questionNumber"></div>
                    <div class="question-text" id="questionText"></div>
                    <div class="options-container" id="optionsContainer"></div>
                    <div class="answer-status d-none" id="answerStatus"></div>
                    
                    <div class="navigation-container">
                        <button class="nav-button btn-previous" id="prevButton" onclick="previousQuestion()">
                            <i class="fas fa-arrow-left"></i> Sebelumnya
                        </button>
                        <button class="nav-button btn-next" id="nextButton" onclick="nextQuestion()">
                            Selanjutnya <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        const currentQuizId = <?= (int)($quiz['quiz_id'] ?? 0); ?>;
        const passingScore = <?= (float)($quiz['passing_score'] ?? 70.00); ?>;
        const durationMinutes = <?= (int)($quiz['duration_minutes'] ?? 30); ?>;

        let questions = [];
        let currentQuestionIndex = 0;
        let userAnswers = [];
        let score = 0;
        let correctAnswers = 0;
        let quizCompleted = false;

        let quizTimerInterval;
        let timeRemainingSeconds = durationMinutes * 60;

        document.addEventListener('DOMContentLoaded', async function() {
            await loadQuizContent();
        });

        async function loadQuizContent() {
            try {
                const res = await fetch(`index.php?url=quiz/apiQuestions&quiz_id=${currentQuizId}`);
                if (!res.ok) throw new Error("Gagal mengambil data soal kuis.");
                const data = await res.json();

                if (data.error) throw new Error(data.error);

                questions = data.questions || [];

                if (questions.length > 0) {
                    document.getElementById('loadingQuizContent').classList.add('d-none');
                    document.getElementById('questionContainer').classList.remove('d-none');

                    initializeQuiz();
                    displayQuestion();
                    startQuizTimer();
                } else {
                    document.getElementById('loadingQuizContent').innerHTML = `
                        <div class="text-center text-danger py-4">
                            <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                            <h5>Kuis Belum Memiliki Pertanyaan</h5>
                            <p class="text-muted">Dosen pengampu belum menambahkan soal pada kuis ini.</p>
                            <a href="index.php?url=quiz/list" class="btn btn-primary rounded-pill mt-2">
                                <i class="fas fa-arrow-left me-1"></i> Kembali ke Menu Kuis
                            </a>
                        </div>
                    `;
                }
            } catch (err) {
                document.getElementById('loadingQuizContent').innerHTML = `
                    <div class="text-center text-danger py-4">
                        <i class="fas fa-times-circle fa-3x mb-3"></i>
                        <h5>Gagal Memuat Kuis</h5>
                        <p>${err.message}</p>
                        <a href="index.php?url=quiz/list" class="btn btn-secondary rounded-pill mt-2">Kembali</a>
                    </div>
                `;
            }
        }

        function initializeQuiz() {
            userAnswers = new Array(questions.length).fill(null).map(() => ({
                selectedOptionId: null,
                correctOptionId: null
            }));
            currentQuestionIndex = 0;
            score = 0;
            correctAnswers = 0;
            quizCompleted = false;
            updateProgress();
        }

        function startQuizTimer() {
            if (quizTimerInterval) clearInterval(quizTimerInterval);

            const timerElement = document.getElementById('quizHeaderTimer');
            timeRemainingSeconds = durationMinutes * 60;

            quizTimerInterval = setInterval(function() {
                timeRemainingSeconds--;
                if (timeRemainingSeconds <= 0) {
                    clearInterval(quizTimerInterval);
                    alert("Waktu pengerjaan kuis telah habis. Kuis akan otomatis diselesaikan.");
                    finishQuiz();
                    return;
                }
                const m = Math.floor(timeRemainingSeconds / 60);
                const s = timeRemainingSeconds % 60;
                timerElement.innerHTML = `<i class="far fa-clock me-1"></i> Sisa Waktu: ${m}m ${s < 10 ? '0' : ''}${s}s`;
            }, 1000);
        }

        function displayQuestion() {
            const q = questions[currentQuestionIndex];
            
            document.getElementById('questionNumber').textContent = `Pertanyaan ${currentQuestionIndex + 1} dari ${questions.length}`;
            
            let qHtml = escapeHtml(q.question);
            if (q.formula) {
                qHtml += `<div class="math-formula">${escapeHtml(q.formula)}</div>`;
            }
            document.getElementById('questionText').innerHTML = qHtml;

            const optionsContainer = document.getElementById('optionsContainer');
            optionsContainer.innerHTML = '';

            if (q.options && q.options.length > 0) {
                q.options.forEach((opt, idx) => {
                    const optEl = createOptionElement(opt.option_text, opt.option_id, idx);
                    optionsContainer.appendChild(optEl);
                });
            } else {
                optionsContainer.innerHTML = '<p class="text-muted">Tidak ada pilihan jawaban untuk pertanyaan ini.</p>';
            }

            const answerStatus = document.getElementById('answerStatus');
            answerStatus.classList.add('d-none');
            answerStatus.innerHTML = '';

            const currentAns = userAnswers[currentQuestionIndex];
            if (currentAns && currentAns.selectedOptionId !== null) {
                showAnswerFeedback(currentAns.selectedOptionId, q.correct_option_id);
            }

            updateNavigationButtons();
            updateProgress();
        }

        function createOptionElement(text, optionId, index) {
            const div = document.createElement('div');
            div.className = 'option-item';
            div.setAttribute('data-option-id', optionId);
            div.onclick = () => selectAnswer(optionId);

            const letter = document.createElement('div');
            letter.className = 'option-letter';
            letter.textContent = String.fromCharCode(65 + index);

            const textDiv = document.createElement('div');
            textDiv.className = 'option-text flex-grow-1 ms-2';
            textDiv.textContent = text;

            const icon = document.createElement('i');
            icon.className = 'result-icon fas';

            div.appendChild(letter);
            div.appendChild(textDiv);
            div.appendChild(icon);
            return div;
        }

        function selectAnswer(selectedOptionId) {
            const currentQ = questions[currentQuestionIndex];
            userAnswers[currentQuestionIndex] = {
                questionId: currentQ.id,
                selectedOptionId: selectedOptionId,
                correctOptionId: currentQ.correct_option_id
            };

            document.querySelectorAll('.option-item').forEach(el => {
                el.classList.remove('selected', 'correct', 'incorrect');
                const ic = el.querySelector('.result-icon');
                if (ic) ic.classList.remove('fa-check', 'fa-times');
            });

            const selItem = document.querySelector(`[data-option-id="${selectedOptionId}"]`);
            if (selItem) {
                selItem.classList.add('selected');
            }

            showAnswerFeedback(selectedOptionId, currentQ.correct_option_id);
            updateNavigationButtons();
            updateProgress();
        }

        function showAnswerFeedback(selectedOptionId, correctOptionId) {
            const q = questions[currentQuestionIndex];
            const answerStatus = document.getElementById('answerStatus');
            answerStatus.classList.remove('d-none');

            document.querySelectorAll('.option-item').forEach(item => {
                const itemId = parseInt(item.getAttribute('data-option-id'));
                const icon = item.querySelector('.result-icon');

                if (itemId === correctOptionId) {
                    item.classList.add('correct');
                    if (icon) icon.className = 'result-icon fas fa-check';
                }
                if (itemId === selectedOptionId) {
                    if (itemId !== correctOptionId) {
                        item.classList.add('incorrect');
                        if (icon) icon.className = 'result-icon fas fa-times';
                    }
                }
            });

            if (selectedOptionId === correctOptionId) {
                answerStatus.innerHTML = '<i class="fas fa-check-circle me-1"></i> Jawaban Benar! Excellent!';
                answerStatus.className = 'answer-status correct show';
            } else {
                const correctOpt = q.options.find(o => o.option_id === correctOptionId);
                const correctText = correctOpt ? correctOpt.option_text : 'Tidak diketahui';
                answerStatus.innerHTML = `<i class="fas fa-times-circle me-1"></i> Jawaban Salah! Jawaban yang benar adalah: <strong>${escapeHtml(correctText)}</strong>`;
                answerStatus.className = 'answer-status incorrect show';
            }
        }

        function nextQuestion() {
            const currentAns = userAnswers[currentQuestionIndex];
            if (!currentAns || currentAns.selectedOptionId === null) {
                alert('Silakan pilih jawaban terlebih dahulu sebelum melanjutkan!');
                return;
            }

            if (currentQuestionIndex < questions.length - 1) {
                currentQuestionIndex++;
                displayQuestion();
            } else {
                finishQuiz();
            }
        }

        function previousQuestion() {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                displayQuestion();
            }
        }

        function updateNavigationButtons() {
            const prevButton = document.getElementById('prevButton');
            const nextButton = document.getElementById('nextButton');

            prevButton.disabled = (currentQuestionIndex === 0);
            
            const currentAns = userAnswers[currentQuestionIndex];
            nextButton.disabled = (!currentAns || currentAns.selectedOptionId === null);

            if (currentQuestionIndex === questions.length - 1) {
                nextButton.innerHTML = 'Selesai & Submit <i class="fas fa-check me-1"></i>';
                nextButton.className = 'nav-button btn-finish';
            } else {
                nextButton.innerHTML = 'Selanjutnya <i class="fas fa-arrow-right me-1"></i>';
                nextButton.className = 'nav-button btn-next';
            }
        }

        function updateProgress() {
            const answeredCount = userAnswers.filter(a => a && a.selectedOptionId !== null).length;
            const total = questions.length;
            const pct = total > 0 ? Math.round((answeredCount / total) * 100) : 0;

            const progressBar = document.getElementById('progressBar');
            progressBar.style.width = pct + '%';
            progressBar.setAttribute('aria-valuenow', pct);

            document.getElementById('progressText').textContent =
                `Pertanyaan ${currentQuestionIndex + 1} dari ${total} (${answeredCount} terjawab)`;
        }

        async function finishQuiz() {
            if (quizTimerInterval) clearInterval(quizTimerInterval);
            quizCompleted = true;

            correctAnswers = userAnswers.filter(a => a && a.selectedOptionId === a.correctOptionId).length;
            score = questions.length > 0 ? Math.round((correctAnswers / questions.length) * 100) : 0;

            try {
                await fetch('index.php?url=quiz/submitResult', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        quiz_id: currentQuizId,
                        score: score
                    })
                });
            } catch (err) {
                console.error("Gagal mengirimkan hasil kuis ke server:", err);
            }

            displayResults();
        }

        function displayResults() {
            const quizContent = document.getElementById('quizContent');
            const passed = (score >= passingScore);
            const statusText = passed ? 'LULUS!' : 'TIDAK LULUS';

            quizContent.innerHTML = `
                <div class="results-container">
                    <h2><i class="fas fa-trophy text-warning me-2"></i>Hasil Kuis Anda</h2>
                    
                    <div class="score-box">
                        <div class="score-display ${passed ? '' : 'fail'}">${score}%</div>
                        <div class="status-text ${passed ? '' : 'fail'}">${statusText}</div>
                        <p class="text-muted mt-2 mb-0">Passing Score: ${passingScore}%</p>
                    </div>

                    <div class="score-details-grid">
                        <div class="score-item">
                            <h4>Total Soal</h4>
                            <div class="number">${questions.length}</div>
                        </div>
                        <div class="score-item">
                            <h4>Jawaban Benar</h4>
                            <div class="number" style="color: #28a745;">${correctAnswers}</div>
                        </div>
                        <div class="score-item">
                            <h4>Jawaban Salah</h4>
                            <div class="number" style="color: #dc3545;">${questions.length - correctAnswers}</div>
                        </div>
                    </div>

                    <div class="review-section">
                        <h4><i class="fas fa-list-check me-2"></i>Review Jawaban</h4>
                        <div id="reviewContainer">
                            ${generateReviewHTML()}
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="index.php?url=quiz/list" class="btn-back-to-menu">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Kuis
                        </a>
                    </div>
                </div>
            `;
        }

        function generateReviewHTML() {
            let html = '';
            questions.forEach((q, idx) => {
                const ans = userAnswers[idx];
                const selectedOptId = ans ? ans.selectedOptionId : null;
                const correctOptId = q.correct_option_id;
                const isCorrect = (selectedOptId === correctOptId);

                const userOpt = q.options.find(o => o.option_id === selectedOptId);
                const userText = userOpt ? userOpt.option_text : 'Tidak dijawab';

                const correctOpt = q.options.find(o => o.option_id === correctOptId);
                const correctText = correctOpt ? correctOpt.option_text : 'Tidak diketahui';

                html += `
                    <div class="review-item ${isCorrect ? 'correct' : 'incorrect'}">
                        <strong>Pertanyaan ${idx + 1}:</strong>
                        <p class="mb-2 font-weight-bold">${escapeHtml(q.question)}</p>
                        ${q.formula ? `<div class="math-formula">${escapeHtml(q.formula)}</div>` : ''}
                        <p class="mb-1">
                            <strong>Jawaban Anda:</strong> 
                            <span class="${isCorrect ? 'text-success' : 'text-danger'} fw-bold">
                                ${escapeHtml(userText)} 
                                <i class="fas ${isCorrect ? 'fa-check text-success' : 'fa-times text-danger'} ms-1"></i>
                            </span>
                        </p>
                        ${!isCorrect ? `<p class="mb-1 text-success"><strong>Jawaban Benar:</strong> ${escapeHtml(correctText)}</p>` : ''}
                        <p class="text-muted mt-2 small mb-0">
                            <i class="fas fa-info-circle me-1"></i><strong>Penjelasan:</strong> ${escapeHtml(q.explanation || 'Tidak ada penjelasan.')}
                        </p>
                    </div>
                `;
            });
            return html;
        }

        function escapeHtml(text) {
            if (!text) return '';
            const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>
</html>
