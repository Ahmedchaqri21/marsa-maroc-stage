<?php
/**
 * Outil de diagnostic de la base de données Marsa Maroc
 * Vérifie l'état de la connexion à la base de données et l'intégrité du schéma
 */

// Afficher les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure le fichier de configuration de la base de données
require_once 'config/database.php';

echo "<h1>Diagnostic de la base de données - Marsa Maroc</h1>";

// Définir les tables attendues dans le schéma
$expectedTables = [
    'users' => ['id', 'username', 'email', 'password', 'role', 'status'],
    'emplacements' => ['id', 'code', 'nom', 'type', 'superficie', 'etat'],
    'reservations' => ['id', 'numero_reservation', 'user_id', 'emplacement_id', 'statut']
];

// Style CSS pour le rapport
echo <<<HTML
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        line-height: 1.6;
        color: #333;
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
    }
    h1, h2, h3 {
        color: #1a365d;
    }
    .success {
        color: #2f855a;
        background-color: #f0fff4;
        border-left: 4px solid #48bb78;
        padding: 10px;
    }
    .error {
        color: #c53030;
        background-color: #fff5f5;
        border-left: 4px solid #f56565;
        padding: 10px;
    }
    .warning {
        color: #c05621;
        background-color: #fffaf0;
        border-left: 4px solid #ed8936;
        padding: 10px;
    }
    .info {
        color: #2b6cb0;
        background-color: #ebf8ff;
        border-left: 4px solid #4299e1;
        padding: 10px;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        margin: 20px 0;
    }
    table, th, td {
        border: 1px solid #e2e8f0;
    }
    th, td {
        text-align: left;
        padding: 12px;
    }
    th {
        background-color: #edf2f7;
        color: #2d3748;
    }
    tr:nth-child(even) {
        background-color: #f7fafc;
    }
    .code {
        font-family: monospace;
        background-color: #f1f1f1;
        padding: 2px 4px;
        border-radius: 3px;
        font-size: 14px;
    }
</style>
HTML;

