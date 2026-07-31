<?php
include '../db_connection.php';
header('Content-Type: application/json');

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
$questions = ['error' => 'No questions found or invalid quiz ID'];

if ($quiz_id > 0) {
    $sql = "
        SELECT
            qq.question_id,
            qq.quiz_id,
            qq.question_text,
            qq.question_formula,
            qq.question_type,
            qq.explanation,
            qo.option_id,
            qo.option_text,
            qo.is_correct,
            (SELECT option_id FROM question_options WHERE question_id = qq.question_id AND is_correct = TRUE LIMIT 1) AS correct_option_id
        FROM
            quiz_questions qq
        LEFT JOIN
            question_options qo ON qq.question_id = qo.question_id
        WHERE
            qq.quiz_id = ?
        ORDER BY
            qq.question_id, qo.option_id
    ";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->execute([$quiz_id]);
        $questions = [];
        while ($row = $stmt->fetch()) {
            $questions[] = $row;
        }
    }
}
$conn = null;
echo json_encode($questions);
?>