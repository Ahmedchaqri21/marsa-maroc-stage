<?php
// Test de connexion à la base de données
header('Content-Type: application/json; charset=utf-8');

try {
    // Test de connexion sans base de données spécifique
    $pdo_test = new PDO(
        "mysql:host=localhost;charset=utf8",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Vérifier si la base de données existe
    $stmt = $pdo_test->query("SHOW DATABASES LIKE 'marsa_maroc_db'");
    $db_exists = $stmt->fetch();
    
    if (!$db_exists) {
        // Créer la base de données si elle n'existe pas
        $pdo_test->exec("CREATE DATABASE marsa_maroc_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $message = "Base de données 'marsa_maroc_db' créée avec succès";
    } else {
        $message = "Base de données 'marsa_maroc_db' existe déjà";
    }
    
    // Maintenant tester la connexion avec la base de données
    $pdo = new PDO(
        "mysql:host=localhost;dbname=marsa_maroc_db;charset=utf8",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Tester une requête simple
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'database_test' => $result,
        'connection' => 'OK'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'connection' => 'FAILED'
    ]);
}
?>
