<?php
// nilai.php
include 'middleware.php';
include 'db_connection.php';
include 'helpers.php'; 
require_role('student');

$current_student_id = $_SESSION['user_id'];

// Ambil Data Mahasiswa
$student_data = [];
$sql_student = "SELECT full_name, nim, study_program FROM users WHERE user_id = ?";
$stmt_student = $conn->prepare($sql_student);
if ($stmt_student) {
    $stmt_student->execute([$current_student_id]);
    $student_data = $stmt_student->fetch();
}

// Inisialisasi variabel IPK
$total_sks_kumulatif = 0;
$total_poin_kumulatif = 0;

// Ambil semua mata kuliah yang memiliki entri nilai untuk mahasiswa ini
$graded_courses = [];
$sql_graded = "
    SELECT DISTINCT c.course_id, c.course_name, c.credits 
    FROM grades g
    JOIN courses c ON g.course_id = c.course_id
    WHERE g.student_id = ?
    ORDER BY c.course_name ASC;
";
$stmt_graded = $conn->prepare($sql_graded);
if ($stmt_graded) {
    $stmt_graded->execute([$current_student_id]);
    while ($row = $stmt_graded->fetch()) {
        $graded_courses[$row['course_id']] = [
            'course_name' => $row['course_name'],
            'credits' => $row['credits'],
            'grades_by_type' => [],
            'final_grade' => '-',
            'grade_letter' => '-',
            'grade_points' => 0
        ];
    }
}

// Ambil semua nilai (grades) mahasiswa dan simpan ke struktur data yang sudah dibuat
$sql_grades = "
    SELECT g.grade_value, g.grade_type, g.course_id, g.graded_at
    FROM grades g
    WHERE g.student_id = ? AND g.grade_type IN ('Assignment', 'UTS', 'UAS', 'Partisipasi')
    ORDER BY g.graded_at DESC;
";
$stmt_grades = $conn->prepare($sql_grades);
if ($stmt_grades) {
    $stmt_grades->execute([$current_student_id]);
    while ($grade = $stmt_grades->fetch()) {
        $course_id = $grade['course_id'];
        $grade_type = $grade['grade_type'];
        
        // Cek jika mata kuliah ada di daftar yang memiliki nilai dan nilai untuk grade_type belum disetel
        if (isset($graded_courses[$course_id]) && !isset($graded_courses[$course_id]['grades_by_type'][$grade_type])) {
             // Pastikan nilai tidak NULL sebelum menyimpannya
            if ($grade['grade_value'] !== NULL) {
                 $graded_courses[$course_id]['grades_by_type'][$grade_type] = $grade['grade_value'];
            }
        }
    }
}

$conn = null;

$grade_weights = [
    'Assignment' => 0.20,
    'UTS' => 0.30,
    'UAS' => 0.40,
    'Partisipasi' => 0.10,
];

// Menghitung nilai akhir dan IPK dari data yang sudah dikumpulkan
foreach ($graded_courses as $course_id => &$course) {
    $weighted_sum = 0;
    $all_grades_present = true;
    foreach ($grade_weights as $type => $weight) {
        if (!isset($course['grades_by_type'][$type])) {
            $all_grades_present = false;
            break;
        }
    }
    
    if ($all_grades_present) {
        foreach ($grade_weights as $type => $weight) {
            $weighted_sum += $course['grades_by_type'][$type] * $weight;
        }
        
        $course['final_grade'] = $weighted_sum; 
        
        $grade_info = calculate_grade_letter_and_points($course['final_grade']);
        
        $course['grade_letter'] = $grade_info['grade_letter'];
        $course['grade_points'] = $grade_info['grade_points'];
        
        $total_sks_kumulatif += $course['credits'];
        $total_poin_kumulatif += $course['grade_points'] * $course['credits'];
    } else {
        $course['final_grade'] = '-';
        $course['grade_letter'] = '-';
        $course['grade_points'] = 0;
    }
}

$ipk = $total_sks_kumulatif > 0 ? number_format($total_poin_kumulatif / $total_sks_kumulatif, 2) : '0.00';

function getGradeClass($gradeLetter) {
    $gradeLetter = strtoupper(substr($gradeLetter, 0, 1)); 
    if ($gradeLetter == 'A') return 'grade-a';
    if ($gradeLetter == 'B') return 'grade-b';
    if ($gradeLetter == 'C') return 'grade-c';
    if ($gradeLetter == 'D') return 'grade-d';
    return 'grade-e';
}

