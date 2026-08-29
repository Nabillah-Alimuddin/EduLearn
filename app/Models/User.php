<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById(int $userId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findStudentForLogin(string $input): ?array {
        $sql = "SELECT user_id, full_name, password_hash, role, nim, email FROM users WHERE role = 'student' AND (nim = ? OR email = ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$input, $input]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findStaffForLogin(string $role, string $email): ?array {
        $sql = "SELECT user_id, full_name, password_hash, role, nim, email FROM users WHERE role = ? AND email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$role, $email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getStudentInfo(int $studentId): ?array {
        $sql = "SELECT nim, study_program, full_name, profile_picture_url FROM users WHERE user_id = ? AND role = 'student'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getLecturerInfo(int $lecturerId): ?array {
        $sql = "SELECT full_name, gelar, email, phone_number, ruang_kerja, jam_konsultasi, study_program, jabatan_akademik, bidang_keahlian, status_kepegawaian FROM users WHERE user_id = ? AND role = 'lecturer'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lecturerId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function updateStudentProfile(int $studentId, array $data): bool {
        $sql = "
            UPDATE users SET
            full_name = ?, nim = ?, nik = ?, gender = ?, study_program = ?,
            religion = ?, nationality = ?, place_of_birth = ?, date_of_birth = ?,
            phone_number = ?, previous_school = ?, nisn = ?, school_city = ?
            WHERE user_id = ? AND role = 'student'
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['full_name'] ?? null, $data['nim'] ?? null, $data['nik'] ?? null,
            $data['gender'] ?? null, $data['study_program'] ?? null, $data['religion'] ?? null,
            $data['nationality'] ?? null, $data['place_of_birth'] ?? null, $data['date_of_birth'] ?? null,
            $data['phone_number'] ?? null, $data['previous_school'] ?? null, $data['nisn'] ?? null,
            $data['school_city'] ?? null, $studentId
        ]);
    }

    public function updateLecturerProfile(int $lecturerId, array $data): bool {
        $sql = "
            UPDATE users SET
            full_name = ?, gelar = ?, nik = ?, study_program = ?, jabatan_akademik = ?,
            bidang_keahlian = ?, status_kepegawaian = ?, email = ?, phone_number = ?,
            ruang_kerja = ?, jam_konsultasi = ?, place_of_birth = ?, date_of_birth = ?
            WHERE user_id = ? AND role = 'lecturer'
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['full_name'] ?? null, $data['gelar'] ?? null, $data['nik'] ?? null,
            $data['study_program'] ?? null, $data['jabatan_akademik'] ?? null,
            $data['bidang_keahlian'] ?? null, $data['status_kepegawaian'] ?? null,
            $data['email'] ?? null, $data['phone_number'] ?? null,
            $data['ruang_kerja'] ?? null, $data['jam_konsultasi'] ?? null,
            $data['place_of_birth'] ?? null, $data['date_of_birth'] ?? null,
            $lecturerId
        ]);
    }

    public function updatePassword(int $userId, string $newPasswordHash): bool {
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        return $stmt->execute([$newPasswordHash, $userId]);
    }

    public function updateProfilePicture(int $userId, string $path): bool {
        $stmt = $this->db->prepare("UPDATE users SET profile_picture_url = ? WHERE user_id = ?");
        return $stmt->execute([$path, $userId]);
    }

    public function logFailedLogin(string $username, string $ip, string $user_agent): void {
        try {
            $checkTable = "SELECT 1 FROM information_schema.tables WHERE table_name = 'failed_login_attempts'";
            $exists = $this->db->query($checkTable)->fetch();
            if ($exists) {
                $stmt = $this->db->prepare("INSERT INTO failed_login_attempts (username, ip_address, user_agent) VALUES (?, ?, ?)");
                $stmt->execute([$username, $ip, $user_agent]);
            }
        } catch (\PDOException $e) {
            // Ignore error
        }
    }
}
