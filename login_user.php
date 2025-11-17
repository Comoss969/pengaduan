<?php
include 'config.php';

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login_user'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        // Validasi input
        if (empty($username)) {
            $error = "Username tidak boleh kosong.";
        } elseif (empty($password)) {
            $error = "Password tidak boleh kosong.";
        } else {
            // Login with username and password
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'user'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = 'user';
                $_SESSION['username'] = $user['username'];
                header('Location: user_dashboard.php');
                exit;
            } else {
                $error = "Username atau password salah.";
            }
        }
    }
}

// If already logged in as user with valid user_id, redirect to dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] == 'user' && isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null) {
    header('Location: user_dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login User - Pengaduan</title>
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
                        <!-- PingMe Logo -->
                        <div class="text-center mb-4">
                            <img src="assets/images/ping_me.png" alt="PingMe Logo" class="img-fluid" style="max-height: 120px; width: auto;">
                        </div>
                        <p class="text-center mb-4" style="color: #ffffff;">Masuk untuk mengirim pengaduan</p>

                        <?php if ($error): ?>
                            <div class="alert alert-primary"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label" style="color: #ffffff;">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       required 
                                       autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label" style="color: #ffffff;">Password</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required>
                            </div>
                            <button type="submit" name="login_user" class="btn btn-primary btn-lg w-100">
                                Login sebagai User
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <p class="mb-2" style="color: #ffffff;">Belum punya akun? <a href="register.php" class="text-decoration-none fw-bold" style="color: #60A5FA;">Daftar di sini</a></p>
                            <a href="login_admin.php" class="text-decoration-none" style="color: #ffffff;">Login sebagai Admin</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

