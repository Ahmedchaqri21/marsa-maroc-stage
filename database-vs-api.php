<?php
/**
 * Direct Database to API Comparison Tool
 * This tool bypasses the admin dashboard to directly compare database data vs API results
 */

// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers and styling
echo '<!DOCTYPE html>
<html>
<head>
    <title>Database vs API Data Comparison</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1, h2, h3 { color: #1a365d; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow: auto; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th { background-color: #1a365d; color: white; padding: 8px; text-align: left; }
        td { padding: 8px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .comparison { display: flex; gap: 20px; }
        .comparison-column { flex: 1; }
        button { background-color: #3182ce; color: white; border: none; padding: 10px 15px; 
                border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #2c5282; }
        #fix-section { margin-top: 30px; padding: 20px; background-color: #f0fff4; border: 1px solid #9ae6b4; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Database vs API Data Comparison</h1>
    <p>This tool directly compares data from the database with the data returned by the API.</p>';

// SECTION 1: Test Database Connection
echo '<div class="section">';
echo '<h2>1. Database Connection Test</h2>';

try {
    require_once __DIR__ . '/config/database.php';
    $pdo = getDBConnection();
    echo '<p class="success">✅ Database connection successful</p>';
    
    // Test query
    $testQuery = $pdo->query("SELECT 1 AS test");
    $testResult = $testQuery->fetch(PDO::FETCH_ASSOC);
    if ($testResult && $testResult['test'] == 1) {
        echo '<p class="success">✅ Database query test successful</p>';
    } else {
        echo '<p class="error">❌ Database query test failed</p>';
    }
} catch (Exception $e) {
    echo '<p class="error">❌ Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div></body></html>';
    exit;
}
echo '</div>';

// SECTION 2: Direct Database Query
echo '<div class="section">';
echo '<h2>2. Direct Database Query for Users</h2>';

try {
    // Simple query to get all users
    $stmt = $pdo->query("SELECT id, username, email, full_name, role, status FROM users ORDER BY id");
    $dbUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($dbUsers) > 0) {
        echo '<p class="success">✅ Found ' . count($dbUsers) . ' users in database</p>';
        
        echo '<h3>Users in Database:</h3>';
        echo '<table>';
        echo '<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Role</th><th>Status</th></tr>';
        
        foreach ($dbUsers as $user) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($user['id']) . '</td>';
            echo '<td>' . htmlspecialchars($user['username']) . '</td>';
            echo '<td>' . htmlspecialchars($user['email']) . '</td>';
            echo '<td>' . htmlspecialchars($user['full_name']) . '</td>';
            echo '<td>' . htmlspecialchars($user['role']) . '</td>';
            echo '<td>' . htmlspecialchars($user['status']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p class="error">❌ No users found in database!</p>';
    }
} catch (Exception $e) {
    echo '<p class="error">❌ Error querying database: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
echo '</div>';

// SECTION 3: Test Original API
echo '<div class="section">';
echo '<h2>3. Original API Test (users-real.php)</h2>';

try {
    // Get base URL
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
              "://" . $_SERVER['HTTP_HOST'] . 
              dirname($_SERVER['PHP_SELF']);
    
    // Fetch from original API
    $originalApiUrl = $baseUrl . '/api/users-real.php';
    echo '<p>API URL: ' . htmlspecialchars($originalApiUrl) . '</p>';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $originalApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $apiResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo '<p>HTTP Status Code: <span class="' . ($httpCode >= 200 && $httpCode < 300 ? 'success' : 'error') . 
         '">' . $httpCode . '</span></p>';
    
    // Try to parse the JSON response
    $apiUsers = json_decode($apiResponse, true);
    
    if ($apiUsers === null) {
        echo '<p class="error">❌ Failed to parse API response as JSON</p>';
        echo '<p>Raw API Response:</p>';
        echo '<pre>' . htmlspecialchars(substr($apiResponse, 0, 1000)) . 
             (strlen($apiResponse) > 1000 ? '...[truncated]' : '') . '</pre>';
    } else {
        // Check structure of response
        if (isset($apiUsers['data']) && is_array($apiUsers['data'])) {
            $apiUsers = $apiUsers['data']; // Extract data array
        }
        
        if (is_array($apiUsers) && !empty($apiUsers)) {
            echo '<p class="success">✅ API returned ' . count($apiUsers) . ' users</p>';
            
            echo '<h3>Users from API:</h3>';
            echo '<table>';
            echo '<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Role</th><th>Status</th></tr>';
            
            foreach ($apiUsers as $user) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($user['id'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($user['username'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($user['email'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($user['full_name'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($user['role'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($user['status'] ?? 'N/A') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p class="error">❌ API did not return users data in expected format</p>';
            echo '<p>API Response Structure:</p>';
            echo '<pre>' . htmlspecialchars(json_encode($apiUsers, JSON_PRETTY_PRINT)) . '</pre>';
        }
    }
} catch (Exception $e) {
    echo '<p class="error">❌ Error testing API: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
echo '</div>';

// SECTION 4: Test Fixed API
echo '<div class="section">';
echo '<h2>4. Fixed API Test (users-fixed.php)</h2>';

try {
    // Get base URL
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
              "://" . $_SERVER['HTTP_HOST'] . 
              dirname($_SERVER['PHP_SELF']);
    
    // Fetch from fixed API
    $fixedApiUrl = $baseUrl . '/api/users-fixed.php';
    echo '<p>API URL: ' . htmlspecialchars($fixedApiUrl) . '</p>';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fixedApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $apiResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo '<p>HTTP Status Code: <span class="' . ($httpCode >= 200 && $httpCode < 300 ? 'success' : 'error') . 
         '">' . $httpCode . '</span></p>';
    
    // Try to parse the JSON response
    $apiUsers = json_decode($apiResponse, true);
    
    if ($apiUsers === null) {
        echo '<p class="error">❌ Failed to parse API response as JSON</p>';
        echo '<p>Raw API Response:</p>';
        echo '<pre>' . htmlspecialchars(substr($apiResponse, 0, 1000)) . 
             (strlen($apiResponse) > 1000 ? '...[truncated]' : '') . '</pre>';
    } else {
        // Check structure of response
        if (isset($apiUsers['data']) && is_array($apiUsers['data'])) {
            $apiUsers = $apiUsers['data']; // Extract data array
        }
        
        if (is_array($apiUsers) && !empty($apiUsers)) {
            echo '<p class="success">✅ API returned ' . count($apiUsers) . ' users</p>';
            
            echo '<h3>Users from API:</h3>';
            echo '<table>';
            echo '<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Role</th><th>Status</th></tr>';
            
            foreach ($apiUsers as $user) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($user['id'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($user['username'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($user['email'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($user['full_name'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($user['role'] ?? 'N/A') . '</td>';
                echo '<td>' . htmlspecialchars($user['status'] ?? 'N/A') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p class="error">❌ API did not return users data in expected format</p>';
            echo '<p>API Response Structure:</p>';
            echo '<pre>' . htmlspecialchars(json_encode($apiUsers, JSON_PRETTY_PRINT)) . '</pre>';
        }
    }
} catch (Exception $e) {
    echo '<p class="error">❌ Error testing API: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
echo '</div>';

// SECTION 5: Test Admin Dashboard API Request
echo '<div class="section">';
echo '<h2>5. Simulate Admin Dashboard API Request</h2>';
echo '<p>This test simulates exactly how the admin dashboard loads user data.</p>';

echo '<div id="users-container">Loading users data...</div>';

echo '<script>
    // Function to safely display JSON data
    function htmlEscape(str) {
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/\'/g, "&#039;");
    }
    
    // Function to load users
    async function loadUsers() {
        const container = document.getElementById("users-container");
        
        try {
            container.innerHTML = "<p>Fetching user data...</p>";
            
            // Log everything for debugging
            console.log("Starting API request");
            
            // First try the fixed API
            const response = await fetch("api/users-fixed.php");
            console.log("API Response Status:", response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }
            
            // Get response as text first for debugging
            const responseText = await response.text();
            console.log("Raw API Response:", responseText);
            
            // Try to parse JSON
            let users;
            try {
                users = JSON.parse(responseText);
                console.log("Parsed JSON:", users);
                
                // Check response structure
                if (!Array.isArray(users)) {
                    if (users.data && Array.isArray(users.data)) {
                        users = users.data;
                        console.log("Extracted data array:", users);
                    } else {
                        throw new Error("Unexpected data format - not an array");
                    }
                }
            } catch (parseError) {
                container.innerHTML = `<p class="error">❌ JSON Parse Error: ${parseError.message}</p>
                                      <p>Raw response:</p>
                                      <pre>${htmlEscape(responseText)}</pre>`;
                return;
            }
            
            if (Array.isArray(users) && users.length > 0) {
                container.innerHTML = `<p class="success">✅ Successfully loaded ${users.length} users</p>`;
                
                // Create table
                let tableHtml = `<table>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>`;
                
                // Add rows
                users.forEach(user => {
                    tableHtml += `<tr>
                        <td>${htmlEscape(user.id || "")}</td>
                        <td>${htmlEscape(user.username || "")}</td>
                        <td>${htmlEscape(user.email || "")}</td>
                        <td>${htmlEscape(user.full_name || "")}</td>
                        <td>${htmlEscape(user.role || "")}</td>
                        <td>${htmlEscape(user.status || "")}</td>
                    </tr>`;
                });
                
                tableHtml += "</table>";
                container.innerHTML += tableHtml;
            } else {
                container.innerHTML = `<p class="error">❌ No users returned from API</p>
                                      <pre>${htmlEscape(JSON.stringify(users, null, 2))}</pre>`;
            }
        } catch (error) {
            container.innerHTML = `<p class="error">❌ Error: ${error.message}</p>`;
            console.error("Error loading users:", error);
        }
    }
    
    // Load users when page loads
    window.addEventListener("load", loadUsers);
</script>';
echo '</div>';

// SECTION 6: Create a new direct access script
echo '<div id="fix-section">';
echo '<h2>6. Direct Database Display and Quick Fix</h2>';

echo '<form method="post">';
echo '<button type="submit" name="create_direct_view">Create Direct Database View</button>';
echo '</form>';

// Generate direct access PHP file if requested
if (isset($_POST['create_direct_view'])) {
    // Generate the file content
    $directViewContent = '<?php
/**
 * Direct Database View
 * This script directly queries the database and displays users in a simple table
 */

// Error reporting for debugging
error_reporting(E_ALL);
ini_set("display_errors", 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Users - Direct Database View</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1, h2 { color: #1a365d; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table, th, td { border: 1px solid #ddd; }
        th { background-color: #1a365d; color: white; padding: 8px; text-align: left; }
        td { padding: 8px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .actions { display: flex; gap: 10px; }
        .btn { display: inline-block; padding: 5px 10px; background: #3182ce; color: white;
               border-radius: 4px; text-decoration: none; font-size: 0.8rem; }
        .btn:hover { background: #2c5282; }
        .btn-danger { background: #e53e3e; }
        .btn-danger:hover { background: #c53030; }
    </style>
</head>
<body>
    <h1>Users - Direct Database View</h1>
    <p>This page bypasses the API and directly displays user data from the database.</p>";

// Handle user operations if requested
$message = "";
if (isset($_POST["action"])) {
    try {
        require_once __DIR__ . "/config/database.php";
        $pdo = getDBConnection();
        
        // Handle edit user
        if ($_POST["action"] === "edit" && isset($_POST["id"])) {
            $id = (int)$_POST["id"];
            $fullName = $_POST["full_name"] ?? "";
            $email = $_POST["email"] ?? "";
            $role = $_POST["role"] ?? "";
            $status = $_POST["status"] ?? "";
            
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, status = ? WHERE id = ?");
            if ($stmt->execute([$fullName, $email, $role, $status, $id])) {
                $message = "<p class=\"success\">User updated successfully!</p>";
            } else {
                $message = "<p class=\"error\">Failed to update user.</p>";
            }
        }
        
        // Handle delete user
        if ($_POST["action"] === "delete" && isset($_POST["id"])) {
            $id = (int)$_POST["id"];
            
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$id])) {
                $message = "<p class=\"success\">User deleted successfully!</p>";
            } else {
                $message = "<p class=\"error\">Failed to delete user.</p>";
            }
        }
    } catch (Exception $e) {
        $message = "<p class=\"error\">Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Display any message
echo $message;

// Edit form if requested
if (isset($_GET["edit"])) {
    try {
        require_once __DIR__ . "/config/database.php";
        $pdo = getDBConnection();
        
        $id = (int)$_GET["edit"];
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "<h2>Edit User: " . htmlspecialchars($user["username"]) . "</h2>";
            echo "<form method=\"post\">";
            echo "<input type=\"hidden\" name=\"action\" value=\"edit\">";
            echo "<input type=\"hidden\" name=\"id\" value=\"" . $user["id"] . "\">";
            
            echo "<table>";
            echo "<tr><td>Username:</td><td>" . htmlspecialchars($user["username"]) . "</td></tr>";
            echo "<tr><td>Full Name:</td><td><input type=\"text\" name=\"full_name\" value=\"" . htmlspecialchars($user["full_name"]) . "\" required></td></tr>";
            echo "<tr><td>Email:</td><td><input type=\"email\" name=\"email\" value=\"" . htmlspecialchars($user["email"]) . "\" required></td></tr>";
            echo "<tr><td>Role:</td><td>
                <select name=\"role\">
                    <option value=\"admin\"" . ($user["role"] === "admin" ? " selected" : "") . ">Admin</option>
                    <option value=\"manager\"" . ($user["role"] === "manager" ? " selected" : "") . ">Manager</option>
                    <option value=\"user\"" . ($user["role"] === "user" ? " selected" : "") . ">User</option>
                </select>
            </td></tr>";
            echo "<tr><td>Status:</td><td>
                <select name=\"status\">
                    <option value=\"active\"" . ($user["status"] === "active" ? " selected" : "") . ">Active</option>
                    <option value=\"inactive\"" . ($user["status"] === "inactive" ? " selected" : "") . ">Inactive</option>
                    <option value=\"suspended\"" . ($user["status"] === "suspended" ? " selected" : "") . ">Suspended</option>
                </select>
            </td></tr>";
            echo "<tr><td colspan=\"2\"><button type=\"submit\">Save Changes</button></td></tr>";
            echo "</table>";
            echo "</form>";
            
            echo "<p><a href=\"direct-users.php\">Cancel and go back</a></p>";
        } else {
            echo "<p class=\"error\">User not found!</p>";
            echo "<p><a href=\"direct-users.php\">Go back to user list</a></p>";
        }
        
        // Exit early so we don\'t show the user list
        echo "</body></html>";
        exit;
    } catch (Exception $e) {
        echo "<p class=\"error\">Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// List users
try {
    require_once __DIR__ . "/config/database.php";
    $pdo = getDBConnection();
    
    $stmt = $pdo->query("SELECT id, username, email, full_name, role, status FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "<h2>Users in Database</h2>";
        echo "<table>";
        echo "<tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Full Name</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user["id"]) . "</td>";
            echo "<td>" . htmlspecialchars($user["username"]) . "</td>";
            echo "<td>" . htmlspecialchars($user["email"]) . "</td>";
            echo "<td>" . htmlspecialchars($user["full_name"]) . "</td>";
            echo "<td>" . htmlspecialchars($user["role"]) . "</td>";
            echo "<td>" . htmlspecialchars($user["status"]) . "</td>";
            echo "<td class=\"actions\">
                    <a href=\"direct-users.php?edit=" . $user["id"] . "\" class=\"btn\">Edit</a>
                    <form method=\"post\" onsubmit=\"return confirm(\'Are you sure you want to delete this user?\')\" style=\"display: inline;\">
                        <input type=\"hidden\" name=\"action\" value=\"delete\">
                        <input type=\"hidden\" name=\"id\" value=\"" . $user["id"] . "\">
                        <button type=\"submit\" class=\"btn btn-danger\">Delete</button>
                    </form>
                  </td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class=\"error\">No users found in database!</p>";
    }
} catch (Exception $e) {
    echo "<p class=\"error\">Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Add a new user form
echo "<h2>Add New User</h2>";
echo "<form method=\"post\">";
echo "<input type=\"hidden\" name=\"action\" value=\"add\">";
echo "<table>";
echo "<tr><td>Username:</td><td><input type=\"text\" name=\"username\" required></td></tr>";
echo "<tr><td>Full Name:</td><td><input type=\"text\" name=\"full_name\" required></td></tr>";
echo "<tr><td>Email:</td><td><input type=\"email\" name=\"email\" required></td></tr>";
echo "<tr><td>Password:</td><td><input type=\"password\" name=\"password\" required></td></tr>";
echo "<tr><td>Role:</td><td>
    <select name=\"role\">
        <option value=\"admin\">Admin</option>
        <option value=\"manager\">Manager</option>
        <option value=\"user\" selected>User</option>
    </select>
</td></tr>";
echo "<tr><td colspan=\"2\"><button type=\"submit\">Add User</button></td></tr>";
echo "</table>";
echo "</form>";

echo "<p><a href=\"admin-dashboard.php\">Go to Admin Dashboard</a></p>";
echo "</body></html>";
';

    // Save the file
    $directViewFile = __DIR__ . '/direct-users.php';
    if (file_put_contents($directViewFile, $directViewContent)) {
        echo '<p class="success">✅ Direct database view created successfully!</p>';
        echo '<p>You can now <a href="direct-users.php" target="_blank">access the direct user management page</a> to view and manage users directly.</p>';
    } else {
        echo '<p class="error">❌ Failed to create direct database view file. Check file permissions.</p>';
    }
}

echo '</div>';

echo '<p><a href="admin-dashboard.php">Return to Admin Dashboard</a></p>';
echo '</body></html>';
?>
