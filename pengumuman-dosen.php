<?php
include 'middleware.php';
include 'db_connection.php';
require_role('lecturer');

// Inisialisasi variabel untuk form (edit/create)
$announcement_id_edit = null;
$edit_title = '';
$edit_content = '';
$edit_course_id = '';
$lecturer_name_for_header = "Dosen";

$current_lecturer_id = $_SESSION['user_id'];

// --- Fungsi Helper ---
function formatDate($dateString) {
    return date('d F Y H:i', strtotime($dateString));
}

// --- Handle Form Submission (Create/Update) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $course_id = $_POST['subject'] ?? null;
    $announcement_id = $_POST['announcementId'] ?? null;

    if (empty($title) || empty($content)) {
        header("Location: pengumuman-dosen.php?message=error&alert=" . urlencode('Judul dan isi pengumuman harus diisi!'));
        exit;
    }

    // Jika course_id kosong, set menjadi NULL
    $course_id = !empty($course_id) ? (int)$course_id : null;

    $conn->beginTransaction();
    try {
        if (!empty($announcement_id)) {
            // Update an existing announcement
            $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ?, course_id = ? WHERE announcement_id = ? AND lecturer_id = ?");
            if (!$stmt) {
                throw new Exception("Prepare statement failed.");
            }
            $stmt->execute([$title, $content, $course_id, $announcement_id, $current_lecturer_id]);
        } else {
            // Create a new announcement
            $stmt = $conn->prepare("INSERT INTO announcements (title, content, lecturer_id, course_id) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare statement failed.");
            }
            $stmt->execute([$title, $content, $current_lecturer_id, $course_id]);
        }

        $conn->commit();
        header("Location: pengumuman-dosen.php?message=success&alert=" . urlencode('Pengumuman berhasil disimpan!'));

    } catch (Exception $e) {
        $conn->rollBack();
        header("Location: pengumuman-dosen.php?message=error&alert=" . urlencode("Terjadi kesalahan: " . $e->getMessage()));
    }
    exit;
}

// --- Handle Delete Action ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM announcements WHERE announcement_id = ? AND lecturer_id = ?");
    if ($stmt) {
        if ($stmt->execute([$delete_id, $current_lecturer_id])) {
            if ($stmt->rowCount() > 0) {
                header("Location: pengumuman-dosen.php?message=success&alert=" . urlencode('Pengumuman berhasil dihapus!'));
            } else {
                header("Location: pengumuman-dosen.php?message=error&alert=" . urlencode('Pengumuman tidak ditemukan atau Anda tidak memiliki izin untuk menghapusnya.'));
            }
        } else {
            header("Location: pengumuman-dosen.php?message=error&alert=" . urlencode('Gagal menghapus pengumuman.'));
        }
    }
    exit;
}

// --- Handle Edit Data Population ---
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id_param = (int)$_GET['id'];
    $stmt_edit = $conn->prepare("SELECT title, content, course_id FROM announcements WHERE announcement_id = ? AND lecturer_id = ?");
    if ($stmt_edit) {
        $stmt_edit->execute([$edit_id_param, $current_lecturer_id]);
        if ($row_edit = $stmt_edit->fetch()) {
            $announcement_id_edit = $edit_id_param;
            $edit_title = $row_edit['title'];
            $edit_content = $row_edit['content'];
            $edit_course_id = $row_edit['course_id'];
        } else {
            header("Location: pengumuman-dosen.php?message=error&alert=" . urlencode('Pengumuman tidak ditemukan atau Anda tidak memiliki izin untuk mengeditnya.'));
            exit;
        }
    }
}

// --- Fetch data for display ---
$announcements = [];
$sql_select_announcements = "SELECT
                                a.announcement_id,
                                a.title,
                                a.content,
                                a.published_at,
                                u.full_name AS lecturer_name,
                                c.course_name
                            FROM
                                announcements a
                            LEFT JOIN
                                users u ON a.lecturer_id = u.user_id
                            LEFT JOIN
                                courses c ON a.course_id = c.course_id
                            WHERE
                                a.lecturer_id = ?
                            ORDER BY
                                a.published_at DESC";

