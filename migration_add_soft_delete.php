<?php
/**
 * Migration Script: Menambahkan Soft Delete ke Tabel Posts
 * 
 * Script ini menambahkan kolom deleted_at ke tabel posts untuk fitur trash bin.
 * Jalankan script ini sekali untuk update struktur database.
 * 
 * Akses: http://localhost/pengaduan/migration_add_soft_delete.php
 */

include 'config.php';

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Migration: Add Soft Delete</title>";
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
    code { background: #334155; padding: 2px 6px; border-radius: 4px; }
</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h2>🔄 Migration: Add Soft Delete to Posts Table</h2>";
echo "<hr>";

try {
    // Cek apakah kolom deleted_at sudah ada
    $stmt = $pdo->query("SHOW COLUMNS FROM posts LIKE 'deleted_at'");
    $column_exists = $stmt->fetch();
    
    if ($column_exists) {
        echo "<p class='info'>ℹ️ Kolom <code>deleted_at</code> sudah ada di tabel posts.</p>";
        echo "<p class='success'>✅ Migration sudah dilakukan sebelumnya.</p>";
    } else {
        echo "<p class='info'>🔄 Menambahkan kolom <code>deleted_at</code> ke tabel posts...</p>";
        
        // Tambahkan kolom deleted_at
        $pdo->exec("ALTER TABLE posts ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER profanity_count");
        echo "<p class='success'>✅ Kolom <code>deleted_at</code> berhasil ditambahkan!</p>";
        
        // Tambahkan index untuk performa query
        echo "<p class='info'>🔄 Menambahkan index pada kolom <code>deleted_at</code>...</p>";
        try {
            $pdo->exec("CREATE INDEX idx_posts_deleted_at ON posts(deleted_at)");
            echo "<p class='success'>✅ Index berhasil ditambahkan!</p>";
        } catch (PDOException $e) {
            // Index mungkin sudah ada, tidak masalah
            if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                echo "<p class='error'>⚠️ Warning: " . htmlspecialchars($e->getMessage()) . "</p>";
            } else {
                echo "<p class='info'>ℹ️ Index sudah ada.</p>";
            }
        }
        
        // Verifikasi
        $stmt = $pdo->query("SHOW COLUMNS FROM posts LIKE 'deleted_at'");
        $verify = $stmt->fetch();
        if ($verify) {
            echo "<hr>";
            echo "<h3>✅ Migration Berhasil!</h3>";
            echo "<p class='success'>Kolom <code>deleted_at</code> sudah ditambahkan ke tabel posts.</p>";
            echo "<p class='info'>Struktur kolom:</p>";
            echo "<ul>";
            echo "<li>Nama: <code>deleted_at</code></li>";
            echo "<li>Type: <code>DATETIME</code></li>";
            echo "<li>Nullable: <code>YES</code> (NULL = aktif, NOT NULL = terhapus)</li>";
            echo "<li>Default: <code>NULL</code></li>";
            echo "</ul>";
        }
    }
    
    echo "<hr>";
    echo "<h3>📋 Fitur Trash Bin:</h3>";
    echo "<ul>";
    echo "<li>✅ Soft delete: Postingan tidak benar-benar dihapus dari database</li>";
    echo "<li>✅ Admin dapat melihat postingan terhapus di halaman Trash</li>";
    echo "<li>✅ Auto purge: Postingan akan terhapus permanen setelah 30 hari</li>";
    echo "<li>✅ Admin dapat menghapus permanen secara manual</li>";
    echo "</ul>";
    
    echo "<p class='warning'><strong>⚠️ PENTING:</strong> Setelah migration selesai, hapus atau rename file ini untuk keamanan!</p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";
echo "</body>";
echo "</html>";
?>

