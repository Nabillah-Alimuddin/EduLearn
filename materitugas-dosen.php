<?php
include 'middleware.php';
include 'db_connection.php';
require_role('lecturer');

$current_lecturer_id = $_SESSION['user_id'];

// Inisialisasi variabel
$current_course_id = null; // Akan ditentukan setelah fetch course yang diampu dosen
$current_pertemuan_id = null; // Asumsi pertemuan ID sama dengan course ID
$materials_data_for_current_course = [];
$assignments_data_for_current_course = [];
$courses_data_for_sidebar = []; // Data semua mata kuliah untuk sidebar
$course_name_for_header = "Materi & Tugas"; // Default course name for header
$lecturer_full_name_header = "Dosen";
$lecturer_gelar_header = "";

// --- Fungsi Helper ---
function getFileIcon($fileType) {
    if (!$fileType) return 'fas fa-file';
    $fileType = strtolower($fileType);
    if (strpos($fileType, 'pdf') !== false) return 'fas fa-file-pdf';
    if (strpos($fileType, 'doc') !== false || strpos($fileType, 'docx') !== false) return 'fas fa-file-word';
    if (strpos($fileType, 'ppt') !== false || strpos($fileType, 'pptx') !== false) return 'fas fa-file-powerpoint';
    if (strpos($fileType, 'xls') !== false || strpos($fileType, 'xlsx') !== false) return 'fas fa-file-excel';
    if (strpos($fileType, 'zip') !== false || strpos($fileType, 'rar') !== false) return 'fas fa-file-archive';
    if (strpos($fileType, 'jpg') !== false || strpos($fileType, 'jpeg') !== false || strpos($fileType, 'png') !== false || strpos($fileType, 'gif') !== false) return 'fas fa-file-image';
    if (strpos($fileType, 'txt') !== false) return 'fas fa-file-alt';
    return 'fas fa-file';
}

function formatDateForDisplay($dateString) {
    return date('d F Y H:i', strtotime($dateString)); // Contoh format Indonesia
}

