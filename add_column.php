<?php
include 'config.php';

try {
    $pdo->exec('ALTER TABLE comments ADD COLUMN foto VARCHAR(255) NULL');
    echo 'Column "foto" added successfully to comments table.';
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
