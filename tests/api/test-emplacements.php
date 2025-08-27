<?php
// Test de validation pour les opérations CRUD des emplacements
session_start();

// Simuler une session admin pour les tests
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['user_nom'] = 'Admin Test';

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test CRUD Emplacements</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        button { margin: 5px; padding: 10px 15px; border: none; border-radius: 3px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-danger { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <h1>Test des Opérations CRUD - Emplacements</h1>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='test-section'>
            <h2>✅ Connexion à la base de données</h2>
            <p class='success'>Base de données connectée avec succès</p>
          </div>";
    
    // Test 1: Vérifier la table emplacements
    echo "<div class='test-section'>
            <h2>Test 1: Structure de la table emplacements</h2>";
    
    $stmt = $pdo->query("DESCRIBE emplacements");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p class='info'>Colonnes de la table emplacements:</p><ul>";
    foreach ($columns as $column) {
        echo "<li><strong>{$column['Field']}</strong> - {$column['Type']} ({$column['Null']} NULL)</li>";
    }
    echo "</ul></div>";
    
    // Test 2: Tester l'API de lecture
    echo "<div class='test-section'>
            <h2>Test 2: API de lecture des emplacements</h2>";
    
    $apiUrl = 'http://localhost/marsa maroc project/api/emplacements-fixed.php';
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json'
        ]
    ]);
    
    $response = file_get_contents($apiUrl, false, $context);
    if ($response !== false) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "<p class='success'>✅ API de lecture fonctionne</p>";
            echo "<p class='info'>Nombre d'emplacements: " . count($data['data']) . "</p>";
        } else {
            echo "<p class='error'>❌ Erreur API: " . ($data['message'] ?? 'Erreur inconnue') . "</p>";
        }
    } else {
        echo "<p class='error'>❌ Impossible de contacter l'API</p>";
    }
    echo "</div>";
    
    // Test 3: Vérifier l'existence des fichiers nécessaires
    echo "<div class='test-section'>
            <h2>Test 3: Fichiers nécessaires</h2>";
    
    $requiredFiles = [
        'admin-dashboard.php' => 'Dashboard administrateur',
        'api/emplacements-fixed.php' => 'API des emplacements',
        'config/database.php' => 'Configuration base de données'
    ];
    
    foreach ($requiredFiles as $file => $description) {
        if (file_exists($file)) {
            echo "<p class='success'>✅ {$description} ({$file})</p>";
        } else {
            echo "<p class='error'>❌ {$description} manquant ({$file})</p>";
        }
    }
    echo "</div>";
    
    // Test 4: Buttons de test interactifs
    echo "<div class='test-section'>
            <h2>Test 4: Tests interactifs</h2>
            <p>Utilisez ces boutons pour tester les fonctionnalités:</p>
            
            <button class='btn-primary' onclick='window.open(\"admin-dashboard.php\", \"_blank\")'>
                🔗 Ouvrir Dashboard Admin
            </button>
            
            <button class='btn-success' onclick='testCreateEmplacement()'>
                ➕ Tester Création
            </button>
            
            <button class='btn-warning' onclick='testEditEmplacement()'>
                ✏️ Tester Modification
            </button>
            
            <button class='btn-danger' onclick='testDeleteEmplacement()'>
                🗑️ Tester Suppression
            </button>
            
            <div id='test-results' style='margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 5px;'></div>
          </div>";
    
} catch (PDOException $e) {
    echo "<div class='test-section'>
            <h2>❌ Erreur de connexion</h2>
            <p class='error'>Erreur: " . htmlspecialchars($e->getMessage()) . "</p>
          </div>";
}

echo "
<script>
async function testCreateEmplacement() {
    const testData = {
        nom: 'Emplacement Test ' + Date.now(),
        type: 'quai',
        longueur: 100,
        largeur: 25,
        profondeur: 12,
        capacite_max: 5000,
        prix_par_heure: 50,
        equipements: 'Grue, Éclairage',
        statut: 'disponible'
    };
    
    try {
        const response = await fetch('api/emplacements-fixed.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(testData)
        });
        
        const result = await response.json();
        displayTestResult('Création', result);
    } catch (error) {
        displayTestResult('Création', { success: false, message: error.message });
    }
}

async function testEditEmplacement() {
    // D'abord, récupérer un emplacement existant
    try {
        const getResponse = await fetch('api/emplacements-fixed.php');
        const getResult = await getResponse.json();
        
        if (getResult.success && getResult.data.length > 0) {
            const emplacement = getResult.data[0];
            const updatedData = {
                ...emplacement,
                nom: emplacement.nom + ' (Modifié)',
                capacite_max: parseInt(emplacement.capacite_max) + 1000
            };
            
            const response = await fetch('api/emplacements-fixed.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(updatedData)
            });
            
            const result = await response.json();
            displayTestResult('Modification', result);
        } else {
            displayTestResult('Modification', { success: false, message: 'Aucun emplacement trouvé pour modification' });
        }
    } catch (error) {
        displayTestResult('Modification', { success: false, message: error.message });
    }
}

async function testDeleteEmplacement() {
    // Créer d'abord un emplacement de test, puis le supprimer
    const testData = {
        nom: 'Emplacement à Supprimer ' + Date.now(),
        type: 'bassin',
        longueur: 50,
        largeur: 20,
        profondeur: 8,
        capacite_max: 2000,
        prix_par_heure: 30,
        equipements: 'Test',
        statut: 'maintenance'
    };
    
    try {
        // Créer
        const createResponse = await fetch('api/emplacements-fixed.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(testData)
        });
        
        const createResult = await createResponse.json();
        
        if (createResult.success && createResult.id) {
            // Supprimer
            const deleteResponse = await fetch(`api/emplacements-fixed.php?id=${createResult.id}`, {
                method: 'DELETE'
            });
            
            const deleteResult = await deleteResponse.json();
            displayTestResult('Suppression', deleteResult);
        } else {
            displayTestResult('Suppression', { success: false, message: 'Impossible de créer un emplacement de test' });
        }
    } catch (error) {
        displayTestResult('Suppression', { success: false, message: error.message });
    }
}

function displayTestResult(operation, result) {
    const resultsDiv = document.getElementById('test-results');
    const timestamp = new Date().toLocaleTimeString();
    const status = result.success ? '✅' : '❌';
    const className = result.success ? 'success' : 'error';
    
    resultsDiv.innerHTML += `
        <div class='${className}' style='margin: 5px 0; padding: 5px; border-left: 3px solid ${result.success ? 'green' : 'red'}'>
            <strong>[${timestamp}] ${status} ${operation}:</strong> ${result.message || (result.success ? 'Succès' : 'Échec')}
        </div>
    `;
    
    // Faire défiler vers le bas
    resultsDiv.scrollTop = resultsDiv.scrollHeight;
}
</script>

</body>
</html>";
?>
