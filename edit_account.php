<?php
include 'config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login_admin.php');
    exit;
}

$page_title = 'Edit Akun';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validasi input
    $errors = [];
    if (empty($username)) {
        $errors[] = "Username tidak boleh kosong.";
    }
    if (!empty($password) && strlen($password) < 6) {
        $errors[] = "Password baru minimal 6 karakter.";
    }

    // Check if username already exists (hanya untuk admin lain, user bisa pakai username yang sama dengan admin)
    if (!empty($username)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND role = 'admin' AND id != ?");
        $stmt->execute([$username, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $errors[] = "Username sudah digunakan oleh admin lain.";
        }
    }

    if (empty($errors)) {
        try {
            // Ambil data user saat ini
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if ($user) {
                // Update username
                $updateFields = ["username = ?"];
                $params = [$username];

                // Jika password diisi, hash dan update
                if (!empty($password)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateFields[] = "password = ?";
                    $params[] = $hashedPassword;
                } else {
                    // Jika password kosong, jangan update password (biarkan yang lama)
                    // Tidak ada yang ditambahkan ke $updateFields
                }

                $params[] = $_SESSION['user_id'];

                $stmt = $pdo->prepare("UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?");
                $stmt->execute($params);

                // Update session username if changed
                if ($username !== $user['username']) {
                    $_SESSION['username'] = $username;
                }

                // Set flash message dan redirect
                $_SESSION['success_message'] = "Perubahan berhasil disimpan!";
                header('Location: edit_account.php');
                exit;
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
                    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                    unset($_SESSION['success_message']);
                }
                
                if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form id="editAccountForm" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password Baru (kosongkan jika tidak ingin mengubah)</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                        <span id="btnText">Simpan Perubahan</span>
                        <span id="btnSpinner" style="display: none;">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Menyimpan...
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript untuk handle form submission
document.getElementById('editAccountForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

    // Validasi input di frontend
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;

    let errors = [];
    if (!username) {
        errors.push("Username tidak boleh kosong.");
    }
    if (password && password.length < 6) {
        errors.push("Password baru minimal 6 karakter.");
    }

    if (errors.length > 0) {
        e.preventDefault();
        alert("Error:\n" + errors.join("\n"));
        return;
    }

    // Tampilkan loading state
    submitBtn.disabled = true;
    btnText.style.display = 'none';
    btnSpinner.style.display = 'inline';
    
    // Form akan submit normal, tidak perlu preventDefault
});
</script>

<?php include 'includes/footer.php'; ?>
