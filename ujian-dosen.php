<?php
include 'middleware.php';
include 'db_connection.php';
require_role('lecturer');

$current_lecturer_id = $_SESSION['user_id'];

// Inisialisasi variabel
$current_exam_type = isset($_GET['exam_type']) ? $_GET['exam_type'] : 'UTS'; // Default ke 'UTS'
$exams_for_display = [];

// Fungsi Helper
function formatDateTimeForDisplay($dateString, $timeString = null) {
    if (empty($dateString) || $dateString === '0000-00-00') {
        return '-';
    }
    $date = new DateTime($dateString);
    $dayName = $date->format('l');
    $dateFormatted = $date->format('d F Y');
    
    $dayNames = [
        'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu'
    ];
    
    $dayNameInId = $dayNames[$dayName];

    return [
        'full' => "{$dayNameInId}, {$dateFormatted}",
        'date' => $dateFormatted
    ];
}


// Ambil data ujian yang diampu oleh dosen yang sedang login
$sql_exams = "
    SELECT
        e.exam_id,
        e.title,
        e.exam_type,
        e.exam_date,
        e.start_time,
        e.end_time,
        e.room,
        e.is_online,
        e.online_link,
        c.course_name,
        c.course_code
    FROM
        exams e
    JOIN
        courses c ON e.course_id = c.course_id
    WHERE
        c.lecturer_id = ? AND e.exam_type = ?
    ORDER BY
        e.exam_date ASC, e.start_time ASC;
";
$stmt_exams = $conn->prepare($sql_exams);
if ($stmt_exams) {
    $stmt_exams->execute([$current_lecturer_id, $current_exam_type]);

    while ($row = $stmt_exams->fetch()) {
        $exams_for_display[] = $row;
    }
}

$conn = null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Jadwal Ujian - Dosen</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #B6D0EF;
            --secondary-blue: #63A3F1;
            --light-green: #FAFFEE;
            --dark-teal: #4F8A9E;
            --white: #FFFFFF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .dashboard-container {
            min-height: 100vh;
            padding: 20px;
        }
        
        .header-container {
            position: relative;
            margin-bottom: 2rem;
            padding: 1rem;
        }

        .back-to-dashboard-btn {
            position: absolute;
            top: 2rem;
            left: 1rem;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 8px 15px;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 100;
        }

        .back-to-dashboard-btn:hover {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
        }

        .header h1 {
            color: var(--dark-teal);
            font-weight: 700;
            margin-bottom: 10px;
            text-align: center;
        }

        .header p {
            color: #666;
            font-size: 16px;
            margin: 0;
            text-align: center;
        }

        .exam-selector {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
        }

        .exam-tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .exam-tab {
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            background: var(--light-green);
            color: var(--dark-teal);
            text-decoration: none;
        }

        .exam-tab.active {
            background: var(--dark-teal);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(79, 138, 158, 0.3);
        }

        .exam-tab:hover:not(.active) {
            background: var(--primary-blue);
            transform: translateY(-2px);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .card-header {
            background: var(--dark-teal);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .table thead th {
            background: var(--light-green);
            color: var(--dark-teal);
            border: none;
            font-weight: 600;
            text-align: center;
            padding: 1rem;
        }

        .table tbody td {
            padding: 1rem;
            text-align: center;
            vertical-align: middle;
            border-color: rgba(79, 138, 158, 0.1);
        }

        .online-badge {
            background: var(--secondary-blue);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-block;
        }

        .subject-name {
            font-weight: 600;
            color: var(--dark-teal);
            margin-bottom: 0.5rem;
        }
        
        .exam-time-display {
            color: #666;
            font-size: 0.9rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6C757D;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <a href="dash-dosen.php" class="back-to-dashboard-btn">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>

        <div class="header">
            <h1><i class="fas fa-calendar-check"></i> Jadwal Ujian</h1>
            <p>Jadwal Ujian Tengah Semester & Ujian Akhir Semester</p>
        </div>

        <div class="exam-selector">
            <div class="exam-tabs">
                <a href="ujian-dosen.php?exam_type=UTS" class="exam-tab <?php echo ($current_exam_type === 'UTS') ? 'active' : ''; ?>">
                    <i class="fas fa-clipboard-check"></i> UTS (Ujian Tengah Semester)
                </a>
                <a href="ujian-dosen.php?exam_type=UAS" class="exam-tab <?php echo ($current_exam_type === 'UAS') ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i> UAS (Ujian Akhir Semester)
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-calendar-week me-2"></i>
                        Jadwal Ujian <?php echo htmlspecialchars($current_exam_type); ?>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Hari & Tanggal</th>
                                        <th>Mata Kuliah</th>
                                        <th>Waktu</th>
                                        <th>Ruangan/Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($exams_for_display)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-5">
                                                <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                                                <p>Tidak ada jadwal ujian <?php echo htmlspecialchars($current_exam_type); ?> yang ditemukan.</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($exams_for_display as $exam): ?>
                                            <?php
                                                $dateInfo = formatDateTimeForDisplay($exam['exam_date']);
                                                $roomStatus = $exam['is_online'] ? 
                                                    '<span class="online-badge"><i class="fas fa-laptop me-1"></i>Ujian Online</span>' : 
                                                    htmlspecialchars($exam['room']);
                                            ?>
                                            <tr>
                                                <td><strong><?php echo $dateInfo['full']; ?></strong></td>
                                                <td>
                                                    <div class="subject-name"><?php echo htmlspecialchars($exam['course_name']); ?></div>
                                                    <div class="exam-time-display"><?php echo htmlspecialchars($exam['title']); ?></div>
                                                </td>
                                                <td><?php echo date('H:i', strtotime($exam['start_time'])) . ' - ' . date('H:i', strtotime($exam['end_time'])); ?></td>
                                                <td><?php echo $roomStatus; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>