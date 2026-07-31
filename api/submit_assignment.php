<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Izinkan akses dari semua domain (untuk pengembangan)
header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // Tambah GET karena unsubmit akan pakai GET
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request (penting untuk CORS dengan POST)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include '../middleware.php';
include '../db_connection.php';
require_api_auth('student');

$student_id = $_SESSION['user_id'];

// Cek apakah ada aksi 'delete' (untuk unsubmit)
$action = $_GET['action'] ?? null;

if ($action === 'delete') {
    $assignment_id = isset($_GET['assignment_id']) ? intval($_GET['assignment_id']) : 0;

    if ($assignment_id === 0) {
        echo json_encode(["success" => false, "error" => "Assignment ID not provided or invalid for delete action."]);
        $conn = null;
        exit();
    }

    try {
        // Ambil path file submission sebelum menghapus record
        $sql_get_file = "SELECT submission_file_path FROM submissions WHERE assignment_id = ? AND student_id = ?";
        $stmt_get_file = $conn->prepare($sql_get_file);
        $stmt_get_file->execute([$assignment_id, $student_id]);
        $file_to_delete_path = null;
        if ($row = $stmt_get_file->fetch()) {
            $file_to_delete_path = $row['submission_file_path'];
        }

        // Hapus entri dari database
        $sql_delete = "DELETE FROM submissions WHERE assignment_id = ? AND student_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        
        if ($stmt_delete->execute([$assignment_id, $student_id])) {
            if ($stmt_delete->rowCount() > 0) {
                // Jika berhasil dihapus dari DB, hapus juga file fisik
                if ($file_to_delete_path) {
                    // Pastikan path absolut untuk unlink
                    $absolute_file_path = __DIR__ . '/../' . $file_to_delete_path;
                    if (file_exists($absolute_file_path) && is_file($absolute_file_path)) {
                        unlink($absolute_file_path);
                    }
                }
                echo json_encode(["success" => true, "message" => "Submission successfully deleted."]);
            } else {
                echo json_encode(["success" => false, "error" => "No submission found to delete."]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "Failed to delete submission."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
    }
    
    $conn = null;
    exit();
}


// --- Logic untuk POST (Submit Tugas) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = isset($_POST['assignment_id']) ? intval($_POST['assignment_id']) : 0;

    if ($assignment_id === 0) {
        echo json_encode(["success" => false, "error" => "Assignment ID not provided or invalid."]);
        $conn = null;
        exit();
    }

    $file_path = null;
    $upload_dir = 'uploads/submissions/'; // Direktori penyimpanan file (relatif ke folder API)

    // Pastikan direktori upload ada
    $absolute_upload_dir = __DIR__ . '/../' . $upload_dir; 
    if (!is_dir($absolute_upload_dir)) {
        mkdir($absolute_upload_dir, 0777, true);
    }

    // Tangani upload file
    if (isset($_FILES['file_submission']) && $_FILES['file_submission']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['file_submission']['tmp_name'];
        $file_name = basename($_FILES['file_submission']['name']);
        
        // Buat nama file unik untuk menghindari konflik
        $unique_file_name = $student_id . '_' . $assignment_id . '_' . time() . '_' . $file_name;
        $destination = $absolute_upload_dir . $unique_file_name;

        // Path yang disimpan di database harus relatif dari root htdocs/elearning
        $file_path_for_db = $upload_dir . $unique_file_name;

        if (move_uploaded_file($file_tmp_name, $destination)) {
            $file_path = $file_path_for_db; // Path yang akan disimpan ke database
        } else {
            echo json_encode(["success" => false, "error" => "Failed to move uploaded file."]);
            $conn = null;
            exit();
        }
    } else {
        echo json_encode(["success" => false, "error" => "File upload error: " . ($_FILES['file_submission']['error'] ?? 'No file uploaded or unknown error')]);
        $conn = null;
        exit();
    }

    try {
        // Cek apakah sudah ada submission sebelumnya untuk tugas ini oleh mahasiswa ini
        $check_sql = "SELECT submission_id FROM submissions WHERE assignment_id = ? AND student_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([$assignment_id, $student_id]);
        $row = $check_stmt->fetch();

        if ($row) {
            // Jika sudah ada, update submission yang ada
            $submission_id = $row['submission_id'];
            $update_sql = "UPDATE submissions SET submission_file_path = ?, submitted_at = NOW() WHERE submission_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            if ($update_stmt->execute([$file_path, $submission_id])) {
                echo json_encode(["success" => true, "message" => "Assignment updated successfully.", "file_path" => $file_path]);
            } else {
                echo json_encode(["success" => false, "error" => "Failed to update assignment."]);
            }
        } else {
            // Jika belum ada, insert submission baru
            $insert_sql = "INSERT INTO submissions (assignment_id, student_id, submission_file_path, submitted_at) VALUES (?, ?, ?, NOW())";
            $insert_stmt = $conn->prepare($insert_sql);
            
            if ($insert_stmt->execute([$assignment_id, $student_id, $file_path])) {
                echo json_encode(["success" => true, "message" => "Assignment submitted successfully.", "file_path" => $file_path]);
            } else {
                echo json_encode(["success" => false, "error" => "Failed to submit assignment."]);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
    }

} else {
    echo json_encode(["success" => false, "error" => "Invalid request method."]);
}

$conn = null;
?>