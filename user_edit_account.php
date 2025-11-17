hk<?php
include 'config.php';

// Check if user is logged in as user
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header('Location: login_user.php');
    exit;
}

$page_title = 'Edit Akun';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $current_password = $_POST['current_password'];
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validasi input
    $errors = [];

    // Validasi current password
    if (empty($current_password)) {
        $errors[] = "Password saat ini wajib diisi.";
    } else {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_data = $stmt->fetch();

        if (!$user_data || !password_verify($current_password, $user_data['password'])) {
            $errors[] = "Password saat ini salah.";
        }
    }

    // Validasi username
    if (empty($username)) {
        $errors[] = "Username tidak boleh kosong.";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username minimal 3 karakter.";
    } elseif (strlen($username) > 50) {
        $errors[] = "Username maksimal 50 karakter.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username hanya boleh mengandung huruf, angka, dan underscore.";
    }

    // Check if username already exists (exclude current user)
    if (!empty($username)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $errors[] = "Username sudah digunakan oleh user lain.";
        }
    }

    // Validasi password baru (jika diisi)
    if (!empty($new_password)) {
        if (strlen($new_password) < 6) {
            $errors[] = "Password baru minimal 6 karakter.";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "Password baru dan konfirmasi password tidak cocok.";
        }
    } elseif (!empty($confirm_password)) {
        $errors[] = "Password baru wajib diisi jika ingin mengubah password.";
    }

    if (empty($errors)) {
        try {
            // Ambil data user saat ini
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if ($user) {
                // Update fields
                $updateFields = ["username = ?"];
                $params = [$username];

                // Jika password baru diisi, hash dan update
                if (!empty($new_password)) {
                    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                    $updateFields[] = "password = ?";
                    $params[] = $hashedPassword;
                }

                $params[] = $_SESSION['user_id'];

                $stmt = $pdo->prepare("UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?");
                $stmt->execute($params);

                // Update session data
                $_SESSION['username'] = $username;

                // Set success message dan redirect
                $_SESSION['success_message'] = "Akun berhasil diperbarui! Mengalihkan ke dashboard...";
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'user_dashboard.php';
                    }, 2000);
                </script>";
            } else {
                $errors[] = "Akun tidak ditemukan.";
            }
        } catch (PDOException $e) {
            $errors[] = "Gagal memperbarui data: " . $e->getMessage();
        }
    }
}

// Ambil data user untuk pre-fill form
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<?php include 'includes/header.php'; ?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Edit Akun Saya</h4>
            </div>
            <div class="card-body">
                <?php
                // Tampilkan flash message dari session jika ada
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success" id="successAlert">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                    unset($_SESSION['success_message']);
                }

                if (!empty($errors)): ?>
                    <div class="alert alert-danger" id="errorAlert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form id="editAccountForm" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                        <small class="text-muted">Minimal 3 karakter, hanya huruf, angka, dan underscore</small>
                    </div>

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                        <small class="text-muted">Diperlukan untuk verifikasi sebelum menyimpan perubahan</small>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">Password Baru (Opsional)</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                        <small class="text-muted">Minimal 6 karakter, kosongkan jika tidak ingin mengubah</small>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill" id="submitBtn">
                            <span id="btnText">Simpan Perubahan</span>
                            <span id="btnSpinner" style="display: none;">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Menyimpan...
                            </span>
                        </button>
                        <a href="user_dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript untuk handle form submission dan validasi
document.getElementById('editAccountForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

    // Validasi input di frontend
    const username = document.getElementById('username').value.trim();
    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    let errors = [];

    if (!username) {
        errors.push("Username tidak boleh kosong.");
    }
    if (!currentPassword) {
        errors.push("Password saat ini wajib diisi.");
    }
    if (newPassword && newPassword.length < 6) {
        errors.push("Password baru minimal 6 karakter.");
    }
    if (newPassword && newPassword !== confirmPassword) {
        errors.push("Password baru dan konfirmasi password tidak cocok.");
    }
    if (!newPassword && confirmPassword) {
        errors.push("Password baru wajib diisi jika ingin mengubah password.");
    }

    if (errors.length > 0) {
        e.preventDefault();
        showAlert("Error validasi:\n" + errors.join("\n"), 'danger');
        return;
    }

    // Tampilkan loading state
    submitBtn.disabled = true;
    btnText.style.display = 'none';
    btnSpinner.style.display = 'inline';
});

// Fungsi untuk menampilkan alert
function showAlert(message, type = 'danger') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = message.replace(/\n/g, '<br>');
    alertDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;';

    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
}

// Real-time password confirmation check
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;

    if (confirmPassword && newPassword !== confirmPassword) {
        this.setCustomValidity('Password tidak cocok');
    } else {
        this.setCustomValidity('');
    }
});

// Auto-hide alerts after 5 seconds
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);
</script>

<?php include 'includes/footer.php'; ?>
