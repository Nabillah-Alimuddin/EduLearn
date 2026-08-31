<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Nilai & Transkrip - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #4A90E2, #7FB3D3); color: white; padding: 2rem 0; border-radius: 0 0 20px 20px; }
        .gpa-card { background: white; border-radius: 15px; padding: 1.5rem; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border-left: 5px solid #4A90E2; }
        .gpa-number { font-size: 2.8rem; font-weight: 700; color: #4A90E2; line-height: 1; }
        @media print {
            .no-print { display: none !important; }
            .header { background: none; color: black; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="header mb-4 no-print">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <a href="index.php?url=student/dashboard" class="btn btn-light btn-sm rounded-pill mb-2"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
                <h2><i class="fas fa-file-invoice me-2"></i>Kartu Hasil Studi (KHS) & Transkrip</h2>
                <p class="mb-0 text-white-50">Transkrip nilai mahasiswa terintegrasi seluruh mata kuliah yang diambil.</p>
            </div>
            <button onclick="window.print()" class="btn btn-light rounded-pill px-4"><i class="fas fa-print me-1"></i> Cetak KHS</button>
        </div>
    </div>

    <div class="container mb-4">
        <!-- Student Info & Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="gpa-card">
                    <small class="text-muted text-uppercase fw-bold">Indeks Prestasi Kumulatif (IPK)</small>
                    <div class="gpa-number mt-2 mb-1"><?= number_format($ipk, 2); ?></div>
                    <div class="small text-secondary">Skala Maksimum 4.00</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="gpa-card" style="border-left-color: #2ec4b6;">
                    <small class="text-muted text-uppercase fw-bold">Total SKS Kredit Diambil</small>
                    <div class="gpa-number mt-2 mb-1" style="color: #2ec4b6;"><?= $totalSks; ?></div>
                    <div class="small text-secondary">SKS Terakumulasi Semester Ini</div>
                </div>
            </div>
        </div>

        <!-- Grade Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-graduation-cap me-2"></i>Daftar Nilai Mata Kuliah</h5>
                <span class="badge bg-primary rounded-pill"><?= count($gradedCourses); ?> Mata Kuliah</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 40px;">No</th>
                                <th class="text-start">Kode & Mata Kuliah</th>
                                <th>Dosen Pengampu</th>
                                <th>SKS</th>
                                <th>Tugas (20%)</th>
                                <th>UTS (30%)</th>
                                <th>UAS (40%)</th>
                                <th>Partisipasi (10%)</th>
                                <th>Nilai Akhir</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($gradedCourses)): ?>
                                <tr><td colspan="10" class="text-center py-4 text-muted">Belum ada mata kuliah yang diambil.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($gradedCourses as $cId => $c): ?>
                                    <tr>
                                        <td class="text-center font-weight-bold"><?= $no++; ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= html_escape($c['course_name']); ?></div>
                                            <small class="text-muted">Kode: <?= html_escape($c['course_code']); ?></small>
                                        </td>
                                        <td class="small text-secondary"><?= html_escape($c['lecturer_name']); ?></td>
                                        <td class="text-center fw-bold"><?= $c['credits']; ?></td>
                                        <td class="text-center"><?= $c['grades_by_type']['Assignment'] ?? '-'; ?></td>
                                        <td class="text-center"><?= $c['grades_by_type']['UTS'] ?? '-'; ?></td>
                                        <td class="text-center"><?= $c['grades_by_type']['UAS'] ?? '-'; ?></td>
                                        <td class="text-center"><?= $c['grades_by_type']['Partisipasi'] ?? '-'; ?></td>
                                        <td class="text-center font-weight-bold text-primary"><?= $c['final_grade']; ?></td>
                                        <td class="text-center">
                                            <?php 
                                                $gLetter = $c['grade_letter'];
                                                $badgeBg = match($gLetter) {
                                                    'A' => 'bg-success',
                                                    'B' => 'bg-primary',
                                                    'C' => 'bg-warning text-dark',
                                                    'D' => 'bg-danger',
                                                    'E' => 'bg-dark',
                                                    default => 'bg-secondary'
                                                };
                                            ?>
                                            <span class="badge <?= $badgeBg; ?> px-3 py-2 fs-6"><?= $gLetter; ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
