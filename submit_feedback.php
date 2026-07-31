<?php
// submit_feedback.php
include 'middleware.php';
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $name = $_POST['name'] ?? '';       
    $email = $_POST['email'] ?? '';     
    $subject = $_POST['subject'] ?? ''; 
    $message = $_POST['message'] ?? ''; 

    // Periksa apakah data penting tidak kosong
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo "<script>alert('Error: Semua kolom wajib diisi.'); window.location.href='landingpage.html#kontak';</script>";
        exit();
    }

    // Siapkan query SQL untuk menyimpan data
    $sql = "INSERT INTO feedback (name, email, subject, message) VALUES (?, ?, ?, ?)";
    
    try {
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            // Eksekusi query dengan parameter
            if ($stmt->execute([$name, $email, $subject, $message])) {
                echo "<script>alert('Pesan Anda berhasil dikirim! Kami akan segera menghubungi Anda.'); window.location.href='landingpage.html';</script>";
            } else {
                echo "<script>alert('Error: Terjadi kesalahan saat menyimpan pesan Anda. Silakan coba lagi.'); window.location.href='landingpage.html#kontak';</script>";
            }
        }
    } catch (PDOException $e) {
        error_log("Error preparing/inserting feedback statement: " . $e->getMessage());
        echo "<script>alert('Error: Terjadi kesalahan database. Silakan coba lagi.'); window.location.href='landingpage.html#kontak';</script>";
    }
} else {
    // Jika diakses tidak melalui POST request
    header("Location: landingpage.html");
    exit();
}

$conn = null;
?>