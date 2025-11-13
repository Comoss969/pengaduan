<?php
include 'config.php';

try {
    // Create post_photos table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS post_photos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            photo_path VARCHAR(255) NOT NULL,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
        )
    ");

    // Migrate existing single photos to post_photos table
    $stmt = $pdo->query("SELECT id, foto FROM posts WHERE foto IS NOT NULL AND foto != ''");
    $posts = $stmt->fetchAll();

    foreach ($posts as $post) {
        $stmt_insert = $pdo->prepare("INSERT INTO post_photos (post_id, photo_path) VALUES (?, ?)");
        $stmt_insert->execute([$post['id'], $post['foto']]);
    }

    echo "Migration completed successfully. post_photos table created and existing photos migrated.";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
