<?php
/**
 * Halaman Admin: Postingan Terhapus (Trash Bin)
 * 
 * Halaman ini menampilkan semua postingan yang sudah dihapus (soft delete)
 * Admin dapat:
 * - Melihat postingan yang terhapus
 * - Restore postingan (mengembalikan ke aktif)
 * - Hapus permanen (hard delete)
 * - Melihat countdown hari sampai auto purge (30 hari)
 */

include 'config.php';
include 'includes/profanity_filter.php';

// Initialize profanity filter
$profanityFilter = new ProfanityFilter($pdo);

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login_admin.php');
    exit;
}

$page_title = 'Admin Trash - Postingan Terhapus';

// Handle restore post (mengembalikan postingan ke aktif)
// Restore dilakukan dengan mengosongkan deleted_at (set NULL)
if (isset($_GET['restore_post'])) {
    $post_id = (int)$_GET['restore_post'];
    
    if ($post_id > 0) {
        // Restore: Set deleted_at kembali ke NULL
        $stmt = $pdo->prepare("UPDATE posts SET deleted_at = NULL WHERE id = ?");
        $stmt->execute([$post_id]);
        
        if ($stmt->rowCount() > 0) {
            $success = "Postingan berhasil dikembalikan!";
        } else {
            $error = "Postingan tidak ditemukan atau sudah aktif.";
        }
    } else {
        $error = "ID postingan tidak valid.";
    }
}

// Handle permanent delete (hard delete)
// Hapus permanen: benar-benar menghapus postingan dari database
// Comments akan terhapus otomatis karena ON DELETE CASCADE di foreign key
if (isset($_GET['permanent_delete'])) {
    $post_id = (int)$_GET['permanent_delete'];
    
    if ($post_id > 0) {
        // Cek apakah postingan ada sebelum dihapus
        $stmt = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch();
        
        if (!$post) {
            $error = "Postingan tidak ditemukan.";
        } else {
            // Hapus permanen: DELETE FROM database
            // Comments akan terhapus otomatis karena ON DELETE CASCADE
            // Foreign key constraint: FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
            $stmt->execute([$post_id]);
            
            if ($stmt->rowCount() > 0) {
                $success = "Postingan dan semua komentarnya berhasil dihapus permanen!";
            } else {
                $error = "Gagal menghapus postingan.";
            }
        }
    } else {
        $error = "ID postingan tidak valid.";
    }
}

// Handle bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['selected_ids'])) {
    $selected_ids = $_POST['selected_ids'];
    $bulk_action = $_POST['bulk_action'];

    if ($bulk_action === 'restore') {
        $restored_count = 0;
        foreach ($selected_ids as $post_id) {
            $post_id = (int)$post_id;
            if ($post_id > 0) {
                $stmt = $pdo->prepare("UPDATE posts SET deleted_at = NULL WHERE id = ?");
                $stmt->execute([$post_id]);
                if ($stmt->rowCount() > 0) {
                    $restored_count++;
                }
            }
        }
        if ($restored_count > 0) {
            $success = "$restored_count postingan berhasil dikembalikan!";
        }
    } elseif ($bulk_action === 'delete') {
        $deleted_count = 0;
        foreach ($selected_ids as $post_id) {
            $post_id = (int)$post_id;
            if ($post_id > 0) {
                $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
                $stmt->execute([$post_id]);
                if ($stmt->rowCount() > 0) {
                    $deleted_count++;
                }
            }
        }
        if ($deleted_count > 0) {
            $success = "$deleted_count postingan dan semua komentarnya berhasil dihapus permanen!";
        }
    }
}

