<?php
/**
 * MySQL Mode Check and Fix
 * This script checks and potentially adjusts MySQL mode settings that might cause issues with GROUP BY queries
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>MySQL Mode Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1, h2 { color: #1a365d; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow: auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .code { font-family: monospace; background: #f5f5f5; padding: 2px 5px; }
    </style>
</head>
<body>
    <h1>MySQL Mode Check and Fix</h1>
    <p>This tool checks and adjusts MySQL mode settings that might cause issues with GROUP BY queries</p>";

// Include database configuration
try {
    require_once __DIR__ . '/config/database.php';
    $pdo = getDBConnection();
    echo "<p class='success'>✅ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Check current MySQL mode
try {
    $stmt = $pdo->query("SELECT @@sql_mode");
    $mode = $stmt->fetchColumn();
    
    echo "<h2>Current MySQL Mode</h2>";
    echo "<pre>$mode</pre>";
    
    // Check if ONLY_FULL_GROUP_BY is enabled
    if (strpos($mode, 'ONLY_FULL_GROUP_BY') !== false) {
        echo "<p class='warning'>⚠️ ONLY_FULL_GROUP_BY is enabled, which can cause issues with some GROUP BY queries</p>";
        
        // Remove ONLY_FULL_GROUP_BY from the mode
        $newMode = str_replace('ONLY_FULL_GROUP_BY', '', $mode);
        $newMode = str_replace(',,', ',', $newMode);
        $newMode = trim($newMode, ',');
        
        echo "<h2>Suggested Mode (Session Only)</h2>";
        echo "<pre>$newMode</pre>";
        
        if (isset($_POST['apply_fix'])) {
            // Apply the new mode to the current session
            $pdo->exec("SET SESSION sql_mode = '$newMode'");
            
            // Verify change
            $stmt = $pdo->query("SELECT @@sql_mode");
            $updatedMode = $stmt->fetchColumn();
            
            echo "<h2>Updated MySQL Mode</h2>";
            echo "<pre>$updatedMode</pre>";
            
            echo "<p class='success'>✅ MySQL mode updated for current session</p>";
            echo "<p>Note: This change only affects the current connection. For permanent changes, update your MySQL configuration.</p>";
            
            // Test a problematic query to see if it works now
            echo "<h2>Testing Problem Query</h2>";
            try {
                $sql = "SELECT 
                           u.id,
                           u.username,
                           COUNT(r.id) as total_reservations
                        FROM users u
                        LEFT JOIN reservations r ON u.id = r.user_id
                        GROUP BY u.id
                        LIMIT 5";
                
                echo "<pre>$sql</pre>";
                
                $stmt = $pdo->query($sql);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<p class='success'>✅ Query executed successfully with " . count($results) . " results</p>";
                echo "<pre>" . json_encode($results, JSON_PRETTY_PRINT) . "</pre>";
                
            } catch (Exception $e) {
                echo "<p class='error'>❌ Query still fails: " . $e->getMessage() . "</p>";
            }
            
        } else {
            echo "<form method='post'>";
            echo "<p>Click the button below to apply the suggested mode to this session:</p>";
            echo "<button type='submit' name='apply_fix'>Apply Suggested Mode</button>";
            echo "</form>";
        }
    } else {
        echo "<p class='success'>✅ ONLY_FULL_GROUP_BY is not enabled. SQL mode is OK.</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error checking MySQL mode: " . $e->getMessage() . "</p>";
}

// Provide instructions for permanent fix
echo "<h2>Instructions for Permanent Fix</h2>";
echo "<p>If you want to make this change permanent, you need to update your MySQL configuration:</p>";
echo "<ol>
    <li>Find your MySQL configuration file (my.cnf or my.ini)</li>
    <li>Add or update the sql_mode setting:
        <pre>sql_mode = \"" . str_replace('ONLY_FULL_GROUP_BY', '', $mode) . "\"</pre>
    </li>
    <li>Restart the MySQL server</li>
</ol>";

echo "<h2>Alternative SQL Query Approach</h2>";
echo "<p>Instead of changing MySQL mode, you can also modify your queries to be compatible with ONLY_FULL_GROUP_BY:</p>";
echo "<pre>SELECT 
    u.id,
    u.username,
    u.email,
    u.full_name,
    u.role,
    u.status,
    COUNT(r.id) as total_reservations
FROM users u
LEFT JOIN reservations r ON u.id = r.user_id
GROUP BY u.id, u.username, u.email, u.full_name, u.role, u.status</pre>";
echo "<p>Notice that we included all selected columns in the GROUP BY clause.</p>";

echo "<p><a href='admin-dashboard.php'>Return to Admin Dashboard</a></p>";
echo "</body></html>";
?>
