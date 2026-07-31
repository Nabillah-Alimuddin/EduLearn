<?php
ob_start(); // Start output buffering
include 'middleware.php';
include 'db_connection.php';
require_role('student');

$current_student_id = $_SESSION['user_id'];
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : null;

if ($quiz_id === null || $quiz_id <= 0) {
    header("Location: lpquiz.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuis - Portal Pembelajaran</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* New Blue-Themed CSS */
        body {
            background: #f0f4f8; /* Light blue-gray background */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
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
        }

        .progress-container {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 5px;
            margin-top: 15px;
        }

        .progress-bar {
            background: #28a745; /* Green color for progress */
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
            font-size: 1rem;
            font-weight: bold;
            color: #ffffff;
            margin-top: 10px;
        }
        
        .quiz-content {
            flex: 1;
            padding: 25px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            position: relative;
            min-height: 400px; /* Ensure content area has a min height */
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
            background: #28a745;
            color: white;
            border-color: #218838;
        }

        .option-item.incorrect {
            background: #dc3545;
            color: white;
            border-color: #c82333;
        }

        .option-letter {
            font-weight: bold;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            background: rgba(255, 255, 255, 0.2);
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
            padding: 20px;
            border-top: 1px solid #e0e0e0;
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

        /* Result Page Styles */
        .results-container {
            text-align: center;
            padding: 30px;
        }

        .results-container h2 {
            font-size: 2rem;
            color: #3068b5;
            margin-bottom: 20px;
        }

        .score-box {
            background: #eaf3ff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border: 2px solid #c8d9e8;
        }

        .score-display {
            font-size: 3.5rem;
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
            gap: 20px;
            margin-top: 25px;
        }

        .score-item {
            background: #f0f4f8;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            border: 1px solid #d1d9e6;
        }

        .score-item h4 {
            font-size: 1rem;
            color: #555;
            margin-bottom: 5px;
        }

        .score-item .number {
            font-size: 2rem;
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
            font-size: 1.5rem;
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

        .review-item p {
            margin-bottom: 5px;
            line-height: 1.5;
        }

        .review-item strong {
            color: #3068b5;
        }

        .btn-back-to-menu {
            background: #6c757d;
            color: white;
            border-radius: 30px;
            padding: 12px 25px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="quiz-container">
        <div class="quiz-card" id="quizCard">
            <div class="quiz-header">
                <h1 class="quiz-title-header" id="quizHeaderTitle">Memuat Kuis...</h1>
                <p class="quiz-subtitle-header" id="quizHeaderSubtitle"></p>
                <div class="progress-container">
                    <div class="progress-bar" id="progressBar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="progress-text" id="progressText"></div>
                <p id="quizHeaderTimer"></p>
            </div>

            <div class="quiz-content" id="quizContent">
                <div id="loadingQuizContent">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-3">Memuat kuis...</p>
                </div>
                
                <div class="question-container d-none">
                    <div class="question-number" id="questionNumber"></div>
                    <div class="question-text" id="questionText"></div>
                    <div class="options-container" id="optionsContainer"></div>
                    <div class="answer-status d-none" id="answerStatus"></div>
                </div>
                
                <div class="navigation-container d-none">
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        const currentStudentId = <?php echo json_encode($current_student_id); ?>;
        const urlParams = new URLSearchParams(window.location.search);
        let currentQuizId = parseInt(urlParams.get('quiz_id'));

        if (isNaN(currentQuizId) || currentQuizId <= 0) {
            alert('Quiz ID tidak valid atau tidak ditemukan. Kembali ke halaman Quiz Landing Page.');
            window.location.href = 'lpquiz.php';
        }

        let questions = [];
        let currentQuestionIndex = 0;
        let userAnswers = [];
        let score = 0;
        let correctAnswers = 0;
        let quizCompleted = false;
        let quizDetails = null;

        let quizTimerInterval;
        let timeRemainingSeconds = 0;
        const TIMER_ELEMENT_ID = 'quizHeaderTimer';

        async function fetchQuizContent(quizId) {
            try {
                const quizDetailsResponse = await fetch(`api/get_quiz_details.php?quiz_id=${quizId}`);
                if (!quizDetailsResponse.ok) throw new Error(`HTTP error fetching quiz details! Status: ${quizDetailsResponse.status}`);
                quizDetails = await quizDetailsResponse.json();
                if (quizDetails.error) throw new Error(quizDetails.error);

                const questionsResponse = await fetch(`api/get_quiz_questions.php?quiz_id=${quizId}`);
                if (!questionsResponse.ok) throw new Error(`HTTP error fetching questions! Status: ${questionsResponse.status}`);
                const fetchedQuestions = await questionsResponse.json();
                if (fetchedQuestions.error) throw new Error(fetchedQuestions.error);

                const organizedQuestions = {};
                fetchedQuestions.forEach(q => {
                    if (!organizedQuestions[q.question_id]) {
                        organizedQuestions[q.question_id] = {
                            id: q.question_id,
                            quiz_id: q.quiz_id,
                            question: q.question_text,
                            formula: q.question_formula,
                            question_type: q.question_type,
                            correct_option_id: q.correct_option_id,
                            explanation: q.explanation,
                            options: []
                        };
                    }
                    if (q.option_id && q.option_id !== 0) {
                        organizedQuestions[q.question_id].options.push({
                            option_id: q.option_id,
                            option_text: q.option_text,
                            is_correct: q.is_correct
                        });
                    }
                });

                return Object.values(organizedQuestions).sort((a, b) => a.id - b.id);
            } catch (error) {
                console.error("Error fetching quiz content:", error);
                return [];
            }
        }

        async function submitQuizResult(finalScore, correctCount, totalAnswered, totalQuestions) {
            try {
                const response = await fetch('api/submit_quiz_result.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        quiz_id: currentQuizId,
                        student_id: currentStudentId,
                        score: finalScore,
                        correct_answers_count: correctCount,
                        total_questions_answered: totalAnswered,
                        total_questions: totalQuestions
                    })
                });
                const result = await response.json();
                if (!result.success) {
                    console.error("Failed to submit quiz result:", result.error);
                } else {
                    console.log("Quiz result submitted successfully:", result.message);
                }
            } catch (error) {
                console.error("Error submitting quiz result:", error);
            }
        }

        function startQuizTimer() {
            if (quizTimerInterval) clearInterval(quizTimerInterval);

            let timerElement = document.getElementById(TIMER_ELEMENT_ID);
            if (!timerElement) {
                timerElement = document.createElement('p');
                timerElement.id = TIMER_ELEMENT_ID;
                document.querySelector('.quiz-header').appendChild(timerElement);
            }

            timeRemainingSeconds = quizDetails.duration_minutes * 60;
            quizTimerInterval = setInterval(function() {
                timeRemainingSeconds--;
                if (timeRemainingSeconds <= 0) {
                    clearInterval(quizTimerInterval);
                    autoSubmitExam();
                    return;
                }
                const minutes = Math.floor(timeRemainingSeconds / 60);
                const seconds = timeRemainingSeconds % 60;
                timerElement.textContent = `Sisa Waktu: ${minutes} Menit ${seconds} Detik`;
            }, 1000);
        }

        function stopQuizTimer() {
            clearInterval(quizTimerInterval);
        }

        document.addEventListener('DOMContentLoaded', async function() {
            document.getElementById('loadingQuizContent').classList.remove('d-none');
            document.querySelector('.question-container').classList.add('d-none');
            document.querySelector('.navigation-container').classList.add('d-none');
            
            questions = await fetchQuizContent(currentQuizId);

            if (questions.length > 0 && quizDetails) {
                document.getElementById('loadingQuizContent').classList.add('d-none');
                document.querySelector('.question-container').classList.remove('d-none');
                document.querySelector('.navigation-container').classList.remove('d-none');
                
                document.getElementById('quizHeaderTitle').textContent = quizDetails.title;
                document.getElementById('quizHeaderSubtitle').textContent = quizDetails.description;

                initializeQuiz();
                displayQuestion();
                startQuizTimer();
            } else {
                document.getElementById('loadingQuizContent').innerHTML = `
                    <div class="text-center text-danger">
                        <i class="fas fa-exclamation-circle me-2"></i> Gagal memuat kuis atau kuis tidak memiliki pertanyaan.
                    </div>
                    <button class="btn btn-primary mt-3" onclick="window.location.href='lpquiz.php'">
                        <i class="fas fa-arrow-left me-2"></i> Kembali ke Halaman Kuis
                    </button>
                `;
            }
        });

        function initializeQuiz() {
            userAnswers = new Array(questions.length).fill({ selectedOptionId: null, essayAnswer: null, isCorrect: null });
            currentQuestionIndex = 0;
            score = 0;
            correctAnswers = 0;
            quizCompleted = false;
            updateProgress();
        }

        function displayQuestion() {
            const question = questions[currentQuestionIndex];
            
            document.getElementById('questionNumber').textContent = `Pertanyaan ${currentQuestionIndex + 1} dari ${questions.length}`;
            let questionHTML = question.question;
            if (question.formula) {
                questionHTML += `<div class="math-formula">${escapeHtml(question.formula)}</div>`;
            }
            document.getElementById('questionText').innerHTML = questionHTML;
            
            const optionsContainer = document.getElementById('optionsContainer');
            optionsContainer.innerHTML = '';
            
            if (question.question_type === 'multiple_choice' && question.options.length > 0) {
                question.options.forEach((option, index) => {
                    const optionElement = createOptionElement(option.option_text, option.option_id, question.id, index);
                    optionsContainer.appendChild(optionElement);
                });
            } else {
                optionsContainer.innerHTML = '<p class="text-muted">Tipe pertanyaan tidak didukung atau tidak ada pilihan jawaban.</p>';
            }

            document.getElementById('answerStatus').classList.add('d-none');
            document.getElementById('answerStatus').innerHTML = '';

            const currentAnswerData = userAnswers[currentQuestionIndex];
            if (currentAnswerData.selectedOptionId !== null) {
                const radio = document.querySelector(`[data-option-id="${currentAnswerData.selectedOptionId}"]`);
                if (radio) {
                    showFeedbackOnReturn(currentAnswerData.selectedOptionId, question.correct_option_id);
                }
            }

            updateNavigationButtons();
            updateProgress();
        }

        function createOptionElement(optionText, optionId, questionId, index) {
            const optionDiv = document.createElement('div');
            optionDiv.className = 'option-item';
            optionDiv.setAttribute('data-option-id', optionId);
            optionDiv.addEventListener('click', () => selectAnswer(optionId));

            const letter = document.createElement('div');
            letter.className = 'option-letter';
            letter.textContent = String.fromCharCode(65 + index);

            const text = document.createElement('div');
            text.className = 'option-text';
            text.textContent = optionText;

            const icon = document.createElement('i');
            icon.className = 'result-icon fas';

            optionDiv.appendChild(letter);
            optionDiv.appendChild(text);
            optionDiv.appendChild(icon);
            return optionDiv;
        }

        function selectAnswer(selectedOptionId) {
            const currentQuestion = questions[currentQuestionIndex];
            userAnswers[currentQuestionIndex] = {
                questionId: currentQuestion.id,
                selectedOptionId: selectedOptionId,
                correctOptionId: currentQuestion.correct_option_id
            };

            document.querySelectorAll('.option-item').forEach(el => {
                el.classList.remove('selected', 'correct', 'incorrect');
                el.querySelector('.result-icon').classList.remove('fa-check', 'fa-times');
            });
            
            const selectedItem = document.querySelector(`[data-option-id="${selectedOptionId}"]`);
            if (selectedItem) {
                selectedItem.classList.add('selected');
                showAnswerFeedback(selectedOptionId, currentQuestion.correct_option_id);
            }

            updateNavigationButtons();
            updateProgress();
        }

        function showFeedbackOnReturn(selectedOptionId, correctOptionId) {
            const question = questions[currentQuestionIndex];
            document.getElementById('answerStatus').classList.remove('d-none');
            const answerStatus = document.getElementById('answerStatus');
            
            document.querySelectorAll('.option-item').forEach(item => {
                const itemId = item.getAttribute('data-option-id');
                if (itemId == correctOptionId) {
                    item.classList.add('correct');
                    item.querySelector('.result-icon').classList.add('fa-check');
                }
                if (itemId == selectedOptionId) {
                    item.classList.add('selected');
                    if (itemId != correctOptionId) {
                        item.classList.add('incorrect');
                        item.querySelector('.result-icon').classList.add('fa-times');
                    }
                }
            });

            if (selectedOptionId == correctOptionId) {
                answerStatus.innerHTML = 'Jawaban Benar! Excellent!';
                answerStatus.className = 'answer-status correct show';
            } else {
                const correctOptionText = question.options.find(opt => opt.option_id == correctOptionId)?.option_text || 'Tidak diketahui';
                answerStatus.innerHTML = `Jawaban Salah! Jawaban yang benar adalah <span class="fw-bold">${escapeHtml(correctOptionText)}</span>.`;
                answerStatus.className = 'answer-status incorrect show';
            }
        }

        function showAnswerFeedback(selectedOptionId, correctOptionId) {
            const question = questions[currentQuestionIndex];
            document.getElementById('answerStatus').classList.remove('d-none');
            const answerStatus = document.getElementById('answerStatus');

            document.querySelectorAll('.option-item').forEach(item => {
                const itemId = item.getAttribute('data-option-id');
                if (itemId == correctOptionId) {
                    item.classList.add('correct');
                    item.querySelector('.result-icon').classList.add('fa-check');
                }
                if (itemId == selectedOptionId) {
                    if (itemId != correctOptionId) {
                        item.classList.add('incorrect');
                        item.querySelector('.result-icon').classList.add('fa-times');
                    }
                }
            });

            if (selectedOptionId == correctOptionId) {
                answerStatus.innerHTML = 'Jawaban Benar! Excellent!';
                answerStatus.className = 'answer-status correct show';
            } else {
                const correctOptionText = question.options.find(opt => opt.option_id == correctOptionId)?.option_text || 'Tidak diketahui';
                answerStatus.innerHTML = `Jawaban Salah! Jawaban yang benar adalah <span class="fw-bold">${escapeHtml(correctOptionText)}</span>.`;
                answerStatus.className = 'answer-status incorrect show';
            }
        }

        function nextQuestion() {
            const currentAnswerData = userAnswers[currentQuestionIndex];
            if (currentAnswerData.selectedOptionId === null) {
                alert('Silakan pilih jawaban terlebih dahulu!');
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

            prevButton.disabled = currentQuestionIndex === 0;
            
            const currentAnswerData = userAnswers[currentQuestionIndex];
            nextButton.disabled = currentAnswerData.selectedOptionId === null;
            
            if (currentQuestionIndex === questions.length - 1) {
                nextButton.innerHTML = 'Selesai <i class="fas fa-check"></i>';
                nextButton.className = 'nav-button btn-finish';
            } else {
                nextButton.innerHTML = 'Selanjutnya <i class="fas fa-arrow-right"></i>';
                nextButton.className = 'nav-button btn-next';
            }
        }

        function updateProgress() {
            let answeredCount = userAnswers.filter(a => a.selectedOptionId !== null).length;
            const totalQuestions = questions.length;
            const progress = (answeredCount / totalQuestions) * 100;
            
            document.getElementById('progressBar').style.width = progress + '%';
            document.getElementById('progressBar').setAttribute('aria-valuenow', progress);
            document.getElementById('progressText').textContent =
                `Pertanyaan ${currentQuestionIndex + 1} dari ${totalQuestions} (${answeredCount} dijawab)`;
        }

        async function finishQuiz() {
            stopQuizTimer();
            quizCompleted = true;
            correctAnswers = userAnswers.filter(answer => answer.selectedOptionId == answer.correctOptionId).length;
            score = Math.round((correctAnswers / questions.length) * 100);
            if (isNaN(score) || !isFinite(score)) score = 0;

            await submitQuizResult(score, correctAnswers, userAnswers.length, questions.length);
            displayResults();
        }

        function displayResults() {
            const quizContent = document.getElementById('quizContent');
            
            let gradeText = '';
            let gradeColor = '';
            const finalPassingScore = quizDetails ? quizDetails.passing_score : 70;
            if (score >= finalPassingScore) {
                gradeText = 'LULUS!';
                gradeColor = '#28a745';
            } else {
                gradeText = 'TIDAK LULUS';
                gradeColor = '#dc3545';
            }

            quizContent.innerHTML = `
                <div class="results-container">
                    <h2 class="results-title">Hasil Kuis Anda</h2>
                    <div class="score-box">
                        <div class="score-display ${score < finalPassingScore ? 'fail' : ''}">${score}%</div>
                        <div class="status-text ${score < finalPassingScore ? 'fail' : ''}">${gradeText}</div>
                    </div>
                    <div class="score-details-grid">
                        <div class="score-item">
                            <h4>Total Pertanyaan</h4>
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
                        <div class="score-item">
                            <h4>Nilai Lulus</h4>
                            <div class="number" style="color: #4b89dc;">${finalPassingScore}%</div>
                        </div>
                    </div>
                    <div class="review-section">
                        <h4>Review Jawaban</h4>
                        <div id="reviewContainer">
                            ${generateReviewHTML()}
                        </div>
                    </div>
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button class="btn btn-back-to-menu" onclick="backToQuizLanding()">
                            <i class="fas fa-arrow-left"></i> Kembali ke Menu Kuis
                        </button>
                    </div>
                </div>
            `;
        }

        function generateReviewHTML() {
            let reviewHTML = '';
            questions.forEach((question, index) => {
                const userAnswerData = userAnswers[index];
                const selectedOptionId = userAnswerData ? userAnswerData.selectedOptionId : null;
                const correctOptionId = question.correct_option_id;
                const isCorrect = (selectedOptionId == correctOptionId);
                const statusColor = isCorrect ? '#28a745' : '#dc3545';
                const statusIcon = isCorrect ? 'fa-check' : 'fa-times';
                const userAnswerText = selectedOptionId ? question.options.find(opt => opt.option_id == selectedOptionId)?.option_text : 'Tidak dijawab';
                const correctAnswerText = question.options.find(opt => opt.option_id == correctOptionId)?.option_text || 'Tidak diketahui';

                reviewHTML += `
                    <div class="review-item ${isCorrect ? 'correct' : 'incorrect'}">
                        <strong>Pertanyaan ${index + 1}:</strong>
                        <p class="mb-2">${escapeHtml(question.question)}</p>
                        ${question.formula ? `<div class="math-formula">${escapeHtml(question.formula)}</div>` : ''}
                        <p><strong>Jawaban Anda:</strong> <span class="fw-bold" style="color: ${statusColor};">${escapeHtml(userAnswerText)} <i class="fas ${statusIcon}"></i></span></p>
                        ${!isCorrect ? `<p class="text-success"><strong>Jawaban Benar:</strong> ${escapeHtml(correctAnswerText)}</p>` : ''}
                        <p class="text-muted mt-2"><small><strong>Penjelasan:</strong> ${escapeHtml(question.explanation || 'Tidak ada penjelasan.')}</small></p>
                    </div>
                `;
            });
            return reviewHTML;
        }


        function backToQuizLanding() {
            stopQuizTimer();
            window.location.href = 'lpquiz.php';
        }

        function autoSubmitExam() {
            alert('Waktu ujian telah habis. Ujian akan disubmit otomatis.');
            finishQuiz();
        }

        function escapeHtml(text) {
            const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>
</html>