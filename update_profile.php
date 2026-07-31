<?php
ob_start();
include 'middleware.php';
include 'db_connection.php';
require_api_auth('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error("Invalid request method.");
}

$current_student_id = $_SESSION['user_id'];

$conn->beginTransaction();
try {
    // Ambil data dari POST
    $full_name = $_POST['full_name'] ?? null;
    $nim = $_POST['nim'] ?? null;
    $nik = $_POST['nik'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $study_program = $_POST['study_program'] ?? null;
    $religion = $_POST['religion'] ?? null;
    $nationality = $_POST['nationality'] ?? null;
    $place_of_birth = $_POST['place_of_birth'] ?? null;
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $phone_number = $_POST['phone_number'] ?? null;
    $previous_school = $_POST['previous_school'] ?? null;
    $nisn = $_POST['nisn'] ?? null;
    $school_city = $_POST['school_city'] ?? null;

    // Perbarui data pengguna
    $sql_update = "
        UPDATE users SET
        full_name = ?, nim = ?, nik = ?, gender = ?, study_program = ?,
        religion = ?, nationality = ?, place_of_birth = ?, date_of_birth = ?,
        phone_number = ?, previous_school = ?, nisn = ?, school_city = ?
        WHERE user_id = ? AND role = 'student'
    ";
    
    $stmt_update = $conn->prepare($sql_update);
    if (!$stmt_update) {
        throw new Exception("Failed to prepare statement.");
    }
    
    $stmt_update->execute([
        $full_name, $nim, $nik, $gender, $study_program,
        $religion, $nationality, $place_of_birth, $date_of_birth,
        $phone_number, $previous_school, $nisn, $school_city,
        $current_student_id
    ]);

    // Perbarui foto profil jika ada
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['profile_picture']['tmp_name'];
        $file_name = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
        $upload_dir = 'uploads/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($file_tmp_path, $file_path)) {
            $sql_update_photo = "UPDATE users SET profile_picture_url = ? WHERE user_id = ?";
            $stmt_photo = $conn->prepare($sql_update_photo);
            if (!$stmt_photo) {
                throw new Exception("Failed to prepare photo statement.");
            }
            $stmt_photo->execute([$file_path, $current_student_id]);
        } else {
            throw new Exception("Failed to move uploaded file.");
        }
    }

    $conn->commit();
    json_success('Profil berhasil diperbarui.');

} catch (Exception $e) {
    $conn->rollBack();
    json_error($e->getMessage());
}

$conn = null;
ob_end_flush();
?>