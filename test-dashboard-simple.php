<?php
session_start();

// Simple test
$user_name = 'Test User';
$user_role = 'admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Dashboard - Marsa Maroc</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            color: white;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Tableau de Bord - Test</h1>
            <p>Bienvenue, <?php echo htmlspecialchars($user_name); ?></p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">24</div>
                <div>Emplacements Actifs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">18</div>
                <div>Réservations en Cours</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">€45,280</div>
                <div>Revenus du Mois</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">78%</div>
                <div>Taux d'Occupation</div>
            </div>
        </div>
    </div>
</body>
</html>
