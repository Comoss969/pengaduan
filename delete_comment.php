<?php
/**
 * Script untuk menghapus komentar dengan kontrol akses dan logging
 * Menggunakan MySQLi untuk keamanan SQL injection
 * Digunakan oleh AJAX dari user_dashboard.php dan admin_dashboard.php
 */

session_start();
include 'config.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login untuk menghapus komentar.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

$comment_id = $_POST['comment_id'] ?? null;
$post_id = $_POST['post_id'] ?? null;

if (!$comment_id || !$post_id) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap.']);
    exit;
}

// Validasi input sebagai integer
$comment_id = (int)$comment_id;
$post_id = (int)$post_id;

if ($comment_id <= 0 || $post_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak valid.']);
    exit;
}

// Ambil informasi komentar untuk validasi menggunakan MySQLi prepared statement
$stmt = $mysqli->prepare("SELECT user_id, is_admin, komentar FROM comments WHERE id = ? AND post_id = ?");
$stmt->bind_param("ii", $comment_id, $post_id);
$stmt->execute();
$result = $stmt->get_result();
$comment = $result->fetch_assoc();
$stmt->close();

if (!$comment) {
    echo json_encode(['success' => false, 'message' => 'Komentar tidak ditemukan.']);
    exit;
}

$canDelete = false;
$isAdminAction = false;

// Kontrol akses penghapusan
if ($_SESSION['role'] === 'admin') {
    // Admin dapat menghapus semua komentar
    $canDelete = true;
    $isAdminAction = true;
} elseif ($_SESSION['role'] === 'user') {
    // User hanya bisa hapus komentar mereka sendiri (bukan admin comment)
    if (!$comment['is_admin'] && $comment['user_id'] == $_SESSION['user_id']) {
        $canDelete = true;
    }
}

if (!$canDelete) {
    echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki izin untuk menghapus komentar ini.']);
    exit;
}

// Hapus komentar menggunakan MySQLi prepared statement
$stmt_delete = $mysqli->prepare("DELETE FROM comments WHERE id = ?");
$stmt_delete->bind_param("i", $comment_id);
$delete_result = $stmt_delete->execute();
$stmt_delete->close();

if (!$delete_result) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus komentar.']);
    exit;
}

// Bagian yang membuat log aktivitas saat admin menghapus komentar
if ($isAdminAction) {
    // Pastikan tabel activity_logs ada, jika tidak buat
    $create_table_sql = "CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        action VARCHAR(50) NOT NULL,
        target_type VARCHAR(50) NOT NULL,
        target_id INT NOT NULL,
        details TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $mysqli->query($create_table_sql);

    $stmt_log = $mysqli->prepare("INSERT INTO activity_logs (admin_id, action, target_type, target_id, details) VALUES (?, ?, ?, ?, ?)");
    $action = 'delete_comment';
    $target_type = 'comment';
    $details = 'Admin menghapus komentar: ' . substr($comment['komentar'], 0, 100) . '...';
    $stmt_log->bind_param("issis", $_SESSION['user_id'], $action, $target_type, $comment_id, $details);
    $stmt_log->execute();
    $stmt_log->close();
}

echo json_encode(['success' => true, 'message' => 'Komentar berhasil dihapus.']);
?>
