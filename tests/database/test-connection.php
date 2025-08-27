<?php
// Test de connexion à la base de données
require_once 'config/database.php';

echo "<h1>Test de Connexion - Marsa Maroc</h1>";

try {
    // Test de connexion
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✅ Connexion à la base de données réussie!</p>";
    
    // Test des tables
    echo "<h2>Vérification des Tables</h2>";
    
    // Test table users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    echo "<p>👥 Utilisateurs: $userCount</p>";
    
    // Test table emplacements
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM emplacements");
    $empCount = $stmt->fetch()['count'];
    echo "<p>🏗️ Emplacements: $empCount</p>";
    
    // Test table reservations
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM reservations");
    $resCount = $stmt->fetch()['count'];
    echo "<p>📋 Réservations: $resCount</p>";
    
    // Afficher quelques données de test
    echo "<h2>Données de Test</h2>";
    
    // Utilisateurs
    echo "<h3>Utilisateurs</h3>";
    $stmt = $pdo->query("SELECT username, email, role, status FROM users LIMIT 5");
    $users = $stmt->fetchAll();
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Username</th><th>Email</th><th>Rôle</th><th>Statut</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "<td>{$user['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Emplacements
    echo "<h3>Emplacements</h3>";
    $stmt = $pdo->query("SELECT nom, superficie, tarif, etat FROM emplacements LIMIT 5");
    $emplacements = $stmt->fetchAll();
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Nom</th><th>Superficie</th><th>Tarif</th><th>État</th></tr>";
    foreach ($emplacements as $emp) {
        echo "<tr>";
        echo "<td>{$emp['nom']}</td>";
        echo "<td>{$emp['superficie']} m²</td>";
        echo "<td>{$emp['tarif']} DH</td>";
        echo "<td>{$emp['etat']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Réservations
    echo "<h3>Réservations</h3>";
    $stmt = $pdo->query("
        SELECT r.date_debut, r.date_fin, r.statut, r.montant_total, 
               u.full_name, e.nom as emplacement
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        JOIN emplacements e ON r.emplacement_id = e.id
        LIMIT 5
    ");
    $reservations = $stmt->fetchAll();
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Date Début</th><th>Date Fin</th><th>Statut</th><th>Montant</th><th>Utilisateur</th><th>Emplacement</th></tr>";
    foreach ($reservations as $res) {
        echo "<tr>";
        echo "<td>{$res['date_debut']}</td>";
        echo "<td>{$res['date_fin']}</td>";
        echo "<td>{$res['statut']}</td>";
        echo "<td>{$res['montant_total']} DH</td>";
        echo "<td>{$res['full_name']}</td>";
        echo "<td>{$res['emplacement']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>✅ Configuration Terminée!</h2>";
    echo "<p>Votre base de données est prête. Vous pouvez maintenant :</p>";
    echo "<ul>";
    echo "<li><a href='login.php'>Accéder à la page de connexion</a></li>";
    echo "<li>Utiliser le compte admin : <strong>admin</strong> / <strong>password</strong></li>";
    echo "<li>Accéder au tableau de bord administrateur</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur: " . $e->getMessage() . "</p>";
    echo "<h3>Vérifications à effectuer :</h3>";
    echo "<ul>";
    echo "<li>MySQL est-il démarré dans XAMPP ?</li>";
    echo "<li>La base de données 'marsa_maroc_db' existe-t-elle ?</li>";
    echo "<li>Les tables ont-elles été créées ?</li>";
    echo "<li>Les paramètres de connexion dans config/database.php sont-ils corrects ?</li>";
    echo "</ul>";
    echo "<p><a href='database/schema.sql'>Télécharger le script SQL</a></p>";
}
?>

