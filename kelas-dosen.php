<?php
include 'middleware.php';
include 'db_connection.php';
require_role('lecturer');

$lecturer_user_id = $_SESSION['user_id'];
$all_classes_data_for_js = []; // Variabel PHP yang akan di-JSON-encode untuk JavaScript
$lecturer_name_for_header = "Dosen"; // Nama default untuk header

// --- Handle data Kelas yang diajar oleh Dosen ini ---
$classes_data_raw = [];
$sql_courses = "SELECT course_id, course_name, course_code, credits, lecturer_id FROM courses WHERE lecturer_id = ? ORDER BY course_name ASC";
$stmt_courses = $conn->prepare($sql_courses);
if ($stmt_courses) {
    $stmt_courses->execute([$lecturer_user_id]);
    while ($row_course = $stmt_courses->fetch()) {
        $classes_data_raw[$row_course['course_id']] = $row_course; // Menggunakan course_id sebagai kunci
    }
}

// --- Ambil semua mahasiswa yang terdaftar di mata kuliah yang diajar oleh Dosen ini ---
$students_by_course = [];
if (!empty($classes_data_raw)) {
    // Buat daftar course_id yang diajar dosen ini untuk query mahasiswa
    $course_ids_in = implode(',', array_keys($classes_data_raw));
    
    $sql_students_enrollments = "
        SELECT
            u.user_id AS student_id,
            u.full_name AS student_name,
            u.nim,
            ce.course_id
        FROM
            users u
        JOIN
            course_enrollments ce ON u.user_id = ce.student_id
        WHERE
            u.role = 'student' AND ce.course_id IN ($course_ids_in)
        ORDER BY
            ce.course_id, u.full_name;
    ";
    $result_students_enrollments = $conn->query($sql_students_enrollments);
    if ($result_students_enrollments) {
        while ($row_student = $result_students_enrollments->fetch()) {
            $course_id = $row_student['course_id'];
            if (!isset($students_by_course[$course_id])) {
                $students_by_course[$course_id] = [];
            }
            // Pastikan kunci 'name' dan 'nim' selalu ada, meskipun mungkin kosong dari database
            $students_by_course[$course_id][] = [
                'id' => $row_student['student_id'],
                'name' => htmlspecialchars($row_student['student_name'] ?? 'Nama Tidak Diketahui'), // Default jika null
                'nim' => htmlspecialchars($row_student['nim'] ?? 'NIM Tidak Diketahui') // Default jika null
            ];
        }
    } else {
        error_log("Error fetching student enrollments.");
    }
}


// Gabungkan data untuk ALL_CLASSES_DATA JavaScript
foreach ($classes_data_raw as $course_id => $class_info) {
    $students_in_this_course = $students_by_course[$course_id] ?? [];
    // MENGGUNAKAN course_id SEBAGAI KEY UNTUK JAVASCRIPT AGAR LEBIH ROBUST
    $all_classes_data_for_js[$class_info['course_id']] = [
        'id' => $class_info['course_id'],
        'code' => htmlspecialchars($class_info['course_code']),
        'name' => htmlspecialchars($class_info['course_name']),
        'credits' => $class_info['credits'],
        'students' => $students_in_this_course
    ];
}

// Ambil nama dosen untuk header
$sql_lecturer_name = "SELECT full_name, gelar FROM users WHERE user_id = ? AND role = 'lecturer'";
$stmt_lecturer_name = $conn->prepare($sql_lecturer_name);
if ($stmt_lecturer_name) {
    $stmt_lecturer_name->execute([$lecturer_user_id]);
    if ($row_lecturer = $stmt_lecturer_name->fetch()) {
        $lecturer_name_for_header = htmlspecialchars($row_lecturer['full_name']);
        if (!empty($row_lecturer['gelar'])) {
             $lecturer_name_for_header .= ", " . htmlspecialchars($row_lecturer['gelar']);
        }
    }
}


