<?php
include 'middleware.php';
include 'db_connection.php';
require_role('student');
$current_student_id = $_SESSION['user_id'];

// Ambil semua data profil mahasiswa
$sql_profile = "SELECT * FROM users WHERE user_id = ? AND role = 'student'";
$stmt_profile = $conn->prepare($sql_profile);
$profile_data = null;

if ($stmt_profile) {
    $stmt_profile->execute([$current_student_id]);
    $profile_data = $stmt_profile->fetch();
}

$conn = null;

function html_escape($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Mahasiswa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS yang sudah ada */
        :root {
            --primary-color: #B6D0EF;
            --secondary-color: #75A2D7;
            --white-color: #FFFFFF;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--white-color) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            background: var(--white-color);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(181, 208, 239, 0.3);
            margin: 20px auto;
            overflow: hidden;
        }
        
        .header-section {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .profile-section {
            background: var(--primary-color);
            padding: 30px;
            text-align: center;
            color: #2c3e50;
        }
        
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid var(--white-color);
            object-fit: cover;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .btn-custom {
            background: var(--secondary-color);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            background: #5a8bc4;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(117, 162, 215, 0.4);
            color: white;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(117, 162, 215, 0.25);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .card-header {
            background: var(--primary-color);
            border-radius: 15px 15px 0 0 !important;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .table-container {
            background: var(--white-color);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .table th {
            background: var(--secondary-color);
            color: white;
            border: none;
            font-weight: 500;
        }
        
        .table td {
            border-color: #e9ecef;
            vertical-align: middle;
        }
        
        .modal-header {
            background: var(--primary-color);
            color: #2c3e50;
            border-bottom: none;
        }
        
        .alert-custom {
            background-color: #d4edda;
            border-color: var(--secondary-color);
            color: #155724;
            border-radius: 10px;
        }
        
        .nav-tabs .nav-link {
            color: var(--secondary-color);
            border: none;
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            background-color: var(--secondary-color);
            color: white;
            border-radius: 10px 10px 0 0;
        }
        
        .tab-content {
            background: var(--white-color);
            padding: 25px;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="main-container">
            <div class="header-section">
                <h1><i class="fas fa-user-graduate me-3"></i>Sistem Biodata Mahasiswa</h1>
                <p class="mb-0">Kelola Data Mahasiswa dengan Mudah</p>
            </div>
            
            <div class="profile-section">
                <img id="profileImage" src="<?php echo html_escape($profile_data['profile_picture_url'] ?? 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUwIiBoZWlnaHQ9IjE1MCIgdmlld0JveD0iMCAwIDE1MCAxNTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxNTAiIGhlaWdodD0iMTUwIiBmaWxsPSIjRTVFNUU1Ii8+CjxwYXRoIGQ9Ik03NSA3NUMzOCA3NSA4IDQ1IDggNDVTMzggMTUgNzUgMTVTMTQyIDQ1IDE0MiA0NVMxMTIgNzUgNzUgNzVaIiBmaWxsPSIjOTk5Ii8+CjxwYXRoIGQ9Ik03NSA5MEMxMDUgOTAgMTMwIDExNSAxMzAgMTQ1SDE1MFYxNTBIMEgwVjE0NUMwIDExNSAyNSA5MCA3NSA5MFoiIGZpbGw9IiM5OTkiLz4KPC9zdmc+'); ?>" alt="Profile" class="profile-img">
                <h3 id="displayName"><?php echo html_escape($profile_data['full_name'] ?? 'Nama Mahasiswa'); ?></h3>
                <p id="displayNim" class="mb-3">NIM: <?php echo html_escape($profile_data['nim'] ?? '-'); ?></p>
                <button class="btn btn-custom me-2" onclick="openEditModal()">
                    <i class="fas fa-edit me-2"></i>Edit Biodata
                </button>
                <button class="btn btn-custom" onclick="openPasswordModal()">
                    <i class="fas fa-key me-2"></i>Ubah Password
                </button>
            </div>
            
            <div class="container py-4">
                <ul class="nav nav-tabs mb-4" id="mainTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="biodata-tab" data-bs-toggle="tab" data-bs-target="#biodata" type="button" role="tab" aria-controls="biodata" aria-selected="true">
                            <i class="fas fa-user me-2"></i>Biodata
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="mainTabContent">
                    <div class="tab-pane fade show active" id="biodata" role="tabpanel" aria-labelledby="biodata-tab">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>NIM:</strong>
                                <p id="biodataNim"><?php echo html_escape($profile_data['nim'] ?? '-'); ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>NIK:</strong>
                                <p id="biodataNik"><?php echo html_escape($profile_data['nik'] ?? '-'); ?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Nama Lengkap:</strong>
                                <p id="biodataNama"><?php echo html_escape($profile_data['full_name'] ?? '-'); ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Jenis Kelamin:</strong>
                                <p id="biodataGender"><?php echo html_escape($profile_data['gender'] ?? '-'); ?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Program Studi:</strong>
                                <p id="biodataProdi"><?php echo html_escape($profile_data['study_program'] ?? '-'); ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Agama:</strong>
                                <p id="biodataAgama"><?php echo html_escape($profile_data['religion'] ?? '-'); ?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Kewarganegaraan:</strong>
                                <p id="biodataKewarganegaraan"><?php echo html_escape($profile_data['nationality'] ?? '-'); ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Tempat Lahir:</strong>
                                <p id="biodataTempatLahir"><?php echo html_escape($profile_data['place_of_birth'] ?? '-'); ?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Tanggal Lahir:</strong>
                                <p id="biodataTanggalLahir"><?php echo html_escape($profile_data['date_of_birth'] ?? '-'); ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Nomor Telepon:</strong>
                                <p id="biodataTelepon"><?php echo html_escape($profile_data['phone_number'] ?? '-'); ?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Nama SMA/SMK/Univ Asal:</strong>
                                <p id="biodataSekolahAsal"><?php echo html_escape($profile_data['previous_school'] ?? '-'); ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>NISN:</strong>
                                <p id="biodataNisn"><?php echo html_escape($profile_data['nisn'] ?? '-'); ?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Kota Asal Sekolah:</strong>
                                <p id="biodataKotaSekolah"><?php echo html_escape($profile_data['school_city'] ?? '-'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel"><i class="fas fa-edit me-2"></i>Edit Biodata Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="biodataForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nimInput" class="form-label">NIM *</label>
                                <input type="text" class="form-control" id="nimInput" required>
                            </div>
                            <div class="col-md-6">
                                <label for="nikInput" class="form-label">NIK *</label>
                                <input type="text" class="form-control" id="nikInput" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="namaLengkapInput" class="form-label">Nama Lengkap *</label>
                                <input type="text" class="form-control" id="namaLengkapInput" required>
                            </div>
                            <div class="col-md-6">
                                <label for="jenisKelaminInput" class="form-label">Jenis Kelamin *</label>
                                <select class="form-control" id="jenisKelaminInput" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="programStudiInput" class="form-label">Program Studi *</label>
                                <input type="text" class="form-control" id="programStudiInput" required>
                            </div>
                            <div class="col-md-6">
                                <label for="agamaInput" class="form-label">Agama *</label>
                                <select class="form-control" id="agamaInput" required>
                                    <option value="">Pilih Agama</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen">Kristen</option>
                                    <option value="Katolik">Katolik</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Konghucu">Konghucu</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="kewarganegaraanInput" class="form-label">Kewarganegaraan *</label>
                                <input type="text" class="form-control" id="kewarganegaraanInput" value="Indonesia" required>
                            </div>
                            <div class="col-md-6">
                                <label for="tempatLahirInput" class="form-label">Tempat Lahir *</label>
                                <input type="text" class="form-control" id="tempatLahirInput" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tanggalLahirInput" class="form-label">Tanggal Lahir *</label>
                                <input type="date" class="form-control" id="tanggalLahirInput" required>
                            </div>
                            <div class="col-md-6">
                                <label for="nomorTeleponInput" class="form-label">Nomor Telepon *</label>
                                <input type="tel" class="form-control" id="nomorTeleponInput" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="sekolahAsalInput" class="form-label">Nama SMA/SMK/Univ Asal *</label>
                                <input type="text" class="form-control" id="sekolahAsalInput" required>
                            </div>
                            <div class="col-md-6">
                                <label for="nisnInput" class="form-label">NISN *</label>
                                <input type="text" class="form-control" id="nisnInput" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="kotaSekolahInput" class="form-label">Kota SMA/SMK/Univ Asal *</label>
                                <input type="text" class="form-control" id="kotaSekolahInput" required>
                            </div>
                            <div class="col-md-6">
                                <label for="fotoProfilInput" class="form-label">Foto Profil</label>
                                <input type="file" class="form-control" id="fotoProfilInput" accept="image/*">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-custom" onclick="saveBiodata()">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordModalLabel"><i class="fas fa-key me-2"></i>Ubah Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="passwordForm">
                        <div class="mb-3">
                            <label for="oldPassword" class="form-label">Password Lama *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="oldPassword" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('oldPassword')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">Password Baru *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="newPassword" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('newPassword')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Password minimal 6 karakter</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Konfirmasi Password Baru *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirmPassword" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmPassword')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-custom" onclick="changePassword()">Ubah Password</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
        <div id="statusAlert" class="alert alert-custom alert-dismissible fade" role="alert">
            <i class="fas fa-check-circle me-2" id="alertIcon"></i>
            <span id="alertMessage"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Data PHP yang di-generate dari server
        const studentData = <?php echo json_encode($profile_data); ?>;
        const currentStudentId = <?php echo json_encode($current_student_id); ?>;

        // Fungsi untuk membuka modal edit
        function openEditModal() {
            // Isi form dengan data yang ada
            if (studentData) {
                document.getElementById('nimInput').value = studentData.nim || '';
                document.getElementById('nikInput').value = studentData.nik || '';
                document.getElementById('namaLengkapInput').value = studentData.full_name || '';
                document.getElementById('jenisKelaminInput').value = studentData.gender || '';
                document.getElementById('programStudiInput').value = studentData.study_program || '';
                document.getElementById('agamaInput').value = studentData.religion || '';
                document.getElementById('kewarganegaraanInput').value = studentData.nationality || 'Indonesia';
                document.getElementById('tempatLahirInput').value = studentData.place_of_birth || '';
                document.getElementById('tanggalLahirInput').value = studentData.date_of_birth || '';
                document.getElementById('nomorTeleponInput').value = studentData.phone_number || '';
                document.getElementById('sekolahAsalInput').value = studentData.previous_school || '';
                document.getElementById('nisnInput').value = studentData.nisn || '';
                document.getElementById('kotaSekolahInput').value = studentData.school_city || '';
            }
            
            const modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        }
        
        // Fungsi untuk menyimpan biodata
        async function saveBiodata() {
            const form = document.getElementById('biodataForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            const formData = new FormData();
            formData.append('user_id', currentStudentId);
            formData.append('nim', document.getElementById('nimInput').value);
            formData.append('nik', document.getElementById('nikInput').value);
            formData.append('full_name', document.getElementById('namaLengkapInput').value);
            formData.append('gender', document.getElementById('jenisKelaminInput').value);
            formData.append('study_program', document.getElementById('programStudiInput').value);
            formData.append('religion', document.getElementById('agamaInput').value);
            formData.append('nationality', document.getElementById('kewarganegaraanInput').value);
            formData.append('place_of_birth', document.getElementById('tempatLahirInput').value);
            formData.append('date_of_birth', document.getElementById('tanggalLahirInput').value);
            formData.append('phone_number', document.getElementById('nomorTeleponInput').value);
            formData.append('previous_school', document.getElementById('sekolahAsalInput').value);
            formData.append('nisn', document.getElementById('nisnInput').value);
            formData.append('school_city', document.getElementById('kotaSekolahInput').value);
            
            const fotoInput = document.getElementById('fotoProfilInput');
            if (fotoInput.files && fotoInput.files[0]) {
                formData.append('profile_picture', fotoInput.files[0]);
            }

            try {
                const response = await fetch('update_profile.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    showAlert('Data biodata berhasil disimpan!', 'success');
                    // Reload halaman untuk melihat perubahan
                    window.location.reload(); 
                } else {
                    showAlert(`Gagal menyimpan biodata: ${result.error || 'Terjadi kesalahan.'}`, 'danger');
                }
            } catch (error) {
                console.error("Error saving biodata:", error);
                showAlert('Terjadi kesalahan saat menyimpan biodata. Silakan coba lagi.', 'danger');
            }
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
            modal.hide();
        }
        
        // Fungsi untuk membuka modal password
        function openPasswordModal() {
            document.getElementById('passwordForm').reset();
            const modal = new bootstrap.Modal(document.getElementById('passwordModal'));
            modal.show();
        }
        
        // Fungsi untuk toggle visibility password
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Fungsi untuk mengubah password
        async function changePassword() {
            const oldPassword = document.getElementById('oldPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (!oldPassword || !newPassword || !confirmPassword) {
                showAlert('Semua field password harus diisi!', 'warning');
                return;
            }
            
            if (newPassword.length < 6) {
                showAlert('Password baru minimal 6 karakter!', 'warning');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showAlert('Konfirmasi password tidak sesuai!', 'warning');
                return;
            }

            try {
                const response = await fetch('change_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        user_id: currentStudentId,
                        old_password: oldPassword,
                        new_password: newPassword
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    showAlert('Password berhasil diubah!', 'success');
                } else {
                    showAlert(`Gagal mengubah password: ${result.error || 'Password lama tidak sesuai atau terjadi kesalahan.'}`, 'danger');
                }
            } catch (error) {
                console.error("Error changing password:", error);
                showAlert('Terjadi kesalahan saat mengubah password. Silakan coba lagi.', 'danger');
            }
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('passwordModal'));
            modal.hide();
        }
        
        // Fungsi untuk menampilkan alert
        function showAlert(message, type) {
            const alertDiv = document.getElementById('statusAlert');
            const alertMessage = document.getElementById('alertMessage');
            const alertIcon = document.getElementById('alertIcon');

            alertDiv.className = 'alert alert-dismissible fade position-fixed top-0 end-0 p-3';
            alertIcon.className = 'fas me-2';

            if (type === 'success') {
                alertDiv.classList.add('alert-custom');
                alertIcon.classList.add('fa-check-circle');
            } else if (type === 'danger') {
                alertDiv.classList.add('alert-danger');
                alertIcon.classList.add('fa-times-circle');
            } else if (type === 'warning') {
                alertDiv.classList.add('alert-warning');
                alertIcon.classList.add('fa-exclamation-triangle');
            }

            alertMessage.textContent = message;
            alertDiv.classList.add('show');
            
            setTimeout(() => {
                alertDiv.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html>