// --- Handle File Upload ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_type'])) {
    $upload_type = $_POST['upload_type'];
    $upload_course_id = (int)$_POST['course_id_upload'];
    $upload_pertemuan_id = (int)$_POST['pertemuan_id_upload'];

    // Periksa apakah ada file yang diunggah dan array 'name' tidak kosong
    if (isset($_FILES['uploaded_file']) && !empty($_FILES['uploaded_file']['name'][0])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $allowed_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif'];
        $uploaded_count = 0;
        $failed_uploads = [];

        // Loop melalui setiap file yang diunggah
        foreach ($_FILES['uploaded_file']['name'] as $key => $uploaded_file_name) {
            // Pastikan tidak ada error untuk file spesifik ini
            if ($_FILES['uploaded_file']['error'][$key] === UPLOAD_ERR_OK) {
                $uploaded_file_tmp_name = $_FILES['uploaded_file']['tmp_name'][$key];

                $unique_file_name = uniqid() . '_' . basename($uploaded_file_name);
                // Path relatif dari root aplikasi (elearning/) untuk disimpan di DB
                $file_path_for_db = $target_dir . $unique_file_name; 
                $target_file_path_on_server = __DIR__ . '/' . $target_dir . $unique_file_name; // Path absolut untuk move_uploaded_file
                $file_extension = strtolower(pathinfo($uploaded_file_name, PATHINFO_EXTENSION));

                // Validasi tipe file
                if (!in_array($file_extension, $allowed_types)) {
                    $failed_uploads[] = htmlspecialchars($uploaded_file_name) . " (Format file tidak diizinkan)";
                    continue; // Lanjut ke file berikutnya
                }

                // Pindahkan file yang diunggah ke folder tujuan
                if (move_uploaded_file($uploaded_file_tmp_name, $target_file_path_on_server)) {
                    if ($upload_type === 'materi') {
                        // Simpan data materi ke database
                        $stmt = $conn->prepare("INSERT INTO materials (course_id, title, file_path, file_type) VALUES (?, ?, ?, ?)");
                        if ($stmt) {
                            if ($stmt->execute([$upload_course_id, $uploaded_file_name, $file_path_for_db, $file_extension])) {
                                $uploaded_count++;
                            } else {
                                // Hapus file jika gagal disimpan ke database
                                if (file_exists($target_file_path_on_server)) unlink($target_file_path_on_server);
                                $failed_uploads[] = htmlspecialchars($uploaded_file_name) . " (Gagal menyimpan ke database)";
                            }
                        }
                    } elseif ($upload_type === 'tugas') {
                        // Untuk tugas, tambahkan judul default dan deskripsi
                        $assignment_title = "Tugas: " . pathinfo($uploaded_file_name, PATHINFO_FILENAME);
                        $assignment_description = "Silakan unduh file tugas ini.";
                        // Ambil deadline dari POST. Jika kosong, gunakan default.
                        $due_date = !empty($_POST['due_date_upload']) ? $_POST['due_date_upload'] : date('Y-m-d H:i:s', strtotime('+1 week'));
                         
                        // Simpan data tugas ke database, termasuk file_path dan file_type
                        $stmt = $conn->prepare("INSERT INTO assignments (course_id, title, description, due_date, file_path, file_type) VALUES (?, ?, ?, ?, ?, ?)");
                        if ($stmt) {
                            if ($stmt->execute([$upload_course_id, $assignment_title, $assignment_description, $due_date, $file_path_for_db, $file_extension])) {
                                $uploaded_count++;
                            } else {
                                // Hapus file jika gagal disimpan ke database
                                if (file_exists($target_file_path_on_server)) unlink($target_file_path_on_server);
                                $failed_uploads[] = htmlspecialchars($uploaded_file_name) . " (Gagal menyimpan ke database)";
                            }
                        }
                    }
                } else {
                    $failed_uploads[] = htmlspecialchars($uploaded_file_name) . " (Gagal memindahkan file)";
                }
            } else {
                $failed_uploads[] = htmlspecialchars($uploaded_file_name) . " (Error upload: " . $_FILES['uploaded_file']['error'][$key] . ")";
            }
        }

        // Tampilkan pesan sukses atau gagal setelah semua file diproses
        $alert_message = "";
        if ($uploaded_count > 0) {
            $alert_message .= "Berhasil mengupload {$uploaded_count} file!";
        }
        if (!empty($failed_uploads)) {
            if ($uploaded_count > 0) {
                $alert_message .= "\\n\\n"; // Tambahkan baris baru jika ada pesan sukses sebelumnya
            }
            $alert_message .= "Gagal mengupload beberapa file:\\n" . implode('\\n', $failed_uploads);
        }
        if (!empty($alert_message)) {
            echo "<script>alert('{$alert_message}');</script>";
        }

    } else {
        echo "<script>alert('Tidak ada file yang diupload atau terjadi error upload.');</script>";
    }
    // Redirect untuk membersihkan POST dan param upload, dan refresh data dengan GET parameter yang sama
    header("Location: materitugas-dosen.php?course_id={$upload_course_id}&pertemuan_id={$upload_pertemuan_id}");
    exit;
}


