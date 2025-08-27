<?php
/**
 * Script de configuration et de réparation de la base de données Marsa Maroc
 */

// Afficher les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Paramètres de connexion MySQL
$host = 'localhost';
$port = '3306';
$user = 'root';
$password = '';
$database = 'gestion_operations_portuaires';

echo "<h1>Configuration de la base de données Marsa Maroc</h1>";

try {
    // Étape 1: Connexion au serveur MySQL sans spécifier de base de données
    echo "<h2>Étape 1: Connexion au serveur MySQL</h2>";
    $pdo = new PDO(
        "mysql:host=$host;port=$port;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "<p style='color: green;'>✅ Connexion au serveur MySQL établie</p>";

    // Étape 2: Vérifier si la base de données existe
    echo "<h2>Étape 2: Vérification de la base de données</h2>";
    $stmt = $pdo->query("SHOW DATABASES LIKE '$database'");
    $dbExists = $stmt->rowCount() > 0;
    
    if ($dbExists) {
        echo "<p>✅ La base de données <strong>$database</strong> existe déjà</p>";
    } else {
        echo "<p>La base de données <strong>$database</strong> n'existe pas. Création en cours...</p>";
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p style='color: green;'>✅ Base de données <strong>$database</strong> créée avec succès</p>";
    }
    
    // Étape 3: Sélectionner la base de données
    echo "<h2>Étape 3: Sélection de la base de données</h2>";
    $pdo->exec("USE $database");
    echo "<p style='color: green;'>✅ Base de données <strong>$database</strong> sélectionnée</p>";

    // Étape 4: Créer les tables si elles n'existent pas
    echo "<h2>Étape 4: Initialisation du schéma</h2>";
    
    // Tableau pour suivre les tables à créer
    $tables = ['users', 'emplacements', 'reservations'];
    $existingTables = [];
    
    // Vérifier quelles tables existent déjà
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch()) {
        $existingTables[] = $row[0];
    }
    
    // Afficher les tables existantes
    echo "<p>Tables existantes: " . (empty($existingTables) ? "aucune" : implode(", ", $existingTables)) . "</p>";
    
    // Lire et exécuter le script SQL du schéma
    echo "<h2>Étape 5: Exécution du script SQL</h2>";
    
    // Chemin vers le fichier de schéma
    $schemaFile = __DIR__ . '/database/schema.sql';
    
    if (file_exists($schemaFile)) {
        echo "<p>Lecture du fichier de schéma: $schemaFile</p>";
        $sql = file_get_contents($schemaFile);
        
        // Diviser en requêtes individuelles
        $queries = explode(';', $sql);
        $totalQueries = count($queries);
        $executedQueries = 0;
        $errors = [];
        
        // Exécuter chaque requête
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                try {
                    $pdo->exec($query);
                    $executedQueries++;
                } catch (PDOException $e) {
                    // Ignorer les erreurs "table already exists"
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        $errors[] = $e->getMessage();
                    }
                }
            }
        }
        
        // Afficher le résultat de l'exécution
        if (empty($errors)) {
            echo "<p style='color: green;'>✅ Script SQL exécuté avec succès ($executedQueries requêtes)</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Script SQL exécuté avec {$executedQueries} requêtes, mais avec des erreurs:</p>";
            echo "<ul>";
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color: red;'>❌ Le fichier de schéma n'existe pas: $schemaFile</p>";
    }
    
    // Étape 6: Vérification finale des tables
    echo "<h2>Étape 6: Vérification finale</h2>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $finalTables = [];
    while ($row = $stmt->fetch()) {
        $finalTables[] = $row[0];
    }
    
    echo "<p>Tables dans la base de données après initialisation: " . implode(", ", $finalTables) . "</p>";
    
    // Vérifier la présence des utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    echo "<p>Nombre d'utilisateurs: $userCount</p>";
    
    if ($userCount > 0) {
        echo "<p style='color: green;'>✅ Les utilisateurs ont été créés avec succès</p>";
        echo "<p>Détails des utilisateurs:</p>";
        echo "<ul>";
        $stmt = $pdo->query("SELECT username, email, role FROM users");
        while ($user = $stmt->fetch()) {
            echo "<li><strong>{$user['username']}</strong> - {$user['email']} - Rôle: {$user['role']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Aucun utilisateur n'a été créé</p>";
    }
    
    echo "<h2>Configuration terminée</h2>";
    echo "<p style='color: green;'>✅ La base de données a été configurée avec succès.</p>";
    echo "<p>Vous pouvez maintenant accéder à l'application à l'adresse: <a href='login.php'>Page de connexion</a></p>";
    
    // Récapitulation des informations de connexion
    echo "<h3>Informations de connexion à la base de données:</h3>";
    echo "<ul>";
    echo "<li>Hôte: $host</li>";
    echo "<li>Port: $port</li>";
    echo "<li>Base de données: $database</li>";
    echo "<li>Utilisateur: $user</li>";
    echo "</ul>";
    
    echo "<h3>Comptes utilisateurs disponibles:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin / admin123</li>";
    echo "<li><strong>Manager:</strong> manager / admin123</li>";
    echo "<li><strong>Utilisateur:</strong> user1 / admin123</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>❌ Erreur lors de la configuration de la base de données</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    
    // Afficher des informations de débogage
    echo "<h3>Informations de débogage:</h3>";
    echo "<ul>";
    echo "<li>Hôte: $host</li>";
    echo "<li>Port: $port</li>";
    echo "<li>Base de données: $database</li>";
    echo "<li>Utilisateur: $user</li>";
    echo "<li>PHP version: " . phpversion() . "</li>";
    echo "<li>PDO drivers disponibles: " . implode(", ", PDO::getAvailableDrivers()) . "</li>";
    echo "</ul>";
}
?>
