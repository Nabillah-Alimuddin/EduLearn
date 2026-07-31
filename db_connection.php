<?php
// db_connection.php — Koneksi database terpusat
// Semua credentials dibaca dari .env via config.php

// Cegah double-include
if (isset($conn) && $conn instanceof PDO) {
    return;
}

// Load konfigurasi & error handler
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/error_handler.php';

// Validasi konfigurasi database
if (empty(DB_HOST) || empty(DB_USER) || empty(DB_PASSWORD)) {
    show_error('Konfigurasi database tidak lengkap. Periksa file .env');
}

try {
    $dsn = sprintf(
        "pgsql:host=%s;port=%s;dbname=%s;sslmode=%s",
        DB_HOST, DB_PORT, DB_NAME, DB_SSLMODE
    );
    
    $conn = new PDO($dsn, DB_USER, DB_PASSWORD, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    
} catch (PDOException $e) {
    log_error('Database connection failed', [
        'host'    => DB_HOST,
        'dbname'  => DB_NAME,
        'message' => $e->getMessage()
    ]);
    show_error('Koneksi database gagal. Silakan coba lagi nanti.');
}
?>