$stmt_announcements = $conn->prepare($sql_select_announcements);
if ($stmt_announcements) {
    $stmt_announcements->execute([$current_lecturer_id]);
    while ($row = $stmt_announcements->fetch()) {
        $announcements[] = $row;
    }
}


// --- Fetch data for dropdowns (Mata Kuliah) ---
$courses_data = [];
$sql_courses = "SELECT course_id, course_name FROM courses WHERE lecturer_id = ? ORDER BY course_name ASC";
$stmt_courses = $conn->prepare($sql_courses);
if ($stmt_courses) {
    $stmt_courses->execute([$current_lecturer_id]);
    while ($row = $stmt_courses->fetch()) {
        $courses_data[] = $row;
    }
}


// --- Calculate Stats ---
$total_announcements = count($announcements);
$today_announcements = 0;
$today_date_str = date('Y-m-d');
foreach ($announcements as $announcement) {
    if (date('Y-m-d', strtotime($announcement['published_at'])) === $today_date_str) {
        $today_announcements++;
    }
}

// Ambil nama dosen untuk header
$sql_lecturer_name = "SELECT full_name, gelar FROM users WHERE user_id = ? AND role = 'lecturer'";
$stmt_lecturer_name = $conn->prepare($sql_lecturer_name);
if ($stmt_lecturer_name) {
    $stmt_lecturer_name->execute([$current_lecturer_id]);
    if ($row_lecturer = $stmt_lecturer_name->fetch()) {
        $lecturer_name_for_header = htmlspecialchars($row_lecturer['full_name']);
        if (!empty($row_lecturer['gelar'])) {
             $lecturer_name_for_header .= ", " . htmlspecialchars($row_lecturer['gelar']);
        }
    }
}

$conn = null;

