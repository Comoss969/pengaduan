<?php
// Pastikan file hanya di-include sekali
if (!defined('HEADER_INCLUDED')) {
    define('HEADER_INCLUDED', true);
    
    if (!isset($_SESSION['role'])) {
        header('Location: login_user.php');
        exit;
    }
    
    // Fungsi untuk mendapatkan jumlah postingan terhapus
    function getTrashCount($pdo) {
        if (!isset($pdo)) {
            return 0;
        }
        try {
            $stmt_trash = $pdo->query("SELECT COUNT(*) as count FROM posts WHERE deleted_at IS NOT NULL");
            $trash_count = $stmt_trash->fetch()['count'];
            return $trash_count;
        } catch (PDOException $e) {
            return 0;
        }
    }
    $trash_count = getTrashCount($pdo ?? null);
    ?>
    
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo isset($page_title) ? $page_title : 'Pengaduan'; ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="assets/css/style.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body>
        <!-- Navbar -->
        <nav class="navbar navbar-dark bg-dark custom-navbar">
            <div class="navbar-container">
                <!-- Welcome text di tengah -->
                <div class="navbar-welcome">
                    <span class="welcome-text">Welcome, <?php 
                        if (isset($_SESSION['username'])) {
                            echo htmlspecialchars($_SESSION['username']);
                        } else {
                            echo $_SESSION['role'] == 'admin' ? 'Admin' : 'User';
                        }
                    ?></span>
                </div>
                
                <!-- Menu buttons di kanan (Desktop) -->
                <div class="navbar-buttons d-none d-lg-flex">
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <a href="admin_dashboard.php" class="nav-button">Dashboard</a>
                        <a href="admin_trash.php" class="nav-button">
                            <span class="d-flex align-items-center">
                                Trash
                                <?php if ($trash_count > 0): ?>
                                    <span class='badge bg-primary ms-2'><?php echo $trash_count; ?></span>
                                <?php endif; ?>
                            </span>
                        </a>
                        <a href="edit_account.php" class="nav-button">Edit Akun</a>
                        <a href="logout.php" class="nav-button nav-button-logout">Logout</a>
                    <?php else: ?>
                        <a href="logout.php" class="nav-button nav-button-logout">Logout</a>
                    <?php endif; ?>
                </div>
                
                <!-- Hamburger Menu Button (Mobile) -->
                <button class="navbar-toggler d-lg-none hamburger-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </nav>
        
        <!-- Offcanvas Menu (Mobile) -->
        <div class="offcanvas offcanvas-end custom-offcanvas" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav">
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item mb-2">
                            <a class="nav-link-mobile" href="admin_dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link-mobile" href="admin_trash.php">
                                <span class="d-flex align-items-center">
                                    Trash
                                    <?php if ($trash_count > 0): ?>
                                        <span class='badge bg-primary ms-2'><?php echo $trash_count; ?></span>
                                    <?php endif; ?>
                                </span>
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link-mobile" href="edit_account.php">Edit Akun</a>
                        </li>
                        <li class="nav-item mt-3">
                            <hr class="dropdown-divider">
                        </li>
                        <li class="nav-item">
                            <a class="nav-link-mobile nav-link-logout" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link-mobile nav-link-logout" href="logout.php">Logout</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="container mt-4">
<?php
}
?>
