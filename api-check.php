<?php
/**
 * API Check Tool
 * This file tests the API endpoints and displays detailed results
 */

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once __DIR__ . '/config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>API Check Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1, h2, h3 { color: #1a365d; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow: auto; white-space: pre-wrap; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #3182ce; color: white; border: none; padding: 10px 15px; 
                border-radius: 4px; cursor: pointer; margin: 5px; }
        button:hover { background: #2c5282; }
        .tabs { display: flex; margin-bottom: 20px; }
        .tab { padding: 10px 20px; cursor: pointer; background: #f0f0f0; border: 1px solid #ddd; border-bottom: none; }
        .tab.active { background: white; font-weight: bold; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .highlight { background-color: yellow; }
    </style>
    <script>
        function switchTab(evt, tabName) {
            const tabContents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }
            
            const tabs = document.getElementsByClassName('tab');
            for (let i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove('active');
            }
            
            document.getElementById(tabName).classList.add('active');
            evt.currentTarget.classList.add('active');
        }
    </script>
</head>
<body>
    <h1>API Check Tool</h1>
    <p>This tool tests the API endpoints and displays detailed results</p>";

// Get base URL
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
          "://" . $_SERVER['HTTP_HOST'] . 
          dirname($_SERVER['PHP_SELF']);

// Function to test API endpoint
function testApiEndpoint($url, $method = 'GET', $data = null) {
    echo "<div class='section'>";
    echo "<h2>Testing API Endpoint</h2>";
    echo "<p><strong>URL:</strong> $url</p>";
    echo "<p><strong>Method:</strong> $method</p>";
    
    if ($data !== null) {
        echo "<p><strong>Request Data:</strong></p>";
        echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if (($method === 'POST' || $method === 'PUT') && $data !== null) {
        $jsonData = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);
    }
    
    $start = microtime(true);
    $response = curl_exec($ch);
    $end = microtime(true);
    
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Response time
    $responseTime = round(($end - $start) * 1000); // in ms
    
    echo "<p><strong>Response Time:</strong> {$responseTime}ms</p>";
    echo "<p><strong>HTTP Status:</strong> <span class='" . ($info['http_code'] >= 200 && $info['http_code'] < 300 ? "success" : "error") . "'>{$info['http_code']}</span></p>";
    
    // cURL error
    if ($error) {
        echo "<p><strong>cURL Error:</strong> <span class='error'>$error</span></p>";
    }
    
    // Response headers
    echo "<h3>Response Headers</h3>";
    echo "<pre>" . $info['content_type'] . "</pre>";
    
    // Raw response
    echo "<h3>Raw Response</h3>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Try to parse JSON
    $parsedResponse = json_decode($response, true);
    if ($parsedResponse !== null) {
        echo "<h3>Parsed JSON Response</h3>";
        echo "<pre>" . htmlspecialchars(json_encode($parsedResponse, JSON_PRETTY_PRINT)) . "</pre>";
        
        // Check if the API returned success
        if (isset($parsedResponse['success'])) {
            echo "<p><strong>API Success:</strong> <span class='" . ($parsedResponse['success'] ? "success" : "error") . "'>" . 
                ($parsedResponse['success'] ? "Yes" : "No") . "</span></p>";
        }
        
        // Check for error messages
        if (isset($parsedResponse['message'])) {
            $messageClass = isset($parsedResponse['success']) && $parsedResponse['success'] ? "success" : "error";
            echo "<p><strong>Message:</strong> <span class='$messageClass'>" . htmlspecialchars($parsedResponse['message']) . "</span></p>";
        }
    } else {
        echo "<p class='error'><strong>JSON Parse Error:</strong> " . json_last_error_msg() . "</p>";
    }
    
    echo "</div>";
    
    return [
        'status' => $info['http_code'],
        'response' => $parsedResponse,
        'raw' => $response,
        'error' => $error
    ];
}

// Display tabs for different API tests
echo "<div class='tabs'>
    <div class='tab active' onclick=\"switchTab(event, 'tab-users-get')\">Users GET</div>
    <div class='tab' onclick=\"switchTab(event, 'tab-users-post')\">Users POST</div>
    <div class='tab' onclick=\"switchTab(event, 'tab-users-put')\">Users PUT</div>
    <div class='tab' onclick=\"switchTab(event, 'tab-users-delete')\">Users DELETE</div>
    <div class='tab' onclick=\"switchTab(event, 'tab-emplacements')\">Emplacements</div>
    <div class='tab' onclick=\"switchTab(event, 'tab-reservations')\">Reservations</div>
</div>";

// Users GET test
echo "<div id='tab-users-get' class='tab-content active'>";
echo "<h2>Testing Users API (GET)</h2>";

// Test both API versions
$originalResult = testApiEndpoint("$baseUrl/api/users-real.php");
$fixedResult = testApiEndpoint("$baseUrl/api/users-fixed.php");

// Compare results
echo "<div class='section'>";
echo "<h2>Comparison of API Responses</h2>";
echo "<p><strong>Original API Status:</strong> <span class='" . ($originalResult['status'] >= 200 && $originalResult['status'] < 300 ? "success" : "error") . "'>{$originalResult['status']}</span></p>";
echo "<p><strong>Fixed API Status:</strong> <span class='" . ($fixedResult['status'] >= 200 && $fixedResult['status'] < 300 ? "success" : "error") . "'>{$fixedResult['status']}</span></p>";

if ($originalResult['response'] !== null && $fixedResult['response'] !== null) {
    // Compare data count
    $originalCount = isset($originalResult['response']['data']) ? count($originalResult['response']['data']) : 0;
    $fixedCount = isset($fixedResult['response']['data']) ? count($fixedResult['response']['data']) : 0;
    
    echo "<p><strong>Original API Data Count:</strong> $originalCount</p>";
    echo "<p><strong>Fixed API Data Count:</strong> $fixedCount</p>";
    
    // If counts differ, there's an issue
    if ($originalCount != $fixedCount) {
        echo "<p class='warning'>⚠️ Data count differs between APIs</p>";
    } else {
        echo "<p class='success'>✅ Both APIs return the same number of records</p>";
    }
}
echo "</div>";
echo "</div>";

// Users POST test
echo "<div id='tab-users-post' class='tab-content'>";
echo "<h2>Testing Users API (POST)</h2>";

$timestamp = time();
$testUser = [
    'username' => "testuser_$timestamp",
    'email' => "test_$timestamp@example.com",
    'password' => 'test123',
    'full_name' => 'Test User',
    'phone' => '123-456-7890',
    'role' => 'user'
];

testApiEndpoint("$baseUrl/api/users-fixed.php", 'POST', $testUser);
echo "</div>";

// Users PUT test - first get a user to update
echo "<div id='tab-users-put' class='tab-content'>";
echo "<h2>Testing Users API (PUT)</h2>";

// Get user to update
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT id FROM users LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $userId = $user['id'];
        $updateData = [
            'full_name' => 'Updated User ' . time(),
            'phone' => '999-888-7777'
        ];
        
        testApiEndpoint("$baseUrl/api/users-fixed.php?id=$userId", 'PUT', $updateData);
    } else {
        echo "<p class='error'>No users found to test update operation</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Error retrieving test user: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Users DELETE test
echo "<div id='tab-users-delete' class='tab-content'>";
echo "<h2>Testing Users API (DELETE)</h2>";
echo "<p class='warning'>⚠️ This test is skipped to prevent accidental deletion of users</p>";
echo "<p>To test DELETE functionality, implement a separate test that creates and then deletes a test user</p>";
echo "</div>";

// Emplacements test
echo "<div id='tab-emplacements' class='tab-content'>";
echo "<h2>Testing Emplacements API</h2>";
testApiEndpoint("$baseUrl/api/emplacements-fixed.php");
echo "</div>";

// Reservations test
echo "<div id='tab-reservations' class='tab-content'>";
echo "<h2>Testing Reservations API</h2>";
testApiEndpoint("$baseUrl/api/reservations-real.php");
echo "</div>";

// Direct database query test
echo "<div class='section'>";
echo "<h2>Direct Database Query Results</h2>";

try {
    $pdo = getDBConnection();
    
    // Test users query
    $stmt = $pdo->query("SELECT id, username, email, role, status FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Users in Database:</h3>";
    echo "<pre>" . htmlspecialchars(json_encode($users, JSON_PRETTY_PRINT)) . "</pre>";
    
    // Check if there are users in the database
    if (empty($users)) {
        echo "<p class='error'>⚠️ No users found in database!</p>";
    } else {
        echo "<p class='success'>✅ Found " . count($users) . " users in database</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Database error: " . $e->getMessage() . "</p>";
}

echo "</div>";

echo "<p><a href='admin-dashboard.php'>Return to Admin Dashboard</a></p>";
echo "</body></html>";
?>
