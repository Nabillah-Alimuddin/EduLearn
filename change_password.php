<?php
session_start();
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connection.php';

header('Content-Type: application/json');

function send_json_error($message) {
    echo json_encode(['success' => false, 'error' => $message]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

$current_student_id = $input['user_id'] ?? null;
$old_password = $input['old_password'] ?? null;
$new_password = $input['new_password'] ?? null;

if (!$current_student_id || !$old_password || !$new_password) {
    send_json_error("Invalid or incomplete data.");
}

$conn->beginTransaction();
try {
    // Ambil hash password lama dari database
    $sql_get_password = "SELECT password_hash FROM users WHERE user_id = ? AND role = 'student'";
    $stmt_get_password = $conn->prepare($sql_get_password);
    if (!$stmt_get_password) {
        throw new Exception("Failed to prepare statement.");
    }
    $stmt_get_password->execute([$current_student_id]);
    $user = $stmt_get_password->fetch();

    if (!$user || !password_verify($old_password, $user['password_hash'])) {
        throw new Exception("Password lama tidak sesuai.");
    }

    // Hash password baru
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    // Perbarui password di database
    $sql_update_password = "UPDATE users SET password_hash = ? WHERE user_id = ?";
    $stmt_update_password = $conn->prepare($sql_update_password);
    if (!$stmt_update_password) {
        throw new Exception("Failed to prepare update statement.");
    }
    $stmt_update_password->execute([$new_password_hash, $current_student_id]);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Password changed successfully.']);

} catch (Exception $e) {
    $conn->rollBack();
    send_json_error($e->getMessage());
}

$conn = null;
ob_end_flush();
?>