$conn = null; // Tutup koneksi database
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Halaman Dosen - Kelas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&family=Playfair+Display:wght@400;500;600&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Definisikan warna-warna konsisten dari dashboard sebelumnya */
    :root {
        --primary-blue: #B6D0EF; /* Light blue */
        --secondary-blue: #63A3F1; /* Medium blue */
        --accent-blue: #4A90E2; /* Darker blue for accents */
        --light-bg: #FAFFEE; /* Very light green/yellowish for subtle background */
        --dark-teal: #4F8A9E; /* Dark teal for accents */
        --white: #FFFFFF;
        --text-dark: #333;
        --text-muted: #666;
        --header-bg: #b3cce6; /* Your existing header background color */
        --card-shadow: rgba(74, 144, 226, 0.15);
        --hover-shadow: rgba(74, 144, 226, 0.25);
    }

    body {
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      color: var(--text-dark);
      background-color: var(--white);
    }

    /* Styles untuk Halaman Dosen utama (Kelas) */
    #dosenPage {
        background-color: var(--white); 
        min-height: 100vh;
        padding-top: 30px;
        padding-bottom: 30px;
    }

    .header {
      background-color: var(--header-bg);
      padding: 15px 30px;
      border-radius: 10px;
      margin-bottom: 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .header-title {
      font-weight: 600;
      color: var(--text-dark);
      font-size: 1.5rem;
      margin: 0;
      font-family: 'Montserrat', sans-serif;
    }

    .header-buttons {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .header-buttons a {
      color: var(--text-dark);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s ease;
    }
    .header-buttons a:hover {
        color: var(--dark-teal);
    }
    
    .back-btn {
        background: rgba(255, 255, 255, 0.5);
        color: var(--text-dark);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        padding: 8px 15px;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .back-btn:hover {
        background: rgba(255, 255, 255, 0.8);
        transform: translateY(-2px);
    }

    .avatar-header {
      width: 40px;
      height: 40px;
      background-color: var(--white);
      border-radius: 50%;
      border: 2px solid var(--primary-blue);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      color: var(--text-dark);
    }

    .kelas-card {
      background-color: var(--white);
      border-radius: 12px;
      border: none;
      overflow: hidden;
      margin-bottom: 20px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.08);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    .kelas-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 30px rgba(0,0,0,0.15);
    }

    .kelas-header {
      background: linear-gradient(90deg, var(--primary-blue), var(--secondary-blue));
      padding: 20px;
      position: relative;
      color: var(--text-dark);
    }

    .kelas-title {
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 5px;
      font-size: 1.3rem;
      font-family: 'Montserrat', sans-serif;
    }

    .kelas-subtitle {
      color: var(--text-dark);
      font-size: 0.95rem;
      opacity: 0.8;
    }

    .kelas-body {
      padding: 20px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .kelas-info {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      font-size: 0.9rem;
    }

    .kelas-info-item {
      color: var(--text-muted);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .kelas-info-item i {
        color: var(--dark-teal);
        font-size: 1rem;
    }

    .kelas-progress {
      height: 10px;
      border-radius: 5px;
      margin-bottom: 20px;
      background-color: #e9ecef;
    }
    .kelas-progress .progress-bar {
        background-color: var(--dark-teal);
        border-radius: 5px;
    }

    .kelas-action {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      margin-top: auto;
    }

    .btn-join {
      background-color: var(--secondary-blue);
      color: var(--white);
      border: none;
      padding: 8px 25px;
      border-radius: 8px;
      font-weight: 600;
      transition: background-color 0.3s ease, transform 0.2s ease;
      font-size: 0.9rem;
    }

    .btn-join:hover {
      background-color: var(--dark-teal);
      transform: translateY(-2px);
    }

    .main-container {
      padding: 0 15px;
    }

    /* Anggota Kelas Styles - Perbaikan untuk posisi yang lebih presisi */
    .anggota-page {
      display: none;
      background-color: var(--white);
      min-height: 100vh;
      padding: 20px 0;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      z-index: 1000;
      overflow-y: auto;
    }

    .anggota-container {
      max-width: 900px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .header-container {
      background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
      border-radius: 15px;
      padding: 25px 30px;
      margin-bottom: 30px;
      box-shadow: 0 5px 25px var(--card-shadow);
      border: none;
    }

    .header-title-anggota {
      color: var(--text-dark);
      font-weight: 600;
      font-size: 1.8rem;
      margin: 0;
      text-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .header-btn {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: var(--text-dark);
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
      backdrop-filter: blur(5px);
      font-size: 0.95rem;
    }

    .header-btn:hover {
      background: rgba(255, 255, 255, 0.35);
      transform: translateY(-2px);
      color: var(--text-dark);
    }

    .back-btn {
      background: rgba(108, 117, 125, 0.6) !important;
      color: white !important;
    }

    .back-btn:hover {
      background: rgba(108, 117, 125, 0.8) !important;
      color: white !important;
    }

    /* Container untuk pencarian dan grid - diperbaiki untuk alignment */
    .content-wrapper {
      max-width: 800px;
      margin: 0 auto;
    }

    .search-container {
      width: 100%;
      margin-bottom: 30px;
      position: relative;
    }

    .search-input {
      width: 100%;
      padding: 15px 50px 15px 20px;
      border: 2px solid var(--primary-blue);
      border-radius: 15px;
      background: rgba(255, 255, 255, 0.95);
      font-size: 1rem;
      box-shadow: 0 5px 20px var(--card-shadow);
      transition: all 0.3s ease;
      color: var(--text-dark);
    }

    .search-input:focus {
      outline: none;
      border-color: var(--secondary-blue);
      box-shadow: 0 8px 30px var(--hover-shadow);
      background: white;
    }

    .search-input::placeholder {
      color: var(--text-muted);
    }

    .search-icon {
      position: absolute;
      right: 20px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--secondary-blue);
      font-size: 1.1rem;
    }

    /* Grid mahasiswa - diperbaiki untuk alignment yang sempurna */
    .students-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      width: 100%;
    }

    .student-card {
      background-color: var(--white);
      border: 2px solid var(--primary-blue);
      border-radius: 15px;
      padding: 20px;
      box-shadow: 0 4px 20px var(--card-shadow);
      transition: all 0.3s ease;
      cursor: pointer;
      height: 100px;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .student-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 30px var(--hover-shadow);
      border-color: var(--secondary-blue);
    }

    .student-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--primary-blue), var(--secondary-blue));
      transform: scaleX(0);
      transition: transform 0.3s ease;
    }

    .student-card:hover::before {
      transform: scaleX(1);
    }

    .student-info {
      width: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .student-name {
      font-weight: 600;
      font-size: 1.1rem;
      color: var(--text-dark);
      margin: 0 0 5px 0;
      line-height: 1.2;
      white-space: normal;
      word-break: break-word;
    }
    
    .student-nim {
      font-size: 0.9rem;
      color: var(--text-muted);
      font-weight: 400;
      margin: 0;
      opacity: 0.8;
    }

    .student-card.selected {
      background: linear-gradient(135deg, var(--secondary-blue), var(--accent-blue));
      color: white;
      border-color: var(--accent-blue);
      transform: scale(1.02);
      box-shadow: 0 8px 35px var(--hover-shadow);
    }

    .student-card.selected .student-name,
    .student-card.selected .student-nim {
      color: white;
    }

    .student-card.selected::before {
      background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));
      transform: scaleX(1);
    }

    /* Empty state styling */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: var(--text-muted);
      grid-column: 1 / -1;
    }

    .empty-state i {
      font-size: 3rem;
      margin-bottom: 20px;
      color: var(--primary-blue);
    }

    .empty-state p {
      font-size: 1.1rem;
      margin: 0;
    }

    /* Media Queries for Responsiveness */
    @media (max-width: 768px) {
        .anggota-container {
            padding: 0 15px;
        }
        
        .content-wrapper {
            max-width: 100%;
        }
        
        .students-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .header-container {
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .header-title-anggota {
            font-size: 1.4rem;
        }
        
        .header-actions {
            gap: 10px;
        }
        
        .student-card {
            height: 90px;
            padding: 15px;
        }
        
        .student-name {
            font-size: 1rem;
        }
        
        .student-nim {
            font-size: 0.85rem;
        }
        
        .search-input {
            padding: 12px 45px 12px 15px;
            font-size: 0.95rem;
        }
        
        .search-icon {
            right: 15px;
            font-size: 1rem;
        }
    }

    @media (max-width: 480px) {
        .student-card {
            height: 80px;
            padding: 12px;
        }
        
        .student-name {
            font-size: 0.95rem;
        }
        
        .student-nim {
            font-size: 0.8rem;
        }
    }

    .fade-in {
      animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Loading animation */
    .loading {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px;
      color: var(--text-muted);
    }

    .loading i {
      color: var(--secondary-blue);
      margin-right: 10px;
    }
  </style>
</head>
<body>
  <div id="dosenPage" class="container mt-4 mb-4">
    <div class="header d-flex justify-content-between align-items-center">
      <a href="dash-dosen.php" class="back-btn d-flex align-items-center me-3" style="text-decoration: none;">
          <i class="fas fa-arrow-left me-2"></i>
          Kembali
      </a>
      <h1 class="header-title">Halaman Dosen</h1>
      <div class="header-buttons">
        <a href="#" onclick="logout()">Logout</a>
        <div class="avatar-header">
          <i class="fas fa-chalkboard-teacher"></i>
        </div>
      </div>
    </div>

    <div class="main-container">
      <div class="row" id="kelasContainer">
        <div class="loading">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Memuat daftar kelas...</p>
        </div>
      </div>
    </div>
  </div>

  <div id="anggotaPage" class="anggota-page">
    <div class="anggota-container">
      <div class="header-container d-flex justify-content-between align-items-center">
        <h1 class="header-title-anggota" id="anggotaTitle"></h1>
        <div class="header-actions">
          <button class="header-btn back-btn" onclick="backToDosen()">
            <i class="fas fa-arrow-left me-2"></i>Kembali
          </button>
        </div>
      </div>
      
      <div class="content-wrapper">
        <div class="search-container">
          <input type="text" class="search-input" id="searchInput" placeholder="Cari nama mahasiswa atau NIM..." onkeyup="searchStudents()">
          <i class="fas fa-search search-icon"></i>
        </div>
        
        <div class="students-grid" id="studentsGrid">
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Global variables to store PHP-rendered data for client-side JS manipulation (search, select)
    let ALL_CLASSES_DATA = <?php echo json_encode($all_classes_data_for_js); ?>;
    let CURRENT_STUDENTS_LIST = [];
    let SELECTED_STUDENTS = [];
    let CURRENT_KELAS_NAME = '';
    let CURRENT_KELAS_ID = '';

    document.addEventListener('DOMContentLoaded', function() {
        renderClassCards();
    });

    // Function to render class cards based on ALL_CLASSES_DATA
    function renderClassCards() {
        const kelasContainer = document.getElementById('kelasContainer');
        kelasContainer.innerHTML = '';

        const classesArray = Object.values(ALL_CLASSES_DATA);

        if (classesArray.length === 0) {
            kelasContainer.innerHTML = `
                <div class="col-12 text-center text-muted py-5">
                    <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                    <p>Tidak ada kelas yang ditemukan untuk dosen ini.</p>
                </div>
            `;
            return;
        }

        classesArray.forEach(kelas => {
            const numStudents = kelas.students.length || 0;
            // Simulasi progress karena tidak ada di database_fix.sql
            const progressPercentage = Math.floor(Math.random() * (90 - 60 + 1)) + 60;

            const cardHtml = `
                <div class="col-md-4 mb-4">
                    <div class="kelas-card" onclick="showAnggotaKelas(${kelas.id}, '${kelas.name}')">
                        <div class="kelas-header">
                            <h3 class="kelas-title">Kelas ${kelas.name}</h3>
                            <p class="kelas-subtitle">${kelas.code} - ${kelas.name}</p>
                        </div>
                        <div class="kelas-body">
                            <div class="kelas-info">
                                <div class="kelas-info-item">
                                    <i class="fas fa-book"></i>
                                    ${kelas.credits} SKS
                                </div>
                                <div class="kelas-info-item">
                                    <i class="fas fa-user-graduate"></i>
                                    ${numStudents} Mahasiswa
                                </div>
                            </div>
                            <div class="progress kelas-progress">
                                <div class="progress-bar bg-info" role="progressbar" style="width: ${progressPercentage}%" aria-valuenow="${progressPercentage}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="kelas-action">
                                <button class="btn btn-join" onclick="event.stopPropagation(); showAnggotaKelas(${kelas.id}, '${kelas.name}');">Masuk Kelas</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            kelasContainer.insertAdjacentHTML('beforeend', cardHtml);
        });
    }

    // Function to show class members page
    function showAnggotaKelas(kelasId, kelasName) {
      CURRENT_KELAS_ID = kelasId;
      CURRENT_KELAS_NAME = kelasName;
      document.getElementById('dosenPage').style.display = 'none';
      document.getElementById('anggotaPage').style.display = 'block';
      document.getElementById('anggotaTitle').textContent = `Anggota Kelas ${kelasName}`;
      
      // Mengakses data menggunakan kelasId sebagai kunci
      CURRENT_STUDENTS_LIST = ALL_CLASSES_DATA[kelasId] ? ALL_CLASSES_DATA[kelasId].students : [];

      // Generate student cards
      generateStudentCards(CURRENT_STUDENTS_LIST);
      
      // Reset selections and search input
      SELECTED_STUDENTS = [];
      document.getElementById('searchInput').value = '';
    }

    // Function to go back to the main lecturer page
    function backToDosen() {
      document.getElementById('anggotaPage').style.display = 'none';
      document.getElementById('dosenPage').style.display = 'block';
      SELECTED_STUDENTS = [];
      CURRENT_STUDENTS_LIST = [];
      CURRENT_KELAS_NAME = '';
      CURRENT_KELAS_ID = '';
      document.getElementById('searchInput').value = '';
    }

    // Function to dynamically generate student cards for a given class
    function generateStudentCards(students) {
      const studentsGrid = document.getElementById('studentsGrid');
      studentsGrid.innerHTML = '';

      if (students.length === 0) {
        studentsGrid.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-user-graduate"></i>
                <p>Tidak ada mahasiswa terdaftar di kelas ini.</p>
            </div>
        `;
        return;
      }

      students.forEach((student, index) => {
        const studentCard = document.createElement('div');
        studentCard.className = 'student-card fade-in';
        studentCard.setAttribute('data-id', student.id);
        studentCard.setAttribute('data-name', student.name);
        studentCard.setAttribute('data-nim', student.nim);
        studentCard.onclick = () => selectStudent(studentCard);
        studentCard.innerHTML = `
            <div class="student-info">
                <h3 class="student-name">${student.name}</h3>
                <p class="student-nim">${student.nim}</p>
            </div>
        `;
        studentsGrid.appendChild(studentCard);
      });
    }

    // Function to filter students based on search input
    function searchStudents() {
      const searchTerm = document.getElementById('searchInput').value.toLowerCase();
      const filteredStudents = CURRENT_STUDENTS_LIST.filter(student =>
        student.name.toLowerCase().includes(searchTerm) || student.nim.toLowerCase().includes(searchTerm)
      );
      generateStudentCards(filteredStudents);
      
      // Reselect any previously selected students that are still visible
      SELECTED_STUDENTS.forEach(selected => {
        const cardElement = document.querySelector(`[data-id="${selected.id}"]`);
        if (cardElement) {
          cardElement.classList.add('selected');
        }
      });
    }

    // Function to handle student selection
    function selectStudent(cardElement) {
      const studentId = cardElement.getAttribute('data-id');
      const studentName = cardElement.getAttribute('data-name');
      const studentNim = cardElement.getAttribute('data-nim');
      
      // Toggle selection
      if (cardElement.classList.contains('selected')) {
        // Deselect
        cardElement.classList.remove('selected');
        SELECTED_STUDENTS = SELECTED_STUDENTS.filter(s => s.id !== studentId);
      } else {
        // Select
        cardElement.classList.add('selected');
        SELECTED_STUDENTS.push({
          id: studentId,
          name: studentName,
          nim: studentNim
        });
      }
      
      // Optional: Update UI to show selection count
      updateSelectionCount();
    }

    // Function to update selection count display (optional)
    function updateSelectionCount() {
      const count = SELECTED_STUDENTS.length;
      // You can add a selection counter UI element here if needed
      console.log(`Selected students: ${count}`);
    }

    // Function to get selected students (for future use)
    function getSelectedStudents() {
      return SELECTED_STUDENTS;
    }

    // Function to clear all selections
    function clearAllSelections() {
      SELECTED_STUDENTS = [];
      document.querySelectorAll('.student-card.selected').forEach(card => {
        card.classList.remove('selected');
      });
      updateSelectionCount();
    }

    // Function to handle logout
    function logout() {
      if (confirm('Apakah Anda yakin ingin logout?')) {
        window.location.href = 'login.php';
      }
    }

    // Function to handle escape key to go back
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        if (document.getElementById('anggotaPage').style.display === 'block') {
          backToDosen();
        }
      }
    });

    // Function to handle smooth scrolling
    function smoothScrollTo(element) {
      element.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
      });
    }

    // Add loading animation for better UX
    function showLoading(container) {
      container.innerHTML = `
        <div class="loading">
          <i class="fas fa-spinner fa-spin fa-2x"></i>
          <p>Memuat data...</p>
        </div>
      `;
    }

    // Error handling function
    function showError(container, message) {
      container.innerHTML = `
        <div class="empty-state">
          <i class="fas fa-exclamation-triangle text-warning"></i>
          <p class="text-danger">${message}</p>
        </div>
      `;
    }

    // Initialize tooltips if Bootstrap is available
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl);
        });
      }
    });

    // Performance optimization: Debounce search function
    function debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }

    // Apply debounce to search function
    const debouncedSearch = debounce(searchStudents, 300);

    // Update search input to use debounced function
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('searchInput');
      if (searchInput) {
        searchInput.onkeyup = debouncedSearch;
      }
    });
  </script>
</body>
</html>