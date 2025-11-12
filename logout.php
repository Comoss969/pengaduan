<?php
include 'config.php';

// Get role before destroying session
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;

// Destroy session
session_destroy();

// Redirect based on role
if ($role == 'admin') {
    header('Location: login_admin.php');
} else {
    header('Location: login_user.php');
}
exit;
?>