try {
    // Tester la connexion à la base de données
    echo "<h2>1. Vérification de la connexion</h2>";
    
    $startTime = microtime(true);
    $pdo = getDBConnection();
    $endTime = microtime(true);
    $connectionTime = round(($endTime - $startTime) * 1000, 2);
    
    echo "<div class='success'>✅ Connexion à la base de données réussie! (en {$connectionTime}ms)</div>";
    
    // Obtenir des informations sur la connexion
    echo "<h3>Informations sur la connexion:</h3>";
    echo "<ul>";
    echo "<li>Hôte: " . DB_HOST . "</li>";
    echo "<li>Base de données: " . DB_NAME . "</li>";
    echo "<li>Version MySQL: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "</li>";
    echo "<li>Jeu de caractères: utf8mb4</li>";
    echo "</ul>";
    
    // Vérifier les tables
    echo "<h2>2. Vérification des tables</h2>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<div class='error'>❌ Aucune table n'a été trouvée dans la base de données!</div>";
        echo "<p>Vous devez initialiser la base de données en exécutant le script <span class='code'>setup-database.php</span>.</p>";
    } else {
        echo "<div class='success'>✅ " . count($tables) . " tables trouvées dans la base de données</div>";
        
        echo "<table>";
        echo "<tr><th>Nom de la table</th><th>Nombre d'enregistrements</th><th>Statut</th></tr>";
        
        foreach ($tables as $table) {
            // Vérifier le nombre d'enregistrements
            $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
            $count = $stmt->fetchColumn();
            
            // Vérifier si la table est attendue
            $status = isset($expectedTables[$table]) ? 'OK' : 'Table non attendue';
            $statusClass = isset($expectedTables[$table]) ? 'success' : 'warning';
            
            echo "<tr>";
            echo "<td>$table</td>";
            echo "<td>$count</td>";
            echo "<td class='$statusClass'>$status</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Vérifier les tables manquantes
        $missingTables = array_diff(array_keys($expectedTables), $tables);
        if (!empty($missingTables)) {
            echo "<div class='error'>❌ Tables manquantes: " . implode(', ', $missingTables) . "</div>";
        }
    }
    
    // Vérifier les données utilisateur
    echo "<h2>3. Vérification des utilisateurs</h2>";
    
    if (in_array('users', $tables)) {
        $stmt = $pdo->query("SELECT id, username, email, role, status FROM users");
        $users = $stmt->fetchAll();
        
        if (empty($users)) {
            echo "<div class='warning'>⚠️ Aucun utilisateur n'a été trouvé dans la table 'users'</div>";
            echo "<p>Vous devez créer au moins un utilisateur administrateur pour utiliser l'application.</p>";
        } else {
            echo "<div class='success'>✅ " . count($users) . " utilisateurs trouvés</div>";
            
            echo "<table>";
            echo "<tr><th>ID</th><th>Nom d'utilisateur</th><th>Email</th><th>Rôle</th><th>Statut</th></tr>";
            
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>{$user['id']}</td>";
                echo "<td>{$user['username']}</td>";
                echo "<td>{$user['email']}</td>";
                echo "<td>{$user['role']}</td>";
                echo "<td>{$user['status']}</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
    }
    
    // Vérifier les réservations
    echo "<h2>4. Vérification des réservations</h2>";
    
    if (in_array('reservations', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM reservations");
        $reservationCount = $stmt->fetchColumn();
        
        if ($reservationCount > 0) {
            echo "<div class='success'>✅ $reservationCount réservations trouvées</div>";
        } else {
            echo "<div class='info'>ℹ️ Aucune réservation n'a encore été créée</div>";
        }
    } else {
        echo "<div class='error'>❌ La table 'reservations' n'existe pas!</div>";
    }
    
    // Tests de requêtes SQL courantes
    echo "<h2>5. Tests de requêtes SQL</h2>";
    
    $tests = [
        "Authentification utilisateur" => "SELECT id, username, role FROM users WHERE username = 'admin' LIMIT 1",
        "Emplacements disponibles" => "SELECT id, code, nom FROM emplacements WHERE etat = 'disponible'",
        "Détails des réservations" => "SELECT r.id, r.numero_reservation, u.username, e.nom 
                                      FROM reservations r 
                                      LEFT JOIN users u ON r.user_id = u.id 
                                      LEFT JOIN emplacements e ON r.emplacement_id = e.id 
                                      LIMIT 5"
    ];
    
    foreach ($tests as $testName => $query) {
        echo "<h3>$testName</h3>";
        
        try {
            $stmt = $pdo->query($query);
            $result = $stmt->fetchAll();
            
            if (!empty($result)) {
                echo "<div class='success'>✅ La requête a retourné " . count($result) . " résultats</div>";
            } else {
                echo "<div class='info'>ℹ️ La requête n'a retourné aucun résultat</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Erreur lors de l'exécution de la requête: " . $e->getMessage() . "</div>";
        }
    }
    
    // Résumé du diagnostic
    echo "<h2>Résumé du diagnostic</h2>";
    echo "<div class='info'>";
    echo "<p><strong>État général:</strong> ";
    
    if (
        !empty($tables) && 
        empty($missingTables) && 
        in_array('users', $tables) && 
        !empty($users)
    ) {
        echo "La base de données semble correctement configurée et fonctionnelle.</p>";
        echo "<p>Vous pouvez accéder à l'application à l'adresse: <a href='login.php'>Page de connexion</a></p>";
    } else {
        echo "Des problèmes ont été détectés avec la base de données.</p>";
        echo "<p>Actions recommandées:</p>";
        echo "<ol>";
        if (empty($tables) || !empty($missingTables)) {
            echo "<li>Exécutez le script <a href='setup-database.php'>setup-database.php</a> pour initialiser la base de données</li>";
        }
        if (in_array('users', $tables) && empty($users)) {
            echo "<li>Créez au moins un utilisateur administrateur</li>";
        }
        echo "</ol>";
    }
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Erreur de connexion à la base de données: " . $e->getMessage() . "</div>";
    
    // Suggestions de résolution
    echo "<h2>Suggestions de résolution</h2>";
    echo "<ol>";
    echo "<li>Vérifiez que le serveur MySQL est en cours d'exécution</li>";
    echo "<li>Vérifiez les paramètres de connexion dans le fichier <span class='code'>config/database.php</span>:</li>";
    echo "<ul>";
    echo "<li>Hôte: " . DB_HOST . " (essayez '127.0.0.1' si 'localhost' ne fonctionne pas)</li>";
    echo "<li>Port: " . DB_PORT . " (vérifiez le port utilisé par MySQL)</li>";
    echo "<li>Base de données: " . DB_NAME . "</li>";
    echo "<li>Utilisateur: " . DB_USER . "</li>";
    echo "<li>Mot de passe: " . (empty(DB_PASS) ? "vide" : "défini") . "</li>";
    echo "</ul>";
    echo "<li>Exécutez le script <a href='setup-database.php'>setup-database.php</a> pour créer la base de données</li>";
    echo "</ol>";
}

// Afficher des informations sur le système
echo "<h2>Informations système</h2>";
echo "<ul>";
echo "<li>PHP version: " . phpversion() . "</li>";
echo "<li>Extensions PHP chargées: " . implode(", ", get_loaded_extensions()) . "</li>";
echo "<li>Pilotes PDO disponibles: " . implode(", ", PDO::getAvailableDrivers()) . "</li>";
echo "<li>Serveur: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li>Système d'exploitation: " . PHP_OS . "</li>";
echo "</ul>";

// Afficher des informations sur XAMPP si disponibles
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/../VERSION.txt')) {
    $xamppVersion = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../VERSION.txt');
    echo "<div class='info'><p>Version XAMPP: $xamppVersion</p></div>";
}

echo "<p><a href='index.php'>Retour à la page d'accueil</a></p>";
?>
