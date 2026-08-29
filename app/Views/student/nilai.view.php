<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transkrip Nilai - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #4A90E2, #7FB3D3); color: white; padding: 2rem 0; border-radius: 0 0 20px 20px; }
        .gpa-card { background: white; border-radius: 15px; padding: 1.5rem; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .gpa-number { font-size: 2.5rem; font-weight: 700; color: #4A90E2; }
    </style>
</head>
<body>
    <div class="header mb-4">
        <div class="container">
            <a href="index.php?url=student/dashboard" class="btn btn-light btn-sm rounded-pill mb-2"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            <h2><i class="fas fa-file-alt me-2"></i>Rekapitulasi Nilai Akademik</h2>
        </div>
    </div>

    <div class="container mb-4">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="gpa-card">
                    <div class="gpa-number"><?= number_format($ipk, 2); ?></div>
                    <div class="text-muted">Indeks Prestasi Kumulatif (IPK)</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="gpa-card">
                    <div class="gpa-number"><?= $totalSks; ?></div>
                    <div class="text-muted">Total SKS Diambil</div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Mata Kuliah</th>
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
                                <tr><td colspan="9" class="text-center py-4 text-muted">Belum ada nilai yang diinputkan.</td></tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($gradedCourses as $cId => $c): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><strong><?= html_escape($c['course_name']); ?></strong></td>
                                        <td><?= $c['credits']; ?></td>
                                        <td><?= $c['grades_by_type']['Assignment'] ?? '-'; ?></td>
                                        <td><?= $c['grades_by_type']['UTS'] ?? '-'; ?></td>
                                        <td><?= $c['grades_by_type']['UAS'] ?? '-'; ?></td>
                                        <td><?= $c['grades_by_type']['Partisipasi'] ?? '-'; ?></td>
                                        <td><strong><?= $c['final_grade']; ?></strong></td>
                                        <td><span class="badge bg-primary px-3 py-2"><?= $c['grade_letter']; ?></span></td>
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
