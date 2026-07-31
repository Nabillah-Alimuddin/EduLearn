<?php
ob_start();
include 'middleware.php';
include 'db_connection.php';
require_api_auth('student');

$current_student_id = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);

$old_password = $input['old_password'] ?? null;
$new_password = $input['new_password'] ?? null;

if (!$old_password || !$new_password) {
    json_error("Data tidak lengkap.");
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
    json_success('Password berhasil diubah.');

} catch (Exception $e) {
    $conn->rollBack();
    json_error($e->getMessage());
}

$conn = null;
ob_end_flush();
?>