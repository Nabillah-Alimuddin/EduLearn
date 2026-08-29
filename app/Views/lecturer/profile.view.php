<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Dosen - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #63A3F1, #4F8A9E); color: white; padding: 2rem 0; border-radius: 0 0 20px 20px; }
        .card-custom { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <div class="header mb-4">
        <div class="container">
            <a href="index.php?url=lecturer/dashboard" class="btn btn-light btn-sm rounded-pill mb-2"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            <h2><i class="fas fa-user-tie me-2"></i>Profil Pengajar</h2>
        </div>
    </div>

    <div class="container max-width-800">
        <div class="card card-custom p-4 mb-4 text-center">
            <h3><?= html_escape(($profileData['full_name'] ?? 'Dosen') . ($profileData['gelar'] ? ', ' . $profileData['gelar'] : '')); ?></h3>
            <p class="text-muted mb-1"><strong>NIP/NIK:</strong> <?= html_escape($profileData['nik'] ?? '-'); ?></p>
            <p class="text-muted mb-3"><strong>Program Studi:</strong> <?= html_escape($profileData['study_program'] ?? '-'); ?></p>
            <div>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#editModal"><i class="fas fa-edit me-1"></i> Edit Profil</button>
            </div>
        </div>

        <div class="card card-custom p-4">
            <h5 class="border-bottom pb-2 mb-3">Detail Informasi Dosen</h5>
            <div class="row g-3">
                <div class="col-md-6"><strong>Email:</strong> <?= html_escape($profileData['email'] ?? '-'); ?></div>
                <div class="col-md-6"><strong>Telepon:</strong> <?= html_escape($profileData['phone_number'] ?? '-'); ?></div>
                <div class="col-md-6"><strong>Jabatan Akademik:</strong> <?= html_escape($profileData['jabatan_akademik'] ?? '-'); ?></div>
                <div class="col-md-6"><strong>Bidang Keahlian:</strong> <?= html_escape($profileData['bidang_keahlian'] ?? '-'); ?></div>
                <div class="col-md-6"><strong>Ruang Kerja:</strong> <?= html_escape($profileData['ruang_kerja'] ?? '-'); ?></div>
                <div class="col-md-6"><strong>Jam Konsultasi:</strong> <?= html_escape($profileData['jam_konsultasi'] ?? '-'); ?></div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="index.php?url=lecturer/profile" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Profil Dosen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-md-6"><label>Nama Lengkap</label><input type="text" class="form-control" name="nama" value="<?= html_escape($profileData['full_name'] ?? ''); ?>" required></div>
                        <div class="col-md-6"><label>Gelar</label><input type="text" class="form-control" name="gelar" value="<?= html_escape($profileData['gelar'] ?? ''); ?>"></div>
                        <div class="col-md-6"><label>NIP/NIK</label><input type="text" class="form-control" name="nip" value="<?= html_escape($profileData['nik'] ?? ''); ?>"></div>
                        <div class="col-md-6"><label>Email</label><input type="email" class="form-control" name="email" value="<?= html_escape($profileData['email'] ?? ''); ?>" required></div>
                        <div class="col-md-6"><label>Program Studi</label><input type="text" class="form-control" name="prodi" value="<?= html_escape($profileData['study_program'] ?? ''); ?>"></div>
                        <div class="col-md-6"><label>Jabatan Akademik</label><input type="text" class="form-control" name="jabatan" value="<?= html_escape($profileData['jabatan_akademik'] ?? ''); ?>"></div>
                        <div class="col-md-6"><label>Ruang Kerja</label><input type="text" class="form-control" name="ruang" value="<?= html_escape($profileData['ruang_kerja'] ?? ''); ?>"></div>
                        <div class="col-md-6"><label>Jam Konsultasi</label><input type="text" class="form-control" name="jamKonsul" value="<?= html_escape($profileData['jam_konsultasi'] ?? ''); ?>"></div>
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
