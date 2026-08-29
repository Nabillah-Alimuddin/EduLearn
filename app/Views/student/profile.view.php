<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Mahasiswa - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
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
            max-width: 900px;
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
            font-weight: 600;
        }
        .btn-custom:hover { background: #5c8fc7; color: white; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="main-container">
            <div class="header-section">
                <a href="index.php?url=student/dashboard" class="btn btn-light btn-sm float-start rounded-pill"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
                <h2><i class="fas fa-user-graduate me-2"></i>Profil Mahasiswa</h2>
            </div>

            <div class="profile-section">
                <img src="<?= html_escape($profileData['profile_picture_url'] ?? 'default_profile.svg'); ?>" alt="Foto Profil" class="profile-img">
                <h3><?= html_escape($profileData['full_name'] ?? 'Mahasiswa'); ?></h3>
                <p class="mb-1"><strong>NIM:</strong> <?= html_escape($profileData['nim'] ?? '-'); ?></p>
                <p class="mb-3"><strong>Program Studi:</strong> <?= html_escape($profileData['study_program'] ?? '-'); ?></p>
                <button type="button" class="btn btn-custom me-2" data-bs-toggle="modal" data-bs-target="#editModal"><i class="fas fa-edit me-1"></i> Edit Profil</button>
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#passwordModal"><i class="fas fa-key me-1"></i> Ubah Password</button>
            </div>

            <div class="p-4">
                <h4 class="border-bottom pb-2 mb-3 text-secondary">Informasi Pribadi</h4>
                <div class="row g-3">
                    <div class="col-md-6"><strong>Email:</strong> <?= html_escape($profileData['email'] ?? '-'); ?></div>
                    <div class="col-md-6"><strong>No. Telepon:</strong> <?= html_escape($profileData['phone_number'] ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Jenis Kelamin:</strong> <?= html_escape($profileData['gender'] ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Agama:</strong> <?= html_escape($profileData['religion'] ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Tempat, Tgl Lahir:</strong> <?= html_escape($profileData['place_of_birth'] ?? '-'); ?>, <?= html_escape($profileData['date_of_birth'] ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Kewarganegaraan:</strong> <?= html_escape($profileData['nationality'] ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Asal Sekolah:</strong> <?= html_escape($profileData['previous_school'] ?? '-'); ?></div>
                    <div class="col-md-6"><strong>NISN:</strong> <?= html_escape($profileData['nisn'] ?? '-'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="index.php?url=student/updateProfile" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Profil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-md-6"><label>Nama Lengkap</label><input type="text" class="form-control" name="full_name" value="<?= html_escape($profileData['full_name'] ?? ''); ?>" required></div>
                        <div class="col-md-6"><label>NIM</label><input type="text" class="form-control" name="nim" value="<?= html_escape($profileData['nim'] ?? ''); ?>" required></div>
                        <div class="col-md-6"><label>Program Studi</label><input type="text" class="form-control" name="study_program" value="<?= html_escape($profileData['study_program'] ?? ''); ?>"></div>
                        <div class="col-md-6"><label>No Telepon</label><input type="text" class="form-control" name="phone_number" value="<?= html_escape($profileData['phone_number'] ?? ''); ?>"></div>
                        <div class="col-md-12"><label>Foto Profil Baru (Opsional)</label><input type="file" class="form-control" name="profile_picture" accept="image/*"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
