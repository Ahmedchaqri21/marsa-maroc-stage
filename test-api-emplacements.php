<script>
// Function to test all API endpoints for emplacements
async function testEmplacementsAPI() {
    const results = document.getElementById('results');
    results.innerHTML = '<h3>Starting API Tests...</h3>';
    
    try {
        // Step 1: Get all emplacements
        results.innerHTML += '<h4>Test 1: GET all emplacements</h4>';
        const getAllResponse = await fetch('api/emplacements.php');
        const getAllData = await getAllResponse.json();
        results.innerHTML += `<p>Status: ${getAllResponse.status} ${getAllResponse.statusText}</p>`;
        results.innerHTML += `<p>Found: ${getAllData.data ? getAllData.data.length : 0} emplacements</p>`;
        results.innerHTML += '<pre>' + JSON.stringify(getAllData, null, 2).substring(0, 300) + '...</pre>';
        
        // Step 2: Create a new emplacement
        results.innerHTML += '<h4>Test 2: POST new emplacement</h4>';
        const newEmplacement = {
            code: 'TEST' + Math.floor(Math.random() * 10000),
            nom: 'Test Emplacement',
            superficie: 200,
            tarif_journalier: 500,
            etat: 'disponible',
            description: 'Created for testing'
        };
        
        results.innerHTML += '<p>Sending data: ' + JSON.stringify(newEmplacement) + '</p>';
        
        const createResponse = await fetch('api/emplacements.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(newEmplacement)
        });
        
        const createData = await createResponse.json();
        results.innerHTML += `<p>Status: ${createResponse.status} ${createResponse.statusText}</p>`;
        results.innerHTML += '<pre>' + JSON.stringify(createData, null, 2) + '</pre>';
        
        if (!createData.success || !createData.id) {
            throw new Error('Failed to create test emplacement');
        }
        
        const newId = createData.id;
        results.innerHTML += `<p>Successfully created emplacement with ID: ${newId}</p>`;
        
        // Step 3: Get the new emplacement
        results.innerHTML += `<h4>Test 3: GET emplacement with ID ${newId}</h4>`;
        const getResponse = await fetch(`api/emplacements.php?id=${newId}`);
        const getData = await getResponse.json();
        results.innerHTML += `<p>Status: ${getResponse.status} ${getResponse.statusText}</p>`;
        results.innerHTML += '<pre>' + JSON.stringify(getData, null, 2) + '</pre>';
        
        // Step 4: Update the emplacement
        results.innerHTML += `<h4>Test 4: PUT (update) emplacement with ID ${newId}</h4>`;
        const updateData = {
            id: newId,
            nom: 'Test Emplacement Updated',
            description: 'Updated for testing'
        };
        
        results.innerHTML += '<p>Sending data: ' + JSON.stringify(updateData) + '</p>';
        
        const updateResponse = await fetch(`api/emplacements.php?id=${newId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(updateData)
        });
        
        const updateResult = await updateResponse.json();
        results.innerHTML += `<p>Status: ${updateResponse.status} ${updateResponse.statusText}</p>`;
        results.innerHTML += '<pre>' + JSON.stringify(updateResult, null, 2) + '</pre>';
        
        // Step 5: Delete the emplacement
        results.innerHTML += `<h4>Test 5: DELETE emplacement with ID ${newId}</h4>`;
        const deleteResponse = await fetch(`api/emplacements.php?id=${newId}`, {
            method: 'DELETE'
        });
        
        const deleteResult = await deleteResponse.json();
        results.innerHTML += `<p>Status: ${deleteResponse.status} ${deleteResponse.statusText}</p>`;
        results.innerHTML += '<pre>' + JSON.stringify(deleteResult, null, 2) + '</pre>';
        
        // Final report
        results.innerHTML += '<h3>Test Summary</h3>';
        results.innerHTML += '<p class="success">✅ All tests completed!</p>';
        
    } catch (error) {
        results.innerHTML += `<p class="error">❌ Error: ${error.message}</p>`;
        console.error('API Test Error:', error);
    }
}

// Run the test on page load
document.addEventListener('DOMContentLoaded', testEmplacementsAPI);
</script>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test des API Emplacements</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.5; }
        h1, h2, h3, h4 { color: #333; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <h1>Test des API Emplacements</h1>
    <p>Cette page teste les fonctionnalités CRUD de l'API Emplacements.</p>
    
    <div id="results">
        <p>Chargement des tests...</p>
    </div>
    
    <button onclick="testEmplacementsAPI()">Relancer les tests</button>
</body>
</html>
