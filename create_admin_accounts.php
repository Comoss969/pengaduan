<?php
/**
 * Script untuk membuat 3 akun admin
 * Username: akira1, akira2, akira3
 * Password: akira01
 * 
 * Akses file ini melalui browser: http://localhost/pengaduan/create_admin_accounts.php
 * Setelah akun dibuat, hapus atau rename file ini untuk keamanan
 */

include 'config.php';

// Pastikan hanya bisa diakses jika belum ada akun-akun ini (opsional, bisa dihapus)
// Atau tambahkan autentikasi admin jika diperlukan

// Data akun admin yang akan dibuat
$admin_accounts = [
    ['username' => 'akira1', 'password' => 'akira01'],
    ['username' => 'akira2', 'password' => 'akira01'],
    ['username' => 'akira3', 'password' => 'akira01']
];

$success_count = 0;
$error_count = 0;
$errors = [];

echo "<h2>Membuat Akun Admin</h2>";
echo "<hr>";

foreach ($admin_accounts as $account) {
    $username = $account['username'];
    $password = $account['password'];
    
    try {
        // Cek apakah username sudah ada
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            echo "<p style='color: orange;'>⚠️ Username <strong>$username</strong> sudah ada. Dilewati.</p>";
            continue;
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert akun admin
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
        $stmt->execute([$username, $hashedPassword]);
        
        echo "<p style='color: green;'>✅ Akun admin <strong>$username</strong> berhasil dibuat!</p>";
        $success_count++;
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error membuat akun <strong>$username</strong>: " . $e->getMessage() . "</p>";
        $errors[] = $username . ": " . $e->getMessage();
        $error_count++;
    }
}

echo "<hr>";
echo "<h3>Ringkasan:</h3>";
echo "<p>✅ Berhasil dibuat: <strong>$success_count</strong> akun</p>";
if ($error_count > 0) {
    echo "<p>❌ Error: <strong>$error_count</strong> akun</p>";
}

if ($success_count > 0) {
    echo "<p style='color: green;'><strong>Semua akun admin berhasil dibuat!</strong></p>";
    echo "<p>Anda bisa login dengan:</p>";
    echo "<ul>";
    foreach ($admin_accounts as $account) {
        echo "<li>Username: <strong>{$account['username']}</strong> | Password: <strong>{$account['password']}</strong></li>";
    }
    echo "</ul>";
}
?>

