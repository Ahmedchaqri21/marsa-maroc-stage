<?php
/**
 * Dashboard API Debug Tool
 * This file helps test all API endpoints used by the admin dashboard
 */

// Force display of errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// HTML header
echo '<!DOCTYPE html>
<html>
<head>
    <title>Dashboard API Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; color: #333; }
        h1, h2 { color: #1a365d; }
        pre { background: #fff; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .section { margin-bottom: 30px; padding: 20px; background: white; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; color: white; }
        .status-admin { background-color: #e53e3e; }
        .status-manager { background-color: #dd6b20; }
        .status-user { background-color: #3182ce; }
        .status-active { background-color: #38a169; }
        .status-inactive { background-color: #718096; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th { background: #1a365d; color: white; padding: 10px; text-align: left; }
        .data-table td { padding: 8px; border-bottom: 1px solid #ddd; }
        .data-table tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Dashboard API Debug Tool</h1>
    <p>This tool tests all API endpoints used by the admin dashboard to identify any issues.</p>
';

// Function to test API endpoint
function testEndpoint($name, $url, $method = 'GET', $data = null) {
    echo "<div class='section'>";
    echo "<h2>Testing $name API</h2>";
    echo "<p>URL: <code>$url</code></p>";
    echo "<p>Method: <code>$method</code></p>";
    
    if ($data) {
        echo "<p>Data:</p>";
        echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";
    }
    
    try {
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
        
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        echo "<p>HTTP Status: <span class='" . ($status >= 200 && $status < 300 ? "success" : "error") . "'>$status</span></p>";
        
        if ($error) {
            echo "<p class='error'>cURL Error: $error</p>";
        } else {
            echo "<p>Raw Response:</p>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
            
            // Try to parse JSON
            $jsonData = json_decode($response, true);
            if ($jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
                echo "<p class='error'>JSON Parse Error: " . json_last_error_msg() . "</p>";
            } else {
                if (isset($jsonData['success'])) {
                    echo "<p>API Success: <span class='" . ($jsonData['success'] ? "success" : "error") . "'>" . 
                        ($jsonData['success'] ? "Yes" : "No") . "</span></p>";
                }
                
                if (isset($jsonData['message'])) {
                    echo "<p>Message: " . htmlspecialchars($jsonData['message']) . "</p>";
                }
                
                // Display data table if it's a user list
                if ($name === 'Users List' && isset($jsonData['data']) && is_array($jsonData['data'])) {
                    displayUserTable($jsonData['data']);
                }
            }
        }
    } catch (Exception $e) {
        echo "<p class='error'>Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "</div>";
}

// Function to display user data in a table
function displayUserTable($users) {
    if (empty($users)) {
        echo "<p class='warning'>No users found</p>";
        return;
    }
    
    echo "<h3>Users Data</h3>";
    echo "<table class='data-table'>";
    echo "<thead><tr>
        <th>ID</th>
        <th>Username</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Status</th>
        <th>Created</th>
    </tr></thead><tbody>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td><span class='status-badge status-" . htmlspecialchars($user['role']) . "'>" . 
            htmlspecialchars($user['role']) . "</span></td>";
        echo "<td><span class='status-badge status-" . htmlspecialchars($user['status']) . "'>" . 
            htmlspecialchars($user['status']) . "</span></td>";
        echo "<td>" . htmlspecialchars($user['created_at'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    
    echo "</tbody></table>";
}

// Base URL for API calls
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
          "://" . $_SERVER['HTTP_HOST'] . 
          dirname($_SERVER['PHP_SELF']);

// Test Users API
testEndpoint('Users List', $baseUrl . '/api/users-fixed.php');

// Test single user if we have any
$users = json_decode(file_get_contents($baseUrl . '/api/users-fixed.php'), true);
if (isset($users['data'][0]['id'])) {
    $userId = $users['data'][0]['id'];
    testEndpoint('Single User', $baseUrl . '/api/users-fixed.php?id=' . $userId);
}

// Footer
echo '
</body>
</html>';
?>
