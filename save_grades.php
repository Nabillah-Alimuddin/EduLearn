<?php
// save_grades.php
include 'middleware.php';
include 'db_connection.php';
require_api_auth('lecturer');

$data = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'message' => 'Terjadi kesalahan.', 'error_detail' => null];

if (isset($data['course_id']) && isset($data['grades'])) {
    $course_id = (int)$data['course_id'];
    $grades_to_save = $data['grades'];

    $conn->beginTransaction();
    $all_success = true;

    function getGradeLetterForSave($final_score) {
        if ($final_score >= 85) return 'A';
        if ($final_score >= 70) return 'B';
        if ($final_score >= 55) return 'C';
        if ($final_score >= 40) return 'D';
        return 'E';
    }

    function getGradePointsForSave($grade_letter) {
        switch ($grade_letter) {
            case 'A': return 4.00;
            case 'B': return 3.00;
            case 'C': return 2.00;
            case 'D': return 1.00;
            case 'E': return 0.00;
            default: return 0.00;
        }
    }

    $grade_type_map = [
        'tugas' => 'Assignment',
        'uts' => 'UTS',
        'uas' => 'UAS',
        'partisipasi' => 'Partisipasi'
    ];

    foreach ($grades_to_save as $student_grade) {
        $student_id = (int)$student_grade['user_id'];

        foreach (['tugas', 'uts', 'uas', 'partisipasi'] as $type_key) {
            $value = (float)$student_grade[$type_key];
            $db_grade_type = $grade_type_map[$type_key];

            $grade_letter = getGradeLetterForSave($value);
            $grade_points = getGradePointsForSave($grade_letter);

            // Cek apakah entri grade sudah ada
            $check_sql = "SELECT grade_id FROM grades WHERE student_id = ? AND course_id = ? AND grade_type = ?";
            $stmt_check = $conn->prepare($check_sql);
            if (!$stmt_check) {
                $all_success = false;
                $response['error_detail'] = "Failed to prepare check statement.";
                break 2;
            }
            $stmt_check->execute([$student_id, $course_id, $db_grade_type]);
            $row_check = $stmt_check->fetch();

            if ($row_check) {
                // Update existing grade
                $update_sql = "UPDATE grades SET grade_value = ?, grade_letter = ?, grade_points = ?, graded_at = NOW() WHERE student_id = ? AND course_id = ? AND grade_type = ?";
                $stmt_update = $conn->prepare($update_sql);
                if (!$stmt_update) {
                    $all_success = false;
                    $response['error_detail'] = "Failed to prepare update statement for " . $db_grade_type;
                    break 2;
                }
                if (!$stmt_update->execute([$value, $grade_letter, $grade_points, $student_id, $course_id, $db_grade_type])) {
                    $all_success = false;
                    $response['error_detail'] = "Failed to execute update for " . $db_grade_type;
                    break 2;
                }
            } else {
                // Insert new grade
                $insert_sql = "INSERT INTO grades (student_id, course_id, item_id, grade_value, grade_letter, grade_points, grade_type, graded_at) VALUES (?, ?, NULL, ?, ?, ?, ?, NOW())";
                $stmt_insert = $conn->prepare($insert_sql);
                if (!$stmt_insert) {
                    $all_success = false;
                    $response['error_detail'] = "Failed to prepare insert statement for " . $db_grade_type;
                    break 2;
                }
                if (!$stmt_insert->execute([$student_id, $course_id, $value, $grade_letter, $grade_points, $db_grade_type])) {
                    $all_success = false;
                    $response['error_detail'] = "Failed to execute insert for " . $db_grade_type;
                    break 2;
                }
            }
        } // End of foreach type_key

        if (!$all_success) break;

        // Handle Final Course Grade
        $final_score = ($student_grade['tugas'] * 0.20) + ($student_grade['uts'] * 0.30) + ($student_grade['uas'] * 0.40) + ($student_grade['partisipasi'] * 0.10);
        $final_grade_letter = getGradeLetterForSave($final_score);
        $final_grade_points = getGradePointsForSave($final_grade_letter);

        $check_final_sql = "SELECT grade_id FROM grades WHERE student_id = ? AND course_id = ? AND grade_type = 'Final Course'";
        $stmt_check_final = $conn->prepare($check_final_sql);
        if (!$stmt_check_final) {
             $all_success = false;
             $response['error_detail'] = "Failed to prepare final check statement.";
             break;
        }
        $stmt_check_final->execute([$student_id, $course_id]);
        $row_final = $stmt_check_final->fetch();

        if ($row_final) {
            $update_final_sql = "UPDATE grades SET grade_value = ?, grade_letter = ?, grade_points = ?, graded_at = NOW() WHERE student_id = ? AND course_id = ? AND grade_type = 'Final Course'";
            $stmt_update_final = $conn->prepare($update_final_sql);
            if (!$stmt_update_final) {
                $all_success = false;
                $response['error_detail'] = "Failed to prepare final update statement.";
                break;
            }
            if (!$stmt_update_final->execute([$final_score, $final_grade_letter, $final_grade_points, $student_id, $course_id])) {
                $all_success = false;
                $response['error_detail'] = "Failed to execute final update.";
                break;
            }
        } else {
            $insert_final_sql = "INSERT INTO grades (student_id, course_id, item_id, grade_value, grade_letter, grade_points, grade_type, graded_at) VALUES (?, ?, NULL, ?, ?, ?, 'Final Course', NOW())";
            $stmt_insert_final = $conn->prepare($insert_final_sql);
            if (!$stmt_insert_final) {
                $all_success = false;
                $response['error_detail'] = "Failed to prepare final insert statement.";
                break;
            }
            if (!$stmt_insert_final->execute([$student_id, $course_id, $final_score, $final_grade_letter, $final_grade_points])) {
                $all_success = false;
                $response['error_detail'] = "Failed to execute final insert.";
                break;
            }
        }

    } // End of foreach student_grade

    if ($all_success) {
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Nilai berhasil disimpan.';
    } else {
        $conn->rollBack();
        $response['message'] = 'Gagal menyimpan nilai.';
    }

} else {
    $response['message'] = 'Data tidak lengkap.';
}

echo json_encode($response);
$conn = null;
?>