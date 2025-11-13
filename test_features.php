<?php
include 'config.php';

echo "Testing database tables and features...\n\n";

// Test post_photos table
try {
    $stmt = $pdo->query('SHOW TABLES LIKE "post_photos"');
    if ($stmt->rowCount() > 0) {
        echo "✓ Table post_photos exists.\n";
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM post_photos');
        $count = $stmt->fetch()['count'];
        echo "✓ Records in post_photos: $count\n";
    } else {
        echo "✗ Table post_photos does not exist.\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking post_photos table: " . $e->getMessage() . "\n";
}

// Test posts table structure
try {
    $stmt = $pdo->query('DESCRIBE posts');
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    echo "✓ Posts table columns: " . implode(', ', $columns) . "\n";
} catch (Exception $e) {
    echo "✗ Error checking posts table: " . $e->getMessage() . "\n";
}

// Test sample data
try {
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM posts WHERE deleted_at IS NULL');
    $count = $stmt->fetch()['count'];
    echo "✓ Active posts count: $count\n";
} catch (Exception $e) {
    echo "✗ Error checking posts count: " . $e->getMessage() . "\n";
}

echo "\nTesting completed.\n";
?>