// --- Handle Delete Action (Tidak ada perubahan di sini) ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['type']) && isset($_GET['id'])) {
    $delete_type = $_GET['type'];
    $delete_id = (int)$_GET['id'];
    $redirect_course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;
    $redirect_pertemuan_id = isset($_GET['pertemuan_id']) ? (int)$_GET['pertemuan_id'] : null;

    $sql_delete = "";
    if ($delete_type === 'materi') {
        $stmt_path = $conn->prepare("SELECT file_path FROM materials WHERE material_id = ?");
        if ($stmt_path) {
            $stmt_path->execute([$delete_id]);
            if ($row_path = $stmt_path->fetch()) {
                $file_to_delete = $row_path['file_path'];
                // Pastikan path absolut untuk unlink
                $absolute_file_path = __DIR__ . '/' . $file_to_delete;
                if (file_exists($absolute_file_path) && is_file($absolute_file_path)) {
                    unlink($absolute_file_path);
                }
            }
        }
        $sql_delete = "DELETE FROM materials WHERE material_id = ?";
    } elseif ($delete_type === 'tugas') {
        $stmt_path = $conn->prepare("SELECT file_path FROM assignments WHERE assignment_id = ?");
        if ($stmt_path) {
            $stmt_path->execute([$delete_id]);
            if ($row_path = $stmt_path->fetch()) {
                $file_to_delete = $row_path['file_path'];
                // Pastikan path absolut untuk unlink
                $absolute_file_path = __DIR__ . '/' . $file_to_delete;
                if (!empty($file_to_delete) && file_exists($absolute_file_path) && is_file($absolute_file_path)) {
                    unlink($absolute_file_path);
                }
            }
        }
        $sql_delete = "DELETE FROM assignments WHERE assignment_id = ?";
    } else {
        echo "<script>alert('Tipe delete tidak valid!');</script>";
        header("Location: materitugas-dosen.php?course_id={$redirect_course_id}&pertemuan_id={$redirect_pertemuan_id}");
        exit;
    }

    $stmt_delete = $conn->prepare($sql_delete);
    if ($stmt_delete) {
        if ($stmt_delete->execute([$delete_id])) {
            if ($stmt_delete->rowCount() > 0) {
                echo "<script>alert('Berhasil menghapus " . htmlspecialchars($delete_type) . "!');</script>";
            } else {
                echo "<script>alert('" . htmlspecialchars($delete_type) . " tidak ditemukan atau sudah dihapus.');</script>";
            }
        } else {
            echo "<script>alert('Gagal menghapus " . htmlspecialchars($delete_type) . "');</script>";
        }
    }
    header("Location: materitugas-dosen.php?course_id={$redirect_course_id}&pertemuan_id={$redirect_pertemuan_id}");
    exit;
}

// --- Fetch data for sidebar (all courses assigned to the lecturer) ---
$courses_data_for_sidebar = [];
$sql_all_courses = "SELECT course_id, course_name, course_code FROM courses WHERE lecturer_id = ? ORDER BY course_id ASC";
$stmt_all_courses = $conn->prepare($sql_all_courses);
if ($stmt_all_courses) {
    $stmt_all_courses->execute([$current_lecturer_id]);
    while ($row = $stmt_all_courses->fetch()) {
        $courses_data_for_sidebar[] = $row;
    }
} else {
    error_log("Error preparing all courses query.");
}

// Set current_course_id dan course_name_for_header jika ada perubahan di sidebar
if (!empty($courses_data_for_sidebar)) {
    $course_id_from_get = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;
    $course_found_and_valid = false;
    foreach ($courses_data_for_sidebar as $course) {
        if ($course['course_id'] === $course_id_from_get) {
            $current_course_id = $course_id_from_get;
            // Perbaikan: Judul tidak perlu di-escape lagi di sini, karena sudah di-output
            $course_name_for_header = $course['course_name'];
            $current_pertemuan_id = $current_course_id;
            $course_found_and_valid = true;
            break;
        }
    }
    if (!$course_found_and_valid) {
        $current_course_id = $courses_data_for_sidebar[0]['course_id'];
        $current_pertemuan_id = $current_course_id;
        // Perbaikan: Judul tidak perlu di-escape lagi di sini
        $course_name_for_header = $courses_data_for_sidebar[0]['course_name'];
    }
} else {
    $current_course_id = null;
    $current_pertemuan_id = null;
    $course_name_for_header = "Tidak Ada Mata Kuliah Diampu";
}


// --- Fetch data for current course materials and assignments ---
$materials_data_for_current_course = [];
$assignments_data_for_current_course = [];
if ($current_course_id) {
    $sql_materials = "SELECT material_id, title, description, file_path, file_type, uploaded_at FROM materials WHERE course_id = ? ORDER BY uploaded_at DESC";
    $stmt_materials = $conn->prepare($sql_materials);
    if ($stmt_materials) {
        $stmt_materials->execute([$current_course_id]);
        while ($row = $stmt_materials->fetch()) {
            $materials_data_for_current_course[] = $row;
        }
    } else {
        error_log("Error preparing materials query.");
    }

    $sql_assignments = "SELECT assignment_id, title, description, due_date, created_at, file_path, file_type FROM assignments WHERE course_id = ? ORDER BY due_date ASC";
    $stmt_assignments = $conn->prepare($sql_assignments);
    if ($stmt_assignments) {
        $stmt_assignments->execute([$current_course_id]);
        while ($row = $stmt_assignments->fetch()) {
            $assignments_data_for_current_course[] = $row;
        }
    } else {
        error_log("Error preparing assignments query.");
    }
}


