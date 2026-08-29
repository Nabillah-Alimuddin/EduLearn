<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Akademik EduLearn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #B6D0EF;
            --secondary-blue: #63A3F1;
            --light-cream: #FAFFEE;
            --dark-teal: #4F8A9E;
            --white: #FFFFFF;
        }

        body {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: var(--white);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(79, 138, 158, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.05);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transform: translateY(0);
            transition: all 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 70px rgba(79, 138, 158, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.1);
        }

        .login-header {
            background: linear-gradient(135deg, var(--secondary-blue), var(--dark-teal));
            color: var(--white);
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="30" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="60" cy="70" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="30" cy="80" r="1.5" fill="rgba(255,255,255,0.1)"/></svg>');
        }

        .login-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 1.8rem;
            position: relative;
            z-index: 1;
        }

        .login-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .login-body {
            padding: 2.5rem 2rem;
            background: var(--light-cream);
        }

        .role-selector {
            display: flex;
            margin-bottom: 2rem;
            background: var(--white);
            border-radius: 15px;
            padding: 0.5rem;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        }

        .role-option {
            flex: 1;
            padding: 0.75rem 1rem;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--dark-teal);
            font-weight: 600;
            border: none;
            background: transparent;
        }

        .role-option.active {
            background: linear-gradient(135deg, var(--secondary-blue), var(--dark-teal));
            color: var(--white);
            box-shadow: 0 6px 20px rgba(99, 163, 241, 0.4);
            transform: translateY(-3px) scale(1.05);
        }

        .role-option:hover:not(.active) {
            background: linear-gradient(135deg, var(--primary-blue), rgba(99, 163, 241, 0.3));
            color: var(--dark-teal);
            transform: translateY(-2px);
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            color: var(--dark-teal);
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .label-icon {
            color: var(--dark-teal);
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .form-control {
            border: 2px solid #e8f0fe;
            border-radius: 15px;
            padding: 1rem 1rem;
            font-size: 1rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--white);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .form-control:focus {
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 0.3rem rgba(99, 163, 241, 0.15), inset 0 2px 4px rgba(0, 0, 0, 0.05);
            background: var(--white);
            transform: translateY(-2px);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--secondary-blue), var(--dark-teal));
            border: none;
            border-radius: 15px;
            padding: 1rem 2rem;
            font-weight: 700;
            font-size: 1.1rem;
            width: 100%;
            color: var(--white);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, var(--dark-teal), var(--secondary-blue));
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 35px rgba(99, 163, 241, 0.5);
        }

        .forgot-password {
            text-align: center;
            margin-top: 1.5rem;
        }

        .forgot-password a {
            color: var(--dark-teal);
            text-decoration: none;
            font-weight: 500;
        }

        .login-footer {
            background: var(--white);
            padding: 1.5rem 2rem;
            text-align: center;
            color: var(--dark-teal);
            font-size: 0.9rem;
        }

        .animate-fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card animate-fade-in">
            <div class="login-header">
                <h2><i class="fas fa-graduation-cap me-2"></i>Sistem Akademik</h2>
                <p>Silakan masuk ke akun Anda</p>
            </div>
            
            <div class="login-body">
                <form id="loginForm" method="POST" action="index.php?url=auth/processLogin">
                    <div class="role-selector">
                        <button type="button" class="role-option active" data-role-value="student" id="mahasiswaRoleBtn">
                            <i class="fas fa-user-graduate me-2"></i>Mahasiswa
                        </button>
                        <button type="button" class="role-option" data-role-value="lecturer" id="dosenRoleBtn">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Dosen
                        </button>
                        <input type="hidden" name="role" id="selectedRole" value="student">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-user label-icon"></i>
                            Username
                        </label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan NIM Anda" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock label-icon"></i>
                            Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password Anda" required>
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Masuk
                    </button>

                    <div class="forgot-password">
                        <a href="#" id="forgotPassword">Lupa password?</a>
                    </div>
                </form>
            </div>

            <div class="login-footer">
                <small>&copy; 2026 EduLearn. Semua hak dilindungi.</small>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            updateUsernamePlaceholder(document.getElementById('selectedRole').value);

            document.getElementById('mahasiswaRoleBtn').addEventListener('click', function() {
                updateRole('student', 'mahasiswaRoleBtn', 'dosenRoleBtn');
            });

            document.getElementById('dosenRoleBtn').addEventListener('click', function() {
                updateRole('lecturer', 'dosenRoleBtn', 'mahasiswaRoleBtn');
            });

            function updateRole(roleValue, activeBtnId, inactiveBtnId) {
                document.getElementById(activeBtnId).classList.add('active');
                document.getElementById(inactiveBtnId).classList.remove('active');
                document.getElementById('selectedRole').value = roleValue;
                updateUsernamePlaceholder(roleValue);
            }

            function updateUsernamePlaceholder(role) {
                const usernameInput = document.getElementById('username');
                if (role === 'student') {
                    usernameInput.placeholder = 'Masukkan NIM Anda';
                } else if (role === 'lecturer') {
                    usernameInput.placeholder = 'Masukkan Email Dosen Anda';
                }
            }

            document.getElementById('forgotPassword').addEventListener('click', function(e) {
                e.preventDefault();
                const selectedRole = document.getElementById('selectedRole').value;
                alert(`Fitur reset password untuk ${selectedRole === 'student' ? 'Mahasiswa' : 'Dosen'} akan segera tersedia. Silakan hubungi administrator.`);
            });
        });
    </script>
</body>
</html>
