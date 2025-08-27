<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test des boutons - Marsa Maroc</title>
    <link rel="stylesheet" href="assets/css/marsa-maroc-style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        
        .test-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .btn {
            padding: 8px 16px;
            margin: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-primary { background-color: #007bff; color: white; }
        .btn-warning { background-color: #ffc107; color: black; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-info { background-color: #17a2b8; color: white; }
        
        .btn:hover { opacity: 0.8; }
        
        .result {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }
        
        #log {
            height: 200px;
            overflow-y: auto;
            background: #000;
            color: #0f0;
            padding: 10px;
            font-family: monospace;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <h1>Test de Fonctionnalité - Marsa Maroc</h1>
    
    <div class="test-section">
        <h3>Test des API</h3>
        <button class="btn btn-primary" onclick="testAPI('emplacements')">Test Emplacements API</button>
        <button class="btn btn-primary" onclick="testAPI('users')">Test Users API</button>
        <button class="btn btn-primary" onclick="testAPI('reservations')">Test Reservations API</button>
        <div id="api-results"></div>
    </div>
    
    <div class="test-section">
        <h3>Test des Actions</h3>
        <button class="btn btn-warning" onclick="testEdit('emplacement', 1)">Edit Emplacement</button>
        <button class="btn btn-danger" onclick="testDelete('emplacement', 1)">Delete Emplacement</button>
        <button class="btn btn-success" onclick="testValidate(1)">Validate Reservation</button>
        <button class="btn btn-danger" onclick="testReject(1)">Reject Reservation</button>
        <button class="btn btn-info" onclick="testView(1)">View Reservation</button>
        <div id="action-results"></div>
    </div>
    
    <div class="test-section">
        <h3>Console Log</h3>
        <div id="log"></div>
        <button class="btn btn-secondary" onclick="clearLog()">Clear Log</button>
    </div>

    <script>
        function log(message) {
            const logDiv = document.getElementById('log');
            const time = new Date().toLocaleTimeString();
            logDiv.textContent += `[${time}] ${message}\n`;
            logDiv.scrollTop = logDiv.scrollHeight;
            console.log(message);
        }

        function showResult(containerId, message) {
            const container = document.getElementById(containerId);
            container.innerHTML = `<div class="result">${message}</div>`;
        }

        function clearLog() {
            document.getElementById('log').textContent = '';
        }

        async function testAPI(endpoint) {
            log(`Testing ${endpoint} API...`);
            try {
                const response = await fetch(`api/${endpoint}.php`);
                const data = await response.json();
                log(`${endpoint} API response: ${JSON.stringify(data).substring(0, 200)}...`);
                showResult('api-results', `✅ ${endpoint} API fonctionne - ${Array.isArray(data) ? data.length : 1} résultat(s)`);
            } catch (error) {
                log(`Error testing ${endpoint} API: ${error.message}`);
                showResult('api-results', `❌ Erreur ${endpoint} API: ${error.message}`);
            }
        }

        async function testEdit(type, id) {
            log(`Testing edit ${type} ${id}...`);
            try {
                const response = await fetch(`api/${type}s.php?id=${id}`);
                const data = await response.json();
                if (data && !data.error) {
                    log(`Edit ${type} data loaded successfully`);
                    showResult('action-results', `✅ Edit ${type} - Données chargées avec succès`);
                } else {
                    throw new Error(data.error || 'No data found');
                }
            } catch (error) {
                log(`Error testing edit: ${error.message}`);
                showResult('action-results', `❌ Erreur edit: ${error.message}`);
            }
        }

        async function testDelete(type, id) {
            log(`Testing delete ${type} ${id}...`);
            if (confirm(`Tester la suppression de ${type} ${id} ?`)) {
                showResult('action-results', `⚠️ Test de suppression confirmé pour ${type} ${id}`);
                log(`Delete test confirmed for ${type} ${id}`);
            } else {
                showResult('action-results', `❌ Test de suppression annulé`);
                log(`Delete test cancelled`);
            }
        }

        async function testValidate(id) {
            log(`Testing validate reservation ${id}...`);
            try {
                // Test de l'API sans vraiment valider
                const response = await fetch(`api/reservations.php?id=${id}`);
                const data = await response.json();
                if (data && !data.error) {
                    log(`Validation test - reservation data loaded`);
                    showResult('action-results', `✅ Test validation - Réservation trouvée`);
                } else {
                    throw new Error(data.error || 'Reservation not found');
                }
            } catch (error) {
                log(`Error testing validation: ${error.message}`);
                showResult('action-results', `❌ Erreur test validation: ${error.message}`);
            }
        }

        async function testReject(id) {
            log(`Testing reject reservation ${id}...`);
            const motif = prompt('Motif du refus (test):');
            if (motif !== null) {
                log(`Reject test with reason: ${motif}`);
                showResult('action-results', `✅ Test refus avec motif: ${motif}`);
            } else {
                log(`Reject test cancelled`);
                showResult('action-results', `❌ Test refus annulé`);
            }
        }

        async function testView(id) {
            log(`Testing view reservation ${id}...`);
            try {
                const response = await fetch(`api/reservations.php?id=${id}`);
                const data = await response.json();
                if (data && !data.error) {
                    const info = `Numéro: ${data.numero_reservation || data.id}\nStatut: ${data.statut || 'N/A'}`;
                    alert(`Détails de la réservation:\n${info}`);
                    log(`View test successful`);
                    showResult('action-results', `✅ Test affichage réussie`);
                } else {
                    throw new Error(data.error || 'Reservation not found');
                }
            } catch (error) {
                log(`Error testing view: ${error.message}`);
                showResult('action-results', `❌ Erreur test affichage: ${error.message}`);
            }
        }

        // Test automatique au chargement
        window.onload = function() {
            log('Page de test chargée');
            log('Tous les boutons sont prêts pour les tests');
        };
    </script>
</body>
</html>
