<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Suppression d'Emplacement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 2rem;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-section {
            margin-bottom: 2rem;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .test-section h3 {
            margin-top: 0;
            color: #333;
        }
        button {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            margin: 0.5rem;
        }
        button:hover {
            background: #c82333;
        }
        .success {
            background: #28a745;
        }
        .success:hover {
            background: #218838;
        }
        .result {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 4px;
            white-space: pre-wrap;
        }
        .success-result {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error-result {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .info-result {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test de Suppression d'Emplacement</h1>
        
        <div class="test-section">
            <h3>1. Charger les Emplacements Existants</h3>
            <button onclick="loadEmplacements()" class="success">Charger Emplacements</button>
            <div id="emplacements-result" class="result"></div>
            <div id="emplacements-table"></div>
        </div>
        
        <div class="test-section">
            <h3>2. Test de Suppression par ID</h3>
            <label for="delete-id">ID à supprimer:</label>
            <input type="number" id="delete-id" placeholder="Entrez l'ID" style="margin: 0.5rem; padding: 0.5rem;">
            <button onclick="testDelete()">Supprimer Emplacement</button>
            <div id="delete-result" class="result"></div>
        </div>
        
        <div class="test-section">
            <h3>3. Résultats des Tests</h3>
            <div id="test-log" class="result info-result"></div>
        </div>
    </div>

    <script>
        let testLog = [];
        
        function log(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            testLog.push(`[${timestamp}] ${message}`);
            document.getElementById('test-log').textContent = testLog.join('\n');
            console.log(message);
        }
        
        async function loadEmplacements() {
            try {
                log('🔄 Chargement des emplacements...');
                
                const response = await fetch('api/emplacements-fixed.php');
                const data = await response.json();
                
                const resultDiv = document.getElementById('emplacements-result');
                const tableDiv = document.getElementById('emplacements-table');
                
                if (data.success) {
                    resultDiv.className = 'result success-result';
                    resultDiv.textContent = `✅ ${data.data.length} emplacements chargés avec succès`;
                    
                    // Créer un tableau avec les emplacements
                    let tableHTML = `
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Code</th>
                                    <th>Nom</th>
                                    <th>Type</th>
                                    <th>Statut</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    data.data.forEach(emp => {
                        tableHTML += `
                            <tr>
                                <td>${emp.id}</td>
                                <td>${emp.code}</td>
                                <td>${emp.nom}</td>
                                <td>${emp.type}</td>
                                <td>${emp.statut}</td>
                                <td>
                                    <button class="delete-btn" onclick="quickDelete(${emp.id})">
                                        Supprimer
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    tableHTML += '</tbody></table>';
                    tableDiv.innerHTML = tableHTML;
                    
                    log(`✅ ${data.data.length} emplacements affichés dans le tableau`);
                } else {
                    resultDiv.className = 'result error-result';
                    resultDiv.textContent = `❌ Erreur: ${data.message}`;
                    log(`❌ Erreur lors du chargement: ${data.message}`);
                }
            } catch (error) {
                const resultDiv = document.getElementById('emplacements-result');
                resultDiv.className = 'result error-result';
                resultDiv.textContent = `❌ Erreur réseau: ${error.message}`;
                log(`❌ Erreur réseau: ${error.message}`);
            }
        }
        
        async function testDelete() {
            const id = document.getElementById('delete-id').value;
            const resultDiv = document.getElementById('delete-result');
            
            if (!id) {
                resultDiv.className = 'result error-result';
                resultDiv.textContent = '❌ Veuillez entrer un ID valide';
                log('❌ ID manquant pour la suppression');
                return;
            }
            
            if (!confirm(`Êtes-vous sûr de vouloir supprimer l'emplacement ID: ${id} ?`)) {
                log(`🚫 Suppression annulée par l'utilisateur pour ID: ${id}`);
                return;
            }
            
            try {
                log(`🗑️ Tentative de suppression de l'emplacement ID: ${id}...`);
                
                const response = await fetch(`api/emplacements-fixed.php?id=${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.className = 'result success-result';
                    resultDiv.textContent = `✅ ${data.message}`;
                    log(`✅ Suppression réussie: ${data.message}`);
                    
                    // Recharger automatiquement la liste
                    setTimeout(loadEmplacements, 1000);
                } else {
                    resultDiv.className = 'result error-result';
                    resultDiv.textContent = `❌ Erreur: ${data.message}`;
                    log(`❌ Erreur de suppression: ${data.message}`);
                }
            } catch (error) {
                resultDiv.className = 'result error-result';
                resultDiv.textContent = `❌ Erreur réseau: ${error.message}`;
                log(`❌ Erreur réseau lors de la suppression: ${error.message}`);
            }
        }
        
        async function quickDelete(id) {
            if (!confirm(`Supprimer l'emplacement ID: ${id} ?`)) return;
            
            try {
                log(`🗑️ Suppression rapide de l'emplacement ID: ${id}...`);
                
                const response = await fetch(`api/emplacements-fixed.php?id=${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    log(`✅ Suppression rapide réussie: ${data.message}`);
                    loadEmplacements(); // Recharger immédiatement
                } else {
                    log(`❌ Erreur de suppression rapide: ${data.message}`);
                    alert(`Erreur: ${data.message}`);
                }
            } catch (error) {
                log(`❌ Erreur réseau lors de la suppression rapide: ${error.message}`);
                alert(`Erreur réseau: ${error.message}`);
            }
        }
        
        // Charger automatiquement au démarrage
        document.addEventListener('DOMContentLoaded', function() {
            log('🚀 Page de test chargée');
            loadEmplacements();
        });
    </script>
</body>
</html>
