<?php
include 'config.php';

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login_admin'])) {
        // Admin login
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'admin';
            $_SESSION['username'] = $user['username'];
            header('Location: admin_dashboard.php');
            exit;
        } else {
            $error = "Login gagal";
        }
    }
}

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    header('Location: admin_dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Pengaduan</title>
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
                        <h2 class="text-center mb-4 fw-bold" style="color: #ffffff;">Login sebagai Admin</h2>
                        <p class="text-center mb-4" style="color: #ffffff;">Masuk untuk mengelola pengaduan</p>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label" style="color: #ffffff;">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label" style="color: #ffffff;">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" name="login_admin" class="btn btn-dark btn-lg w-100">
                                Login sebagai Admin
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <a href="login_user.php" class="text-decoration-none" style="color: #ffffff;">Login sebagai User</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

