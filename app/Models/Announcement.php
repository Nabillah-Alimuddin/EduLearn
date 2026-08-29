<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Announcement {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getForStudent(int $studentId): array {
        $sql = "
            SELECT
                a.announcement_id, a.title, a.content, a.published_at, a.lecturer_id, a.course_id,
                c.course_name, u.full_name AS lecturer_full_name
            FROM announcements a
            LEFT JOIN courses c ON a.course_id = c.course_id
            LEFT JOIN users u ON a.lecturer_id = u.user_id
            WHERE (a.course_id IN (
                SELECT course_id FROM course_enrollments WHERE student_id = ?
            ) OR a.course_id IS NULL)
            ORDER BY a.published_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function getForLecturer(int $lecturerId): array {
        $sql = "
            SELECT a.*, c.course_name 
            FROM announcements a 
            LEFT JOIN courses c ON a.course_id = c.course_id 
            WHERE a.lecturer_id = ? 
            ORDER BY a.published_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$lecturerId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): bool {
        $sql = "INSERT INTO announcements (title, content, lecturer_id, course_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['title'], $data['content'], $data['lecturer_id'], $data['course_id'] ?? null
        ]);
    }

    public function update(int $announcementId, int $lecturerId, array $data): bool {
        $sql = "UPDATE announcements SET title = ?, content = ?, course_id = ? WHERE announcement_id = ? AND lecturer_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['title'], $data['content'], $data['course_id'] ?? null, $announcementId, $lecturerId
        ]);
    }

    public function delete(int $announcementId, int $lecturerId): bool {
        $sql = "DELETE FROM announcements WHERE announcement_id = ? AND lecturer_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$announcementId, $lecturerId]);
    }
}
