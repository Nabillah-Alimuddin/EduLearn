<?php
include 'middleware.php';
include 'db_connection.php';
require_role('lecturer');

$current_lecturer_id = $_SESSION['user_id'];

// Inisialisasi variabel
$lecturer_full_name = "Dosen";
$lecturer_gelar = "";
$courses_data = []; // Untuk dropdown mata kuliah di sidebar
$schedules_data = []; // Untuk menampilkan jadwal

// --- Fetch Nama Dosen untuk Header ---
$sql_lecturer = "SELECT full_name, gelar FROM users WHERE user_id = ? AND role = 'lecturer'";
$stmt_lecturer = $conn->prepare($sql_lecturer);
if ($stmt_lecturer) {
    $stmt_lecturer->execute([$current_lecturer_id]);
    if ($row_lecturer = $stmt_lecturer->fetch()) {
        $lecturer_full_name = htmlspecialchars($row_lecturer['full_name']);
        if (!empty($row_lecturer['gelar'])) {
             $lecturer_full_name .= ", " . htmlspecialchars($row_lecturer['gelar']);
        }
    }
} else {
    error_log("Error preparing lecturer name query");
}

// --- Fetch Mata Kuliah yang Diampu Dosen Ini ---
$sql_courses = "SELECT course_id, course_name, course_code FROM courses WHERE lecturer_id = ? ORDER BY course_name ASC";
$stmt_courses = $conn->prepare($sql_courses);
if ($stmt_courses) {
    $stmt_courses->execute([$current_lecturer_id]);
    while ($row = $stmt_courses->fetch()) {
        $courses_data[] = $row;
    }
} else {
    error_log("Error preparing courses query");
}

// Tentukan course_id yang sedang dipilih (dari GET parameter atau yang pertama jika belum ada)
$selected_course_id = null;
if (isset($_GET['course_id']) && !empty($courses_data)) {
    // Validasi apakah course_id dari GET memang diampu oleh dosen ini
    $found_valid_course = false;
    foreach ($courses_data as $course) {
        if ($course['course_id'] == (int)$_GET['course_id']) {
            $selected_course_id = (int)$_GET['course_id'];
            $found_valid_course = true;
            break;
        }
    }
    // Jika course_id di GET tidak valid untuk dosen ini, fallback ke yang pertama
    if (!$found_valid_course) {
        $selected_course_id = $courses_data[0]['course_id'];
    }
} elseif (!empty($courses_data)) {
    // Jika tidak ada GET parameter, pilih mata kuliah pertama sebagai default
    $selected_course_id = $courses_data[0]['course_id'];
}

// --- Fetch Jadwal untuk Mata Kuliah yang Dipilih ---
if ($selected_course_id) {
    $sql_schedules = "
        SELECT 
            s.day_of_week, 
            s.start_time, 
            s.end_time, 
            s.room, 
            s.class_type,
            c.course_name,
            c.course_code
        FROM schedules s
        JOIN courses c ON s.course_id = c.course_id
        WHERE s.course_id = ? AND c.lecturer_id = ?
        ORDER BY CASE s.day_of_week 
            WHEN 'Senin' THEN 1 
            WHEN 'Selasa' THEN 2 
            WHEN 'Rabu' THEN 3 
            WHEN 'Kamis' THEN 4 
            WHEN 'Jumat' THEN 5 
            WHEN 'Sabtu' THEN 6 
            WHEN 'Minggu' THEN 7 
            ELSE 8 
        END, s.start_time ASC;
    ";
    $stmt_schedules = $conn->prepare($sql_schedules);
    if ($stmt_schedules) {
        $stmt_schedules->execute([$selected_course_id, $current_lecturer_id]);
        while ($row = $stmt_schedules->fetch()) {
            $schedules_data[] = $row;
        }
    } else {
        error_log("Error preparing schedules query");
    }
}

$conn = null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Dosen - <?php echo $lecturer_full_name; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Mengubah latar belakang body menjadi putih */
        body {
            background-color: white; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0; /* Menghilangkan padding agar konten bisa full-width */
        }

        .main-container {
            max-width: 100%; /* Mengubah ke full-width */
            margin: 0; /* Menghilangkan margin */
            background: white; /* Mengubah latar belakang kontainer utama menjadi putih */
            border-radius: 0; /* Menghilangkan border-radius */
            box-shadow: none; /* Menghilangkan shadow */
            overflow: hidden;
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #89CFF0, #6FA8DC);
            padding: 30px;
            text-align: center;
            color: white;
            font-size: 2.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            position: relative; /* Menambahkan ini untuk posisi tombol kembali */
        }
        
        /* CSS untuk tombol kembali */
        .back-btn {
            position: absolute;
            top: 25px;
            left: 25px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 12px;
            padding: 8px 15px;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.4);
            color: white;
            transform: translateY(-2px);
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

        .table thead th {
            background: linear-gradient(135deg, #4A90E2, #357ABD);
            color: white;
            vertical-align: middle;
            text-align: center;
        }

        .table tbody td {
            vertical-align: middle;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header">
            <a href="dash-dosen.php" class="back-btn">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <i class="fas fa-calendar-alt me-3"></i>
            Jadwal Kuliah Dosen
            <br>
            <small style="font-size: 1.2rem;"><?php echo $lecturer_full_name; ?></small>
        </div>

        <div class="content-wrapper">
            <div class="sidebar">
                <h3><i class="fas fa-book me-2"></i>Mata Kuliah Diampu</h3>
                <?php if (empty($courses_data)): ?>
                    <p class="text-center text-muted">Tidak ada mata kuliah yang diampu.</p>
                <?php else: ?>
                    <?php foreach ($courses_data as $course): ?>
                        <button class="pertemuan-btn <?php echo ($selected_course_id == $course['course_id']) ? 'active' : ''; ?>" onclick="window.location.href='jadwal-dosen.php?course_id=<?php echo htmlspecialchars($course['course_id']); ?>';">
                            <?php echo htmlspecialchars($course['course_name']); ?> (<?php echo htmlspecialchars($course['course_code']); ?>)
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="main-content">
                <?php if ($selected_course_id === null): ?>
                    <div class="section-card">
                        <div class="section-header">
                            <i class="fas fa-info-circle me-2"></i>Informasi
                        </div>
                        <div class="content-box">
                            <p class="text-muted">Pilih mata kuliah dari sidebar untuk melihat jadwalnya.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="section-card">
                        <div class="section-header">
                            <i class="fas fa-clock me-2"></i>Jadwal Mata Kuliah: 
                            <?php 
                                $current_course_name = "N/A";
                                $current_course_code = "N/A";
                                foreach ($courses_data as $course) {
                                    if ($course['course_id'] == $selected_course_id) {
                                        $current_course_name = $course['course_name'];
                                        $current_course_code = $course['course_code'];
                                        break;
                                    }
                                }
                                echo htmlspecialchars($current_course_name) . " (" . htmlspecialchars($current_course_code) . ")";
                            ?>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Hari</th>
                                        <th>Waktu</th>
                                        <th>Ruangan</th>
                                        <th>Tipe Kelas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($schedules_data)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Tidak ada jadwal untuk mata kuliah ini.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($schedules_data as $schedule): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($schedule['day_of_week']); ?></td>
                                                <td><?php echo htmlspecialchars(date('H:i', strtotime($schedule['start_time']))) . ' - ' . htmlspecialchars(date('H:i', strtotime($schedule['end_time']))); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['room'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['class_type']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>