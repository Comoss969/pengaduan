<?php
/**
 * Script to create the profanity_logs table
 * Run this once to add the missing table
 */

include 'config.php';

try {
    $pdo->exec('CREATE TABLE IF NOT EXISTS profanity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT DEFAULT NULL,
        content_type ENUM("post", "comment") NOT NULL,
        original_text TEXT NOT NULL,
        found_words TEXT NOT NULL,
        detected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        moderated BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )');

    echo '<div style="color: green; font-weight: bold;">Profanity logs table created successfully!</div>';
    echo '<p>The table includes:</p>';
    echo '<ul>';
    echo '<li>id - Primary key</li>';
    echo '<li>user_id - Reference to users table</li>';
    echo '<li>content_type - Type of content (post/comment)</li>';
    echo '<li>original_text - The original text with profanity</li>';
    echo '<li>found_words - List of detected profane words</li>';
    echo '<li>detected_at - Timestamp of detection</li>';
    echo '<li>moderated - Whether the content has been moderated</li>';
    echo '</ul>';
    echo '<p>You can now delete this file (create_profanity_table.php) for security.</p>';

} catch (Exception $e) {
    echo '<div style="color: red; font-weight: bold;">Error creating table: ' . $e->getMessage() . '</div>';
}
?>
