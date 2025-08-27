<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test API Marsa Maroc</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        button { margin: 10px; padding: 10px 20px; }
        .result { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .error { background: #ffebee; color: #c62828; }
        .success { background: #e8f5e8; color: #2e7d32; }
    </style>
</head>
<body>
    <h1>Test API Marsa Maroc</h1>
    
    <h2>Tests API</h2>
    <button onclick="testUsers()">Test API Users</button>
    <button onclick="testEmplacements()">Test API Emplacements</button>
    <button onclick="testReservations()">Test API Réservations</button>
    
    <div id="results"></div>

    <script>
        async function testAPI(endpoint, name) {
            const resultsDiv = document.getElementById('results');
            
            try {
                console.log(`Testing ${endpoint}...`);
                const response = await fetch(`api/${endpoint}`);
                const text = await response.text();
                
                console.log(`Response for ${endpoint}:`, text);
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error(`Response is not JSON: ${text}`);
                }
                
                const div = document.createElement('div');
                div.className = 'result success';
                div.innerHTML = `
                    <h3>✅ ${name} - SUCCESS</h3>
                    <p><strong>Status:</strong> ${response.status}</p>
                    <p><strong>Data count:</strong> ${Array.isArray(data) ? data.length : 'N/A'}</p>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
                resultsDiv.appendChild(div);
                
            } catch (error) {
                console.error(`Error testing ${endpoint}:`, error);
                
                const div = document.createElement('div');
                div.className = 'result error';
                div.innerHTML = `
                    <h3>❌ ${name} - ERROR</h3>
                    <p><strong>Error:</strong> ${error.message}</p>
                `;
                resultsDiv.appendChild(div);
            }
        }

        function testUsers() {
            testAPI('users.php', 'Users API');
        }

        function testEmplacements() {
            testAPI('emplacements.php', 'Emplacements API');
        }

        function testReservations() {
            testAPI('reservations.php', 'Reservations API');
        }

        // Test automatique au chargement
        window.onload = function() {
            setTimeout(() => {
                testUsers();
                setTimeout(() => testEmplacements(), 1000);
                setTimeout(() => testReservations(), 2000);
            }, 1000);
        };
    </script>
</body>
</html>
