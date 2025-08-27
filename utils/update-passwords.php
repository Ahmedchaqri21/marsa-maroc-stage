<?php
/**
 * Update User Passwords Script
 * This script updates the passwords in the database to use proper hashing
 */

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Update User Passwords</h1>";

try {
    // Include database configuration
    require_once __DIR__ . '/config/database.php';
    $pdo = getDBConnection();
    
    // Get all users
    $stmt = $pdo->query("SELECT id, username, password FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Found " . count($users) . " users in the database.</p>";
    
    // Update passwords for each user
    $updated = 0;
    foreach ($users as $user) {
        // Check if password is already hashed
        if (password_verify($user['password'], $user['password'])) {
            echo "<p>User '{$user['username']}' already has a hashed password. Skipping.</p>";
            continue;
        }
        
        // Hash the plain text password
        $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
        
        // Update in database
        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($updateStmt->execute([$hashedPassword, $user['id']])) {
            $updated++;
            echo "<p>✅ Updated password for user '{$user['username']}'</p>";
        } else {
            echo "<p>❌ Failed to update password for user '{$user['username']}'</p>";
        }
    }
    
    echo "<h2>Summary:</h2>";
    echo "<p>Updated passwords for $updated users.</p>";
    echo "<p><a href='admin-dashboard.php'>Go to Admin Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>
