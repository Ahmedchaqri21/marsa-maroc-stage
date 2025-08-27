<?php
// Database Diagnostic Tool
// This script checks the database connection, schema, and performs basic diagnostic tests

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Diagnostics</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.5; }
        h1, h2, h3 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
        .section { margin: 20px 0; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { text-align: left; padding: 8px; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Database Diagnostics</h1>";

// Step 1: Test the database connection
echo "<div class='section'>";
echo "<h2>Database Connection</h2>";

try {
    require_once 'config/database.php';
    $pdo = getDBConnection();
    echo "<p class='success'>✅ Connection successful</p>";
    
    // Show database information
    $dbInfo = $pdo->query("SELECT version() AS version, database() AS db_name")->fetch();
    echo "<p><strong>MySQL Version:</strong> " . $dbInfo['version'] . "</p>";
    echo "<p><strong>Database Name:</strong> " . $dbInfo['db_name'] . "</p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Connection failed: " . $e->getMessage() . "</p>";
    die("</div></body></html>");
}

// Step 2: Check the tables
echo "</div><div class='section'>";
echo "<h2>Tables Check</h2>";

$requiredTables = ['users', 'emplacements', 'reservations'];
$tablesQuery = $pdo->query("SHOW TABLES");
$existingTables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);

echo "<p>Found " . count($existingTables) . " tables:</p>";
echo "<ul>";
foreach ($existingTables as $table) {
    $class = in_array($table, $requiredTables) ? 'success' : 'info';
    echo "<li class='$class'>$table</li>";
}
echo "</ul>";

$missingTables = array_diff($requiredTables, $existingTables);
if (!empty($missingTables)) {
    echo "<p class='error'>Missing tables: " . implode(', ', $missingTables) . "</p>";
}

// Step 3: Check the table structures
echo "</div><div class='section'>";
echo "<h2>Table Structures</h2>";

foreach ($existingTables as $table) {
    echo "<h3>Table: $table</h3>";
    
    // Get columns
    $columnsQuery = $pdo->query("DESCRIBE `$table`");
    $columns = $columnsQuery->fetchAll();
    
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>" . ($column['Default'] === null ? "NULL" : $column['Default']) . "</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Count records
    $countQuery = $pdo->query("SELECT COUNT(*) AS count FROM `$table`");
    $count = $countQuery->fetch()['count'];
    echo "<p>Records in table: <strong>$count</strong></p>";
}

// Step 4: Test INSERT, UPDATE, DELETE operations
echo "</div><div class='section'>";
echo "<h2>CRUD Operations Test</h2>";

// Start a transaction so we can rollback the test operations
$pdo->beginTransaction();

try {
    // Test INSERT
    echo "<h3>INSERT Test</h3>";
    $code = "TEST" . rand(1000, 9999);
    $insertStmt = $pdo->prepare("
        INSERT INTO emplacements (code, nom, superficie, tarif_horaire, tarif_journalier, tarif_mensuel) 
        VALUES (?, 'Test Emplacement', 100.00, 50.00, 500.00, 10000.00)
    ");
    $insertResult = $insertStmt->execute([$code]);
    $newId = $pdo->lastInsertId();
    
    if ($insertResult) {
        echo "<p class='success'>✅ Successfully inserted test record with ID $newId and code $code</p>";
    } else {
        echo "<p class='error'>❌ Failed to insert test record</p>";
    }
    
    // Test UPDATE
    echo "<h3>UPDATE Test</h3>";
    $updateStmt = $pdo->prepare("
        UPDATE emplacements 
        SET nom = 'Test Emplacement Updated', superficie = 150.00 
        WHERE id = ?
    ");
    $updateResult = $updateStmt->execute([$newId]);
    
    if ($updateResult) {
        echo "<p class='success'>✅ Successfully updated test record</p>";
    } else {
        echo "<p class='error'>❌ Failed to update test record</p>";
    }
    
    // Test SELECT
    echo "<h3>SELECT Test</h3>";
    $selectStmt = $pdo->prepare("SELECT * FROM emplacements WHERE id = ?");
    $selectStmt->execute([$newId]);
    $record = $selectStmt->fetch();
    
    if ($record) {
        echo "<p class='success'>✅ Successfully retrieved test record:</p>";
        echo "<pre>" . print_r($record, true) . "</pre>";
    } else {
        echo "<p class='error'>❌ Failed to retrieve test record</p>";
    }
    
    // Test DELETE
    echo "<h3>DELETE Test</h3>";
    $deleteStmt = $pdo->prepare("DELETE FROM emplacements WHERE id = ?");
    $deleteResult = $deleteStmt->execute([$newId]);
    
    if ($deleteResult) {
        echo "<p class='success'>✅ Successfully deleted test record</p>";
    } else {
        echo "<p class='error'>❌ Failed to delete test record</p>";
    }
    
    // Rollback the transaction to clean up
    $pdo->rollBack();
    echo "<p class='info'>All test operations rolled back to maintain data integrity</p>";
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "<p class='error'>❌ Test operations failed: " . $e->getMessage() . "</p>";
}

// Step 5: Test the API endpoints
echo "</div><div class='section'>";
echo "<h2>API Endpoints Test</h2>";

$apiEndpoints = [
    'api/emplacements-real.php' => 'GET',
    'api/users-real.php' => 'GET',
    'api/reservations-real.php' => 'GET'
];

foreach ($apiEndpoints as $endpoint => $method) {
    echo "<h3>Testing $endpoint</h3>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['REQUEST_URI']) . "/$endpoint");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "<p class='success'>✅ API responded with HTTP code $httpCode</p>";
        
        // Try to decode the JSON response
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<p>Response contains valid JSON</p>";
        } else {
            echo "<p class='warning'>⚠️ Response is not valid JSON: " . json_last_error_msg() . "</p>";
        }
    } else {
        echo "<p class='error'>❌ API failed with HTTP code $httpCode</p>";
    }
    
    echo "<details>";
    echo "<summary>View Response</summary>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    echo "</details>";
}

echo "</div>";
echo "</body></html>";
