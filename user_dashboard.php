<?php
include 'config.php';
include 'includes/profanity_filter.php';

// Initialize profanity filter
$profanityFilter = new ProfanityFilter($pdo);

// Check if user is logged in as user with valid user_id
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user' || !isset($_SESSION['user_id']) || $_SESSION['user_id'] === null) {
    header('Location: login_user.php');
    exit;
}

$page_title = 'User Dashboard';

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_post'])) {
    $keluhan = trim($_POST['keluhan']);
    $mode = $_POST['mode'];
    $nama = null;
    $is_anonim = false;

    if ($mode == 'normal') {
        // Automatically use username from session for normal mode
        $nama = $_SESSION['username'];
        $is_anonim = false;
    } else {
        $is_anonim = true;
    }

    // Check for profanity in the complaint text
    $profanityCheck = $profanityFilter->checkProfanity($keluhan);
    $censoredKeluhan = $keluhan; // Default to original
    $profanityCount = 0;

    if ($profanityCheck['has_profanity']) {
        // Log the profanity detection
        $profanityFilter->logProfanity($keluhan, $profanityCheck['found_words'], $_SESSION['user_id'], 'post');

        // Censor the text for storage and display to others
        $censoredKeluhan = $profanityFilter->censorText($keluhan);
        $profanityCount = count($profanityCheck['found_words']);

        $warning = "Peringatan: Kata-kata tidak pantas telah dideteksi dan disensor. Harap gunakan bahasa yang sopan.";
    }

    // Insert post first to get post_id
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, nama, keluhan, is_anonim) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $nama, $keluhan, $is_anonim]);
    $post_id = $pdo->lastInsertId();

    // Handle multiple file uploads (up to 3)
    if (isset($_FILES['foto'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $uploaded_count = 0;
        foreach ($_FILES['foto']['name'] as $key => $filename) {
            if ($uploaded_count >= 3) break; // Max 3 photos
            if ($_FILES['foto']['error'][$key] == 0) {
                $foto_path = $target_dir . basename($filename);
                if (move_uploaded_file($_FILES['foto']['tmp_name'][$key], $foto_path)) {
                    $stmt_photo = $pdo->prepare("INSERT INTO post_photos (post_id, photo_path) VALUES (?, ?)");
                    $stmt_photo->execute([$post_id, $foto_path]);
                    $uploaded_count++;
                }
            }
        }
    }

    // Set flash message untuk ditampilkan setelah redirect
    if (isset($warning)) {
        $_SESSION['success_message'] = $warning;
    } else {
        $_SESSION['success_message'] = "Postingan berhasil dikirim!";
    }

    // Redirect untuk menghindari resubmit form dan menghapus parameter GET yang mungkin tersisa
    header('Location: user_dashboard.php');
    exit;
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_comment'])) {
    $post_id = $_POST['post_id'];
    $komentar = trim($_POST['komentar']);

    // Handle file upload for comment
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $foto = $target_dir . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], $foto);
    }

    // Check for profanity in the comment text
    $profanityCheck = $profanityFilter->checkProfanity($komentar);

    if ($profanityCheck['has_profanity']) {
        // Log the profanity detection
        $profanityFilter->logProfanity($komentar, $profanityCheck['found_words'], $_SESSION['user_id'], 'comment');

        // Option 1: Censor the text and allow posting
        $komentar = $profanityFilter->censorText($komentar);
        $warning = "Peringatan: Kata-kata tidak pantas telah dideteksi dan disensor. Harap gunakan bahasa yang sopan.";

        // Option 2: Block the comment (uncomment to use this instead)
        // $error = "Komentar mengandung kata-kata tidak pantas. Harap gunakan bahasa yang sopan.";
        // Instead of proceeding with the insert, show error and skip
    }

    // Only proceed if no blocking error
    if (!isset($error)) {
        // Pastikan user_id valid dan username ada (seperti sistem form pengaduan)
        if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === null || $_SESSION['user_id'] <= 0 || !isset($_SESSION['username'])) {
            // Session tidak valid, redirect ke login
            header('Location: login_user.php');
            exit;
        }
        
        $user_id = $_SESSION['user_id'];
        // Menyimpan komentar user dengan user_id dari session (seperti sistem form pengaduan)
        // Username akan diambil dari users table melalui JOIN saat display
        try {
            $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, komentar, foto, is_admin) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$post_id, $user_id, $komentar, $foto, false]);
            
            // Set flash message untuk ditampilkan setelah redirect
            if (isset($warning)) {
                $_SESSION['success_message'] = $warning;
            } else {
                $_SESSION['success_message'] = "Komentar berhasil dikirim!";
            }
            
            // Redirect untuk menghindari resubmit form
            header('Location: user_dashboard.php');
            exit;
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan saat menyimpan komentar. Silakan coba lagi.";
            $_SESSION['error_message'] = $error;
        }
    }
}