// Fetch deleted posts (hanya yang sudah dihapus)
// Urutkan berdasarkan deleted_at DESC (yang baru dihapus di atas)
$stmt = $pdo->query("SELECT * FROM posts WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
$deleted_posts = $stmt->fetchAll();

// Fungsi untuk menghitung hari sampai auto purge
// Auto purge terjadi setelah 30 hari dari deleted_at
function getDaysUntilPurge($deleted_at) {
    $deleted_timestamp = strtotime($deleted_at);
    $current_timestamp = time();
    $days_passed = floor(($current_timestamp - $deleted_timestamp) / (60 * 60 * 24));
    $days_remaining = 30 - $days_passed;
    return max(0, $days_remaining); // Tidak boleh negatif
}

// Fungsi untuk cek apakah postingan sudah melewati 30 hari
function isExpired($deleted_at) {
    $deleted_timestamp = strtotime($deleted_at);
    $current_timestamp = time();
    $days_passed = floor(($current_timestamp - $deleted_timestamp) / (60 * 60 * 24));
    return $days_passed >= 30;
}
?>

<?php include 'includes/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 style="color: #ffffff;">Postingan Terhapus</h2>
            <div>
                <a href="admin_dashboard.php" class="btn btn-primary">Kembali ke Dashboard</a>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-primary"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-primary"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Info tentang Trash Bin -->
        <div class="alert alert-primary">
            <strong>Info:</strong> Postingan yang dihapus akan otomatis terhapus permanen setelah <strong>30 hari</strong>. 
            Anda dapat mengembalikan (restore) atau menghapus permanen secara manual.
        </div>

        <!-- Form untuk bulk actions -->
        <?php if (count($deleted_posts) > 0): ?>
        <form method="POST" id="bulkForm" class="mb-3">
            <div class="d-flex gap-2 mb-3">
                <button type="button" id="bulkRestoreBtn" class="btn btn-primary">
                    Restore Selected
                </button>
                <button type="button" id="bulkDeleteBtn" class="btn btn-primary">
                    Hapus Permanen Selected
                </button>
            </div>

            <!-- Select All Checkbox -->
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="selectAll" onchange="selectAll(this)">
                <label class="form-check-label" style="color: #ffffff;" for="selectAll">
                    Pilih Semua
                </label>
            </div>
        <?php endif; ?>

        <!-- Daftar Postingan Terhapus -->
        <?php if (count($deleted_posts) == 0): ?>
            <div class="alert alert-primary">
                <strong>Trash Bin kosong.</strong> Tidak ada postingan yang dihapus.
            </div>
        <?php else: ?>
            <p style="color: #94A3B8;">Total: <strong style="color: #ffffff;"><?php echo count($deleted_posts); ?></strong> postingan terhapus</p>
            
            <?php foreach ($deleted_posts as $post): ?>
                <?php
                $days_remaining = getDaysUntilPurge($post['deleted_at']);
                $is_expired = isExpired($post['deleted_at']);
                ?>
                <div class="card timeline-item mb-3">
                    <div class="card-body">
                        <div class="form-check mb-2">
                            <input class="form-check-input itemCheckbox" type="checkbox" name="selected_ids[]" value="<?php echo $post['id']; ?>" form="bulkForm">
                            <label class="form-check-label" style="color: #ffffff;">
                                Pilih untuk bulk action
                            </label>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h5 class="card-title" style="color: #ffffff;">
                                    <?php if ($post['is_anonim']): ?>
                                        Anonim
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($post['nama']); ?>
                                    <?php endif; ?>
                                </h5>
                                <small style="color: #ffffff;">
                                    Diposting: <?php echo $post['tanggal_post']; ?> | 
                                    Dihapus: <?php echo $post['deleted_at']; ?>
                                </small>
                                
                                <!-- Status Auto Purge -->
                                <?php if ($is_expired): ?>
                                    <span class="badge bg-primary ms-2">Expired (Siap untuk auto purge)</span>
                                <?php else: ?>
                                    <span class="badge bg-primary ms-2">
                                        Auto purge dalam <?php echo $days_remaining; ?> hari
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="?restore_post=<?php echo $post['id']; ?>" 
                                   class="btn btn-sm btn-primary" 
                                   onclick="return confirm('Yakin ingin mengembalikan postingan ini?')">
                                    Restore
                                </a>
                                <a href="?permanent_delete=<?php echo $post['id']; ?>" 
                                   class="btn btn-sm btn-primary" 
                                   onclick="return confirm('Yakin ingin menghapus permanen? Tindakan ini tidak bisa dibatalkan!')">
                                    Hapus Permanen
                                </a>
                            </div>
                        </div>
                        
                        <p class="card-text mt-2" style="color: #E2E8F0;">
                            <?php echo htmlspecialchars($post['keluhan']); ?>
                        </p>
                        
                        <?php if ($post['foto']): ?>
                            <img src="<?php echo $post['foto']; ?>" class="img-fluid rounded mt-2" alt="Foto" style="max-width: 300px;">
                        <?php endif; ?>
                        
                        <!-- Profanity Check -->
                        <?php
                        $profanityCheck = $profanityFilter->checkProfanity($post['keluhan']);
                        if ($profanityCheck['has_profanity']): ?>
                            <span class="badge bg-primary mt-2">Kata Kasar: <?php echo count($profanityCheck['found_words']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
const selectAll = document.getElementById("selectAll");
const itemCheckboxes = document.querySelectorAll(".itemCheckbox");

if (selectAll) {
    selectAll.addEventListener("change", function () {
        itemCheckboxes.forEach(cb => cb.checked = selectAll.checked);
    });
}

itemCheckboxes.forEach(cb => {
    cb.addEventListener("change", function () {
        const semuaChecked = Array.from(itemCheckboxes).every(x => x.checked);
        selectAll.checked = semuaChecked;
    });
});

// Bulk restore action
document.getElementById('bulkRestoreBtn').addEventListener('click', async function() {
    const selectedIds = Array.from(document.querySelectorAll('input[name="selected_ids[]"]:checked')).map(cb => cb.value);

    if (selectedIds.length === 0) {
        alert('Tidak ada item yang dipilih.');
        return;
    }

    const confirmed = await customConfirm('Yakin ingin mengembalikan postingan yang dipilih?', 'Konfirmasi Bulk Restore');
    if (confirmed) {
        // Create hidden inputs
        const form = document.getElementById('bulkForm');
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'bulk_action';
        actionInput.value = 'restore';
        form.appendChild(actionInput);

        form.submit();
    }
});

// Bulk delete action
document.getElementById('bulkDeleteBtn').addEventListener('click', async function() {
    const selectedIds = Array.from(document.querySelectorAll('input[name="selected_ids[]"]:checked')).map(cb => cb.value);

    if (selectedIds.length === 0) {
        alert('Tidak ada item yang dipilih.');
        return;
    }

    const confirmed = await customConfirm('Yakin ingin menghapus permanen postingan yang dipilih? Tindakan ini tidak bisa dibatalkan!', 'Konfirmasi Bulk Delete');
    if (confirmed) {
        // Create hidden inputs
        const form = document.getElementById('bulkForm');
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'bulk_action';
        actionInput.value = 'delete';
        form.appendChild(actionInput);

        form.submit();
    }
});
</script>

<?php include 'includes/footer.php'; ?>

