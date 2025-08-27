<?php
/**
 * Test for the Users API
 * This script tests the functionality of the users-real.php API endpoint
 */

header('Content-Type: text/html; charset=utf-8');

// Test functions
function testGetAllUsers() {
    echo "<h2>Test: Get All Users</h2>";
    
    // Call the API
    $response = file_get_contents('http://localhost/marsa%20maroc%20project/api/users-real.php');
    
    // Display raw response
    echo "<h3>Raw Response:</h3>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Parse JSON
    $data = json_decode($response, true);
    
    // Show parsed data
    echo "<h3>Parsed JSON:</h3>";
    echo "<pre>" . htmlspecialchars(print_r($data, true)) . "</pre>";
    
    // Basic validation
    if (isset($data['success']) && $data['success'] === true) {
        echo "<div style='color: green;'>✓ SUCCESS: API returned success response</div>";
    } else {
        echo "<div style='color: red;'>✗ FAILURE: API did not return success response</div>";
    }
    
    if (isset($data['data']) && is_array($data['data'])) {
        echo "<div style='color: green;'>✓ SUCCESS: API returned data array</div>";
        echo "Found " . count($data['data']) . " users";
    } else {
        echo "<div style='color: red;'>✗ FAILURE: API did not return data array</div>";
    }
}

function testGetSingleUser($id) {
    echo "<h2>Test: Get Single User (ID: $id)</h2>";
    
    // Call the API
    $response = file_get_contents("http://localhost/marsa%20maroc%20project/api/users-real.php?id=$id");
    
    // Display raw response
    echo "<h3>Raw Response:</h3>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Parse JSON
    $data = json_decode($response, true);
    
    // Show parsed data
    echo "<h3>Parsed JSON:</h3>";
    echo "<pre>" . htmlspecialchars(print_r($data, true)) . "</pre>";
    
    // Basic validation
    if (isset($data['success']) && $data['success'] === true) {
        echo "<div style='color: green;'>✓ SUCCESS: API returned success response</div>";
    } else {
        echo "<div style='color: red;'>✗ FAILURE: API did not return success response</div>";
    }
    
    if (isset($data['data']) && is_array($data['data'])) {
        echo "<div style='color: green;'>✓ SUCCESS: API returned user data</div>";
    } else {
        echo "<div style='color: red;'>✗ FAILURE: API did not return user data</div>";
    }
}

// Add some styling
echo "
<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
    h2 { color: #333; border-bottom: 2px solid #ddd; padding-bottom: 5px; margin-top: 30px; }
    h3 { color: #555; }
    .test-section { margin-bottom: 30px; }
    .success { color: green; }
    .failure { color: red; }
</style>
";

echo "<h1>Users API Test</h1>";

// Run tests
testGetAllUsers();

// Get the first user ID from the list to test getting a single user
$allUsers = json_decode(file_get_contents('http://localhost/marsa%20maroc%20project/api/users-real.php'), true);
if (isset($allUsers['data'][0]['id'])) {
    $firstUserId = $allUsers['data'][0]['id'];
    testGetSingleUser($firstUserId);
} else {
    echo "<div style='color: orange;'>⚠ WARNING: Could not test getting a single user because no users were returned</div>";
}
