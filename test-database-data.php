<?php
require_once 'config/database.php';

echo "<h2>Test de la Base de Données - Marsa Maroc</h2>";

try {
    $pdo = getDBConnection();
    echo "✅ Connexion à la base de données réussie!<br><br>";
    
    // Test table users
    echo "<h3>👥 Table USERS:</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "Nombre d'utilisateurs: " . $result['count'] . "<br>";
    
    if ($result['count'] > 0) {
        $stmt = $pdo->query("SELECT id, username, full_name, email, role, status FROM users LIMIT 5");
        $users = $stmt->fetchAll();
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Status</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['username'] . "</td>";
            echo "<td>" . $user['full_name'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "<td>" . $user['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "<br>";
    
    // Test table emplacements
    echo "<h3>🏭 Table EMPLACEMENTS:</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM emplacements");
    $result = $stmt->fetch();
    echo "Nombre d'emplacements: " . $result['count'] . "<br>";
    
    if ($result['count'] > 0) {
        $stmt = $pdo->query("SELECT id, code, nom, superficie, tarif_journalier, etat FROM emplacements LIMIT 5");
        $emplacements = $stmt->fetchAll();
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Code</th><th>Nom</th><th>Superficie</th><th>Tarif</th><th>État</th></tr>";
        foreach ($emplacements as $emp) {
            echo "<tr>";
            echo "<td>" . $emp['id'] . "</td>";
            echo "<td>" . $emp['code'] . "</td>";
            echo "<td>" . $emp['nom'] . "</td>";
            echo "<td>" . $emp['superficie'] . "</td>";
            echo "<td>" . $emp['tarif_journalier'] . "</td>";
            echo "<td>" . $emp['etat'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "<br>";
    
    // Test table reservations
    echo "<h3>📅 Table RESERVATIONS:</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM reservations");
    $result = $stmt->fetch();
    echo "Nombre de réservations: " . $result['count'] . "<br>";
    
    if ($result['count'] > 0) {
        $stmt = $pdo->query("
            SELECT r.id, r.numero_reservation, u.full_name as user_name, 
                   e.nom as emplacement_nom, r.date_debut, r.date_fin, r.statut, r.montant_total
            FROM reservations r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN emplacements e ON r.emplacement_id = e.id
            LIMIT 5
        ");
        $reservations = $stmt->fetchAll();
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Numéro</th><th>Client</th><th>Emplacement</th><th>Début</th><th>Fin</th><th>Statut</th><th>Montant</th></tr>";
        foreach ($reservations as $res) {
            echo "<tr>";
            echo "<td>" . $res['id'] . "</td>";
            echo "<td>" . $res['numero_reservation'] . "</td>";
            echo "<td>" . $res['user_name'] . "</td>";
            echo "<td>" . $res['emplacement_nom'] . "</td>";
            echo "<td>" . $res['date_debut'] . "</td>";
            echo "<td>" . $res['date_fin'] . "</td>";
            echo "<td>" . $res['statut'] . "</td>";
            echo "<td>" . $res['montant_total'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<br><h3>🔗 Tests API:</h3>";
    echo "<a href='api/users.php' target='_blank'>Tester API Users</a><br>";
    echo "<a href='api/emplacements.php' target='_blank'>Tester API Emplacements</a><br>";
    echo "<a href='api/reservations.php' target='_blank'>Tester API Réservations</a><br>";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
}
?>
