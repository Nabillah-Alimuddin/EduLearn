<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Rencana Studi (KRS) - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #63A3F1 0%, #4F8A9E 100%); color: white; padding: 2.2rem 0; border-radius: 0 0 20px 20px; box-shadow: 0 6px 20px rgba(79, 138, 158, 0.2); margin-bottom: 2rem; }
        .card-custom { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="header mb-4">
        <div class="container">
            <a href="index.php?url=student/dashboard" class="btn btn-light btn-sm rounded-pill mb-2"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            <h2><i class="fas fa-list-check me-2"></i>Kartu Rencana Studi (KRS) & Ambil Kelas</h2>
            <p class="mb-0 text-white-50">Kelola pendaftaran mata kuliah perkuliahan Anda untuk semester ini.</p>
        </div>
    </div>

    <div class="container">
        <?php
            $totalSksRencana = 0;
            foreach ($enrolledCourses as $ec) {
                $totalSksRencana += (int)($ec['credits'] ?? 3);
            }
        ?>

        <!-- Summary Banner -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card card-custom p-3 bg-primary text-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50">Mata Kuliah Terdaftar</small>
                            <h3 class="mb-0 fw-bold"><?= count($enrolledCourses); ?> Kelas</h3>
                        </div>
                        <i class="fas fa-book-reader fa-3x text-white-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-custom p-3 bg-success text-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-white-50">Total SKS Diambil</small>
                            <h3 class="mb-0 fw-bold"><?= $totalSksRencana; ?> SKS</h3>
                        </div>
                        <i class="fas fa-graduation-cap fa-3x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Courses Table -->
        <div class="card card-custom p-4">
            <h5 class="mb-3"><i class="fas fa-table me-2 text-primary"></i>Katalog Mata Kuliah Perkuliahan</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th>Kode</th>
                            <th>Nama Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Dosen Pengampu</th>
                            <th>Mahasiswa Terdaftar</th>
                            <th class="text-center">Aksi KRS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $cNo = 1; foreach ($allCourses as $c): ?>
                            <tr class="<?= $c['is_enrolled'] ? 'table-success' : ''; ?>">
                                <td class="text-center fw-bold"><?= $cNo++; ?></td>
                                <td><span class="badge bg-secondary"><?= html_escape($c['course_code']); ?></span></td>
                                <td><strong><?= html_escape($c['course_name']); ?></strong></td>
                                <td><span class="badge bg-info text-dark"><?= $c['credits']; ?> SKS</span></td>
                                <td><?= html_escape($c['lecturer_name'] ?? 'Dosen'); ?><?= !empty($c['gelar']) ? ', ' . html_escape($c['gelar']) : ''; ?></td>
                                <td><i class="fas fa-users me-1 text-muted"></i> <?= $c['total_enrolled']; ?> Mahasiswa</td>
                                <td class="text-center">
                                    <?php if ($c['is_enrolled']): ?>
                                        <span class="badge bg-success px-3 py-2 me-1"><i class="fas fa-check-circle me-1"></i> Terdaftar</span>
                                        <a href="index.php?url=student/dropCourse&course_id=<?= $c['course_id']; ?>" 
                                           onclick="return confirm('Apakah Anda yakin ingin membatalkan pendaftaran mata kuliah ini?')" 
                                           class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1">
                                            Batalkan
                                        </a>
                                    <?php else: ?>
                                        <a href="index.php?url=student/enrollCourse&course_id=<?= $c['course_id']; ?>" 
                                           class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold">
                                            <i class="fas fa-plus me-1"></i> Ambil Kelas
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