function getCourseIcon($courseName) {
    if (stripos($courseName, 'Aljabar Linear') !== false) return 'fas fa-square-root-variable';
    if (stripos($courseName, 'Pemrograman Web') !== false) return 'fas fa-globe';
    if (stripos($courseName, 'Analisis Desain') !== false) return 'fas fa-drafting-compass';
    if (stripos($courseName, 'Multimedia') !== false) return 'fas fa-photo-video';
    if (stripos($courseName, 'Big Data') !== false) return 'fas fa-database';
    if (stripos($courseName, 'Kecerdasan Buatan') !== false) return 'fas fa-brain';
    if (stripos($courseName, 'Basis Data') !== false) return 'fas fa-server';
    if (stripos($courseName, 'Mikrokontroler') !== false) return 'fas fa-microchip';
    return 'fas fa-book';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nilai Mahasiswa - Sistem Akademik</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #B6D0EF;
            --secondary-blue: #63A3F1;
            --light-cream: #FAFFEE;
            --dark-teal: #4F8A9E;
            --white: #FFFFFF;
            --grade-a: #28a745;
            --grade-b: #17a2b8;
            --grade-c: #ffc107;
            --grade-d: #fd7e14;
            --grade-e: #dc3545;
        }

        body {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header-card {
            background: linear-gradient(135deg, var(--secondary-blue), var(--dark-teal));
            border-radius: 25px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: var(--white);
            box-shadow: 0 15px 35px rgba(79, 138, 158, 0.3);
            position: relative;
            overflow: hidden;
        }

        .header-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="30" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="60" cy="70" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="30" cy="80" r="1.5" fill="rgba(255,255,255,0.1)"/></svg>');
        }

        .back-button {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: var(--dark-teal);
            font-weight: 600;
            transition: all 0.3s ease;
            border-radius: 50px;
            padding: 12px 24px;
            margin-bottom: 1.5rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            position: relative;
            z-index: 2;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            color: var(--dark-teal);
            text-decoration: none;
        }

        .student-info {
            position: relative;
            z-index: 1;
        }

        .student-info h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .student-info p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .ipk-container {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .ipk-display {
            text-align: center;
        }

        .ipk-number {
            font-size: 3.5rem;
            font-weight: 900;
            color: var(--white);
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            margin-bottom: 0.5rem;
            display: block;
        }

        .ipk-label {
            font-size: 1.2rem;
            font-weight: 600;
            opacity: 0.9;
        }
        
        .semester-card {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .semester-card:hover {
            transform: translateY(-5px);
        }

        .table-nilai {
            width: 100%;
            border-collapse: collapse;
        }

        .table-nilai th, .table-nilai td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .table-nilai th {
            background-color: var(--light-cream);
            color: var(--dark-teal);
            font-weight: 600;
        }
        
        .table-nilai tbody tr:last-child td {
            border-bottom: none;
        }

        .course-details .course-name {
            font-weight: 600;
            color: var(--dark-teal);
        }

        .course-details .course-credits {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .grade-badge {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            font-weight: bold;
            border-radius: 50%;
            color: var(--white);
            font-size: 1.1rem;
        }

        .grade-a { background-color: var(--grade-a); }
        .grade-b { background-color: var(--grade-b); }
        .grade-c { background-color: var(--grade-c); }
        .grade-d { background-color: var(--grade-d); }
        .grade-e { background-color: var(--grade-e); }

        .grade-value {
            font-weight: bold;
            color: #333;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6C757D;
        }
        .empty-state i {
            font-size: 3rem;
            color: var(--primary-blue);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header-card animate-fade-in">
            <a href="dash-mahasiswa.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Dashboard
            </a>
            
            <div class="student-info">
                <h2><i class="fas fa-user-graduate me-2"></i><span id="studentFullName"><?= htmlspecialchars($student_data['full_name'] ?? 'Nama Mahasiswa') ?></span></h2>
                <p><i class="fas fa-id-card me-2"></i>NIM: <span><?= htmlspecialchars($student_data['nim'] ?? '-') ?></span></p>
                <p><i class="fas fa-graduation-cap me-2"></i><span><?= htmlspecialchars($student_data['study_program'] ?? 'Program Studi') ?></span></p>
                
                <div class="ipk-container">
                    <div class="ipk-display">
                        <span class="ipk-number"><?= $ipk ?></span>
                        <div class="ipk-label">Indeks Prestasi Kumulatif</div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($graded_courses)): ?>
            <div class="semester-card animate-fade-in">
                <div class="table-responsive">
                    <table class="table table-hover table-nilai">
                        <thead>
                            <tr>
                                <th class="col-8">Mata Kuliah</th>
                                <th>SKS</th>
                                <th class="text-center">Nilai Angka</th>
                                <th class="text-center">Nilai Huruf</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($graded_courses as $course): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="<?= getCourseIcon($course['course_name']) ?> me-3 text-muted"></i>
                                            <div class="course-details">
                                                <div class="course-name"><?= htmlspecialchars($course['course_name']) ?></div>
                                                <div class="course-credits"><?= $course['credits'] ?> SKS</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= $course['credits'] ?></td>
                                    <td class="text-center">
                                        <span class="grade-value">
                                            <?php 
                                                if ($course['final_grade'] !== '-') {
                                                    echo number_format($course['final_grade'], 2);
                                                } else {
                                                    echo '-';
                                                }
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="grade-badge <?= getGradeClass($course['grade_letter']) ?>">
                                            <?= htmlspecialchars($course['grade_letter']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state semester-card">
                <i class="fas fa-exclamation-circle"></i>
                <h4>Tidak ada data mata kuliah ditemukan.</h4>
                <p>Silakan hubungi bagian akademik untuk informasi lebih lanjut.</p>
            </div>
        <?php endif; ?>

    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fadeElements = document.querySelectorAll('.animate-fade-in');
            fadeElements.forEach((element, index) => {
                element.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>