<?php
/**
 * Script untuk menghapus semua akun admin dan membuat 3 akun admin baru
 * Username: akira, estelle, mustofa
 * Password: akira123, estelle123, mustofa123
 * 
 * Akses file ini melalui browser: http://localhost/pengaduan/update_admin_accounts.php
 * Setelah akun dibuat, hapus atau rename file ini untuk keamanan
 */

include 'config.php';

// Data akun admin baru yang akan dibuat
$admin_accounts = [
    ['username' => 'akira', 'password' => 'akira123'],
    ['username' => 'estelle', 'password' => 'estelle123'],
    ['username' => 'mustofa', 'password' => 'mustofa123']
];

$success_count = 0;
$error_count = 0;
$errors = [];
$deleted_count = 0;

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Update Admin Accounts</title>";
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
    .warning { color: #F59E0B; }
    .info { color: #60A5FA; }
    ul { list-style-type: none; padding-left: 0; }
    li { padding: 8px; background: #334155; margin: 5px 0; border-radius: 6px; }
</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h2>🔄 Update Akun Admin</h2>";
echo "<hr>";

try {
    // Langkah 1: Hapus semua akun admin yang ada
    echo "<h3>📋 Langkah 1: Menghapus semua akun admin yang ada</h3>";
    
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE role = 'admin'");
    $stmt->execute();
    $existing_admins = $stmt->fetchAll();
    
    if (count($existing_admins) > 0) {
        echo "<p class='info'>Ditemukan <strong>" . count($existing_admins) . "</strong> akun admin yang akan dihapus:</p>";
        echo "<ul>";
        foreach ($existing_admins as $admin) {
            echo "<li>ID: {$admin['id']} - Username: <strong>{$admin['username']}</strong></li>";
        }
        echo "</ul>";
        
        // Hapus semua akun admin
        $stmt = $pdo->prepare("DELETE FROM users WHERE role = 'admin'");
        $stmt->execute();
        $deleted_count = $stmt->rowCount();
        
        echo "<p class='success'>✅ Berhasil menghapus <strong>$deleted_count</strong> akun admin!</p>";
    } else {
        echo "<p class='warning'>⚠️ Tidak ada akun admin yang ditemukan untuk dihapus.</p>";
    }
    
    echo "<hr>";
    
    // Langkah 2: Buat akun admin baru
    echo "<h3>📋 Langkah 2: Membuat akun admin baru</h3>";
    
    foreach ($admin_accounts as $account) {
        $username = $account['username'];
        $password = $account['password'];
        
        try {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert akun admin
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
            $stmt->execute([$username, $hashedPassword]);
            
            echo "<p class='success'>✅ Akun admin <strong>$username</strong> berhasil dibuat!</p>";
            $success_count++;
            
        } catch (PDOException $e) {
            echo "<p class='error'>❌ Error membuat akun <strong>$username</strong>: " . htmlspecialchars($e->getMessage()) . "</p>";
            $errors[] = $username . ": " . $e->getMessage();
            $error_count++;
        }
    }
    
    echo "<hr>";
    echo "<h3>📊 Ringkasan:</h3>";
    echo "<p class='info'>🗑️ Akun admin yang dihapus: <strong>$deleted_count</strong></p>";
    echo "<p class='success'>✅ Akun admin baru yang berhasil dibuat: <strong>$success_count</strong></p>";
    if ($error_count > 0) {
        echo "<p class='error'>❌ Error: <strong>$error_count</strong> akun</p>";
    }
    
    if ($success_count > 0) {
        echo "<hr>";
        echo "<h3>🔐 Informasi Login:</h3>";
        echo "<p class='info'>Anda bisa login dengan akun berikut:</p>";
        echo "<ul>";
        foreach ($admin_accounts as $account) {
            echo "<li>Username: <strong>{$account['username']}</strong> | Password: <strong>{$account['password']}</strong></li>";
        }
        echo "</ul>";
        echo "<p class='warning'><strong>⚠️ PENTING:</strong> Setelah selesai, hapus atau rename file <code>update_admin_accounts.php</code> untuk keamanan!</p>";
        echo "<p class='info'>Login di: <a href='login_admin.php' style='color: #60A5FA;'>login_admin.php</a></p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error koneksi database: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";
echo "</body>";
echo "</html>";
?>