// Handle edit post
if (isset($_GET['edit_post'])) {
    $edit_post_id = (int)$_GET['edit_post'];
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ? AND deleted_at IS NULL");
    $stmt->execute([$edit_post_id, $_SESSION['user_id']]);
    $edit_post = $stmt->fetch();
    if (!$edit_post) {
        $error = "Postingan tidak ditemukan atau Anda tidak memiliki izin untuk mengeditnya.";
    }
}

// Handle edit post submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_edit_post'])) {
    $post_id = $_POST['post_id'];
    $keluhan = trim($_POST['keluhan']);
    $mode = $_POST['mode'];
    $nama = null;
    $is_anonim = false;

    if ($mode == 'normal') {
        $nama = $_SESSION['username'];
        $is_anonim = false;
    } else {
        $is_anonim = true;
    }

    // Check for profanity
    $profanityCheck = $profanityFilter->checkProfanity($keluhan);
    if ($profanityCheck['has_profanity']) {
        $profanityFilter->logProfanity($keluhan, $profanityCheck['found_words'], $_SESSION['user_id'], 'post');
        $keluhan = $profanityFilter->censorText($keluhan);
        $warning = "Peringatan: Kata-kata tidak pantas telah dideteksi dan disensor.";
    }

    // Update post
    $stmt = $pdo->prepare("UPDATE posts SET nama = ?, keluhan = ?, is_anonim = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$nama, $keluhan, $is_anonim, $post_id, $_SESSION['user_id']]);

    // Handle new photos (replace existing)
    if (isset($_FILES['foto']) && !empty($_FILES['foto']['name'][0])) {
        // Delete existing photos
        $stmt_delete = $pdo->prepare("DELETE FROM post_photos WHERE post_id = ?");
        $stmt_delete->execute([$post_id]);

        // Upload new photos
        $target_dir = "uploads/";
        $uploaded_count = 0;
        foreach ($_FILES['foto']['name'] as $key => $filename) {
            if ($uploaded_count >= 3) break;
            if ($_FILES['foto']['error'][$key] == 0) {
                $foto_path = $target_dir . basename($filename);
                if (move_uploaded_file($_FILES['foto']['tmp_name'][$key], $foto_path)) {
                    $stmt_photo = $pdo->prepare("INSERT INTO post_photos (post_id, photo_path) VALUES (?, ?)");
                    $stmt_photo->execute([$post_id, $foto_path]);
                    $uploaded_count++;
                }
            }
        }
    }

    $_SESSION['success_message'] = isset($warning) ? $warning : "Postingan berhasil diupdate!";
    header('Location: user_dashboard.php');
    exit;
}

