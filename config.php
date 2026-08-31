<?php
// config.php — Konfigurasi terpusat aplikasi E-Learning
// File ini di-include oleh db_connection.php dan file lainnya.

// ============================================
// Load Environment Variables dari .env
// ============================================
$env_path = __DIR__ . '/.env';

if (!file_exists($env_path)) {
    die('File .env tidak ditemukan. Copy .env.example ke .env dan isi konfigurasi.');
}

$env = parse_ini_file($env_path);

if ($env === false) {
    die('Gagal membaca file .env. Periksa format file.');
}

// ============================================
// App Constants
// ============================================
define('APP_NAME', $env['APP_NAME'] ?? 'EduLearn');
define('APP_ENV', $env['APP_ENV'] ?? 'production');     // 'development' atau 'production'
define('APP_DEBUG', filter_var($env['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('APP_TIMEZONE', $env['APP_TIMEZONE'] ?? 'Asia/Makassar');
define('APP_VERSION', '2.0.0');

// ============================================
// Database Constants
// ============================================
define('DB_HOST', $env['DB_HOST'] ?? '');
define('DB_PORT', $env['DB_PORT'] ?? '5432');
define('DB_NAME', $env['DB_NAME'] ?? 'postgres');
define('DB_USER', $env['DB_USER'] ?? '');
define('DB_PASSWORD', $env['DB_PASSWORD'] ?? '');
define('DB_SSLMODE', $env['DB_SSLMODE'] ?? 'require');

// ============================================
// Storage Constants (Local / Supabase Cloud)
// ============================================
define('STORAGE_DRIVER', $env['STORAGE_DRIVER'] ?? 'local'); // 'local' atau 'supabase'
define('SUPABASE_STORAGE_URL', $env['SUPABASE_STORAGE_URL'] ?? '');
define('SUPABASE_ANON_KEY', $env['SUPABASE_ANON_KEY'] ?? '');
define('SUPABASE_BUCKET', $env['SUPABASE_BUCKET'] ?? 'elearning');

// ============================================
// Timezone
// ============================================
date_default_timezone_set(APP_TIMEZONE);

// ============================================
// Error Reporting berdasarkan Environment
// ============================================
if (APP_DEBUG) {
    // Development: tampilkan semua error
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    // Production: sembunyikan error dari user
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/logs/app_error.log');
}
?>
