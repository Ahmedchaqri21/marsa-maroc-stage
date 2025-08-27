<?php
// Test script pour vérifier l'API d'authentification
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test API Authentification - Marsa Maroc</title>
    <link href="assets/css/marsa-maroc-style.css" rel="stylesheet">
    <style>
        body {
            background: var(--light-gray);
            padding: var(--spacing-xl);
            font-family: var(--font-family);
        }
        
        .test-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .test-card {
            background: var(--white);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: var(--spacing-lg);
        }
        
        .test-result {
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            margin-top: var(--spacing-md);
            font-family: monospace;
            white-space: pre-wrap;
        }
        
        .success {
            background: var(--success-green);
            color: white;
        }
        
        .error {
            background: var(--danger-red);
            color: white;
        }
        
        .info {
            background: var(--info-blue);
            color: white;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-card">
            <h1 class="marsa-title">Test API Authentification</h1>
            <p>Test des connexions et de l'API d'authentification</p>
        </div>

        <?php
        // Test 1: Connexion à la base de données
        echo '<div class="test-card">';
        echo '<h2>Test 1: Connexion Base de Données</h2>';
        
        try {
            require_once 'config/database.php';
            $pdo = getDBConnection();
            echo '<div class="test-result success">✅ Connexion à la base de données réussie</div>';
            
            // Test des utilisateurs
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
            $result = $stmt->fetch();
            echo '<div class="test-result info">📊 Utilisateurs actifs: ' . $result['count'] . '</div>';
            
        } catch (Exception $e) {
            echo '<div class="test-result error">❌ Erreur de connexion: ' . $e->getMessage() . '</div>';
        }
        echo '</div>';

        // Test 2: Vérification des utilisateurs de test
        echo '<div class="test-card">';
        echo '<h2>Test 2: Utilisateurs de Test</h2>';
        
        try {
            $stmt = $pdo->query("SELECT username, email, role, status FROM users WHERE username IN ('admin', 'manager', 'user')");
            $users = $stmt->fetchAll();
            
            if (count($users) > 0) {
                echo '<div class="test-result success">✅ Utilisateurs de test trouvés:</div>';
                foreach ($users as $user) {
                    echo '<div class="test-result info">';
                    echo "Username: {$user['username']}\n";
                    echo "Email: {$user['email']}\n";
                    echo "Rôle: {$user['role']}\n";
                    echo "Statut: {$user['status']}\n";
                    echo '</div>';
                }
            } else {
                echo '<div class="test-result error">❌ Aucun utilisateur de test trouvé</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="test-result error">❌ Erreur: ' . $e->getMessage() . '</div>';
        }
        echo '</div>';

        // Test 3: Test API Auth direct
        echo '<div class="test-card">';
        echo '<h2>Test 3: API Auth (Manuel)</h2>';
        ?>
        
        <form id="testForm" style="margin-bottom: 20px;">
            <div class="marsa-form-group">
                <label class="marsa-label">Username:</label>
                <input type="text" id="testUsername" class="marsa-input" value="admin" placeholder="admin">
            </div>
            <div class="marsa-form-group">
                <label class="marsa-label">Password:</label>
                <input type="password" id="testPassword" class="marsa-input" value="admin123" placeholder="admin123">
            </div>
            <button type="submit" class="marsa-btn marsa-btn-primary">Tester l'API</button>
        </form>
        
        <div id="apiResult"></div>
        
        </div>

        <!-- Test 4: Informations système -->
        <div class="test-card">
            <h2>Test 4: Informations Système</h2>
            <?php
            echo '<div class="test-result info">';
            echo "PHP Version: " . phpversion() . "\n";
            echo "Extensions chargées: " . (extension_loaded('pdo_mysql') ? '✅ PDO MySQL' : '❌ PDO MySQL') . "\n";
            echo "Session status: " . (session_status() === PHP_SESSION_ACTIVE ? '✅ Active' : '❌ Inactive') . "\n";
            echo "Error reporting: " . error_reporting() . "\n";
            echo '</div>';
            ?>
        </div>
    </div>

    <script>
        document.getElementById('testForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('testUsername').value;
            const password = document.getElementById('testPassword').value;
            const resultDiv = document.getElementById('apiResult');
            
            resultDiv.innerHTML = '<div class="test-result info">⏳ Test en cours...</div>';
            
            try {
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        username: username,
                        password: password
                    })
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                const responseText = await response.text();
                console.log('Response text:', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    throw new Error('Réponse non-JSON reçue: ' + responseText.substring(0, 200));
                }
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="test-result success">✅ Authentification réussie!</div>
                        <div class="test-result info">Données utilisateur: ${JSON.stringify(data.user, null, 2)}</div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="test-result error">❌ Échec de l'authentification: ${data.error}</div>
                    `;
                }
                
            } catch (error) {
                console.error('Erreur complète:', error);
                resultDiv.innerHTML = `
                    <div class="test-result error">❌ Erreur: ${error.message}</div>
                `;
            }
        });
    </script>
</body>
</html>
