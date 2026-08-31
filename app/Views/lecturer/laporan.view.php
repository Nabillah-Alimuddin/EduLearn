<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekapitulasi Nilai - EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header { background: linear-gradient(135deg, #63A3F1, #4F8A9E); color: white; padding: 2rem 0; border-radius: 0 0 20px 20px; }
        .card-custom { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .stat-card { border-radius: 12px; padding: 1.25rem; color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .stat-card h3 { font-size: 1.8rem; font-weight: 700; margin-bottom: 0; }
    </style>
</head>
<body>
    <div class="header mb-4">
        <div class="container">
            <a href="index.php?url=lecturer/dashboard" class="btn btn-light btn-sm rounded-pill mb-2"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
            <h2><i class="fas fa-file-excel me-2"></i>Laporan Rekapitulasi Nilai</h2>
            <p class="mb-0 text-white-50">Laporan evaluasi hasil belajar mahasiswa dan rekapitulasi nilai akhir kelas.</p>
        </div>
    </div>

    <div class="container">
        <!-- Filter Card & Export Button -->
        <div class="card card-custom p-4 mb-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <label class="form-label font-weight-bold"><i class="fas fa-book me-1 text-primary"></i>Pilih Mata Kuliah</label>
                    <select class="form-select" onchange="location = this.value;">
                        <?php foreach ($courses as $c): ?>
                            <option value="index.php?url=lecturer/laporan&course_id=<?= $c['course_id']; ?>" <?= ($selectedCourseId == $c['course_id']) ? 'selected' : ''; ?>>
                                <?= html_escape($c['course_name']); ?> (<?= html_escape($c['course_code']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <?php if ($selectedCourseId): ?>
                        <a href="index.php?url=grade/exportExcel&course_id=<?= $selectedCourseId; ?>" class="btn btn-success rounded-pill px-4">
                            <i class="fas fa-file-excel me-1"></i> Ekspor ke Excel (.csv)
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($selectedCourseId && !empty($students)): 
            $totalStudents = count($students);
            $sumFinalScores = 0;
            $passedCount = 0;
            $failedCount = 0;

            $recapData = [];
            foreach ($students as $s) {
                $sId = $s['user_id'];
                $t  = $gradesMap[$sId]['Assignment'] ?? 0;
                $u  = $gradesMap[$sId]['UTS'] ?? 0;
                $ua = $gradesMap[$sId]['UAS'] ?? 0;
                $p  = $gradesMap[$sId]['Partisipasi'] ?? 0;

                $finalScore = ($t * 0.20) + ($u * 0.30) + ($ua * 0.40) + ($p * 0.10);
                $sumFinalScores += $finalScore;

                $letter = getGradeLetterPHP($finalScore);
                $isPassed = ($finalScore >= 50);

                if ($isPassed) $passedCount++; else $failedCount++;

                $recapData[] = [
                    'nim'        => $s['nim'],
                    'full_name'  => $s['full_name'],
                    'tugas'      => $t,
                    'uts'        => $u,
                    'uas'        => $ua,
                    'partisipasi'=> $p,
                    'final_score'=> round($finalScore, 2),
                    'letter'     => $letter,
                    'is_passed'  => $isPassed
                ];
            }

            $avgScore = $totalStudents > 0 ? round($sumFinalScores / $totalStudents, 2) : 0;
            $passRate = $totalStudents > 0 ? round(($passedCount / $totalStudents) * 100, 1) : 0;
        ?>
            <!-- Stats Overview -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card bg-primary">
                        <small class="text-white-50">Total Mahasiswa</small>
                        <h3><?= $totalStudents; ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-info">
                        <small class="text-white-50">Rata-Rata Kelas</small>
                        <h3><?= $avgScore; ?></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-success">
                        <small class="text-white-50">Jumlah Lulus</small>
                        <h3><?= $passedCount; ?> <small class="fs-6">(<?= $passRate; ?>%)</small></h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-danger">
                        <small class="text-white-50">Tidak Lulus</small>
                        <h3><?= $failedCount; ?></h3>
                    </div>
                </div>
            </div>

            <!-- Table Recap -->
            <div class="card card-custom p-4">
                <h5 class="mb-3"><i class="fas fa-table me-2 text-primary"></i>Rekapitulasi Nilai Akhir Mahasiswa</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 40px;">No</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Tugas (20%)</th>
                                <th>UTS (30%)</th>
                                <th>UAS (40%)</th>
                                <th>Partisipasi (10%)</th>
                                <th>Nilai Akhir</th>
                                <th>Grade</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $rNo = 1; foreach ($recapData as $rd): ?>
                                <tr>
                                    <td class="text-center"><?= $rNo++; ?></td>
                                    <td><?= html_escape($rd['nim'] ?? '-'); ?></td>
                                    <td><strong><?= html_escape($rd['full_name']); ?></strong></td>
                                    <td class="text-center"><?= $rd['tugas']; ?></td>
                                    <td class="text-center"><?= $rd['uts']; ?></td>
                                    <td class="text-center"><?= $rd['uas']; ?></td>
                                    <td class="text-center"><?= $rd['partisipasi']; ?></td>
                                    <td class="text-center font-weight-bold text-primary"><?= $rd['final_score']; ?></td>
                                    <td class="text-center font-weight-bold"><?= $rd['letter']; ?></td>
                                    <td class="text-center">
                                        <?php if ($rd['is_passed']): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> LULUS</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> TIDAK LULUS</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="card card-custom p-5 text-center text-muted">
                <i class="fas fa-info-circle fa-3x mb-3 text-primary"></i>
                <h5>Pilih Mata Kuliah untuk Melihat Laporan</h5>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
