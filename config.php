<?php
// Database configuration
$host = 'localhost';
$dbname = 'pengaduan';
$username = 'root'; // Default XAMPP MySQL username
$password = ''; // Default XAMPP MySQL password (empty)

// PDO connection (for existing code)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// MySQLi connection (for secure comment deletion)
$mysqli = new mysqli($host, $username, $password, $dbname);
if ($mysqli->connect_error) {
    die("MySQLi connection failed: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8");

// Start session
session_start();
?>
