<?php
include 'middleware.php';
include 'db_connection.php';
require_role('student');

// Ambil user_id mahasiswa dari sesi
$current_student_id = $_SESSION['user_id']; 

// Ambil nama lengkap dan NIM untuk ditampilkan di header (opsional)
$student_name = $_SESSION['full_name'] ?? 'Mahasiswa';
$student_nim = '';

$sql_student_info = "SELECT nim FROM users WHERE user_id = ? AND role = 'student'";
$stmt_student_info = $conn->prepare($sql_student_info);
if ($stmt_student_info) {
    $stmt_student_info->execute([$current_student_id]);
    if ($row_student_info = $stmt_student_info->fetch()) {
        $student_nim = htmlspecialchars($row_student_info['nim']);
    }
}

$conn = null; // Tutup koneksi database
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kuliah</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS yang sudah ada */
        :root {
            --primary-color: #4A90E2;
            --secondary-color: #7FB3D3;
            --accent-color: #E3F2FD;
            --white: #FFFFFF;
            --light-gray: #F8F9FA;
            --text-dark: #2C3E50;
            --shadow: 0 4px 12px rgba(74, 144, 226, 0.15);
            --teal-color: #5A9BA8;
            --blue-gradient: linear-gradient(135deg, #4A90E2, #7FB3D3);
        }

        body {
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .header {
            background: var(--blue-gradient);
            color: white;
            padding: 1.5rem 0;
            box-shadow: var(--shadow);
            border-radius: 0 0 20px 20px;
            margin-bottom: 2rem;
        }

        .back-btn, .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            font-size: 0.9rem;
        }

        .back-btn:hover, .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
        }

        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: var(--primary-color);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-right: 0.5rem;
        }

        .day-tabs {
            background: var(--white);
            border-radius: 15px;
            padding: 0.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            overflow-x: auto;
        }

        .day-tab {
            flex: 1;
            padding: 0.8rem 1rem;
            border: none;
            background: transparent;
            color: var(--text-dark);
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 600;
            min-width: 80px;
            white-space: nowrap;
        }

        .day-tab.active {
            background: var(--blue-gradient);
            color: white;
            box-shadow: 0 3px 8px rgba(74, 144, 226, 0.3);
        }

        .day-tab:hover:not(.active) {
            background: var(--accent-color);
            color: var(--primary-color);
        }

        .schedule-container {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow);
            min-height: 400px;
        }

        .schedule-item {
            background: var(--white);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 3px 10px rgba(74, 144, 226, 0.1);
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .schedule-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.2);
        }

        .course-title {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .lecturer-name {
            font-style: italic;
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-bottom: 0.8rem;
        }

        .schedule-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .time-badge {
            background: var(--primary-color);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .room-badge {
            background: var(--secondary-color);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .type-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .type-badge.teori {
            background: #E3F2FD;
            color: var(--primary-color);
        }

        .type-badge.praktikum {
            background: #FFF3E0;
            color: #FF9800;
        }

        .no-class {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--secondary-color);
            font-style: italic;
            /* display: none; Ditampilkan/disembunyikan oleh JS */
        }

        .no-class i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .day-content {
            display: none;
            animation: fadeInUp 0.5s ease-out;
        }

        .day-content.active {
            display: block;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .schedule-details {
                flex-direction: column;
                align-items: flex-start;
            }

            .day-tabs {
                overflow-x: scroll;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            .day-tabs::-webkit-scrollbar {
                display: none;
            }

            .schedule-container {
                padding: 1rem;
            }
        }

        .weekend-message {
            background: linear-gradient(135deg, var(--accent-color), rgba(184, 212, 227, 0.3));
            border: 2px dashed var(--primary-color);
            color: var(--text-dark);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <a href="dash-mahasiswa.php" class="back-btn">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                </div>
                <div class="col-md-4 text-center">
                    <h3 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Jadwal Kuliah
                    </h3>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex align-items-center justify-content-end">
                        <div class="profile-pic">
                            <i class="fas fa-user"></i>
                        </div>
                        <a href="logout.php" class="logout-btn" onclick="handleLogout()">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="day-tabs">
            <div class="d-flex">
                <button class="day-tab active" data-day="Senin">
                    <i class="fas fa-calendar-day me-1"></i>Senin
                </button>
                <button class="day-tab" data-day="Selasa">
                    <i class="fas fa-calendar-day me-1"></i>Selasa
                </button>
                <button class="day-tab" data-day="Rabu">
                    <i class="fas fa-calendar-day me-1"></i>Rabu
                </button>
                <button class="day-tab" data-day="Kamis">
                    <i class="fas fa-calendar-day me-1"></i>Kamis
                </button>
                <button class="day-tab" data-day="Jumat">
                    <i class="fas fa-calendar-day me-1"></i>Jumat
                </button>
                <button class="day-tab" data-day="Weekend">
                    <i class="fas fa-coffee me-1"></i>Weekend
                </button>
            </div>
        </div>

        <div class="schedule-container">
            <div class="day-content" id="Senin-schedule"></div>
            <div class="day-content" id="Selasa-schedule"></div>
            <div class="day-content" id="Rabu-schedule"></div>
            <div class="day-content" id="Kamis-schedule"></div>
            <div class="day-content" id="Jumat-schedule"></div>
            <div class="day-content" id="Weekend-schedule">
                <div class="weekend-message">
                    <i class="fas fa-coffee mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                    <h4>Weekend Break!</h4>
                    <p class="mb-0">Sabtu & Minggu - Waktu untuk istirahat dan mengerjakan tugas</p>
                </div>
            </div>
            <div class="no-class" id="noClassMessage" style="display: none;">
                <i class="fas fa-calendar-times"></i>
                <p>Tidak ada jadwal kuliah untuk hari ini.</p>
                <p class="text-muted" style="font-size: 0.9rem;">Enjoy your free time!</p>
            </div>
            <div class="text-center text-muted" id="loadingSchedule" style="display: none;">
                <i class="fas fa-spinner fa-spin me-2"></i> Memuat jadwal...
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Pass PHP variable to JavaScript
        const currentStudentId = <?php echo json_encode($current_student_id); ?>;
        
        // Fungsi untuk mengambil data jadwal dari backend
        async function fetchSchedule(day) {
            try {
                // Mengganti URL API ke endpoint yang benar: 'api/get_schedule.php'
                // Pastikan 'day' dikirim dengan kapitalisasi yang sesuai dengan DB (e.g., 'Senin', 'Selasa')
                const response = await fetch(`api/get_schedule.php?day=${day}&student_id=${currentStudentId}`);
                if (!response.ok) {
                    // Log the full response text for more details on HTTP errors
                    const errorText = await response.text();
                    throw new Error(`HTTP error! status: ${response.status}, response: ${errorText}`);
                }
                const data = await response.json();
                if (data.error) {
                    console.error("API Error:", data.error);
                    return [];
                }
                return data;
            } catch (error) {
                console.error("Error fetching schedule:", error);
                // Optionally, display a user-friendly error message on the page
                document.getElementById('loadingSchedule').style.display = 'none';
                document.getElementById('noClassMessage').style.display = 'block';
                document.getElementById('noClassMessage').innerHTML = '<i class="fas fa-exclamation-triangle"></i><p>Gagal memuat jadwal. Cek koneksi atau log error.</p>';
                return [];
            }
        }

        // Fungsi untuk menampilkan jadwal berdasarkan hari yang dipilih
        async function showDay(day) {
            // Sembunyikan semua konten hari dan pesan
            const dayContents = document.querySelectorAll('.day-content');
            dayContents.forEach(content => {
                content.classList.remove('active');
                // Clear content to show loading animation properly for new day
                if (content.id !== 'Weekend-schedule') { // Don't clear static weekend message
                    content.innerHTML = '';
                }
            });
            document.getElementById('noClassMessage').style.display = 'none';

            // Hapus kelas active dari semua tab hari
            const dayTabs = document.querySelectorAll('.day-tab');
            dayTabs.forEach(tab => {
                tab.classList.remove('active');
            });

            // Tampilkan loading
            const loadingScheduleDiv = document.getElementById('loadingSchedule');
            loadingScheduleDiv.style.display = 'block';

            const targetDivId = `${day}-schedule`;
            const targetDiv = document.getElementById(targetDivId);
            
            if (day === 'Weekend') { // Gunakan 'Weekend' sesuai data-day di HTML
                document.getElementById('Weekend-schedule').classList.add('active');
                loadingScheduleDiv.style.display = 'none'; // Sembunyikan loading untuk weekend
                document.querySelector(`.day-tab[data-day="${day}"]`).classList.add('active');
                return; // Exit here as no API call for weekend
            }

            // Fetch schedules for the selected day
            const schedules = await fetchSchedule(day);

            loadingScheduleDiv.style.display = 'none'; // Hide loading after fetch

            if (schedules.length === 0) {
                document.getElementById('noClassMessage').style.display = 'block';
                document.getElementById('noClassMessage').innerHTML = `
                    <i class="fas fa-calendar-times"></i>
                    <p>Tidak ada jadwal kuliah untuk hari ${day}.</p>
                    <p class="text-muted" style="font-size: 0.9rem;">Enjoy your free time!</p>
                `;
            } else {
                schedules.forEach(item => {
                    const scheduleItem = document.createElement('div');
                    scheduleItem.className = 'schedule-item fade-in'; // Tambahkan fade-in class
                    // Menampilkan gelar dosen jika ada
                    const lecturerDisplay = item.lecturer_full_name + (item.lecturer_gelar ? ', ' + item.lecturer_gelar : '');
                    scheduleItem.innerHTML = `
                        <div class="course-title">${item.course_name}</div>
                        <div class="lecturer-name">${lecturerDisplay}</div>
                        <div class="schedule-details">
                            <div>
                                <span class="time-badge">${item.start_time.substring(0, 5)} - ${item.end_time.substring(0, 5)}</span>
                                <span class="room-badge">${item.room}</span>
                            </div>
                            <span class="type-badge ${item.class_type.toLowerCase()}">${item.class_type}</span>
                        </div>
                    `;
                    targetDiv.appendChild(scheduleItem);
                });
            }
            targetDiv.classList.add('active'); // Aktifkan div jadwal hari ini
            document.querySelector(`.day-tab[data-day="${day}"]`).classList.add('active'); // Aktifkan tombol tab
        }

        // Fungsi untuk logout (sama seperti file sebelumnya)
        function handleLogout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = 'logout.php'; // Redirect ke halaman logout.php
            }
        }

        // Inisialisasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Event listener ke setiap tombol hari
            document.querySelectorAll('.day-tab').forEach(button => {
                button.addEventListener('click', function() {
                    showDay(this.dataset.day); // Pass the data-day value directly
                });
            });

            // Tentukan hari ini dan tampilkan jadwal yang relevan
            const today = new Date();
            const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const currentDayName = dayNames[today.getDay()];
            
            let initialDayToShow = currentDayName; 
            if (currentDayName === 'Sabtu' || currentDayName === 'Minggu') {
                initialDayToShow = 'Weekend';
            }
            showDay(initialDayToShow); // Panggil showDay dengan nama hari yang benar

            // Ensure the correct tab is active on load
            const activeTab = document.querySelector(`.day-tab[data-day="${initialDayToShow}"]`);
            if (activeTab) {
                activeTab.classList.add('active');
            } else {
                // Fallback to Monday if initialDayToShow tab is not found
                document.querySelector('.day-tab[data-day="Senin"]').classList.add('active');
            }
            
            // Add click animations to schedule items
            document.addEventListener('click', function(e) {
                const scheduleItem = e.target.closest('.schedule-item');
                if (scheduleItem) {
                    // Add pulse animation
                    scheduleItem.style.transform = 'scale(0.98) translateX(5px)';
                    setTimeout(() => {
                        scheduleItem.style.transform = 'scale(1) translateX(5px)';
                    }, 150);
                    
                    // Get course info (optional for logging/further interaction)
                    const courseTitle = scheduleItem.querySelector('.course-title').textContent;
                    const lecturerName = scheduleItem.querySelector('.lecturer-name').textContent;
                    
                    console.log('Clicked course:', courseTitle, 'by', lecturerName);
                }
            });

            // Add loading animation for body (already present in CSS)
            // document.body.style.opacity = '1';
            // document.body.style.transition = 'opacity 0.3s ease-in';
        });

        // Smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';
    </script>
</body>
</html>