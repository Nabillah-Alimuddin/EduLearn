<?php
namespace App\Core;

class Middleware {

    public static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function requireLogin(): void {
        self::startSession();
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth/login');
            exit();
        }
    }

    public static function requireRole(string|array $roles): void {
        self::requireLogin();
        
        $allowed = is_array($roles) ? $roles : [$roles];
        
        if (!in_array($_SESSION['role'] ?? '', $allowed, true)) {
            $redirect = match ($_SESSION['role'] ?? '') {
                'student'  => 'index.php?url=student/dashboard',
                'lecturer' => 'index.php?url=lecturer/dashboard',
                'admin'    => 'index.php?url=admin/dashboard',
                default    => 'index.php?url=auth/login',
            };
            header("Location: $redirect");
            exit();
        }
    }

    public static function requireApiAuth(string|array|null $roles = null): void {
        self::startSession();
        if (!isset($_SESSION['user_id'])) {
            self::jsonResponse(['error' => 'Unauthorized. Silakan login terlebih dahulu.'], 401);
        }
        
        if ($roles !== null) {
            $allowed = is_array($roles) ? $roles : [$roles];
            if (!in_array($_SESSION['role'] ?? '', $allowed, true)) {
                self::jsonResponse(['error' => 'Forbidden. Anda tidak memiliki akses ke resource ini.'], 403);
            }
        }
    }

    public static function generateCsrfToken(): string {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfField(): string {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    public static function verifyCsrfToken(): void {
        self::startSession();
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            if (self::isApiRequest()) {
                self::jsonResponse(['error' => 'CSRF token tidak valid.'], 403);
            }
            
            http_response_code(403);
            die('Sesi Anda telah kedaluwarsa. Silakan <a href="index.php?url=auth/login">login ulang</a>.');
        }
        
        unset($_SESSION['csrf_token']);
    }

    public static function jsonResponse(mixed $data, int $code = 200, bool $exit = true): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        
        if ($exit) {
            exit();
        }
    }

    public static function jsonSuccess(string $message, array $data = []): void {
        self::jsonResponse(array_merge(['success' => true, 'message' => $message], $data));
    }

    public static function jsonError(string $message, int $code = 400): void {
        self::jsonResponse(['success' => false, 'error' => $message], $code);
    }

    public static function isApiRequest(): bool {
        return (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || (
            isset($_SERVER['HTTP_ACCEPT']) && 
            str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')
        ) || (
            isset($_SERVER['CONTENT_TYPE']) && 
            str_contains($_SERVER['CONTENT_TYPE'], 'application/json')
        );
    }

    public static function currentUserId(): ?int {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }

    public static function currentRole(): ?string {
        self::startSession();
        return $_SESSION['role'] ?? null;
    }

    public static function currentUserName(): string {
        self::startSession();
        return $_SESSION['full_name'] ?? 'User';
    }
}
