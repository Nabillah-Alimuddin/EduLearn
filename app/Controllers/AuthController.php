<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;

class AuthController extends Controller {

    public function landing(): void {
        Middleware::startSession();
        $this->view('auth/landing');
    }

    public function login(): void {
        Middleware::startSession();
        if (isset($_SESSION['user_id'])) {
            $redirect = match ($_SESSION['role'] ?? '') {
                'student'  => 'index.php?url=student/dashboard',
                'lecturer' => 'index.php?url=lecturer/dashboard',
                'admin'    => 'index.php?url=admin/dashboard',
                default    => 'index.php?url=auth/login',
            };
            $this->redirect($redirect);
        }
        $this->view('auth/login');
    }

    public function processLogin(): void {
        Middleware::startSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?url=auth/login');
        }

        $usernameInput = trim($_POST['username'] ?? '');
        $passwordInput = $_POST['password'] ?? '';
        $roleInput = $_POST['role'] ?? '';

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';

        if (empty($usernameInput) || empty($passwordInput) || empty($roleInput)) {
            echo "<script>alert('Mohon lengkapi semua field!'); window.location.href='index.php?url=auth/login';</script>";
            exit();
        }

        /** @var \App\Models\User $userModel */
        $userModel = $this->model('User');
        $userData = null;

        if ($roleInput === 'student') {
            $userData = $userModel->findStudentForLogin($usernameInput);
        } elseif ($roleInput === 'lecturer' || $roleInput === 'admin') {
            $userData = $userModel->findStaffForLogin($roleInput, $usernameInput);
        } else {
            echo "<script>alert('Peran pengguna tidak valid.'); window.location.href='index.php?url=auth/login';</script>";
            exit();
        }

        if ($userData && password_verify($passwordInput, $userData['password_hash'])) {
            $_SESSION['user_id'] = $userData['user_id'];
            $_SESSION['role'] = $userData['role'];
            $_SESSION['full_name'] = $userData['full_name'];

            if ($userData['role'] === 'student') {
                $_SESSION['identifier'] = $userData['nim'];
                $this->redirect('index.php?url=student/dashboard');
            } elseif ($userData['role'] === 'lecturer') {
                $_SESSION['identifier'] = $userData['email'];
                $this->redirect('index.php?url=lecturer/dashboard');
            } elseif ($userData['role'] === 'admin') {
                $_SESSION['identifier'] = $userData['email'];
                $this->redirect('index.php?url=admin/dashboard');
            }
        } else {
            $userModel->logFailedLogin($usernameInput, $ipAddress, $userAgent);
            echo "<script>alert('Kombinasi Username/Email dan Password salah.'); window.location.href='index.php?url=auth/login';</script>";
            exit();
        }
    }

    public function logout(): void {
        Middleware::startSession();
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
        $this->redirect('index.php?url=auth/login');
    }
}
