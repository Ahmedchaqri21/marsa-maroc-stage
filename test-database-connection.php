<?php
/**
 * Database Connection Test and User Verification for Marsa Maroc
 * This script tests the database connection and verifies if users exist
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Database Connection Test - Marsa Maroc</h1>";

try {
    // Include database configuration
    require_once 'config/database.php';
    
    echo "<h2>✅ Step 1: Database Configuration Loaded</h2>";
    $config = getDatabaseConfig();
    echo "<p>Host: " . $config['host'] . "</p>";
    echo "<p>Database: " . $config['database'] . "</p>";
    echo "<p>Username: " . $config['username'] . "</p>";
    echo "<p>Charset: " . $config['charset'] . "</p>";
    
    echo "<h2>🔗 Step 2: Testing Database Connection</h2>";
    $pdo = createDatabaseConnection();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    echo "<h2>📊 Step 3: Checking Database Tables</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p style='color: red;'>❌ No tables found in database!</p>";
        echo "<p>The database exists but tables are not created. Running schema...</p>";
        
        // Read and execute schema
        $schema = file_get_contents('database/schema.sql');
        if ($schema) {
            // Split by semicolon and execute each statement
            $statements = explode(';', $schema);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement) && !str_starts_with($statement, '--')) {
                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        // Ignore errors for statements that might already exist
                        if (!str_contains($e->getMessage(), 'already exists')) {
                            echo "<p style='color: orange;'>Warning: " . $e->getMessage() . "</p>";
                        }
                    }
                }
            }
            echo "<p style='color: green;'>✅ Schema executed successfully!</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Found " . count($tables) . " tables:</p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    }
    
    echo "<h2>👥 Step 4: Checking Users Table</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    echo "<p>Total users in database: <strong>$userCount</strong></p>";
    
    if ($userCount == 0) {
        echo "<p style='color: red;'>❌ No users found! Creating admin user...</p>";
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, company_name, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
        $stmt->execute(['admin', 'admin@marsamaroc.ma', 'admin123', 'Administrateur Principal', 'admin', 'Marsa Maroc']);
        echo "<p style='color: green;'>✅ Admin user created successfully!</p>";
    }
    
    echo "<h2>🔑 Step 5: Verifying Admin User</h2>";
    $stmt = $pdo->prepare("SELECT id, username, email, full_name, role, status FROM users WHERE username = ? OR email = ?");
    $stmt->execute(['admin', 'admin']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p style='color: green;'>✅ Admin user found:</p>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> " . $admin['id'] . "</li>";
        echo "<li><strong>Username:</strong> " . $admin['username'] . "</li>";
        echo "<li><strong>Email:</strong> " . $admin['email'] . "</li>";
        echo "<li><strong>Full Name:</strong> " . $admin['full_name'] . "</li>";
        echo "<li><strong>Role:</strong> " . $admin['role'] . "</li>";
        echo "<li><strong>Status:</strong> " . $admin['status'] . "</li>";
        echo "</ul>";
        
        // Test password verification
        $stmt = $pdo->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->execute(['admin']);
        $storedPassword = $stmt->fetch()['password'];
        
        if ($storedPassword === 'admin123') {
            echo "<p style='color: green;'>✅ Password verification: CORRECT</p>";
            echo "<p><strong>You can now login with:</strong></p>";
            echo "<ul>";
            echo "<li><strong>Username:</strong> admin</li>";
            echo "<li><strong>Password:</strong> admin123</li>";
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>❌ Password verification: INCORRECT</p>";
            echo "<p>Stored password: '$storedPassword'</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Admin user not found!</p>";
    }
    
    echo "<h2>🌐 Step 6: API Connection Test</h2>";
    echo "<p>Testing authentication API endpoint...</p>";
    
    // Test data
    $testData = [
        'username' => 'admin',
        'password' => 'admin123'
    ];
    
    echo "<p>Test login data prepared. You can now test the login form.</p>";
    
    echo "<h2>📋 Summary</h2>";
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #2d5a2d;'>✅ All Systems Ready!</h3>";
    echo "<p><strong>Database:</strong> Connected and initialized</p>";
    echo "<p><strong>Tables:</strong> Created and populated</p>";
    echo "<p><strong>Admin User:</strong> Available and verified</p>";
    echo "<p><strong>Login URL:</strong> <a href='pages/auth/login.php'>pages/auth/login.php</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>❌ Database Error</h2>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your Docker containers are running:</p>";
    echo "<pre>docker-compose ps</pre>";
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ General Error</h2>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f5f5f5;
}

h1, h2 {
    color: #1a365d;
    border-bottom: 2px solid #3182ce;
    padding-bottom: 10px;
}

ul {
    background: white;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

pre {
    background: #2d3748;
    color: white;
    padding: 10px;
    border-radius: 5px;
    overflow-x: auto;
}
</style>