// --- Fetch dosen name for header ---
$sql_lecturer_header = "SELECT full_name, gelar FROM users WHERE user_id = ? AND role = 'lecturer'";
$stmt_lecturer_header = $conn->prepare($sql_lecturer_header);
if ($stmt_lecturer_header) {
    $stmt_lecturer_header->execute([$current_lecturer_id]);
    if ($row_lecturer = $stmt_lecturer_header->fetch()) {
        $lecturer_full_name_header = htmlspecialchars($row_lecturer['full_name']);
        if (!empty($row_lecturer['gelar'])) {
             $lecturer_full_name_header .= ", " . htmlspecialchars($row_lecturer['gelar']);
        }
    }
}


$conn = null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materi & Tugas - <?php echo htmlspecialchars($course_name_for_header); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS Anda yang sudah ada */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            min-height: calc(100vh - 40px);
        }

        .header {
            background: linear-gradient(135deg, #89CFF0, #6FA8DC);
            padding: 30px;
            text-align: center;
            color: white;
            font-size: 2.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            position: relative; /* Tambahkan ini agar tombol kembali bisa diposisikan absolut */
        }
        
        .header-back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 10px;
            padding: 8px 15px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .header-back-btn:hover {
            background: rgba(255, 255, 255, 0.4);
            transform: translateX(-3px);
        }

        .content-wrapper {
            display: flex;
            min-height: calc(100vh - 140px);
        }

        .sidebar {
            background: linear-gradient(180deg, #B8D4F0, #A1C7E8);
            padding: 30px 20px;
            width: 300px;
            min-width: 300px;
        }

        .sidebar h3 {
            color: white;
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .pertemuan-btn {
            display: block;
            width: 100%;
            background: #FFF8DC;
            border: none;
            border-radius: 25px;
            padding: 15px 20px;
            margin: 10px 0;
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .pertemuan-btn:hover {
            background: #F0E68C;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .pertemuan-btn.active {
            background: #FFD700;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .main-content {
            flex: 1;
            padding: 30px;
            background: #f8f9fa;
        }

        .section-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .section-header {
            background: linear-gradient(135deg, #4A90E2, #357ABD);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            margin: -25px -25px 20px -25px;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .content-box {
            background: #f8f9ff;
            border: 2px solid #e0e7ff;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
            min-height: 80px;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .action-btn {
            background: linear-gradient(135deg, #89CFF0, #6FA8DC);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            min-width: 150px;
        }

        .action-btn:hover {
            background: linear-gradient(135deg, #6FA8DC, #5A8AC7);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .file-input {
            display: none;
        }

        .upload-area {
            border: 2px dashed #4A90E2;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            background: #f8f9ff;
            margin: 15px 0;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-area:hover {
            border-color: #357ABD;
            background: #f0f4ff;
        }

        .upload-area.dragover {
            border-color: #FFD700;
            background: #fffdf0;
        }

        .file-list {
            margin-top: 15px;
            max-height: 200px;
            overflow-y: auto;
        }

        .file-item {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px 15px;
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .file-item .file-name {
            font-weight: 500;
            color: #333;
        }

        /* Make file-name clickable look */
        .file-item .file-name a,
        .file-item .file-name span {
            cursor: pointer; /* Change cursor for clickable items */
            transition: color 0.2s ease;
        }

        .file-item .file-name a:hover,
        .file-item .file-name span:hover {
            color: #007bff; /* Highlight on hover */
        }

        .file-item .file-size {
            color: #666;
            font-size: 0.9rem;
        }

        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        .remove-btn:hover {
            background: #c82333;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background: linear-gradient(135deg, #4A90E2, #357ABD);
            color: white;
            border-radius: 15px 15px 0 0;
            border-bottom: none;
        }

        .btn-close {
            filter: brightness(0) invert(1);
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            display: none; /* Akan dikontrol oleh JS untuk notifikasi */
        }

        @media (max-width: 768px) {
            .content-wrapper {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                min-width: unset;
            }

            .action-buttons {
                justify-content: center;
            }

            .action-btn {
                min-width: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header">
            <a href="dash-dosen.php" class="header-back-btn">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <i class="fas fa-graduation-cap me-3"></i>
            <?php echo $course_name_for_header; ?>
        </div>

        <div class="content-wrapper">
            <div class="sidebar">
                <h3><i class="fas fa-calendar-alt me-2"></i>Jadwal</h3>
                <?php if (empty($courses_data_for_sidebar)): ?>
                    <p class="text-center text-muted">Tidak ada mata kuliah yang diampu.</p>
                <?php else: ?>
                    <?php foreach ($courses_data_for_sidebar as $course): ?>
                        <button class="pertemuan-btn <?php echo ($current_course_id == $course['course_id']) ? 'active' : ''; ?>" onclick="window.location.href='materitugas-dosen.php?course_id=<?php echo htmlspecialchars($course['course_id']); ?>';">
                            <i class="fas fa-book me-2"></i><?php echo htmlspecialchars($course['course_name']); ?>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="main-content">
                <?php if ($current_course_id === null): ?>
                    <div class="section-card">
                        <div class="section-header">
                            <i class="fas fa-info-circle me-2"></i>Informasi
                        </div>
                        <div class="content-box">
                            <p class="text-muted">Pilih mata kuliah dari sidebar untuk mengelola materi dan tugas.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="section-card">
                        <div class="section-header">
                            <i class="fas fa-file-alt me-2"></i>Materi
                        </div>
                        <div class="content-box" id="materiContent">
                            <?php if (empty($materials_data_for_current_course)): ?>
                                <p class="text-muted">Tidak ada materi untuk mata kuliah ini.</p>
                            <?php else: ?>
                                <p>Materi-materi yang tersedia untuk mata kuliah ini.</p>
                            <?php endif; ?>
                        </div>
                        <div class="action-buttons">
                            <button class="action-btn" onclick="pilihMateri()">
                                <i class="fas fa-folder-open me-2"></i>Pilih Materi
                            </button>
                            <button class="action-btn" onclick="showUploadModal('materi')">
                                <i class="fas fa-upload me-2"></i>Upload Materi
                            </button>
                        </div>
                        <div class="file-list" id="materiFileList">
                            <?php if (!empty($materials_data_for_current_course)): ?>
                                <?php foreach ($materials_data_for_current_course as $material): ?>
                                    <div class="file-item">
                                        <div>
                                            <div class="file-name">
                                                <i class="<?php echo getFileIcon($material['file_type']); ?> me-2"></i>
                                                <a href="<?php echo htmlspecialchars($material['file_path']); ?>" target="_blank" style="text-decoration: none; color: inherit;"><?php echo htmlspecialchars($material['title']); ?></a>
                                            </div>
                                            <div class="file-size">Diunggah: <?php echo formatDateForDisplay($material['uploaded_at']); ?></div>
                                        </div>
                                        <button class="remove-btn" onclick="confirmDeleteFile('materi', <?php echo $material['material_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        </div>

                    <div class="section-card">
                        <div class="section-header">
                            <i class="fas fa-tasks me-2"></i>Tugas
                        </div>
                        <div class="content-box" id="tugasContent">
                            <?php if (empty($assignments_data_for_current_course)): ?>
                                <p class="text-muted">Tidak ada tugas untuk mata kuliah ini.</p>
                            <?php else: ?>
                                <p>Tugas-tugas yang tersedia untuk mata kuliah ini.</p>
                            <?php endif; ?>
                        </div>
                        <div class="action-buttons">
                            <button class="action-btn" onclick="pilihTugas()">
                                <i class="fas fa-folder-open me-2"></i>Pilih Tugas
                            </button>
                            <button class="action-btn" onclick="showUploadModal('tugas')">
                                <i class="fas fa-upload me-2"></i>Upload Tugas
                            </button>
                        </div>
                        <div class="file-list" id="tugasFileList">
                            <?php if (!empty($assignments_data_for_current_course)): ?>
                                <?php foreach ($assignments_data_for_current_course as $assignment): ?>
                                    <div class="file-item">
                                        <div>
                                            <div class="file-name">
                                                <i class="<?php echo getFileIcon($assignment['file_type'] ?? null); ?> me-2"></i>
                                                <?php if (!empty($assignment['file_path'])): ?>
                                                    <a href="<?php echo htmlspecialchars($assignment['file_path']); ?>" target="_blank" style="text-decoration: none; color: inherit;"><?php echo htmlspecialchars($assignment['title']); ?></a>
                                                <?php else: ?>
                                                    <span onclick="viewAssignmentDetails('<?php echo htmlspecialchars(addslashes($assignment['title'])); ?>', '<?php echo htmlspecialchars(addslashes($assignment['description'])); ?>')"><?php echo htmlspecialchars($assignment['title']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="file-size">Deadline: <?php echo formatDateForDisplay($assignment['due_date']); ?></div>
                                        </div>
                                        <button class="remove-btn" onclick="confirmDeleteFile('tugas', <?php echo $assignment['assignment_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalTitle">
                        <i class="fas fa-upload me-2"></i>Upload File
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" method="POST" action="materitugas-dosen.php" enctype="multipart/form-data">
                        <input type="hidden" name="upload_type" id="modalUploadType" value="">
                        <input type="hidden" name="course_id_upload" id="modalCourseIdUpload" value="<?php echo htmlspecialchars($current_course_id ?? ''); ?>">
                        <input type="hidden" name="pertemuan_id_upload" id="modalPertemuanIdUpload" value="<?php echo htmlspecialchars($current_pertemuan_id ?? ''); ?>">
                        
                        <div class="upload-area" id="uploadArea">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-primary"></i>
                            <h5>Drag & Drop file di sini</h5>
                            <p class="text-muted">atau klik untuk memilih file</p>
                            <small class="text-muted">Format yang didukung: PDF, DOC, DOCX, PPT, PPTX, TXT, JPG, PNG, GIF</small>
                        </div>
                        <input type="file" id="modalFileInput" name="uploaded_file[]" multiple required accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.jpg,.png,.gif">
                        <div class="file-list" id="modalFileList"></div>

                        <div id="dueDateContainer" class="mt-3" style="display: none;">
                            <label for="dueDateInput" class="form-label">Tentukan Deadline:</label>
                            <input type="datetime-local" class="form-control" id="dueDateInput" name="due_date_upload">
                        </div>

                        <div class="modal-footer d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <div id="additionalButtons" style="display: flex; align-items: center; gap: 10px;">
                                <button type="button" class="btn btn-info" id="setDeadlineBtn" style="display: none;">
                                    <i class="fas fa-calendar-alt me-2"></i>Atur Deadline
                                </button>
                                <button type="submit" class="btn btn-primary" id="uploadBtn">
                                    <i class="fas fa-upload me-2"></i>Upload
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pilihMateriModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-folder-open me-2"></i>Daftar Materi Tersedia
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="materiListContainer">
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pilihTugasModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-folder-open me-2"></i>Daftar Tugas Tersedia
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="tugasListContainer">
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // currentCourseId dan currentPertemuan akan diset dari PHP di bagian atas file
        // Digunakan untuk mengirimkan ID course saat upload
        const currentCourseId_js = <?php echo json_encode($current_course_id); ?>;
        const currentPertemuan_js = <?php echo json_encode($current_pertemuan_id); ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Update hidden input fields in the modal form with current course_id
            document.getElementById('modalCourseIdUpload').value = currentCourseId_js;
            document.getElementById('modalPertemuanIdUpload').value = currentPertemuan_js;

            // Inisialisasi drag and drop untuk area upload modal
            const uploadArea = document.getElementById('uploadArea');
            const modalFileInput = document.getElementById('modalFileInput');
            const setDeadlineBtn = document.getElementById('setDeadlineBtn');
            const dueDateContainer = document.getElementById('dueDateContainer');

            uploadArea.addEventListener('click', () => modalFileInput.click());

            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
            });

            modalFileInput.addEventListener('change', (e) => {
                displayModalFiles(e.target.files);
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                modalFileInput.files = e.dataTransfer.files; 
                displayModalFiles(e.dataTransfer.files);
            });
            
            // Fungsionalitas untuk tombol "Upload Tugas" dan "Upload Materi"
            document.querySelectorAll('.action-btn').forEach(btn => {
                if (btn.textContent.includes('Upload Tugas')) {
                    btn.addEventListener('click', function() {
                        setDeadlineBtn.style.display = 'inline-block';
                        dueDateContainer.style.display = 'none'; // Sembunyikan saat membuka modal
                    });
                }
                if (btn.textContent.includes('Upload Materi')) {
                    btn.addEventListener('click', function() {
                        setDeadlineBtn.style.display = 'none';
                        dueDateContainer.style.display = 'none';
                    });
                }
            });

            // PERBAIKAN DI SINI: Event listener untuk tombol "Atur Deadline"
            setDeadlineBtn.addEventListener('click', function() {
                dueDateContainer.style.display = 'block';
            });
        });
        
        // Fungsi untuk menampilkan file yang dipilih di daftar file modal
        function displayModalFiles(files) {
            const modalFileList = document.getElementById('modalFileList');
            modalFileList.innerHTML = ''; // Kosongkan daftar file sebelumnya

            if (files.length === 0) return;

            Array.from(files).forEach(file => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <div>
                        <div class="file-name">
                            <i class="fas fa-file me-2"></i>${file.name}
                        </div>
                        <div class="file-size">${formatFileSize(file.size)}</div>
                    </div>
                `;
                modalFileList.appendChild(fileItem);
            });
        }

        // Helper untuk memformat ukuran file
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // --- Fungsi untuk "Pilih Materi" yang sudah diaktifkan ---
        function pilihMateri() {
            const materiListContainer = document.getElementById('materiListContainer');
            materiListContainer.innerHTML = ''; // Kosongkan konten sebelumnya

            const currentMaterials = <?php echo json_encode($materials_data_for_current_course); ?>;

            if (currentMaterials.length === 0) {
                materiListContainer.innerHTML = '<p class="text-muted text-center">Tidak ada materi yang tersedia untuk mata kuliah ini.</p>';
            } else {
                currentMaterials.forEach(material => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'file-item';
                    fileItem.innerHTML = `
                        <div>
                            <div class="file-name">
                                <i class="${getFileIcon(material.file_type)} me-2"></i>
                                <a href="${material.file_path}" target="_blank" style="text-decoration: none; color: inherit;"><?php echo htmlspecialchars($material['title']); ?></a>
                            </div>
                            <div class="file-size">Diunggah: ${formatDateForDisplay(material.uploaded_at)}</div>
                        </div>
                    `;
                    materiListContainer.appendChild(fileItem);
                });
            }

            const modal = new bootstrap.Modal(document.getElementById('pilihMateriModal'));
            modal.show();
        }

        // --- Fungsi untuk "Pilih Tugas" yang sudah diaktifkan ---
        function pilihTugas() {
            const tugasListContainer = document.getElementById('tugasListContainer');
            tugasListContainer.innerHTML = ''; // Kosongkan konten sebelumnya

            const currentAssignments = <?php echo json_encode($assignments_data_for_current_course); ?>;

            if (currentAssignments.length === 0) {
                tugasListContainer.innerHTML = '<p class="text-muted text-center">Tidak ada tugas yang tersedia untuk mata kuliah ini.</p>';
            } else {
                currentAssignments.forEach(assignment => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'file-item';
                    let fileLinkHtml = '';
                    // Periksa apakah file_path ada dan tidak kosong
                    if (assignment.file_path && assignment.file_path !== '') {
                        fileLinkHtml = `<a href="${assignment.file_path}" target="_blank" style="text-decoration: none; color: inherit;"><?php echo htmlspecialchars($assignment['title']); ?></a>`;
                    } else {
                        // Jika tidak ada file_path, gunakan span dan onclick untuk deskripsi
                        fileLinkHtml = `<span onclick="viewAssignmentDetails('${escapeHtml(assignment.title)}', '${escapeHtml(assignment.description)}')"><?php echo htmlspecialchars($assignment['title']); ?></span>`;
                    }

                    fileItem.innerHTML = `
                        <div>
                            <div class="file-name">
                                <i class="${getFileIcon(assignment.file_type)} me-2"></i> ${fileLinkHtml}
                            </div>
                            <div class="file-size">Deadline: ${formatDateForDisplay(assignment.due_date)}</div>
                        </div>
                    `;
                    tugasListContainer.appendChild(fileItem);
                });
            }

            const modal = new bootstrap.Modal(document.getElementById('pilihTugasModal'));
            modal.show();
        }

        // Fungsi untuk menampilkan detail tugas saat diklik di modal "Pilih Tugas" (jika tidak ada file_path)
        function viewAssignmentDetails(title, description) {
            alert(`Judul Tugas: ${title}\nDeskripsi: ${description}`);
        }

        // Fungsi bantu untuk mendapatkan ikon (duplikat dari PHP function getFileIcon)
        function getFileIcon(fileType) {
            if (!fileType) return 'fas fa-file';
            fileType = fileType.toLowerCase();
            if (fileType.includes('pdf')) return 'fas fa-file-pdf';
            if (fileType.includes('doc') || fileType.includes('docx')) return 'fas fa-file-word';
            if (fileType.includes('ppt') || fileType.includes('pptx')) return 'fas fa-file-powerpoint';
            if (fileType.includes('xls') || fileType.includes('xlsx')) return 'fas fa-file-excel';
            if (fileType.includes('zip') || fileType.includes('rar')) return 'fas fa-file-archive';
            if (fileType.includes('jpg') || fileType.includes('jpeg') || fileType.includes('png') || fileType.includes('gif')) return 'fas fa-file-image';
            if (fileType.includes('txt')) return 'fas fa-file-alt';
            return 'fas fa-file';
        }

        // Fungsi bantu untuk format tanggal (duplikat dari PHP function formatDateForDisplay)
        function formatDateForDisplay(dateString) {
            const options = { day: 'numeric', month: 'long', year: 'numeric', hour: 'numeric', minute: 'numeric' };
            try {
                return new Date(dateString).toLocaleDateString('id-ID', options);
            } catch (e) {
                console.error("Error formatting date:", e);
                return dateString;
            }
        }

        // Fungsi bantu untuk menghindari XSS saat passing string ke onclick
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }


        // Function to open upload modal
        function showUploadModal(type) {
            const title = type === 'materi' ? 'Upload Materi' : 'Upload Tugas';
            document.getElementById('uploadModalTitle').innerHTML = `<i class="fas fa-upload me-2"></i>${title}`;
            document.getElementById('modalUploadType').value = type;
            document.getElementById('modalFileInput').value = ''; 
            document.getElementById('modalFileList').innerHTML = ''; 
            document.getElementById('uploadArea').classList.remove('dragover');

            // Logika untuk menampilkan/menyembunyikan tombol "Atur Deadline"
            const setDeadlineBtn = document.getElementById('setDeadlineBtn');
            const dueDateContainer = document.getElementById('dueDateContainer');
            if (type === 'tugas') {
                if (setDeadlineBtn) {
                    setDeadlineBtn.style.display = 'inline-block';
                }
                dueDateContainer.style.display = 'none'; // Sembunyikan saat membuka modal
            } else {
                if (setDeadlineBtn) {
                    setDeadlineBtn.style.display = 'none';
                }
                dueDateContainer.style.display = 'none';
            }
            
            const modal = new bootstrap.Modal(document.getElementById('uploadModal'));
            modal.show();
        }

        // Fungsi untuk konfirmasi delete file (akan redirect ke halaman PHP dengan parameter GET)
        function confirmDeleteFile(type, id) {
            if (confirm(`Apakah Anda yakin ingin menghapus ${type} ini?`)) {
                window.location.href = `materitugas-dosen.php?action=delete&type=${type}&id=${id}&course_id=${currentCourseId_js}&pertemuan_id=${currentPertemuan_js}`;
            }
        }
    </script>
</body>
</html>