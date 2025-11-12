<?php
include 'config.php';

// If already logged in as user, redirect to dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] == 'user') {
    header('Location: user_dashboard.php');
    exit;
}

$errors = [];
$success = false;

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    if (empty($username)) {
        $errors[] = "Username tidak boleh kosong.";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username minimal 3 karakter.";
    } elseif (strlen($username) > 50) {
        $errors[] = "Username maksimal 50 karakter.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username hanya boleh mengandung huruf, angka, dan underscore.";
    }

    if (empty($password)) {
        $errors[] = "Password tidak boleh kosong.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Password dan konfirmasi password tidak cocok.";
    }

    // Check if username already exists (hanya untuk user, admin bisa pakai username yang sama dengan user)
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND role = 'user'");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = "Username sudah digunakan oleh user lain. Silakan pilih username lain.";
        }
    }

    // Register user if no errors
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
            $stmt->execute([$username, $hashedPassword]);

            // Auto login after registration
            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = 'user';
            $_SESSION['username'] = $username;

            // Redirect to dashboard
            header('Location: user_dashboard.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan saat mendaftar. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Pengaduan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100 justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <!-- Logo -->
                        <!--<div class="text-center mb-4">
                            <img src="assets/images/download.png" alt="Logo SMK Negeri 5 Surakarta" class="img-fluid" style="max-height: 120px; width: auto; filter: invert(1);">
                        </div> -->
                        <h2 class="text-center mb-4 fw-bold" style="color: #ffffff;">Daftar Akun</h2>
                        <p class="text-center mb-4" style="color: #ffffff;">Buat akun baru untuk mengirim pengaduan</p>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="registerForm">
                            <div class="mb-3">
                                <label for="username" class="form-label" style="color: #ffffff;">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                       required 
                                       minlength="3" 
                                       maxlength="50"
                                       pattern="[a-zA-Z0-9_]+"
                                       title="Username hanya boleh mengandung huruf, angka, dan underscore">
                                <small class="text-muted" style="color: #94A3B8;">Minimal 3 karakter, hanya huruf, angka, dan underscore</small>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label" style="color: #ffffff;">Password</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required 
                                       minlength="6">
                                <small class="text-muted" style="color: #94A3B8;">Minimal 6 karakter</small>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label" style="color: #ffffff;">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       required 
                                       minlength="6">
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn">
                                <span id="btnText">Daftar</span>
                                <span id="btnSpinner" style="display: none;">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Mendaftar...
                                </span>
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <p class="mb-0" style="color: #ffffff;">Sudah punya akun? <a href="login_user.php" class="text-decoration-none" style="color: #60A5FA; transition: color 0.3s ease;">Login di sini</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok.');
                return false;
            }

            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');

            submitBtn.disabled = true;
            btnText.style.display = 'none';
            btnSpinner.style.display = 'inline';
        });

        // Real-time password confirmation check
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;

            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Password tidak cocok');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>