// Handle delete post (Soft Delete)
// Saat user menghapus postingan, tidak benar-benar dihapus dari database
// Hanya menandai dengan deleted_at timestamp (soft delete)
if (isset($_GET['delete_post'])) {
    $post_id = (int)$_GET['delete_post'];

    // Validasi: Pastikan post_id valid
    if ($post_id > 0) {
        // Verifikasi bahwa postingan ini milik user yang sedang login
        $stmt = $pdo->prepare("SELECT user_id, deleted_at FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch();

        if ($post && $post['user_id'] == $_SESSION['user_id']) {
            // Cek apakah postingan sudah terhapus
            if ($post['deleted_at'] !== null) {
                $error = "Postingan ini sudah dihapus sebelumnya.";
            } else {
                // Soft delete: Set deleted_at dengan timestamp saat ini
                // Postingan tidak benar-benar dihapus, hanya ditandai sebagai terhapus
                $stmt = $pdo->prepare("UPDATE posts SET deleted_at = NOW() WHERE id = ? AND user_id = ?");
                $stmt->execute([$post_id, $_SESSION['user_id']]);

                if ($stmt->rowCount() > 0) {
                    $_SESSION['success_message'] = "Postingan berhasil dihapus! (Akan terhapus permanen setelah 30 hari)";
                    header('Location: user_dashboard.php');
                    exit;
                } else {
                    $error = "Gagal menghapus postingan.";
                }
            }
        } else {
            $error = "Anda tidak memiliki izin untuk menghapus postingan ini.";
        }
    } else {
        $error = "ID postingan tidak valid.";
    }
}

// Fetch posts (hanya yang tidak terhapus)
// Filter: deleted_at IS NULL berarti postingan masih aktif
// Postingan yang sudah dihapus (deleted_at IS NOT NULL) tidak ditampilkan ke user
$stmt = $pdo->query("SELECT * FROM posts WHERE deleted_at IS NULL ORDER BY tanggal_post DESC");
$posts = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="row">
    <div class="col-md-8">
        <h2 class="mb-4" style="color: #ffffff;">Timeline Pengaduan</h2>

        <?php 
        // Tampilkan flash message dari session jika ada
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            unset($_SESSION['success_message']);
        }
        
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        
        if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php foreach ($posts as $post): ?>
            <div class="card timeline-item">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title">
                                <?php if ($post['is_anonim']): ?>
                                    Anonim
                                <?php else: ?>
                                    <?php echo htmlspecialchars($post['nama']); ?>
                                <?php endif; ?>
                            </h5>
                            <small style="color: #ffffff;"><?php echo $post['tanggal_post']; ?></small>
                        </div>
                        <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                            <div class="btn-group" role="group">
                                <a href="?edit_post=<?php echo $post['id']; ?>" class="btn btn-sm btn-warning rounded-pill">Edit</a>
                                <a href="?delete_post=<?php echo $post['id']; ?>" class="btn btn-sm btn-danger delete-btn ms-2 rounded-pill">Hapus</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <p class="card-text mt-2">
                        <?php
                        // Bagian yang menampilkan kata tersensor untuk user lain dan menahan kata kasar dari pengirim
                        // Admin tetap melihat kata asli
                        $censoredText = $profanityFilter->censorText($post['keluhan']);
                        $displayText = $profanityFilter->getDisplayText(
                            $post['keluhan'],  // Kata asli
                            $censoredText,     // Kata tersensor
                            $post['user_id'],  // ID pemilik post
                            $_SESSION['user_id'] ?? null,  // ID user saat ini
                            $_SESSION['role'] ?? null      // Role user saat ini
                        );
                        echo htmlspecialchars($displayText);
                        ?>
                    </p>
                    <?php
                    // Fetch photos for this post
                    $stmt_photos = $pdo->prepare("SELECT photo_path FROM post_photos WHERE post_id = ?");
                    $stmt_photos->execute([$post['id']]);
                    $photos = $stmt_photos->fetchAll(PDO::FETCH_COLUMN);
                    if ($photos): ?>
                        <div class="mt-2">
                            <?php foreach ($photos as $photo): ?>
                                <img src="<?php echo $photo; ?>" class="img-fluid rounded me-2 mb-2" alt="Foto" style="max-width: 200px;">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Comments -->
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="toggleCommentForm(<?php echo $post['id']; ?>)">Komentar</button>
                        <div id="comment-form-<?php echo $post['id']; ?>" style="display: none;" class="mt-2">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <div class="mb-2">
                                    <input type="text" name="komentar" class="form-control" placeholder="Tulis komentar..." required>
                                </div>
                                <div class="mb-2">
                                    <input type="file" name="foto" class="form-control" accept="image/*">
                                </div>
                                <button type="submit" name="submit_comment" class="btn btn-primary">Kirim</button>
                            </form>
                        </div>

                        <?php
                        // Join with users table to get username (seperti sistem form pengaduan)
                        // Pastikan username selalu diambil dari akun user yang mengomentari
                        $stmt_comments = $pdo->prepare("SELECT c.*, u.username FROM comments c INNER JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.tanggal DESC");
                        $stmt_comments->execute([$post['id']]);
                        $comments = $stmt_comments->fetchAll();
                        ?>

                        <div class="mt-2">
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment <?php echo $comment['is_admin'] ? 'admin' : ''; ?>" id="comment-<?php echo $comment['id']; ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <strong>
                                                <?php 
                                                // Tampilkan username dari akun yang mengomentari (seperti sistem form)
                                                if ($comment['is_admin']) {
                                                    echo '[Admin] ' . htmlspecialchars($comment['username']);
                                                } else {
                                                    echo htmlspecialchars($comment['username']);
                                                }
                                                ?>:
                                            </strong>
                                            <?php echo htmlspecialchars($comment['komentar']); ?>
                                            <?php if (isset($comment['foto']) && $comment['foto']): ?>
                                                <img src="<?php echo $comment['foto']; ?>" class="img-fluid rounded mt-2" alt="Foto komentar" style="max-width: 200px;">
                                            <?php endif; ?>
                                            <small class="ms-2" style="color: #ffffff;"><?php echo $comment['tanggal']; ?></small>
                                        </div>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="col-md-4">
        <?php if (isset($edit_post)): ?>
            <div class="card">
                <div class="card-header">
                    <h5>Edit Pengaduan</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="post_id" value="<?php echo $edit_post['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Mode:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode" id="edit_anonim" value="anonim" <?php echo $edit_post['is_anonim'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="edit_anonim">Anonim</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode" id="edit_normal" value="normal" <?php echo !$edit_post['is_anonim'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="edit_normal">Normal</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="keluhan" class="form-label">Keluhan/Saran:</label>
                            <textarea class="form-control" id="keluhan" name="keluhan" rows="4" required><?php echo htmlspecialchars($edit_post['keluhan']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto baru (opsional, maksimal 3, akan mengganti foto lama):</label>
                            <input type="file" class="form-control" id="foto" name="foto[]" accept="image/*" multiple>
                        </div>

                        <button type="submit" name="submit_edit_post" class="btn btn-primary w-100">Update Pengaduan</button>
                        <a href="user_dashboard.php" class="btn btn-secondary w-100 mt-2">Batal</a>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h5>Buat Pengaduan Baru</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Mode:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode" id="anonim" value="anonim" checked>
                                <label class="form-check-label" for="anonim">Anonim</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode" id="normal" value="normal">
                                <label class="form-check-label" for="normal">Normal</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="keluhan" class="form-label">Keluhan/Saran:</label>
                            <textarea class="form-control" id="keluhan" name="keluhan" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto (opsional, maksimal 3):</label>
                            <input type="file" class="form-control" id="foto" name="foto[]" accept="image/*" multiple>
                        </div>

                        <button type="submit" name="submit_post" class="btn btn-primary w-100">Kirim Pengaduan</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Toggle comment form visibility
function toggleCommentForm(postId) {
    const form = document.getElementById('comment-form-' + postId);
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}

// Validate photo upload count
function validatePhotoCount(input) {
    const files = input.files;
    if (files.length > 3) {
        // Create custom alert modal
        const alertModal = document.createElement('div');
        alertModal.innerHTML = `
            <div class="modal fade" id="photoAlertModal" tabindex="-1" aria-labelledby="photoAlertModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="background-color: #1E293B; border: 1px solid rgba(255, 255, 255, 0.1);">
                        <div class="modal-header" style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                            <h5 class="modal-title" id="photoAlertModalLabel" style="color: #F8FAFC;">Peringatan</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="color: #E2E8F0;">
                            Maksimal hanya 3 foto yang dapat diupload!
                        </div>
                        <div class="modal-footer" style="border-top: 1px solid rgba(255, 255, 255, 0.1);">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(alertModal);

        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('photoAlertModal'));
        modal.show();

        // Remove modal from DOM after it's hidden
        document.getElementById('photoAlertModal').addEventListener('hidden.bs.modal', function() {
            document.body.removeChild(alertModal);
        });

        input.value = ''; // Clear the selection
        return false;
    }
    return true;
}

// Add event listener to photo inputs
document.addEventListener('DOMContentLoaded', function() {
    const photoInputs = document.querySelectorAll('input[name="foto[]"]');
    photoInputs.forEach(input => {
        input.addEventListener('change', function() {
            validatePhotoCount(this);
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
