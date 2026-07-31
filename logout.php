<?php
// logout.php
session_start();

// Hapus semua variabel sesi
$_SESSION = array();

// Jika ingin menghapus sesi, hapus juga cookie sesi.
// Catatan: Ini akan menghancurkan sesi, bukan hanya data sesi!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan sesi
session_destroy();

// Arahkan pengguna kembali ke halaman login
header("Location: login.html");
exit();
?>