<?php
/**
 * Debug page for testing the Users API
 */

// Include database configuration
require_once 'config/database.php';

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to make API request and display results
function makeApiRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    
    // Set the URL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Set method
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    // Add data for POST/PUT
    if (($method === 'POST' || $method === 'PUT') && $data !== null) {
        $jsonData = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);
    }
    
    // Execute request
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'status' => $statusCode,
        'response' => $response,
        'error' => $error
    ];
}

// Test database connection
function testDatabaseConnection() {
    echo "<h2>Testing Database Connection</h2>";
    
    try {
        $pdo = getDBConnection();
        echo "<div class='success'>Database connection successful!</div>";
        
        // Get users table count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<div>Number of users in database: {$result['count']}</div>";
        
        return true;
    } catch (PDOException $e) {
        echo "<div class='error'>Database connection failed: {$e->getMessage()}</div>";
        return false;
    }
}

// Get base URL for API calls
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
           "://" . $_SERVER['HTTP_HOST'] . 
           dirname($_SERVER['PHP_SELF']) . "/api/users-real.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Users API Debug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 20px;
        }
        pre {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            max-height: 400px;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .test-section {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .test-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 16px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }
        button:hover {
            background-color: #45a049;
        }
        .method {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        .get { background-color: #61affe; }
        .post { background-color: #49cc90; }
        .put { background-color: #fca130; }
        .delete { background-color: #f93e3e; }
    </style>
</head>
<body>
    <h1>Users API Debug</h1>
    <p>Testing the functionality of users-real.php API endpoint</p>
    <p>Base URL: <code><?php echo $baseUrl; ?></code></p>
    
    <?php
    // Test database connection
    $dbConnectionOk = testDatabaseConnection();
    
    if ($dbConnectionOk) {
        // GET all users
        echo "<div class='test-section'>";
        echo "<div class='test-title'>";
        echo "<h2><span class='method get'>GET</span> All Users</h2>";
        echo "<button onclick=\"location.reload()\">Run Tests</button>";
        echo "</div>";
        $allUsersResult = makeApiRequest($baseUrl);
        echo "<p>Status Code: {$allUsersResult['status']}</p>";
        if ($allUsersResult['error']) {
            echo "<p class='error'>Error: {$allUsersResult['error']}</p>";
        } else {
            echo "<pre>" . htmlspecialchars($allUsersResult['response']) . "</pre>";
            
            // Parse JSON for further tests
            $usersData = json_decode($allUsersResult['response'], true);
            if (isset($usersData['data']) && !empty($usersData['data'])) {
                $firstUser = $usersData['data'][0];
                $userId = $firstUser['id'];
                
                // GET single user
                echo "<div class='test-section'>";
                echo "<div class='test-title'>";
                echo "<h2><span class='method get'>GET</span> Single User (ID: $userId)</h2>";
                echo "</div>";
                $singleUserResult = makeApiRequest("$baseUrl?id=$userId");
                echo "<p>Status Code: {$singleUserResult['status']}</p>";
                if ($singleUserResult['error']) {
                    echo "<p class='error'>Error: {$singleUserResult['error']}</p>";
                } else {
                    echo "<pre>" . htmlspecialchars($singleUserResult['response']) . "</pre>";
                }
                echo "</div>";
                
                // Test PUT (update user)
                echo "<div class='test-section'>";
                echo "<div class='test-title'>";
                echo "<h2><span class='method put'>PUT</span> Update User (ID: $userId)</h2>";
                echo "</div>";
                
                // Create test data for update
                $updateData = [
                    'full_name' => $firstUser['full_name'] . ' (Updated)',
                    'email' => $firstUser['email'],
                    'phone' => $firstUser['phone'] ?? '123-456-7890',
                    'role' => $firstUser['role']
                ];
                
                $updateResult = makeApiRequest("$baseUrl?id=$userId", 'PUT', $updateData);
                echo "<p>Status Code: {$updateResult['status']}</p>";
                echo "<p>Update Data:</p>";
                echo "<pre>" . htmlspecialchars(json_encode($updateData, JSON_PRETTY_PRINT)) . "</pre>";
                if ($updateResult['error']) {
                    echo "<p class='error'>Error: {$updateResult['error']}</p>";
                } else {
                    echo "<pre>" . htmlspecialchars($updateResult['response']) . "</pre>";
                }
                echo "</div>";
                
                // Test DELETE (would delete the user, but we'll skip for safety)
                echo "<div class='test-section'>";
                echo "<div class='test-title'>";
                echo "<h2><span class='method delete'>DELETE</span> User (Test Only - Not Executed)</h2>";
                echo "</div>";
                echo "<p>Delete functionality available but not executed in this test to preserve data.</p>";
                echo "<p>URL would be: <code>$baseUrl?id=$userId</code> with DELETE method</p>";
                echo "</div>";
            }
            
            // Test POST (create new user)
            echo "<div class='test-section'>";
            echo "<div class='test-title'>";
            echo "<h2><span class='method post'>POST</span> Create New User</h2>";
            echo "</div>";
            
            // Generate unique username and email to prevent conflicts
            $timestamp = time();
            $createData = [
                'username' => "testuser_$timestamp",
                'email' => "test_$timestamp@example.com",
                'password' => 'test123',
                'full_name' => 'Test User',
                'phone' => '123-456-7890',
                'role' => 'user'
            ];
            
            $createResult = makeApiRequest($baseUrl, 'POST', $createData);
            echo "<p>Status Code: {$createResult['status']}</p>";
            echo "<p>Create Data:</p>";
            echo "<pre>" . htmlspecialchars(json_encode($createData, JSON_PRETTY_PRINT)) . "</pre>";
            
            if ($createResult['error']) {
                echo "<p class='error'>Error: {$createResult['error']}</p>";
            } else {
                echo "<pre>" . htmlspecialchars($createResult['response']) . "</pre>";
            }
            echo "</div>";
        }
        echo "</div>";
    }
    ?>

    <script>
        // Optional JavaScript for better interactivity can be added here
        console.log("API Debug page loaded");
    </script>
</body>
</html>
