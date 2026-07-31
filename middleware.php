<?php
// middleware.php — Auth guards, CSRF protection, dan response helpers
// Include file ini di awal setiap halaman/API yang butuh authentication.

// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// Authentication Guards
// ============================================

/**
 * Memastikan user sudah login.
 * Redirect ke login.html jika belum.
 */
function require_login(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.html');
        exit();
    }
}

/**
 * Memastikan user sudah login DAN memiliki role tertentu.
 * Redirect ke login.html jika belum login atau role tidak cocok.
 *
 * @param string|array $roles Role yang diizinkan ('student', 'lecturer', 'admin', atau array)
 */
function require_role(string|array $roles): void {
    require_login();
    
    $allowed = is_array($roles) ? $roles : [$roles];
    
    if (!in_array($_SESSION['role'] ?? '', $allowed, true)) {
        // Role tidak sesuai — redirect ke halaman yang benar
        $redirect = match ($_SESSION['role'] ?? '') {
            'student'  => 'dash-mahasiswa.php',
            'lecturer' => 'dash-dosen.php',
            'admin'    => 'dash-admin.php',
            default    => 'login.html',
        };
        header("Location: $redirect");
        exit();
    }
}

/**
 * Memastikan API request memiliki session yang valid.
 * Mengembalikan JSON error 401 jika tidak terautentikasi.
 *
 * @param string|array|null $roles Role yang diizinkan (opsional, null = semua role)
 */
function require_api_auth(string|array|null $roles = null): void {
    if (!isset($_SESSION['user_id'])) {
        json_response(['error' => 'Unauthorized. Silakan login terlebih dahulu.'], 401);
    }
    
    if ($roles !== null) {
        $allowed = is_array($roles) ? $roles : [$roles];
        if (!in_array($_SESSION['role'] ?? '', $allowed, true)) {
            json_response(['error' => 'Forbidden. Anda tidak memiliki akses ke resource ini.'], 403);
        }
    }
}

// ============================================
// CSRF Protection
// ============================================

/**
 * Generate CSRF token dan simpan di session.
 *
 * @return string CSRF token
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Menghasilkan hidden input field untuk CSRF token.
 * Panggil di dalam <form> tag.
 *
 * @return string HTML hidden input
 */
function csrf_field(): string {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Verifikasi CSRF token dari request POST.
 * Menghentikan eksekusi jika token tidak valid.
 */
function verify_csrf_token(): void {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    
    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        if (is_api_request()) {
            json_response(['error' => 'CSRF token tidak valid.'], 403);
        }
        
        http_response_code(403);
        die('Sesi Anda telah kedaluwarsa. Silakan <a href="login.html">login ulang</a>.');
    }
    
    // Regenerate token setelah verifikasi (one-time use)
    unset($_SESSION['csrf_token']);
}

// ============================================
// Response Helpers
// ============================================

/**
 * Mengirim JSON response dengan status code.
 *
 * @param mixed $data    Data yang akan di-encode ke JSON
 * @param int   $code    HTTP status code (default: 200)
 * @param bool  $exit    Apakah menghentikan eksekusi (default: true)
 */
function json_response(mixed $data, int $code = 200, bool $exit = true): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    
    if ($exit) {
        exit();
    }
}

/**
 * Mengirim JSON success response.
 *
 * @param string $message Pesan sukses
 * @param array  $data    Data tambahan (opsional)
 */
function json_success(string $message, array $data = []): void {
    json_response(array_merge(['success' => true, 'message' => $message], $data));
}

/**
 * Mengirim JSON error response.
 *
 * @param string $message Pesan error
 * @param int    $code    HTTP status code (default: 400)
 */
function json_error(string $message, int $code = 400): void {
    json_response(['success' => false, 'error' => $message], $code);
}

// ============================================
// Utility Helpers
// ============================================

/**
 * Cek apakah request saat ini adalah API/AJAX request.
 *
 * @return bool
 */
function is_api_request(): bool {
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

/**
 * Mendapatkan user ID dari session saat ini.
 *
 * @return int|null
 */
function current_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Mendapatkan role user dari session saat ini.
 *
 * @return string|null
 */
function current_role(): ?string {
    return $_SESSION['role'] ?? null;
}

/**
 * Mendapatkan nama user dari session saat ini.
 *
 * @return string
 */
function current_user_name(): string {
    return $_SESSION['full_name'] ?? 'User';
}
?>
