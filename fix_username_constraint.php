<?php
/**
 * Script untuk memperbaiki constraint username di database
 * Memungkinkan admin dan user menggunakan username yang sama
 * Tapi sesama user atau sesama admin tidak bisa menggunakan username yang sama
 * 
 * Akses file ini melalui browser: http://localhost/pengaduan/fix_username_constraint.php
 * Setelah selesai, hapus atau rename file ini untuk keamanan
 */

include 'config.php';

$success = false;
$error = '';

try {
    // Cek apakah constraint username sudah ada
    $stmt = $pdo->query("SHOW INDEX FROM users WHERE Key_name = 'username'");
    $hasUsernameIndex = $stmt->fetch();
    
    if ($hasUsernameIndex) {
        // Hapus constraint UNIQUE pada kolom username
        $pdo->exec("ALTER TABLE users DROP INDEX username");
        echo "<p style='color: green;'>✅ Constraint UNIQUE pada username berhasil dihapus.</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Constraint UNIQUE pada username tidak ditemukan (mungkin sudah dihapus).</p>";
    }
    
    // Cek apakah composite unique key sudah ada
    $stmt = $pdo->query("SHOW INDEX FROM users WHERE Key_name = 'unique_username_role'");
    $hasCompositeIndex = $stmt->fetch();
    
    if (!$hasCompositeIndex) {
        // Buat composite unique key pada (username, role)
        // Ini memungkinkan username yang sama untuk role yang berbeda
        // Tapi username harus unik untuk role yang sama
        $pdo->exec("ALTER TABLE users ADD UNIQUE KEY unique_username_role (username, role)");
        echo "<p style='color: green;'>✅ Composite unique key (username, role) berhasil dibuat.</p>";
        $success = true;
    } else {
        echo "<p style='color: orange;'>⚠️ Composite unique key (username, role) sudah ada.</p>";
        $success = true;
    }
    
} catch (PDOException $e) {
    $error = $e->getMessage();
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($error) . "</p>";
}

if ($success) {
    echo "<hr>";
    echo "<h3 style='color: green;'>✅ Perbaikan selesai!</h3>";
    echo "<p>Sekarang admin dan user bisa menggunakan username yang sama.</p>";
    echo "<p>Sesama user atau sesama admin tidak bisa menggunakan username yang sama.</p>";
}
?>

