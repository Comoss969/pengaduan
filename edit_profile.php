<?php
include 'config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login_admin.php');
    exit;
}

$page_title = 'Edit Profil';

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];

    // Validasi file
    $errors = [];
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $max_size = 2 * 1024 * 1024; // 2MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error uploading file.";
    } elseif (!in_array($file['type'], $allowed_types)) {
        $errors[] = "Only JPG, PNG, and JPEG files are allowed.";
    } elseif ($file['size'] > $max_size) {
        $errors[] = "File size must be less than 2MB.";
    }

    if (empty($errors)) {
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'admin_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
        $filepath = 'uploads/profile_pictures/' . $filename;

        // Hapus foto lama jika ada
        $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $old_picture = $stmt->fetchColumn();

        if ($old_picture && file_exists($old_picture)) {
            unlink($old_picture);
        }

        // Upload file baru
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Update database
            $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->execute([$filepath, $_SESSION['user_id']]);

            $success = "Foto profil berhasil diperbarui!";
        } else {
            $errors[] = "Gagal menyimpan file.";
        }
    }
}

// Ambil data profil saat ini
$stmt = $pdo->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<?php include 'includes/header.php'; ?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Edit Profil</h4>
            </div>
            <div class="card-body">
                <!-- Alert untuk sukses/error -->
                <?php if (isset($success)): ?>
                    <div class="alert alert-primary"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-primary">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Pratinjau Foto Profil -->
                <div class="text-center mb-4">
                    <div class="profile-picture-container">
                        <img id="profilePreview" src="<?php echo $user['profile_picture'] ? $user['profile_picture'] : 'assets/images/default-avatar.png'; ?>" alt="Foto Profil" class="profile-picture-large">
                    </div>
                    <p class="text-muted mt-2">Foto Profil Saat Ini</p>
                </div>

                <form id="profileForm" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="profile_picture" class="form-label">Pilih Foto Baru</label>
                        <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/jpg" required>
                        <small class="text-muted">Format: JPG, PNG, JPEG. Maksimal 2MB.</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                        <span id="btnText">Simpan Foto Profil</span>
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
// JavaScript untuk pratinjau gambar sebelum upload
document.getElementById('profile_picture').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validasi frontend
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung. Gunakan JPG, PNG, atau JPEG.');
            e.target.value = '';
            return;
        }

        if (file.size > maxSize) {
            alert('Ukuran file terlalu besar. Maksimal 2MB.');
            e.target.value = '';
            return;
        }

        // Pratinjau gambar
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Handle form submission dengan loading state
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

    // Tampilkan loading state
    submitBtn.disabled = true;
    btnText.style.display = 'none';
    btnSpinner.style.display = 'inline';
});
</script>

<?php include 'includes/footer.php'; ?>