// Check for and display session alerts, then clear them
$alert_message = isset($_GET['alert']) ? htmlspecialchars($_GET['alert']) : '';
$alert_type = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengumuman Dosen</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS Anda yang sudah ada */
        :root {
            --color-primary: #B6D0EF;
            --color-secondary: #63A3F1;
            --color-light: #FAFFEE;
            --color-dark: #4F8A9E;
            --color-white: #FFFFFF;
        }

        body {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-light) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(90deg, var(--color-dark) 0%, var(--color-secondary) 100%);
            box-shadow: 0 2px 15px rgba(79, 138, 158, 0.3);
        }

        .navbar-brand {
            color: var(--color-white) !important;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .container-fluid {
            padding: 2rem 1rem;
        }

        .card {
            background: var(--color-white);
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(79, 138, 158, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(79, 138, 158, 0.2);
        }

        .card-header {
            background: linear-gradient(90deg, var(--color-secondary) 0%, var(--color-primary) 100%);
            color: var(--color-white);
            border-radius: 15px 15px 0 0 !important;
            border: none;
            padding: 1.25rem;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--color-secondary) 0%, var(--color-dark) 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, var(--color-dark) 0%, var(--color-secondary) 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 163, 241, 0.4);
        }

        .btn-warning {
            background: linear-gradient(45deg, #ffc107 0%, #ff8c00 100%);
            border: none;
            color: white;
        }

        .btn-danger {
            background: linear-gradient(45deg, #dc3545 0%, #b02a37 100%);
            border: none;
        }

        .form-control, .form-select {
            border: 2px solid var(--color-primary);
            border-radius: 8px;
            padding: 0.75rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--color-secondary);
            box-shadow: 0 0 0 0.2rem rgba(99, 163, 241, 0.25);
        }

        .announcement-item {
            background: var(--color-white);
            border: 2px solid var(--color-primary);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .announcement-item:hover {
            border-color: var(--color-secondary);
            box-shadow: 0 5px 20px rgba(99, 163, 241, 0.2);
        }
        
        .announcement-item {
            border-left: 5px solid var(--color-secondary);
        }

        .announcement-meta {
            color: var(--color-dark);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .announcement-content {
            color: #333;
            line-height: 1.6;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(90deg, var(--color-secondary) 0%, var(--color-primary) 100%);
            color: var(--color-white);
            border-radius: 15px 15px 0 0;
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stats-card {
            background: linear-gradient(135deg, var(--color-secondary) 0%, var(--color-dark) 100%);
            color: var(--color-white);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        /* CSS untuk tombol kembali */
        .back-to-dash-btn {
            background: transparent;
            border: none;
            color: white;
            padding: 8px 15px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .back-to-dash-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a href="dash-dosen.php" class="back-to-dash-btn d-flex align-items-center">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            
            <div class="d-flex align-items-center">
                <a class="navbar-brand me-0" href="#">
                    <i class="fas fa-graduation-cap me-2"></i>
                    Sistem Pengumuman Dosen
                </a>
            </div>
            
            <div class="text-white d-flex align-items-center">
                <i class="fas fa-user-circle me-2"></i>
                Selamat datang, <?php echo $lecturer_name_for_header; ?>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <?php if (!empty($alert_message)): ?>
            <div class="alert alert-<?php echo ($alert_type === 'success' ? 'success' : 'danger'); ?> alert-dismissible fade show" role="alert">
                <?php echo $alert_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-number" id="totalAnnouncements"><?php echo $total_announcements; ?></div>
                    <div>Total Pengumuman</div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-number" id="todayAnnouncements"><?php echo $today_announcements; ?></div>
                    <div>Pengumuman Hari Ini</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-plus-circle me-2"></i>
                            <span id="formTitle"><?php echo $announcement_id_edit ? 'Edit Pengumuman' : 'Buat Pengumuman Baru'; ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="announcementForm" method="POST" action="pengumuman-dosen.php">
                            <input type="hidden" id="announcementId" name="announcementId" value="<?php echo htmlspecialchars($announcement_id_edit ?? ''); ?>">
                            <div class="mb-3">
                                <label for="title" class="form-label">Judul Pengumuman</label>
                                <input type="text" class="form-control" id="title" name="title" placeholder="Contoh: Kelas Hari Ini Kosong" value="<?php echo htmlspecialchars($edit_title); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="content" class="form-label">Isi Pengumuman</label>
                                <textarea class="form-control" id="content" name="content" rows="4" placeholder="Tulis detail pengumuman..." required><?php echo htmlspecialchars($edit_content); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Mata Kuliah</label>
                                <select class="form-select" id="subject" name="subject">
                                    <option value="">Pilih Mata Kuliah (Opsional)</option>
                                    <?php foreach ($courses_data as $course): ?>
                                        <option value="<?php echo htmlspecialchars($course['course_id']); ?>"
                                            <?php echo ($edit_course_id == $course['course_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($course['course_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    <span id="submitButtonText"><?php echo $announcement_id_edit ? 'Perbarui' : 'Publikasikan'; ?></span>
                                </button>
                                <?php if ($announcement_id_edit): ?>
                                    <button type="button" class="btn btn-secondary" onclick="window.location.href='pengumuman-dosen.php';">
                                        <i class="fas fa-times me-2"></i>
                                        Batal Edit
                                    </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Daftar Pengumuman
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="announcementsList">
                            <?php if (empty($announcements)): ?>
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-bullhorn fa-3x mb-3"></i>
                                    <p>Belum ada pengumuman. Buat pengumuman pertama Anda!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($announcements as $announcement): ?>
                                    <div class="announcement-item fade-in">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($announcement['title']); ?></h6>
                                        </div>
                                        <div class="announcement-meta">
                                            <i class="fas fa-book me-1"></i> <?php echo htmlspecialchars($announcement['course_name'] ?? 'Umum'); ?>
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-clock me-1"></i> <?php echo formatDate($announcement['published_at']); ?>
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-user-tie me-1"></i> <?php echo htmlspecialchars($announcement['lecturer_name'] ?? 'Dosen Tidak Dikenal'); ?>
                                        </div>
                                        <div class="announcement-content mb-3">
                                            <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="pengumuman-dosen.php?action=edit&id=<?php echo $announcement['announcement_id']; ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteModal(<?php echo $announcement['announcement_id']; ?>)">
                                                <i class="fas fa-trash me-1"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus pengumuman ini?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        Tindakan ini tidak dapat dibatalkan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a id="confirmDeleteLink" href="#" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>
                        Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk mengisi modal delete
        function confirmDeleteModal(id) {
            const deleteLink = document.getElementById('confirmDeleteLink');
            deleteLink.href = 'pengumuman-dosen.php?action=delete&id=' + id;
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>