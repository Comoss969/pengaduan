<?php
include 'config.php';

try {
    $pdo->exec('CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        action VARCHAR(50) NOT NULL,
        target_type VARCHAR(50) NOT NULL,
        target_id INT NOT NULL,
        details TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
    )');
    echo 'Activity logs table created successfully!';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
