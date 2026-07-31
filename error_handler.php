<?php
// error_handler.php — Error handling & logging terpusat
// Include setelah config.php

// ============================================
// Log Directory Setup
// ============================================
$log_dir = __DIR__ . '/logs';
if (!is_dir($log_dir)) {
    @mkdir($log_dir, 0755, true);
}

// ============================================
// Custom Error Handler
// ============================================
set_error_handler(function ($severity, $message, $file, $line) {
    // Jangan handle error yang di-suppress dengan @
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    $level = match ($severity) {
        E_WARNING, E_USER_WARNING => 'WARNING',
        E_NOTICE, E_USER_NOTICE => 'NOTICE',
        E_USER_ERROR => 'ERROR',
        default => 'ERROR',
    };
    
    log_app($level, "$message in $file on line $line");
    
    if (defined('APP_DEBUG') && APP_DEBUG) {
        return false; // Biarkan PHP handle juga (tampilkan di browser)
    }
    
    return true; // Suppress tampilan di production
});

// ============================================
// Custom Exception Handler
// ============================================
set_exception_handler(function ($exception) {
    log_app('CRITICAL', $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine());
    
    if (defined('APP_DEBUG') && APP_DEBUG) {
        // Development: tampilkan detail error
        echo '<div style="background:#ff4444;color:white;padding:20px;margin:10px;border-radius:8px;font-family:monospace;">';
        echo '<h3>⚠️ Exception</h3>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($exception->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($exception->getFile()) . ':' . $exception->getLine() . '</p>';
        echo '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
        echo '</div>';
    } else {
        // Production: pesan generic
        http_response_code(500);
        if (php_sapi_name() !== 'cli') {
            echo '<div style="text-align:center;padding:50px;font-family:sans-serif;">';
            echo '<h1>😔 Terjadi Kesalahan</h1>';
            echo '<p>Mohon maaf, terjadi kesalahan internal. Silakan coba lagi nanti.</p>';
            echo '</div>';
        }
    }
    exit(1);
});

// ============================================
// Logging Functions
// ============================================

/**
 * Log pesan ke file log aplikasi.
 *
 * @param string $level Level log (INFO, WARNING, ERROR, CRITICAL)
 * @param string $message Pesan yang akan di-log
 * @param array  $context Data tambahan (opsional)
 */
function log_app(string $level, string $message, array $context = []): void {
    $log_file = __DIR__ . '/logs/app_error.log';
    $timestamp = date('Y-m-d H:i:s');
    $context_str = !empty($context) ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    
    $log_entry = "[$timestamp] [$level] $message$context_str" . PHP_EOL;
    
    @file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

/**
 * Log info (untuk debugging, audit trail).
 */
function log_info(string $message, array $context = []): void {
    log_app('INFO', $message, $context);
}

/**
 * Log warning (masalah non-fatal).
 */
function log_warning(string $message, array $context = []): void {
    log_app('WARNING', $message, $context);
}

/**
 * Log error (masalah serius).
 */
function log_error(string $message, array $context = []): void {
    log_app('ERROR', $message, $context);
}

/**
 * Menampilkan halaman error yang user-friendly.
 * Digunakan sebagai pengganti die() di production.
 *
 * @param string $message   Pesan untuk user
 * @param int    $http_code HTTP status code
 */
function show_error(string $message, int $http_code = 500): void {
    http_response_code($http_code);
    
    if (defined('APP_DEBUG') && APP_DEBUG) {
        die("Error: $message");
    }
    
    die('<div style="text-align:center;padding:50px;font-family:sans-serif;">
        <h1>😔 Terjadi Kesalahan</h1>
        <p>' . htmlspecialchars($message) . '</p>
        <a href="login.html">Kembali ke Login</a>
    </div>');
}
?>
