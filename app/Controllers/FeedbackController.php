<?php
namespace App\Controllers;

use App\Core\Controller;

class FeedbackController extends Controller {

    public function submit(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $subject = $_POST['subject'] ?? '';
            $message = $_POST['message'] ?? '';

            if (empty($name) || empty($email) || empty($subject) || empty($message)) {
                echo "<script>alert('Error: Semua kolom wajib diisi.'); window.location.href='index.php?url=auth/landing#kontak';</script>";
                exit();
            }

            /** @var \App\Models\Feedback $feedbackModel */
            $feedbackModel = $this->model('Feedback');
            if ($feedbackModel->create($name, $email, $subject, $message)) {
                echo "<script>alert('Pesan Anda berhasil dikirim! Kami akan segera menghubungi Anda.'); window.location.href='index.php?url=auth/landing';</script>";
            } else {
                echo "<script>alert('Error: Terjadi kesalahan saat menyimpan pesan Anda.'); window.location.href='index.php?url=auth/landing#kontak';</script>";
            }
            exit();
        }
        $this->redirect('index.php?url=auth/landing');
    }
}
