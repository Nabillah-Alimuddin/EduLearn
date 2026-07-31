<?php
session_start(); // Mulai sesi di awal skrip
include 'db_connection.php'; 

// Fungsi untuk mencatat percobaan login gagal (opsional tapi disarankan)
function log_failed_attempt($conn, $username, $ip, $user_agent) {
    $check_table_sql = "SELECT 1 FROM information_schema.tables WHERE table_name = 'failed_login_attempts'";
    $table_exists_result = $conn->query($check_table_sql);
    $table_exists = $table_exists_result && $table_exists_result->fetch() !== false;

    if ($table_exists) {
        $stmt = $conn->prepare("INSERT INTO failed_login_attempts (username, ip_address, user_agent) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->execute([$username, $ip, $user_agent]);
        } else {
            error_log("Failed to prepare log_failed_attempt statement");
        }
    } else {
        error_log("Table 'failed_login_attempts' does not exist.");
    }
}

// Cek apakah request datang dari form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_input = trim($_POST['username'] ?? ''); // Bisa NIM/NIK/Email
    $password_input = $_POST['password'] ?? '';
    $role_input = $_POST['role'] ?? ''; // Akan diambil dari hidden input di login.html

    // Dapatkan IP address dan User Agent
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';

    // Validasi input dasar
    if (empty($username_input) || empty($password_input) || empty($role_input)) {
        echo "<script>alert('Mohon lengkapi semua field!'); window.location.href='login.html';</script>";
        exit;
    }

    $user_data = null;
    $stmt = null;

    // Menentukan query SQL berdasarkan role untuk mencari user
    if ($role_input === 'student') {
        // Mahasiswa login dengan NIM atau Email
        $sql = "SELECT user_id, full_name, password_hash, role, nim, email FROM users WHERE role = ? AND (nim = ? OR email = ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Failed to prepare student login statement");
            echo "<script>alert('Terjadi kesalahan internal. Silakan coba lagi.'); window.location.href='login.html';</script>";
            exit;
        }
        $stmt->execute([$role_input, $username_input, $username_input]);
    } elseif ($role_input === 'lecturer' || $role_input === 'admin') {
        // Dosen/Admin login dengan Email
        $sql = "SELECT user_id, full_name, password_hash, role, nim, email FROM users WHERE role = ? AND email = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Failed to prepare lecturer/admin login statement");
            echo "<script>alert('Terjadi kesalahan internal. Silakan coba lagi.'); window.location.href='login.html';</script>";
            exit;
        }
        $stmt->execute([$role_input, $username_input]);
    } else {
        echo "<script>alert('Peran pengguna tidak valid. Pastikan Anda memilih Mahasiswa atau Dosen.'); window.location.href='login.html';</script>";
        exit;
    }

    $user_data = $stmt->fetch();

    if ($user_data) {
        // Verifikasi Password menggunakan password_verify()
        if (password_verify($password_input, $user_data['password_hash'])) {
            // Login berhasil
            $_SESSION['user_id'] = $user_data['user_id'];
            $_SESSION['role'] = $user_data['role'];
            $_SESSION['full_name'] = $user_data['full_name'];
            
            // Opsional: simpan NIM/Email sesuai peran ke sesi
            if ($user_data['role'] === 'student') {
                $_SESSION['identifier'] = $user_data['nim'];
            } elseif ($user_data['role'] === 'lecturer' || $user_data['role'] === 'admin') {
                $_SESSION['identifier'] = $user_data['email'];
            }

            // Redirect ke dashboard yang sesuai
            $redirect_url = '';
            if ($user_data['role'] === 'lecturer') {
                $redirect_url = 'dash-dosen.php';
            } elseif ($user_data['role'] === 'student') {
                $redirect_url = 'dash-mahasiswa.php';
            } elseif ($user_data['role'] === 'admin') {
                $redirect_url = 'dash-admin.php';
            }

            // Redirect langsung tanpa alert
            header("Location: " . $redirect_url);
            exit;
        } else {
            // Password salah
            log_failed_attempt($conn, $username_input, $ip_address, $user_agent);
            echo "<script>alert('Username atau password salah.'); window.location.href='login.html';</script>";
            exit;
        }
    } else {
        // Username tidak ditemukan untuk role yang dipilih
        log_failed_attempt($conn, $username_input, $ip_address, $user_agent);
        echo "<script>alert('Username atau password salah atau pengguna tidak ditemukan.'); window.location.href='login.html';</script>";
        exit;
    }

    $conn = null;
} else {
    // Jika halaman login.php diakses langsung tanpa metode POST,
    // arahkan kembali ke halaman login.html
    header("Location: login.html");
    exit;
}
?>