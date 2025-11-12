<?php
/**
 * Script Auto Purge: Menghapus Permanen Postingan yang Sudah 30 Hari di Trash
 * 
 * Script ini menghapus permanen (hard delete) semua postingan yang sudah dihapus (soft delete)
 * dan sudah melewati 30 hari sejak deleted_at.
 * 
 * Cara menjalankan:
 * 1. Manual: Akses http://localhost/pengaduan/auto_purge.php
 * 2. Cron Job: Setup cron job untuk menjalankan script ini secara otomatis setiap hari
 * 
 * Contoh Cron Job (Linux):
 * 0 2 * * * /usr/bin/php /path/to/pengaduan/auto_purge.php
 * (Jalankan setiap hari pukul 02:00)
 * 
 * Contoh Task Scheduler (Windows):
 * Buat scheduled task yang menjalankan: php.exe C:\xampp\htdocs\pengaduan\auto_purge.php
 */

include 'config.php';

// Set execution time limit untuk script yang mungkin memproses banyak data
set_time_limit(300); // 5 menit

// Log file untuk tracking (opsional)
$log_file = 'logs/auto_purge.log';
$log_dir = dirname($log_file);
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

/**
 * Fungsi untuk menulis log
 */
function writeLog($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
    echo $log_message;
}

// Cek apakah dijalankan dari command line atau web browser
$is_cli = php_sapi_name() === 'cli';

if (!$is_cli) {
    // Jika dijalankan dari web browser, tampilkan HTML
    echo "<!DOCTYPE html>";
    echo "<html lang='en'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Auto Purge - Trash Bin</title>";
    echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
    echo "<style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            padding: 20px; 
            background: linear-gradient(to bottom right, #0F172A, #1E293B);
            color: #F8FAFC;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #1E293B;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.7);
        }
        h2 { color: #3B82F6; }
        .success { color: #10B981; }
        .error { color: #EF4444; }
        .info { color: #60A5FA; }
        .warning { color: #F59E0B; }
        pre { background: #334155; padding: 15px; border-radius: 8px; overflow-x: auto; }
    </style>";
    echo "</head>";
    echo "<body>";
    echo "<div class='container'>";
    echo "<h2>🗑️ Auto Purge - Trash Bin</h2>";
    echo "<hr>";
    echo "<pre>";
}

writeLog("=== Auto Purge Script Started ===");

try {
    // Query untuk mencari postingan yang sudah dihapus lebih dari 30 hari
    // DATEDIFF menghitung selisih hari antara deleted_at dan NOW()
    // Hanya ambil postingan dengan deleted_at IS NOT NULL dan sudah lebih dari 30 hari
    $sql = "SELECT id, deleted_at, nama, keluhan 
            FROM posts 
            WHERE deleted_at IS NOT NULL 
            AND DATEDIFF(NOW(), deleted_at) >= 30
            ORDER BY deleted_at ASC";
    
    $stmt = $pdo->query($sql);
    $expired_posts = $stmt->fetchAll();
    
    $total_expired = count($expired_posts);
    writeLog("Found $total_expired post(s) that have been in trash for 30+ days");
    
    if ($total_expired == 0) {
        writeLog("No posts to purge. All good!");
        if (!$is_cli) {
            echo "<p class='success'>✅ Tidak ada postingan yang perlu dihapus permanen.</p>";
            echo "<p class='info'>Semua postingan di trash masih dalam batas waktu 30 hari.</p>";
        }
    } else {
        $deleted_count = 0;
        $error_count = 0;
        
        foreach ($expired_posts as $post) {
            $post_id = $post['id'];
            $deleted_at = $post['deleted_at'];
            $days_in_trash = floor((time() - strtotime($deleted_at)) / (60 * 60 * 24));
            
            try {
                // Hapus permanen postingan dari database
                // Comments akan terhapus otomatis karena ON DELETE CASCADE
                // Foreign key constraint: FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
                $delete_stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
                $delete_stmt->execute([$post_id]);
                
                if ($delete_stmt->rowCount() > 0) {
                    $deleted_count++;
                    writeLog("✅ Deleted post ID: $post_id (was in trash for $days_in_trash days)");
                } else {
                    $error_count++;
                    writeLog("❌ Failed to delete post ID: $post_id (post not found)");
                }
            } catch (PDOException $e) {
                $error_count++;
                writeLog("❌ Error deleting post ID: $post_id - " . $e->getMessage());
            }
        }
        
        writeLog("=== Auto Purge Completed ===");
        writeLog("Total expired: $total_expired");
        writeLog("Successfully deleted: $deleted_count");
        writeLog("Errors: $error_count");
        
        if (!$is_cli) {
            echo "<p class='info'>📊 <strong>Hasil Auto Purge:</strong></p>";
            echo "<ul>";
            echo "<li>Total postingan yang expired (30+ hari): <strong>$total_expired</strong></li>";
            echo "<li>Berhasil dihapus permanen: <strong class='success'>$deleted_count</strong></li>";
            if ($error_count > 0) {
                echo "<li>Error: <strong class='error'>$error_count</strong></li>";
            }
            echo "</ul>";
            
            if ($deleted_count > 0) {
                echo "<p class='success'>✅ Auto purge berhasil! $deleted_count postingan telah dihapus permanen.</p>";
            }
            
            echo "<hr>";
            echo "<p class='info'>💡 <strong>Tips:</strong> Setup cron job atau task scheduler untuk menjalankan script ini otomatis setiap hari.</p>";
            echo "<p class='warning'>⚠️ <strong>Keamanan:</strong> Untuk produksi, batasi akses ke file ini atau jalankan hanya melalui cron job.</p>";
        }
    }
    
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
    writeLog("❌ $error_message");
    
    if (!$is_cli) {
        echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

if (!$is_cli) {
    echo "</pre>";
    echo "</div>";
    echo "</body>";
    echo "</html>";
}

?>

