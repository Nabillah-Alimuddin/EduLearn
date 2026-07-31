<?php
include 'middleware.php';
include 'db_connection.php';
require_role('student');

// Ambil user_id mahasiswa dari sesi
$current_student_id = $_SESSION['user_id'];

// Data profil mahasiswa (opsional, untuk tampilan header)
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
    <title>Deadline Tugas - Dashboard Mahasiswa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS yang sudah ada */
        :root {
            --primary-color: #7FB3D3;
            --secondary-color: #B8D4E3;
            --accent-color: #E8F4F8;
            --white: #FFFFFF;
            --light-gray: #F8F9FA;
            --text-dark: #2C3E50;
            --shadow: 0 4px 12px rgba(127, 179, 211, 0.15);
            --danger: #E74C3C;
            --warning: #F39C12;
            --success: #27AE60;
            --info-blue: #17a2b8;
        }

        body {
            background: linear-gradient(135deg, #E8F4F8 0%, #B8D4E3 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem 0;
            box-shadow: var(--shadow);
            border-radius: 0 0 20px 20px;
            margin-bottom: 2rem;
        }

        .profile-section {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .profile-pic {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary-color);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .back-btn, .refresh-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            margin-right: 0.5rem;
        }

        .back-btn:hover, .refresh-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
        }

        .deadline-card {
            background: var(--white);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .deadline-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .deadline-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(127, 179, 211, 0.25);
        }

        .deadline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .mata-kuliah-badge {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .tugas-title {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .tugas-description {
            color: #6C757D;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .deadline-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .deadline-date {
            display: flex;
            align-items: center;
            font-weight: 600;
            color: var(--text-dark);
        }

        .deadline-date i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        /* Perubahan Styling Tombol Aksi */
        .btn-action {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .btn-primary-custom:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
            color: white;
        }

        /* Styling untuk tombol "Unduh Tugas" dari dosen */
        .btn-info-custom {
            background: linear-gradient(135deg, var(--info-blue), #1a8a9e);
            color: white;
        }
        .btn-info-custom:hover {
            background: linear-gradient(135deg, #1a8a9e, var(--info-blue));
            transform: translateY(-2px);
        }

        /* Styling untuk tombol "Batalkan Submit" */
        .btn-warning-custom {
            background: linear-gradient(135deg, var(--warning), #d68910);
            color: white;
        }
        .btn-warning-custom:hover {
            background: linear-gradient(135deg, #d68910, var(--warning));
            transform: translateY(-2px);
        }

        /* Styling untuk tombol Status di deadline-actions */
        .badge.bg-success, .badge.bg-warning, .badge.bg-danger {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .section-title {
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6C757D;
            display: none;
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .upload-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .upload-content {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            box-shadow: var(--shadow);
        }

        .upload-zone {
            border: 2px dashed var(--primary-color);
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            margin: 1rem 0;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-zone:hover {
            border-color: var(--secondary-color);
            background: var(--accent-color);
        }

        .upload-zone.dragover {
            border-color: var(--success);
            background: rgba(39, 174, 96, 0.1);
        }

        .file-input {
            display: none;
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        /* Penempatan Tombol Aksi dalam Modal */
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
        }
        .deadline-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                margin-bottom: 1rem;
            }

            .deadline-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .deadline-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="dashboard-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="profile-section">
                            <div class="d-flex align-items-center">
                                <div class="profile-pic me-3">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h5 class="welcome-text m-0">Deadline Tugas</h5>
                                    <p class="student-id m-0">Kelola deadline tugas kuliah Anda</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="#" class="refresh-btn" onclick="refreshPage()">
                            <i class="fas fa-sync-alt me-2"></i>Refresh
                        </a>
                       <a href="dash-mahasiswa.php" class="back-btn">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h3 class="section-title fade-in">
                        <i class="fas fa-tasks me-2"></i>Daftar Tugas & Deadline
                    </h3>
                </div>
                <div class="col-12 mb-4">
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari berdasarkan nama mata kuliah...">
                        <button class="btn btn-primary" type="button" onclick="searchAssignments()">
                            <i class="fas fa-search me-1"></i> Cari
                        </button>
                    </div>
                </div>
            </div>

            <div id="deadlineContainer" class="row">
                <div class="col-12 text-center text-muted" id="loadingDeadlines">
                    <i class="fas fa-spinner fa-spin me-2"></i> Memuat tugas...
                </div>
            </div>

            <div id="emptyState" class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h4>Tidak ada tugas ditemukan</h4>
                <p>Tidak ada tugas yang sesuai dengan filter yang dipilih.</p>
            </div>
        </div>
    </div>

    <div id="uploadModal" class="upload-modal">
        <div class="upload-content">
            <h4 id="modalTitle" class="mb-3">Upload Tugas</h4>
            <div class="mb-3">
                <p><strong>Judul:</strong> <span id="taskTitle"></span></p>
                <p><strong>Deskripsi:</strong> <span id="taskDesc"></span></p>
                <p><strong>Deadline:</strong> <span id="taskDeadline"></span></p>
                <p><strong>Status Tugas:</strong> <span id="taskStatusModal"></span></p>
            </div>
            <div id="submissionInfo" class="mb-3 hidden">
                <p><strong>File Disubmit:</strong> <span id="submittedFile"></span></p>
                <button class="btn-action btn-primary-custom" onclick="viewSubmission()">
                    <i class="fas fa-eye me-1"></i>Lihat Submission
                </button>
                 <button class="btn-action btn-warning-custom" onclick="confirmUnsubmit()">
                    <i class="fas fa-undo me-1"></i>Batalkan Submit
                </button>
            </div>
            <div id="uploadZone" class="upload-zone">
                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                <p>Drag & drop file Anda di sini atau klik untuk memilih file</p>
                <input type="file" id="fileInput" class="file-input" multiple>
            </div>
            <div id="fileList" class="mb-3"></div>
            <div class="d-flex justify-content-end gap-2 modal-footer"> <button class="btn-action" style="background: var(--danger); color: white;" onclick="closeUploadModal()">Batal</button>
                <button id="submitButton" class="btn-action btn-primary-custom" onclick="submitFiles()">Submit</button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Asumsi student_id saat ini
        const currentStudentId = <?php echo json_encode($current_student_id); ?>;
        let allAssignments = [];

        // Fungsi untuk mengambil semua tugas
        async function fetchAllAssignments() {
            try {
                const response = await fetch(`api/get_all_assignments.php?student_id=${currentStudentId}`);
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error("API get_all_assignments.php error:", errorText);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                if (data.error) {
                    console.error("API Error:", data.error);
                    return [];
                }
                return data;
            } catch (error) {
                console.error("Error fetching assignments:", error);
                return [];
            }
        }

        // Fungsi untuk mengambil status submit tugas
        async function fetchSubmissionStatus(assignmentId, studentId) {
            try {
                const response = await fetch(`api/get_submission_status.php?assignment_id=${assignmentId}&student_id=${studentId}`);
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error("API get_submission_status.php error response:", errorText);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                return {
                    submitted: data.submitted || false,
                    submission_file_path: data.submission_file_path || null
                };
            } catch (error) {
                console.error("Error fetching submission status:", error);
                return { submitted: false, submission_file_path: null };
            }
        }

        // Fungsi untuk menampilkan tugas (sudah diperbaiki untuk menerima data yang difilter)
        function displayDeadlines(filteredAssignments = allAssignments) {
            const deadlineContainer = document.getElementById('deadlineContainer');
            const emptyState = document.getElementById('emptyState');
            
            deadlineContainer.innerHTML = '';

            if (filteredAssignments.length > 0) {
                emptyState.style.display = 'none';
                filteredAssignments.forEach(assignment => {
                    const submissionStatus = assignment.submission_status;
                    const isSubmitted = submissionStatus.submitted;
                    const submittedFilePath = submissionStatus.submission_file_path || '';

                    const now = new Date();
                    const dueDate = new Date(assignment.due_date);
                    const isOverdue = now > dueDate && !isSubmitted;

                    const colDiv = document.createElement('div');
                    colDiv.className = 'col-12 fade-in';
                    
                    const downloadAssignmentButton = assignment.file_path ? `
                        <button class="btn-action btn-info-custom" onclick="downloadLecturerAssignment('${escapeHtml(assignment.file_path)}', '${escapeHtml(assignment.title)}')">
                            <i class="fas fa-download me-1"></i>Unduh Tugas
                        </button>
                    ` : '';

                    colDiv.innerHTML = `
                        <div class="deadline-card ${isOverdue ? 'border-danger' : ''}" data-assignment-id="${assignment.assignment_id}" data-course-id="${assignment.course_id}">
                            <div class="deadline-header">
                                <span class="mata-kuliah-badge">${assignment.course_name || 'Tidak Diketahui'}</span>
                            </div>
                            <h4 class="tugas-title">${assignment.title}</h4>
                            <p class="tugas-description">${assignment.description || 'Tidak ada deskripsi.'}</p>
                            <div class="deadline-info">
                                <div class="deadline-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Deadline: ${formatDateTime(assignment.due_date)}</span>
                                    ${isOverdue ? '<span class="badge bg-danger ms-2">Terlewat</span>' : ''}
                                </div>
                                <div class="deadline-actions">
                                    ${downloadAssignmentButton} ${isSubmitted ? `
                                        <span class="badge bg-success me-2">Sudah Disubmit</span>
                                        <button class="btn-action btn-primary-custom" onclick="openUploadModal(${assignment.assignment_id}, '${escapeHtml(assignment.title)}', '${escapeHtml(assignment.description)}', '${assignment.due_date}', true, '${escapeHtml(submittedFilePath)}')">
                                            <i class="fas fa-eye me-1"></i>Lihat Submission
                                        </button>
                                    ` : `
                                        <span class="badge ${isOverdue ? 'bg-danger' : 'bg-warning text-dark'} me-2">${isOverdue ? 'Terlewat' : 'Belum Disubmit'}</span>
                                        <button class="btn-action btn-primary-custom" onclick="openUploadModal(${assignment.assignment_id}, '${escapeHtml(assignment.title)}', '${escapeHtml(assignment.description)}', '${assignment.due_date}', false, '')">
                                            <i class="fas fa-upload me-1"></i>Submit
                                        </button>
                                    `}
                                </div>
                            </div>
                        </div>
                    `;
                    deadlineContainer.appendChild(colDiv);
                });
            } else {
                emptyState.style.display = 'block';
            }
        }
        
        // Fungsi untuk mencari tugas berdasarkan nama mata kuliah
        function searchAssignments() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const filteredAssignments = allAssignments.filter(assignment => {
                return (assignment.course_name && assignment.course_name.toLowerCase().includes(searchInput));
            });
            displayDeadlines(filteredAssignments);
        }

        // --- Fungsi Modal Upload dan Submit ---
        let currentAssignmentId = null;
        let currentSubmittedFilePath = null;

        function openUploadModal(assignmentId, title, description, deadline, isSubmitted, submittedFilePath) {
            currentAssignmentId = assignmentId;
            currentSubmittedFilePath = submittedFilePath;

            document.getElementById('modalTitle').textContent = `Upload Tugas: ${title}`;
            document.getElementById('taskTitle').textContent = title;
            document.getElementById('taskDesc').textContent = description;
            document.getElementById('taskDeadline').textContent = formatDateTime(deadline);

            const taskStatusModal = document.getElementById('taskStatusModal');
            const submissionInfoDiv = document.getElementById('submissionInfo');
            const uploadZoneDiv = document.getElementById('uploadZone');
            const submitButton = document.getElementById('submitButton');

            if (isSubmitted) {
                taskStatusModal.textContent = 'Sudah Disubmit';
                submissionInfoDiv.style.display = 'block';
                document.getElementById('submittedFile').textContent = submittedFilePath;
                uploadZoneDiv.style.display = 'none';
                submitButton.style.display = 'none';
            } else {
                const now = new Date();
                const dueDate = new Date(deadline);
                taskStatusModal.textContent = now > dueDate ? 'Terlewat' : 'Belum Disubmit';

                submissionInfoDiv.style.display = 'none';
                uploadZoneDiv.style.display = 'block';
                submitButton.style.display = 'block';
                document.getElementById('fileList').innerHTML = '';
                document.getElementById('fileInput').value = '';
            }

            document.getElementById('uploadModal').style.display = 'flex';
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').style.display = 'none';
        }

        function viewSubmission() {
            if (currentSubmittedFilePath && currentSubmittedFilePath !== 'Tidak ada file') {
                const fullUrl = `http://localhost/elearning/${currentSubmittedFilePath}`;
                window.open(fullUrl, '_blank');
            } else {
                alert('Tidak ada file submission untuk ditampilkan atau diunduh.');
            }
        }

        function downloadLecturerAssignment(filePath, title) {
            if (filePath) {
                const fullUrl = `http://localhost/elearning/${filePath}`;
                window.open(fullUrl, '_blank');
            } else {
                alert(`File tugas "${title}" tidak tersedia.`);
            }
        }

        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');

        uploadZone.addEventListener('click', () => fileInput.click());
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });
        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            handleFiles(files);
        });
        fileInput.addEventListener('change', () => {
            handleFiles(fileInput.files);
        });

        function handleFiles(files) {
            fileList.innerHTML = '';
            if (files.length > 0) {
                const file = files[0];
                const fileItem = document.createElement('p');
                fileItem.textContent = `${file.name} (${(file.size / 1024).toFixed(2)} KB)`;
                fileList.appendChild(fileItem);
            }
        }

        async function submitFiles() {
            const files = fileInput.files;
            if (files.length === 0) {
                alert('Pilih file terlebih dahulu!');
                return;
            }

            const fileToUpload = files[0];
            const formData = new FormData();
            formData.append('assignment_id', currentAssignmentId);
            formData.append('student_id', currentStudentId);
            formData.append('file_submission', fileToUpload);

            try {
                const response = await fetch('api/submit_assignment.php', {
                    method: 'POST',
                    body: formData
                });
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error("API submit_assignment.php error response:", errorText);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const result = await response.json();
                if (result.success) {
                    alert('Tugas berhasil disubmit!');
                    fetchAndDisplayAssignments();
                    closeUploadModal();
                } else {
                    alert(`Gagal submit tugas: ${result.error || 'Terjadi kesalahan.'}`);
                }
            } catch (error) {
                console.error("Error submitting files:", error);
                alert('Terjadi kesalahan saat mengupload file. Silakan coba lagi.');
            }
        }

        async function confirmUnsubmit() {
            if (confirm('Apakah Anda yakin ingin membatalkan submit tugas ini? Data submission akan dihapus.')) {
                try {
                    const response = await fetch(`api/submit_assignment.php?action=delete&assignment_id=${currentAssignmentId}&student_id=${currentStudentId}`, {
                        method: 'GET'
                    });
                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error("API delete submission error response:", errorText);
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const result = await response.json();
                    if (result.success) {
                        alert('Submit tugas berhasil dibatalkan!');
                        fetchAndDisplayAssignments();
                        closeUploadModal();
                    } else {
                        alert(`Gagal membatalkan submit: ${result.error || 'Terjadi kesalahan.'}`);
                    }
                } catch (error) {
                    console.error("Error unsubmiting task:", error);
                    alert('Terjadi kesalahan saat membatalkan submit. Silakan coba lagi.');
                }
            }
        }

        function formatDateTime(datetimeString) {
            const date = new Date(datetimeString);
            if (isNaN(date.getTime())) {
                return datetimeString;
            }
            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

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

        function refreshPage() {
            window.location.reload();
        }

        async function fetchAndDisplayAssignments() {
            const loadingDeadlines = document.getElementById('loadingDeadlines');
            loadingDeadlines.style.display = 'block';

            const assignments = await fetchAllAssignments();
            
            const assignmentsWithStatus = await Promise.all(assignments.map(async (assignment) => {
                assignment.submission_status = await fetchSubmissionStatus(assignment.assignment_id, currentStudentId);
                return assignment;
            }));

            allAssignments = assignmentsWithStatus;
            
            displayDeadlines();
            loadingDeadlines.style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            fetchAndDisplayAssignments();
            const fadeElements = document.querySelectorAll('.fade-in');
            fadeElements.forEach((element, index) => {
                element.style.animationDelay = `${index * 0.1}s`;
            });

            document.getElementById('searchInput').addEventListener('input', searchAssignments);
        });
    </script>
</body>
</html>