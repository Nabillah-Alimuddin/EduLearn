<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Izinkan akses dari semua domain (untuk pengembangan)
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request (penting untuk CORS dengan POST)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include '../middleware.php';
include '../db_connection.php';
require_api_auth('student');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];

    // Ambil data dari POST
    $nim = $_POST['nim'] ?? null;
    $nik = $_POST['nik'] ?? null;
    $full_name = $_POST['full_name'] ?? null;
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

    $profile_picture_url = null;
    $upload_dir = 'uploads/profile_pics/'; // Direktori penyimpanan foto profil (relative to PHP file)

    // Pastikan direktori upload ada
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Tangani upload foto profil
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['profile_picture']['tmp_name'];
        $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        
        // Buat nama file unik (misal: user_id_timestamp.ext)
        $unique_file_name = $user_id . '_' . time() . '.' . $file_extension;
        $destination = $upload_dir . $unique_file_name;

        if (move_uploaded_file($file_tmp_name, $destination)) {
            $profile_picture_url = $destination; // Simpan path relatif ke database
        } else {
            echo json_encode(["success" => false, "error" => "Failed to move uploaded profile picture."]);
            $conn = null;
            exit();
        }
    } else {
        // Jika tidak ada file baru diunggah, ambil URL yang sudah ada
        $sql_get_old_pic = "SELECT profile_picture_url FROM users WHERE user_id = ?";
        $stmt_get_old_pic = $conn->prepare($sql_get_old_pic);
        $stmt_get_old_pic->execute([$user_id]);
        if ($row = $stmt_get_old_pic->fetch()) {
            $profile_picture_url = $row['profile_picture_url'];
        }
    }

    // Update data di tabel users
    $sql = "UPDATE users SET 
                nim = ?, nik = ?, full_name = ?, gender = ?, study_program = ?, 
                religion = ?, nationality = ?, place_of_birth = ?, date_of_birth = ?, 
                phone_number = ?, previous_school = ?, nisn = ?, school_city = ?, 
                profile_picture_url = ?, updated_at = NOW()
            WHERE user_id = ?";
    
    try {
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([
            $nim, $nik, $full_name, $gender, $study_program, 
            $religion, $nationality, $place_of_birth, $date_of_birth, 
            $phone_number, $previous_school, $nisn, $school_city, 
            $profile_picture_url, $user_id
        ])) {
            echo json_encode(["success" => true, "message" => "Profile updated successfully.", "profile_picture_url" => $profile_picture_url]);
        } else {
            echo json_encode(["success" => false, "error" => "Failed to update profile."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
    }

} else {
    echo json_encode(["success" => false, "error" => "Invalid request method."]);
}

$conn = null;
?>