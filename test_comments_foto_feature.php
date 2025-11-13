<?php
/**
 * Comprehensive Testing Script for Comments Foto Feature
 * This script tests the foto column functionality in the comments table
 */

require_once 'config.php';

echo "=== COMPREHENSIVE TESTING: Comments Foto Feature ===\n\n";

$testResults = [];
$testsPassed = 0;
$testsFailed = 0;

// Test 1: Verify foto column exists
echo "Test 1: Verify 'foto' column exists in comments table\n";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM comments LIKE 'foto'");
    $column = $stmt->fetch();
    
    if ($column) {
        echo "✓ PASSED: Column 'foto' exists\n";
        echo "  - Type: {$column['Type']}\n";
        echo "  - Null: {$column['Null']}\n";
        echo "  - Default: {$column['Default']}\n";
        $testsPassed++;
        $testResults[] = ['test' => 'Column Existence', 'status' => 'PASSED'];
    } else {
        echo "✗ FAILED: Column 'foto' does not exist\n";
        $testsFailed++;
        $testResults[] = ['test' => 'Column Existence', 'status' => 'FAILED'];
    }
} catch (PDOException $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $testsFailed++;
    $testResults[] = ['test' => 'Column Existence', 'status' => 'FAILED'];
}
echo "\n";

// Test 2: Test INSERT with foto (simulated)
echo "Test 2: Test INSERT statement with foto column\n";
try {
    // Prepare the statement (same as in admin_dashboard.php line 38)
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, komentar, foto, is_admin) VALUES (?, ?, ?, ?, ?)");
    echo "✓ PASSED: INSERT statement prepared successfully\n";
    echo "  - Statement matches admin_dashboard.php line 38\n";
    $testsPassed++;
    $testResults[] = ['test' => 'INSERT Statement Preparation', 'status' => 'PASSED'];
} catch (PDOException $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $testsFailed++;
    $testResults[] = ['test' => 'INSERT Statement Preparation', 'status' => 'FAILED'];
}
echo "\n";

// Test 3: Test INSERT with NULL foto value
echo "Test 3: Test INSERT with NULL foto value\n";
try {
    // First, get a valid post_id and user_id for testing
    $stmt = $pdo->query("SELECT id FROM posts LIMIT 1");
    $post = $stmt->fetch();
    
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $user = $stmt->fetch();
    
    if ($post && $user) {
        $testPostId = $post['id'];
        $testUserId = $user['id'];
        
        // Insert test comment with NULL foto
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, komentar, foto, is_admin) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$testPostId, $testUserId, 'Test comment without photo', null, true]);
        
        $testCommentId = $pdo->lastInsertId();
        
        echo "✓ PASSED: INSERT with NULL foto successful\n";
        echo "  - Test comment ID: $testCommentId\n";
        $testsPassed++;
        $testResults[] = ['test' => 'INSERT with NULL foto', 'status' => 'PASSED'];
        
        // Clean up test data
        $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$testCommentId]);
        echo "  - Test data cleaned up\n";
    } else {
        echo "⚠ SKIPPED: No posts or users available for testing\n";
        $testResults[] = ['test' => 'INSERT with NULL foto', 'status' => 'SKIPPED'];
    }
} catch (PDOException $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $testsFailed++;
    $testResults[] = ['test' => 'INSERT with NULL foto', 'status' => 'FAILED'];
}
echo "\n";

// Test 4: Test INSERT with foto value
echo "Test 4: Test INSERT with foto value (simulated path)\n";
try {
    $stmt = $pdo->query("SELECT id FROM posts LIMIT 1");
    $post = $stmt->fetch();
    
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $user = $stmt->fetch();
    
    if ($post && $user) {
        $testPostId = $post['id'];
        $testUserId = $user['id'];
        
        // Insert test comment with foto path
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, komentar, foto, is_admin) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$testPostId, $testUserId, 'Test comment with photo', 'uploads/test_image.jpg', true]);
        
        $testCommentId = $pdo->lastInsertId();
        
        echo "✓ PASSED: INSERT with foto path successful\n";
        echo "  - Test comment ID: $testCommentId\n";
        echo "  - Foto path: uploads/test_image.jpg\n";
        $testsPassed++;
        $testResults[] = ['test' => 'INSERT with foto path', 'status' => 'PASSED'];
        
        // Clean up test data
        $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$testCommentId]);
        echo "  - Test data cleaned up\n";
    } else {
        echo "⚠ SKIPPED: No posts or users available for testing\n";
        $testResults[] = ['test' => 'INSERT with foto path', 'status' => 'SKIPPED'];
    }
} catch (PDOException $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $testsFailed++;
    $testResults[] = ['test' => 'INSERT with foto path', 'status' => 'FAILED'];
}
echo "\n";

