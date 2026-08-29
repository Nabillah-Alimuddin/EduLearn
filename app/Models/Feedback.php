<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Feedback {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create(string $name, string $email, string $subject, string $message): bool {
        $sql = "INSERT INTO feedback (name, email, subject, message) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $email, $subject, $message]);
    }
}
