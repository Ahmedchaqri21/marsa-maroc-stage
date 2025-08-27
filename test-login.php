<?php
require_once 'config/database.php';

echo "<h2>Database Connection and User Test</h2>";

try {
    // Test database connection
    $pdo = getDBConnection();
    echo "✅ Database connection successful!<br><br>";
    
    // Check if users table exists and has data
    $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
    $result = $stmt->fetch();
    echo "👥 Number of users in database: " . $result['user_count'] . "<br><br>";
    
    // Check specific admin user
    $stmt = $pdo->prepare("SELECT id, username, email, password, full_name, role, status FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "👤 Admin user found:<br>";
        echo "- ID: " . $admin['id'] . "<br>";
        echo "- Username: " . $admin['username'] . "<br>";
        echo "- Email: " . $admin['email'] . "<br>";
        echo "- Full Name: " . $admin['full_name'] . "<br>";
        echo "- Role: " . $admin['role'] . "<br>";
        echo "- Status: " . $admin['status'] . "<br>";
        echo "- Password Hash: " . substr($admin['password'], 0, 30) . "...<br><br>";
        
        // Test password verification (plain text)
        $test_password = 'admin123';
        if ($test_password === $admin['password']) {
            echo "✅ Password matches for 'admin123'<br>";
        } else {
            echo "❌ Password does not match. Expected: 'admin123', Found: '" . $admin['password'] . "'<br>";
            echo "Let's update the password to plain text:<br>";
            
            // Update the password with plain text
            $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
            if ($updateStmt->execute(['admin123'])) {
                echo "✅ Admin password updated to plain text 'admin123'!<br>";
            } else {
                echo "❌ Failed to update admin password<br>";
            }
        }
        
    } else {
        echo "❌ Admin user not found in database<br>";
        echo "Let's create the admin user:<br>";
        
        $insertStmt = $pdo->prepare("
            INSERT INTO users (username, email, password, full_name, role, company_name, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($insertStmt->execute([
            'admin', 
            'admin@marsamaroc.ma', 
            'admin123',  // Plain text password
            'Administrateur Principal', 
            'admin', 
            'Marsa Maroc',
            'active'
        ])) {
            echo "✅ Admin user created successfully!<br>";
        } else {
            echo "❌ Failed to create admin user<br>";
        }
    }
    
    echo "<br><h3>Test Login Credentials:</h3>";
    echo "Username: <strong>admin</strong><br>";
    echo "Password: <strong>admin123</strong><br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
</style>
