<?php
/**
 * Helper Functions untuk Fitur Trash Bin
 * 
 * File ini berisi fungsi-fungsi helper yang digunakan untuk fitur trash bin
 */

/**
 * Menghitung hari tersisa sampai auto purge
 * 
 * @param string $deleted_at Timestamp kapan postingan dihapus
 * @return int Jumlah hari tersisa (0 jika sudah expired)
 */
function getDaysUntilPurge($deleted_at) {
    if ($deleted_at === null) {
        return 0;
    }
    
    $deleted_timestamp = strtotime($deleted_at);
    $current_timestamp = time();
    $days_passed = floor(($current_timestamp - $deleted_timestamp) / (60 * 60 * 24));
    $days_remaining = 30 - $days_passed;
    
    return max(0, $days_remaining); // Tidak boleh negatif
}

/**
 * Cek apakah postingan sudah melewati 30 hari (expired)
 * 
 * @param string $deleted_at Timestamp kapan postingan dihapus
 * @return bool True jika sudah expired (>= 30 hari), False jika belum
 */
function isPostExpired($deleted_at) {
    if ($deleted_at === null) {
        return false;
    }
    
    $deleted_timestamp = strtotime($deleted_at);
    $current_timestamp = time();
    $days_passed = floor(($current_timestamp - $deleted_timestamp) / (60 * 60 * 24));
    
    return $days_passed >= 30;
}

/**
 * Soft delete postingan (menandai sebagai terhapus)
 * 
 * @param PDO $pdo Database connection
 * @param int $post_id ID postingan
 * @param int|null $user_id ID user (optional, untuk validasi ownership)
 * @return bool True jika berhasil, False jika gagal
 */
function softDeletePost($pdo, $post_id, $user_id = null) {
    try {
        if ($user_id !== null) {
            // Validasi ownership untuk user
            $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$post_id]);
            $post = $stmt->fetch();
            
            if (!$post || $post['user_id'] != $user_id) {
                return false; // User tidak memiliki akses
            }
        }
        
        // Soft delete: Set deleted_at dengan timestamp saat ini
        $stmt = $pdo->prepare("UPDATE posts SET deleted_at = NOW() WHERE id = ?");
        $stmt->execute([$post_id]);
        
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error in softDeletePost: " . $e->getMessage());
        return false;
    }
}

/**
 * Restore postingan (mengembalikan ke aktif)
 * 
 * @param PDO $pdo Database connection
 * @param int $post_id ID postingan
 * @return bool True jika berhasil, False jika gagal
 */
function restorePost($pdo, $post_id) {
    try {
        // Restore: Set deleted_at kembali ke NULL
        $stmt = $pdo->prepare("UPDATE posts SET deleted_at = NULL WHERE id = ?");
        $stmt->execute([$post_id]);
        
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error in restorePost: " . $e->getMessage());
        return false;
    }
}

/**
 * Hapus permanen postingan (hard delete)
 * 
 * @param PDO $pdo Database connection
 * @param int $post_id ID postingan
 * @return bool True jika berhasil, False jika gagal
 */
function permanentDeletePost($pdo, $post_id) {
    try {
        // Hard delete: DELETE FROM database
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error in permanentDeletePost: " . $e->getMessage());
        return false;
    }
}

/**
 * Ambil semua postingan yang expired (sudah 30 hari di trash)
 * 
 * @param PDO $pdo Database connection
 * @return array Array of posts
 */
function getExpiredPosts($pdo) {
    try {
        $sql = "SELECT id, deleted_at, nama, keluhan 
                FROM posts 
                WHERE deleted_at IS NOT NULL 
                AND DATEDIFF(NOW(), deleted_at) >= 30
                ORDER BY deleted_at ASC";
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getExpiredPosts: " . $e->getMessage());
        return [];
    }
}

/**
 * Ambil semua postingan terhapus (untuk admin trash page)
 * 
 * @param PDO $pdo Database connection
 * @return array Array of posts
 */
function getDeletedPosts($pdo) {
    try {
        $sql = "SELECT * FROM posts 
                WHERE deleted_at IS NOT NULL 
                ORDER BY deleted_at DESC";
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getDeletedPosts: " . $e->getMessage());
        return [];
    }
}

/**
 * Hitung jumlah postingan terhapus
 * 
 * @param PDO $pdo Database connection
 * @return int Jumlah postingan terhapus
 */
function getDeletedPostsCount($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM posts WHERE deleted_at IS NOT NULL");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'];
    } catch (PDOException $e) {
        error_log("Error in getDeletedPostsCount: " . $e->getMessage());
        return 0;
    }
}

/**
 * Ambil semua postingan aktif (tidak terhapus)
 * 
 * @param PDO $pdo Database connection
 * @return array Array of posts
 */
function getActivePosts($pdo) {
    try {
        $sql = "SELECT * FROM posts 
                WHERE deleted_at IS NULL 
                ORDER BY tanggal_post DESC";
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getActivePosts: " . $e->getMessage());
        return [];
    }
}

?>

