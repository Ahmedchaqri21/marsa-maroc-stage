<?php
// Set error reporting for maximum visibility of issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Force output as plain text
header('Content-Type: text/plain');

echo "==== USERS API DEBUG TOOL ====\n\n";

// Step 1: Test the database connection
echo "STEP 1: Testing database connection\n";
try {
    require_once __DIR__ . '/config/database.php';
    $pdo = getDBConnection();
    echo "✅ Database connection successful\n";
    
    // Test basic query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Database query successful - Found {$result['count']} users\n";
} catch (Exception $e) {
    echo "❌ Database connection error: " . $e->getMessage() . "\n";
    exit();
}

echo "\n";

// Step 2: Make a direct query to the database to see all users
echo "STEP 2: Direct database query for all users\n";
try {
    $stmt = $pdo->query("SELECT id, username, email, full_name, role, status FROM users LIMIT 10");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "✅ Found " . count($users) . " users:\n";
        foreach ($users as $user) {
            echo "ID: {$user['id']}, Username: {$user['username']}, Role: {$user['role']}, Status: {$user['status']}\n";
        }
    } else {
        echo "❌ No users found in database. You need to create users first.\n";
    }
} catch (Exception $e) {
    echo "❌ Error querying users: " . $e->getMessage() . "\n";
}

echo "\n";

// Step 3: Test API directly with file_get_contents
echo "STEP 3: Testing API with file_get_contents\n";
try {
    $apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/api/users-real.php';
    echo "API URL: $apiUrl\n";
    
    // Create stream context to capture HTTP response code
    $context = stream_context_create([
        'http' => ['ignore_errors' => true]
    ]);
    
    $response = file_get_contents($apiUrl, false, $context);
    
    // Get status code
    $status_line = $http_response_header[0];
    preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
    $status = $match[1];
    
    echo "HTTP Status: $status\n";
    echo "Response:\n$response\n";
    
    // Attempt to parse JSON response
    $jsonData = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ JSON parsed successfully\n";
        if (isset($jsonData['success']) && $jsonData['success'] === true) {
            echo "✅ API reports success\n";
        } else {
            echo "❌ API reports failure: " . ($jsonData['message'] ?? 'No error message') . "\n";
        }
    } else {
        echo "❌ Invalid JSON response: " . json_last_error_msg() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Error with API request: " . $e->getMessage() . "\n";
}

echo "\n";

// Step 4: Direct SQL execution for troubleshooting
echo "STEP 4: Check for SQL errors with direct query\n";
try {
    // This is the query used in users-real.php
    $sql = "SELECT 
                u.id,
                u.username,
                u.email,
                u.full_name,
                u.phone,
                u.role,
                u.status,
                u.created_at,
                COUNT(r.id) as total_reservations
            FROM users u
            LEFT JOIN reservations r ON u.id = r.user_id
            GROUP BY u.id, u.username, u.email, u.full_name, u.phone, u.role, u.status, u.created_at
            ORDER BY u.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Direct SQL query successful - Found " . count($users) . " users\n";
    
    // Dump first user for debugging
    if (count($users) > 0) {
        echo "First user details:\n";
        print_r($users[0]);
    }
} catch (Exception $e) {
    echo "❌ SQL Error: " . $e->getMessage() . "\n";
}

echo "\n==== DEBUG COMPLETE ====\n";
?>
