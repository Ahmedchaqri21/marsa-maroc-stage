<?php
session_start();

// Check if user is logged in and has admin or manager role
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if (!in_array($_SESSION['role'], ['admin', 'manager'])) {
    header('Location: login.php');
    exit;
}

// Get user info for display
$user_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Administrateur - Marsa Maroc</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/marsa-maroc-style.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0c0c0c 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            color: #ffffff;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            padding: 2rem 0;
        }

        .logo {
            text-align: center;
            padding: 1rem;
            margin-bottom: 2rem;
        }

        .logo h2 {
            color: #667eea;
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .logo p {
            color: #b8c5d6;
            font-size: 0.9rem;
        }

        .nav-section {
            padding: 0 1rem;
            margin-bottom: 2rem;
        }

        .nav-title {
            color: #8892b0;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1rem;
            padding: 0 1rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            border-radius: 12px;
            color: #b8c5d6;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .nav-item:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            transform: translateX(5px);
        }

        .nav-item.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .nav-item i {
            width: 20px;
            margin-right: 12px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header p {
            color: #b8c5d6;
            margin-top: 0.5rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-details {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            color: white;
        }

        .user-role {
            font-size: 0.9rem;
            color: #b8c5d6;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .logout-btn {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(250, 112, 154, 0.3);
        }

        /* Dashboard Content */
        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 2rem;
            color: #b794f6;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 2rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-title {
            font-size: 0.9rem;
            color: #b8c5d6;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.orange {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .stat-icon.pink {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-icon.blue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-value {
            font-size: 3rem;
            font-weight: 800;
            color: white;
            margin-bottom: 0.5rem;
        }

        .stat-change {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #43e97b;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        .chart-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 2rem;
        }

        .chart-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: white;
        }

        .chart-placeholder {
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #b8c5d6;
            font-size: 1.1rem;
        }

        .activity-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 2rem;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: white;
            margin-bottom: 0.25rem;
        }

        .activity-time {
            font-size: 0.8rem;
            color: #8892b0;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                padding: 1rem 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <h2><i class="fas fa-anchor"></i> Marsa Maroc</h2>
                <p>ADMINISTRATION</p>
            </div>
            
            <nav>
                <div class="nav-section">
                    <div class="nav-title">GESTION</div>
                    <a href="#" class="nav-item active" onclick="showSection('overview')">
                        <i class="fas fa-tachometer-alt"></i>
                        Vue d'ensemble
                    </a>
                    <a href="#" class="nav-item" onclick="showSection('emplacements')">
                        <i class="fas fa-map-marker-alt"></i>
                        Emplacements
                    </a>
                    <a href="#" class="nav-item" onclick="showSection('reservations')">
                        <i class="fas fa-calendar-check"></i>
                        Réservations
                    </a>
                    <a href="#" class="nav-item" onclick="showSection('users')">
                        <i class="fas fa-users"></i>
                        Utilisateurs
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-title">RAPPORTS</div>
                    <a href="#" class="nav-item" onclick="showSection('statistics')">
                        <i class="fas fa-chart-bar"></i>
                        Statistiques
                    </a>
                    <a href="#" class="nav-item" onclick="showSection('reports')">
                        <i class="fas fa-file-alt"></i>
                        Rapports
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <div>
                    <h1>Tableau de Bord</h1>
                    <p>Gestion Portuaire Marsa Maroc</p>
                </div>
                <div class="user-info">
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                        <div class="user-role"><?php echo ucfirst($user_role); ?></div>
                    </div>
                    <div class="user-avatar"><?php echo strtoupper(substr($user_name, 0, 2)); ?></div>
                    <a href="api/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Déconnexion
                    </a>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div id="overview-section">
                <h2 class="section-title">Vue d'ensemble</h2>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Emplacements Actifs</div>
                            <div class="stat-icon orange">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                        </div>
                        <div class="stat-value" id="total-emplacements">24</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up"></i>
                            +12% ce mois
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Réservations en Cours</div>
                            <div class="stat-icon green">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                        <div class="stat-value" id="active-reservations">18</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up"></i>
                            +8% ce mois
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Revenus du Mois</div>
                            <div class="stat-icon pink">
                                <i class="fas fa-euro-sign"></i>
                            </div>
                        </div>
                        <div class="stat-value" id="monthly-revenue">€45,280</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up"></i>
                            +15% ce mois
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Taux d'Occupation</div>
                            <div class="stat-icon blue">
                                <i class="fas fa-percentage"></i>
                            </div>
                        </div>
                        <div class="stat-value" id="occupation-rate">78%</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up"></i>
                            +5% ce mois
                        </div>
                    </div>
                </div>

                <div class="content-grid">
                    <div class="chart-container">
                        <h3 class="chart-title">Évolution des Réservations</h3>
                        <div class="chart-placeholder">
                            <i class="fas fa-chart-line" style="font-size: 2rem; margin-right: 1rem;"></i>
                            Graphique des réservations (à intégrer)
                        </div>
                    </div>

                    <div class="activity-container">
                        <h3 class="chart-title">Activités Récentes</h3>
                        <div class="activity-item">
                            <div class="activity-icon blue">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Nouvelle réservation</div>
                                <div class="activity-time">Il y a 2 heures</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon green">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Réservation validée</div>
                                <div class="activity-time">Il y a 4 heures</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon orange">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Nouvel utilisateur</div>
                                <div class="activity-time">Il y a 6 heures</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sections dynamiques (cachées par défaut) -->
            <div id="emplacements-section" style="display: none;">
                <h2 class="section-title">Gestion des Emplacements</h2>
                <div class="chart-placeholder">Section Emplacements (à développer)</div>
            </div>

            <div id="reservations-section" style="display: none;">
                <h2 class="section-title">Gestion des Réservations</h2>
                <div class="chart-placeholder">Section Réservations (à développer)</div>
            </div>

            <div id="users-section" style="display: none;">
                <h2 class="section-title">Gestion des Utilisateurs</h2>
                <div class="chart-placeholder">Section Utilisateurs (à développer)</div>
            </div>

            <div id="statistics-section" style="display: none;">
                <h2 class="section-title">Statistiques</h2>
                <div class="chart-placeholder">Section Statistiques (à développer)</div>
            </div>

            <div id="reports-section" style="display: none;">
                <h2 class="section-title">Rapports</h2>
                <div class="chart-placeholder">Section Rapports (à développer)</div>
            </div>
        </main>
    </div>

    <script>
        // Navigation entre sections
        function showSection(sectionName) {
            // Cacher toutes les sections
            const sections = ['overview', 'emplacements', 'reservations', 'users', 'statistics', 'reports'];
            sections.forEach(section => {
                const element = document.getElementById(section + '-section');
                if (element) {
                    element.style.display = 'none';
                }
            });

            // Afficher la section demandée
            const targetSection = document.getElementById(sectionName + '-section');
            if (targetSection) {
                targetSection.style.display = 'block';
            }

            // Mettre à jour la navigation
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        // Charger les statistiques au démarrage
        async function loadDashboardStats() {
            try {
                const response = await fetch('api/statistics.php');
                const data = await response.json();
                
                if (data.success) {
                    // Mettre à jour les statistiques
                    if (data.stats) {
                        document.getElementById('total-emplacements').textContent = data.stats.total_emplacements || '0';
                        document.getElementById('active-reservations').textContent = data.stats.active_reservations || '0';
                        document.getElementById('monthly-revenue').textContent = '€' + (data.stats.monthly_revenue || '0');
                        document.getElementById('occupation-rate').textContent = (data.stats.occupation_rate || '0') + '%';
                    }
                }
            } catch (error) {
                console.log('Erreur lors du chargement des statistiques:', error);
            }
        }

        // Charger les stats au démarrage
        document.addEventListener('DOMContentLoaded', loadDashboardStats);
    </script>
</body>
</html>
