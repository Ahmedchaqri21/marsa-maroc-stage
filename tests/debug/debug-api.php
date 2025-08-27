<?php
// Debug script for API testing
echo "<h1>API Debug - Marsa Maroc</h1>";

// Test database connection
echo "<h2>1. Test Connexion Base de Données</h2>";
try {
    require_once 'config/database.php';
    $pdo = getDBConnection();
    echo "<span style='color: green;'>✓ Connexion à la base de données réussie</span><br>";
    
    // Test if tables exist
    $tables = ['users', 'emplacements', 'reservations'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<span style='color: green;'>✓ Table '$table' existe avec $count enregistrements</span><br>";
        } catch (Exception $e) {
            echo "<span style='color: red;'>✗ Erreur table '$table': " . $e->getMessage() . "</span><br>";
        }
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Erreur connexion BDD: " . $e->getMessage() . "</span><br>";
}

echo "<h2>2. Test API Reservations</h2>";
// Test the reservations API directly
try {
    ob_start();
    $_SERVER['REQUEST_METHOD'] = 'GET';
    include 'api/reservations.php';
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "<strong>Sortie API Reservations:</strong><br>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 300px; overflow: auto;'>";
    echo htmlspecialchars($output);
    echo "</pre>";
    
    // Test if it's valid JSON
    $decoded = json_decode($output, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<span style='color: green;'>✓ Sortie JSON valide</span><br>";
    } else {
        echo "<span style='color: red;'>✗ Sortie JSON invalide: " . json_last_error_msg() . "</span><br>";
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Erreur API: " . $e->getMessage() . "</span><br>";
}

echo "<h2>3. Test API Emplacements</h2>";
// Test the emplacements API directly
try {
    ob_start();
    $_SERVER['REQUEST_METHOD'] = 'GET';
    include 'api/emplacements.php';
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "<strong>Sortie API Emplacements:</strong><br>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 300px; overflow: auto;'>";
    echo htmlspecialchars($output);
    echo "</pre>";
    
    // Test if it's valid JSON
    $decoded = json_decode($output, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<span style='color: green;'>✓ Sortie JSON valide</span><br>";
    } else {
        echo "<span style='color: red;'>✗ Sortie JSON invalide: " . json_last_error_msg() . "</span><br>";
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Erreur API: " . $e->getMessage() . "</span><br>";
}

echo "<h2>4. Configuration PHP</h2>";
echo "Version PHP: " . phpversion() . "<br>";
echo "Extensions chargées: " . implode(', ', get_loaded_extensions()) . "<br>";
echo "Error Reporting: " . ini_get('error_reporting') . "<br>";
echo "Display Errors: " . ini_get('display_errors') . "<br>";

?>