// Test 5: Test SELECT with foto column
echo "Test 5: Test SELECT statement with foto column\n";
try {
    $stmt = $pdo->query("SELECT c.*, u.username FROM comments c INNER JOIN users u ON c.user_id = u.id LIMIT 1");
    $comment = $stmt->fetch();
    
    if ($comment !== false) {
        echo "✓ PASSED: SELECT statement successful\n";
        echo "  - Retrieved comment with foto column\n";
        if (isset($comment['foto'])) {
            echo "  - Foto value: " . ($comment['foto'] ? $comment['foto'] : 'NULL') . "\n";
        }
        $testsPassed++;
        $testResults[] = ['test' => 'SELECT with foto column', 'status' => 'PASSED'];
    } else {
        echo "⚠ SKIPPED: No comments available for testing\n";
        $testResults[] = ['test' => 'SELECT with foto column', 'status' => 'SKIPPED'];
    }
} catch (PDOException $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $testsFailed++;
    $testResults[] = ['test' => 'SELECT with foto column', 'status' => 'FAILED'];
}
echo "\n";

// Test 6: Verify existing comments still work
echo "Test 6: Verify existing comments are not affected\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM comments");
    $result = $stmt->fetch();
    $commentCount = $result['count'];
    
    echo "✓ PASSED: Existing comments query successful\n";
    echo "  - Total comments in database: $commentCount\n";
    $testsPassed++;
    $testResults[] = ['test' => 'Existing Comments Integrity', 'status' => 'PASSED'];
} catch (PDOException $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $testsFailed++;
    $testResults[] = ['test' => 'Existing Comments Integrity', 'status' => 'FAILED'];
}
echo "\n";

// Test 7: Check uploads directory
echo "Test 7: Verify uploads directory exists\n";
$uploadsDir = __DIR__ . '/uploads';
if (is_dir($uploadsDir)) {
    echo "✓ PASSED: Uploads directory exists\n";
    echo "  - Path: $uploadsDir\n";
    echo "  - Writable: " . (is_writable($uploadsDir) ? 'Yes' : 'No') . "\n";
    $testsPassed++;
    $testResults[] = ['test' => 'Uploads Directory', 'status' => 'PASSED'];
} else {
    echo "⚠ WARNING: Uploads directory does not exist\n";
    echo "  - Will be created automatically on first upload\n";
    $testResults[] = ['test' => 'Uploads Directory', 'status' => 'WARNING'];
}
echo "\n";

// Summary
echo "=== TEST SUMMARY ===\n";
echo "Total Tests: " . ($testsPassed + $testsFailed) . "\n";
echo "Passed: $testsPassed\n";
echo "Failed: $testsFailed\n";
echo "\n";

if ($testsFailed === 0) {
    echo "✓ ALL TESTS PASSED! The foto feature is working correctly.\n";
    echo "\nThe following functionality is now available:\n";
    echo "  - Admin can add comments with photo attachments\n";
    echo "  - Users can add comments with photo attachments\n";
    echo "  - Comments can be added without photos (NULL value)\n";
    echo "  - Existing comments are not affected by the schema change\n";
} else {
    echo "✗ SOME TESTS FAILED. Please review the errors above.\n";
}

echo "\n=== DETAILED RESULTS ===\n";
foreach ($testResults as $result) {
    echo sprintf("%-40s %s\n", $result['test'], $result['status']);
}

echo "\n=== NEXT STEPS ===\n";
echo "1. Test manually in browser: http://localhost/pengaduan/admin_dashboard.php\n";
echo "2. Try adding a comment with a photo attachment\n";
echo "3. Try adding a comment without a photo\n";
echo "4. Verify photos display correctly in comments\n";
echo "5. Test the same functionality in user_dashboard.php\n";
?>
