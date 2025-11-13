<?php
/**
 * Migration Script: Add foto column to comments table
 * This script adds the 'foto' column to the comments table to support image attachments in comments
 */

require_once 'config.php';

try {
    echo "Starting migration: Adding 'foto' column to comments table...\n";
    
    // Check if the column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM comments LIKE 'foto'");
    $columnExists = $stmt->fetch();
    
    if ($columnExists) {
        echo "✓ Column 'foto' already exists in comments table. No migration needed.\n";
    } else {
        // Add the foto column
        $sql = "ALTER TABLE comments ADD COLUMN foto VARCHAR(255) NULL";
        $pdo->exec($sql);
        
        echo "✓ Successfully added 'foto' column to comments table!\n";
        echo "✓ Migration completed successfully.\n";
    }
    
    // Verify the column was added
    $stmt = $pdo->query("SHOW COLUMNS FROM comments");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nCurrent columns in comments table:\n";
    foreach ($columns as $column) {
        echo "  - $column\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✓ All done! You can now use the admin dashboard without errors.\n";
?>
