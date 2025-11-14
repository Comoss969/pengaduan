<?php
include 'config.php';
include 'includes/profanity_filter.php';

// Initialize profanity filter
$profanityFilter = new ProfanityFilter($pdo);

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login_admin.php');
    exit;
}

$page_title = 'Admin Dashboard';

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

    // Menyimpan komentar admin dengan user_id untuk konsistensi
    // Pastikan user_id valid dan username ada (seperti sistem form pengaduan)
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0 && isset($_SESSION['username'])) {
        $user_id = $_SESSION['user_id'];
        // Username akan diambil dari users table melalui JOIN saat display
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, komentar, foto, is_admin) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$post_id, $user_id, $komentar, $foto, true]);
        $success = "Komentar admin berhasil dikirim!";
    } else {
        $error = "Session tidak valid. Silakan login kembali.";
    }
}

// Handle edit comment
if (isset($_GET['edit_comment'])) {
    $edit_comment_id = (int)$_GET['edit_comment'];
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ?");
    $stmt->execute([$edit_comment_id]);
    $edit_comment = $stmt->fetch();
    if (!$edit_comment) {
        $error = "Komentar tidak ditemukan.";
    }
}

// Handle edit comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_edit_comment'])) {
    $comment_id = $_POST['comment_id'];
    $komentar = trim($_POST['komentar']);

    // Check for profanity
    $profanityCheck = $profanityFilter->checkProfanity($komentar);
    if ($profanityCheck['has_profanity']) {
        $profanityFilter->logProfanity($komentar, $profanityCheck['found_words'], $_SESSION['user_id'], 'comment');
        $komentar = $profanityFilter->censorText($komentar);
        $warning = "Peringatan: Kata-kata tidak pantas telah dideteksi dan disensor.";
    }

    // Update comment (admin can edit any comment)
    $stmt = $pdo->prepare("UPDATE comments SET komentar = ? WHERE id = ?");
    $stmt->execute([$komentar, $comment_id]);

    // Handle new photo (replace existing)
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        // Delete existing photo file if exists
        $stmt_old = $pdo->prepare("SELECT foto FROM comments WHERE id = ?");
        $stmt_old->execute([$comment_id]);
        $old_comment = $stmt_old->fetch();
        if ($old_comment && $old_comment['foto'] && file_exists($old_comment['foto'])) {
            unlink($old_comment['foto']);
        }

        // Upload new photo
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $foto = $target_dir . basename($_FILES['foto']['name']);
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $foto)) {
            $stmt_photo = $pdo->prepare("UPDATE comments SET foto = ? WHERE id = ?");
            $stmt_photo->execute([$foto, $comment_id]);
        }
    }

    $success = isset($warning) ? $warning : "Komentar berhasil diupdate!";
    header('Location: admin_dashboard.php');
    exit;
}

// Handle delete post (Soft Delete untuk Admin)
// Admin juga menggunakan soft delete untuk konsistensi
// Admin bisa melihat postingan terhapus di halaman Trash
if (isset($_GET['delete_post'])) {
    $post_id = (int)$_GET['delete_post'];
    
    // Validasi: Pastikan post_id valid
    if ($post_id > 0) {
        // Cek apakah postingan sudah terhapus
        $stmt = $pdo->prepare("SELECT deleted_at FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch();
        
        if (!$post) {
            $error = "Postingan tidak ditemukan.";
        } elseif ($post['deleted_at'] !== null) {
            $error = "Postingan ini sudah dihapus sebelumnya.";
        } else {
            // Soft delete: Set deleted_at dengan timestamp saat ini
            $stmt = $pdo->prepare("UPDATE posts SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$post_id]);
            
            if ($stmt->rowCount() > 0) {
                $success = "Postingan berhasil dihapus! (Lihat di halaman Trash)";
            } else {
                $error = "Gagal menghapus postingan.";
            }
        }
    } else {
        $error = "ID postingan tidak valid.";
    }
}

// Fetch posts (hanya yang tidak terhapus)
// Admin dashboard hanya menampilkan postingan aktif
// Postingan terhapus bisa dilihat di halaman Trash (admin_trash.php)
$stmt = $pdo->query("SELECT * FROM posts WHERE deleted_at IS NULL ORDER BY tanggal_post DESC");
$posts = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>



<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 style="color: #ffffff;">Admin Dashboard - Semua Pengaduan</h2>
            <a href="admin_trash.php" class="btn btn-primary">
                Postingan Terhapus
                <?php
                // Hitung jumlah postingan terhapus untuk ditampilkan di badge
                try {
                    $stmt_trash = $pdo->query("SELECT COUNT(*) as count FROM posts WHERE deleted_at IS NOT NULL");
                    $trash_count = $stmt_trash->fetch()['count'];
                    if ($trash_count > 0) {
                        echo "<span class='badge bg-primary ms-2'>$trash_count</span>";
                    }
                } catch (PDOException $e) {
                    // Skip jika error (misalnya kolom belum ada)
                }
                ?>
            </a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($edit_comment)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Edit Komentar</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="comment_id" value="<?php echo $edit_comment['id']; ?>">
                        <div class="mb-3">
                            <label for="komentar" class="form-label">Komentar:</label>
                            <textarea class="form-control" id="komentar" name="komentar" rows="4" required><?php echo htmlspecialchars($edit_comment['komentar']); ?></textarea>
                        </div>

                        <?php if (isset($edit_comment['foto']) && $edit_comment['foto']): ?>
                            <div class="mb-3">
                                <label class="form-label">Foto saat ini:</label>
                                <img src="<?php echo $edit_comment['foto']; ?>" class="img-fluid rounded mb-2" alt="Foto komentar" style="max-width: 200px;">
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto baru (opsional, akan mengganti foto lama):</label>
                            <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                        </div>

                        <button type="submit" name="submit_edit_comment" class="btn btn-primary">Update Komentar</button>
                        <a href="admin_dashboard.php" class="btn btn-secondary ms-2">Batal</a>
                    </form>
                </div>
            </div>
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
                        <a href="?delete_post=<?php echo $post['id']; ?>" class="btn btn-sm btn-danger delete-btn">Hapus</a>
                    </div>
                    <p class="card-text mt-2">
                        <?php
                        // Bagian yang menampilkan kata asli untuk admin
                        echo htmlspecialchars($post['keluhan']);
                        ?>
                        <?php
                        // Bagian yang membuat log peringatan dan update jumlah kata kasar terindikasi
                        $profanityCheck = $profanityFilter->checkProfanity($post['keluhan']);
                        if ($profanityCheck['has_profanity']): ?>
                            <span class="badge bg-primary ms-2">Kata Kasar: <?php echo count($profanityCheck['found_words']); ?></span>
                        <?php endif; ?>
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
                        <button class="btn btn-sm btn-outline-primary" onclick="toggleCommentForm(<?php echo $post['id']; ?>)">Komentar sebagai Admin</button>
                        <div id="comment-form-<?php echo $post['id']; ?>" style="display: none;" class="mt-2">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <div class="mb-2">
                                    <input type="text" name="komentar" class="form-control" placeholder="Tulis komentar admin..." required>
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
                                        <div class="btn-group ms-2" role="group">
                                            <a href="?edit_comment=<?php echo $comment['id']; ?>" class="btn btn-sm btn-warning rounded-pill">Edit</a>
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
</div>

<script>
// Toggle comment form
function toggleCommentForm(postId) {
    const form = document.getElementById('comment-form-' + postId);
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}


</script>

<?php include 'includes/footer.php'; ?>
