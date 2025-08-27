<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de test des CRUD</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2 { color: #333; }
        .card { border: 1px solid #ddd; border-radius: 4px; padding: 15px; margin-bottom: 20px; }
        .btn { display: inline-block; background: #007bff; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
        .btn-danger { background: #dc3545; }
        .btn-warning { background: #ffc107; color: #212529; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Page de test des opérations CRUD</h1>
    <p>Cette page permet de tester les fonctionnalités d'ajout, modification et suppression.</p>
    
    <div class="card">
        <h2>Gestion des Emplacements</h2>
        
        <!-- Formulaire d'ajout/modification -->
        <form id="emplacementForm">
            <input type="hidden" id="emplacement_id" name="id">
            
            <div class="form-group">
                <label for="code">Code</label>
                <input type="text" id="code" name="code" placeholder="Code de l'emplacement (généré automatiquement si vide)">
            </div>
            
            <div class="form-group">
                <label for="nom">Nom de l'emplacement*</label>
                <input type="text" id="nom" name="nom" required placeholder="Nom de l'emplacement">
            </div>
            
            <div class="form-group">
                <label for="superficie">Superficie (m²)*</label>
                <input type="number" id="superficie" name="superficie" step="0.01" required placeholder="Superficie en m²">
            </div>
            
            <div class="form-group">
                <label for="tarif_journalier">Tarif journalier (MAD)*</label>
                <input type="number" id="tarif_journalier" name="tarif_journalier" step="0.01" required placeholder="Tarif journalier">
            </div>
            
            <div class="form-group">
                <label for="etat">État</label>
                <select id="etat" name="etat">
                    <option value="disponible">Disponible</option>
                    <option value="occupe">Occupé</option>
                    <option value="maintenance">En maintenance</option>
                    <option value="reserve">Réservé</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" placeholder="Description de l'emplacement"></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn" id="submitBtn">Ajouter un emplacement</button>
                <button type="button" class="btn btn-warning" id="resetBtn">Réinitialiser</button>
            </div>
        </form>
        
        <div id="message" style="margin-top: 15px;"></div>
        
        <!-- Tableau des emplacements -->
        <h3>Liste des emplacements</h3>
        <table id="emplacementsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Nom</th>
                    <th>Superficie</th>
                    <th>Tarif journalier</th>
                    <th>État</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7">Chargement des données...</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emplacementForm = document.getElementById('emplacementForm');
            const messageDiv = document.getElementById('message');
            const submitBtn = document.getElementById('submitBtn');
            const resetBtn = document.getElementById('resetBtn');
            
            // Charger les emplacements au démarrage
            loadEmplacements();
            
            // Gestionnaire d'événement pour le formulaire
            emplacementForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Récupérer les données du formulaire
                const formData = new FormData(emplacementForm);
                const emplacementId = formData.get('id');
                
                // Convertir en objet JSON
                const data = {
                    nom: formData.get('nom'),
                    superficie: parseFloat(formData.get('superficie')),
                    tarif_journalier: parseFloat(formData.get('tarif_journalier')),
                    etat: formData.get('etat'),
                    description: formData.get('description')
                };
                
                // Ajouter le code s'il est fourni
                const code = formData.get('code');
                if (code && code.trim() !== '') {
                    data.code = code;
                }
                
                // Ajouter l'ID pour une mise à jour
                if (emplacementId && emplacementId.trim() !== '') {
                    data.id = emplacementId;
                }
                
                // Déterminer si c'est une création ou une mise à jour
                const method = emplacementId ? 'PUT' : 'POST';
                const url = emplacementId ? `api/emplacements.php?id=${emplacementId}` : 'api/emplacements.php';
                
                // Envoyer la requête à l'API
                fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showMessage('success', result.message);
                        resetForm();
                        loadEmplacements();
                    } else {
                        showMessage('error', result.error + ': ' + result.message);
                    }
                })
                .catch(error => {
                    showMessage('error', 'Erreur technique: ' + error.message);
                    console.error('Erreur:', error);
                });
            });
            
            // Réinitialiser le formulaire
            resetBtn.addEventListener('click', resetForm);
            
            // Fonction pour charger les emplacements
            function loadEmplacements() {
                fetch('api/emplacements.php')
                    .then(response => response.json())
                    .then(result => {
                        if (result.success && result.data) {
                            displayEmplacements(result.data);
                        } else {
                            showMessage('error', 'Erreur lors du chargement des emplacements');
                        }
                    })
                    .catch(error => {
                        showMessage('error', 'Erreur technique: ' + error.message);
                        console.error('Erreur:', error);
                    });
            }
            
            // Fonction pour afficher les emplacements dans le tableau
            function displayEmplacements(emplacements) {
                const tableBody = document.querySelector('#emplacementsTable tbody');
                
                if (!emplacements || emplacements.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="7">Aucun emplacement trouvé</td></tr>';
                    return;
                }
                
                let html = '';
                emplacements.forEach(emp => {
                    html += `<tr>
                        <td>${emp.id}</td>
                        <td>${emp.code}</td>
                        <td>${emp.nom}</td>
                        <td>${emp.superficie}</td>
                        <td>${emp.tarif_journalier}</td>
                        <td>${emp.etat}</td>
                        <td>
                            <button class="btn" onclick="editEmplacement(${emp.id})">Modifier</button>
                            <button class="btn btn-danger" onclick="deleteEmplacement(${emp.id})">Supprimer</button>
                        </td>
                    </tr>`;
                });
                
                tableBody.innerHTML = html;
            }
            
            // Fonction pour réinitialiser le formulaire
            function resetForm() {
                emplacementForm.reset();
                document.getElementById('emplacement_id').value = '';
                submitBtn.textContent = 'Ajouter un emplacement';
            }
            
            // Fonction pour afficher un message
            function showMessage(type, text) {
                messageDiv.className = type;
                messageDiv.textContent = text;
                
                // Effacer le message après 5 secondes
                setTimeout(() => {
                    messageDiv.textContent = '';
                    messageDiv.className = '';
                }, 5000);
            }
            
            // Rendre les fonctions accessibles globalement
            window.editEmplacement = function(id) {
                fetch(`api/emplacements.php?id=${id}`)
                    .then(response => response.json())
                    .then(result => {
                        if (result.success && result.data && result.data.length > 0) {
                            const emp = result.data[0];
                            
                            // Remplir le formulaire
                            document.getElementById('emplacement_id').value = emp.id;
                            document.getElementById('code').value = emp.code || '';
                            document.getElementById('nom').value = emp.nom || '';
                            document.getElementById('superficie').value = emp.superficie || '';
                            document.getElementById('tarif_journalier').value = emp.tarif_journalier || '';
                            document.getElementById('etat').value = emp.etat || 'disponible';
                            document.getElementById('description').value = emp.description || '';
                            
                            // Changer le texte du bouton
                            submitBtn.textContent = 'Modifier l\'emplacement';
                            
                            // Faire défiler jusqu'au formulaire
                            emplacementForm.scrollIntoView({ behavior: 'smooth' });
                        } else {
                            showMessage('error', 'Erreur lors du chargement des données de l\'emplacement');
                        }
                    })
                    .catch(error => {
                        showMessage('error', 'Erreur technique: ' + error.message);
                        console.error('Erreur:', error);
                    });
            };
            
            window.deleteEmplacement = function(id) {
                if (confirm('Êtes-vous sûr de vouloir supprimer cet emplacement ?')) {
                    fetch(`api/emplacements.php?id=${id}`, {
                        method: 'DELETE'
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            showMessage('success', result.message);
                            loadEmplacements();
                        } else {
                            showMessage('error', result.error + ': ' + result.message);
                        }
                    })
                    .catch(error => {
                        showMessage('error', 'Erreur technique: ' + error.message);
                        console.error('Erreur:', error);
                    });
                }
            };
        });
    </script>
</body>
</html>
