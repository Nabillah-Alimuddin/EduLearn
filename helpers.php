<?php
// helpers.php

/**
 * Mengkonversi nilai angka menjadi huruf dan poin.
 * Fungsi ini mengelompokkan logika perhitungan grade.
 *
 * @param float|string|null $score Nilai angka.
 * @return array Asosiatif dengan 'grade_letter' dan 'grade_points'.
 */
if (!function_exists('calculate_grade_letter_and_points')) {
    function calculate_grade_letter_and_points($score) {
        $score = is_numeric($score) ? (float)$score : 0;
        $grade_letter = 'E';
        $grade_points = 0.00;

        if ($score >= 80) {
            $grade_letter = 'A';
            $grade_points = 4.00;
        } elseif ($score >= 70) {
            $grade_letter = 'B';
            $grade_points = 3.00;
        } elseif ($score >= 60) {
            $grade_letter = 'C';
            $grade_points = 2.00;
        } elseif ($score >= 50) {
            $grade_letter = 'D';
            $grade_points = 1.00;
        }

        return [
            'grade_letter' => $grade_letter,
            'grade_points' => $grade_points
        ];
    }
}

/**
 * Mendapatkan huruf nilai dari nilai angka.
 *
 * @param float|string|null $score Nilai angka.
 * @return string Huruf nilai.
 */
if (!function_exists('getGradeLetterPHP')) {
    function getGradeLetterPHP($score) {
        if ($score === null || $score === '') {
            return '-';
        }
        $result = calculate_grade_letter_and_points($score);
        return $result['grade_letter'];
    }
}

/**
 * Mendapatkan poin nilai dari huruf nilai.
 *
 * @param string $grade_letter Huruf nilai (e.g., 'A', 'B').
 * @return float Poin nilai.
 */
if (!function_exists('getGradePointsPHP')) {
    function getGradePointsPHP($grade_letter) {
        switch (strtoupper($grade_letter)) {
            case 'A':
                return 4.00;
            case 'B':
                return 3.00;
            case 'C':
                return 2.00;
            case 'D':
                return 1.00;
            case 'E':
                return 0.00;
            default:
                return 0.00;
        }
    }
}

/**
 * Menentukan status pengumpulan tugas dalam bentuk teks.
 *
 * @param string|null $submitted_at Waktu pengumpulan.
 * @param string $due_date Waktu jatuh tempo.
 * @return string Teks status ('Sudah', 'Terlambat', 'Belum').
 */
if (!function_exists('getSubmissionStatusTextPHP')) {
    function getSubmissionStatusTextPHP($submitted_at, $due_date) {
        if ($submitted_at !== null) {
            try {
                $submitted_datetime = new DateTime($submitted_at);
                $due_datetime = new DateTime($due_date);
                return ($submitted_datetime <= $due_datetime) ? 'Sudah' : 'Terlambat';
            } catch (Exception $e) {
                return 'Tidak Valid';
            }
        }
        return 'Belum';
    }
}

/**
 * Mendapatkan kelas CSS untuk badge status pengumpulan.
 *
 * @param string|null $submitted_at Waktu pengumpulan.
 * @param string $due_date Waktu jatuh tempo.
 * @return string Kelas CSS untuk badge.
 */
if (!function_exists('getStatusBadgeClassPHP')) {
    function getStatusBadgeClassPHP($submitted_at, $due_date) {
        if ($submitted_at !== null) {
            try {
                $submitted_datetime = new DateTime($submitted_at);
                $due_datetime = new DateTime($due_date);
                return ($submitted_datetime <= $due_datetime) ? 'status-graded' : 'status-pending';
            } catch (Exception $e) {
                return 'status-not-submitted';
            }
        }
        return 'status-not-submitted';
    }
}