<?php
// app/Helpers/functions.php — Global Helper Functions for EduLearn

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

if (!function_exists('getGradeLetterPHP')) {
    function getGradeLetterPHP($score) {
        if ($score === null || $score === '') {
            return '-';
        }
        $result = calculate_grade_letter_and_points($score);
        return $result['grade_letter'];
    }
}

if (!function_exists('getGradePointsPHP')) {
    function getGradePointsPHP($grade_letter) {
        switch (strtoupper($grade_letter)) {
            case 'A': return 4.00;
            case 'B': return 3.00;
            case 'C': return 2.00;
            case 'D': return 1.00;
            case 'E': return 0.00;
            default: return 0.00;
        }
    }
}

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

if (!function_exists('getCourseIcon')) {
    function getCourseIcon($courseName) {
        if (strpos($courseName, 'Aljabar Linear') !== false) return 'fas fa-square-root-variable';
        if (strpos($courseName, 'Pemrograman Web') !== false) return 'fas fa-globe';
        if (strpos($courseName, 'Analisis Desain') !== false) return 'fas fa-drafting-compass';
        if (strpos($courseName, 'Multimedia') !== false) return 'fas fa-photo-video';
        if (strpos($courseName, 'Big Data') !== false) return 'fas fa-database';
        if (strpos($courseName, 'Kecerdasan Buatan') !== false) return 'fas fa-brain';
        if (strpos($courseName, 'Basis Data') !== false) return 'fas fa-server';
        if (strpos($courseName, 'Mikrokontroler') !== false) return 'fas fa-microchip';
        if (strpos($courseName, 'Pemrograman Berbasis Objek') !== false) return 'fas fa-laptop-code';
        if (strpos($courseName, 'Jaringan Komputer') !== false) return 'fas fa-network-wired';
        if (strpos($courseName, 'Pengembangan Aplikasi Mobile') !== false) return 'fas fa-mobile-alt';
        return 'fas fa-book';
    }
}

if (!function_exists('getFileIcon')) {
    function getFileIcon($fileType) {
        if (!$fileType) return 'fas fa-file';
        $fileType = strtolower($fileType);
        if (strpos($fileType, 'pdf') !== false) return 'fas fa-file-pdf';
        if (strpos($fileType, 'doc') !== false || strpos($fileType, 'docx') !== false) return 'fas fa-file-word';
        if (strpos($fileType, 'ppt') !== false || strpos($fileType, 'pptx') !== false) return 'fas fa-file-powerpoint';
        if (strpos($fileType, 'xls') !== false || strpos($fileType, 'xlsx') !== false) return 'fas fa-file-excel';
        if (strpos($fileType, 'zip') !== false || strpos($fileType, 'rar') !== false) return 'fas fa-file-archive';
        if (strpos($fileType, 'jpg') !== false || strpos($fileType, 'jpeg') !== false || strpos($fileType, 'png') !== false || strpos($fileType, 'gif') !== false) return 'fas fa-file-image';
        if (strpos($fileType, 'txt') !== false) return 'fas fa-file-alt';
        return 'fas fa-file';
    }
}

if (!function_exists('formatDateDisplay')) {
    function formatDateDisplay($dateString) {
        if (empty($dateString) || $dateString === '0000-00-00 00:00:00' || $dateString === '0000-00-00') {
            return '-';
        }
        return date('d F Y H:i', strtotime($dateString));
    }
}

if (!function_exists('html_escape')) {
    function html_escape($text) {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }
}
