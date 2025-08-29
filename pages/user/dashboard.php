<?php
// Inclusion du fichier de vérification de session
require_once '../../config/session_check.php';

// Vérification du rôle - seuls les utilisateurs réguliers peuvent accéder à cette page
if (!hasRole('user')) {
    // Si ce n'est pas un utilisateur régulier, rediriger en fonction du rôle
    if (hasRole(['admin', 'manager'])) {
        // Rediriger vers le dashboard admin
        header('Location: ../admin/dashboard.php');
    } else {
        redirectToLogin("Vous n'avez pas les autorisations nécessaires pour accéder à cette page.");
    }
    exit;
}

// Récupération des informations utilisateur pour l'affichage
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Utilisateur - Marsa Maroc</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
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
            background: linear-gradient(180deg, rgba(26, 54, 93, 0.8) 0%, rgba(44, 82, 130, 0.7) 100%);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            padding: 2rem 0;
            box-shadow: 5px 0 20px rgba(0, 0, 0, 0.2);
        }

        .logo {
            text-align: center;
            padding: 1rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo h2 {
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .logo p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        .nav-section {
            padding: 0 1.25rem;
            margin-bottom: 2rem;
        }

        .nav-title {
            color: rgba(255, 255, 255, 0.5);
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
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(8px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .nav-item.active {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);
        }

        .nav-item i {
            font-size: 1.2rem;
            margin-right: 1rem;
            width: 20px;
            text-align: center;
        }

        .nav-item span {
            font-weight: 500;
            font-size: 0.95rem;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem;
            background: rgba(15, 23, 42, 0.4);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #ffffff;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .logout-btn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        }

        /* Dashboard Cards */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #ffffff;
        }

        .card-content {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
        }

        .btn {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            font-weight: 500;
            margin-top: 1rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }

        .section {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.3);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            background: rgba(59, 130, 246, 0.2);
            font-weight: 600;
            color: #ffffff;
        }

        td {
            color: rgba(255, 255, 255, 0.8);
        }

        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-en_attente {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }

        .status-validee {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        .status-refusee {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h2>Marsa Maroc</h2>
                <p>Espace Client</p>
            </div>

            <div class="nav-section">
                <div class="nav-title">Navigation</div>
                <div class="nav-item active" onclick="showSection('overview')">
                    <i class="fas fa-chart-line"></i>
                    <span>Vue d'ensemble</span>
                </div>
                <div class="nav-item" onclick="showSection('reservations')">
                    <i class="fas fa-calendar-check"></i>
                    <span>Mes Réservations</span>
                </div>
                <div class="nav-item" onclick="showSection('profile')">
                    <i class="fas fa-user-circle"></i>
                    <span>Mon Profil</span>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-title">Actions</div>
                <div class="nav-item" onclick="showSection('new-reservation')">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nouvelle Réservation</span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <div class="header">
                <h1>Tableau de Bord Client</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                    <div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($user_name); ?></div>
                        <div style="font-size: 0.9rem; color: rgba(255, 255, 255, 0.7);">Client</div>
                    </div>
                    <button class="logout-btn" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </button>
                </div>
            </div>

            <!-- Overview Section -->
            <div id="overview-section">
                <div class="dashboard-grid">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="card-title">Mes Réservations</div>
                        </div>
                        <div class="card-content">
                            <p>Consultez et gérez vos réservations d'emplacements portuaires.</p>
                            <button class="btn" onclick="showSection('reservations')">
                                Voir mes réservations
                            </button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="card-title">Nouvelle Réservation</div>
                        </div>
                        <div class="card-content">
                            <p>Réservez un nouvel emplacement portuaire pour vos besoins.</p>
                            <button class="btn" onclick="showSection('new-reservation')">
                                Créer une réservation
                            </button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="card-title">Mon Profil</div>
                        </div>
                        <div class="card-content">
                            <p>Mettez à jour vos informations personnelles et de société.</p>
                            <button class="btn" onclick="showSection('profile')">
                                Modifier le profil
                            </button>
                        </div>
                    </div>
                </div>
                <div class="dashboard-grid" style="margin-top:1rem;">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon"><i class="fas fa-list"></i></div>
                            <div class="card-title">Total Réservations</div>
                        </div>
                        <div class="card-content"><span id="count-total">-</span></div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="card-title">Validées</div>
                        </div>
                        <div class="card-content"><span id="count-validee">-</span></div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon"><i class="fas fa-hourglass-half"></i></div>
                            <div class="card-title">En attente</div>
                        </div>
                        <div class="card-content"><span id="count-en_attente">-</span></div>
                    </div>
                </div>
            </div>

            <!-- Reservations Section -->
            <div id="reservations-section" style="display: none;">
                <div class="section">
                    <h2 class="section-title">
                        <i class="fas fa-calendar-check"></i>
                        Mes Réservations
                    </h2>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>N° Réservation</th>
                                    <th>Emplacement</th>
                                    <th>Date Début</th>
                                    <th>Date Fin</th>
                                    <th>Statut</th>
                                    <th>Montant</th>
                                </tr>
                            </thead>
                            <tbody id="reservations-table-body">
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 2rem;">
                                        Chargement des réservations...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Profile Section -->
            <div id="profile-section" style="display: none;">
                <div class="section">
                    <h2 class="section-title">
                        <i class="fas fa-user-circle"></i>
                        Mon Profil
                    </h2>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-icon"><i class="fas fa-id-badge"></i></div>
                                <div class="card-title">Informations</div>
                            </div>
                            <div class="card-content">
                                <p><strong>Nom complet:</strong> <?php echo htmlspecialchars($user_name); ?></p>
                                <p><strong>Nom d'utilisateur:</strong> <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></p>
                                <p><strong>Rôle:</strong> Utilisateur</p>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="card-icon"><i class="fas fa-chart-pie"></i></div>
                                <div class="card-title">Statistiques</div>
                            </div>
                            <div class="card-content">
                                <p><strong>Total réservations:</strong> <span id="profile-total">-</span></p>
                                <p><strong>Validées:</strong> <span id="profile-validees">-</span></p>
                                <p><strong>En attente:</strong> <span id="profile-en-attente">-</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Reservation Section -->
            <div id="new-reservation-section" style="display: none;">
                <div class="section">
                    <h2 class="section-title">
                        <i class="fas fa-plus-circle"></i>
                        Nouvelle Réservation
                    </h2>
                    <div id="new-res-msg" style="margin-bottom:1rem;color:#ffd966;"></div>
                    <form id="new-reservation-form" onsubmit="return submitReservationForm(event)">
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem;">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-icon"><i class="fas fa-warehouse"></i></div>
                                    <div class="card-title">Emplacement</div>
                                </div>
                                <div class="card-content">
                                    <label for="emplacementSelect">Choisir un emplacement</label>
                                    <select id="emplacementSelect" required style="width:100%;padding:0.6rem;border-radius:8px;margin-top:0.5rem;">
                                        <option value="">-- Sélectionner --</option>
                                    </select>
                                    <p style="margin-top:0.5rem;font-size:0.9rem;color:rgba(255,255,255,0.8);" id="emp-info"></p>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-icon"><i class="fas fa-clock"></i></div>
                                    <div class="card-title">Période</div>
                                </div>
                                <div class="card-content" style="display:grid;gap:0.75rem;">
                                    <div>
                                        <label for="startInput">Date début</label>
                                        <input type="datetime-local" id="startInput" required style="width:100%;padding:0.6rem;border-radius:8px;margin-top:0.3rem;" />
                                    </div>
                                    <div>
                                        <label for="endInput">Date fin</label>
                                        <input type="datetime-local" id="endInput" required style="width:100%;padding:0.6rem;border-radius:8px;margin-top:0.3rem;" />
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-icon"><i class="fas fa-hand-holding-usd"></i></div>
                                    <div class="card-title">Paiement</div>
                                </div>
                                <div class="card-content" style="display:grid;gap:0.75rem;">
                                    <div>
                                        <label for="amountInput">Montant total (MAD)</label>
                                        <input type="number" id="amountInput" min="0" step="0.01" required style="width:100%;padding:0.6rem;border-radius:8px;margin-top:0.3rem;" />
                                        <div style="font-size:0.85rem;color:rgba(255,255,255,0.7);margin-top:0.3rem;">Calculé automatiquement selon le tarif journalier et la durée. Modifiable si nécessaire.</div>
                                    </div>
                                    <div>
                                        <label for="commentInput">Commentaire (optionnel)</label>
                                        <textarea id="commentInput" rows="3" style="width:100%;padding:0.6rem;border-radius:8px;margin-top:0.3rem;"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top:1rem;display:flex;gap:0.75rem;">
                            <button type="submit" class="btn"><i class="fas fa-check"></i> Envoyer la demande</button>
                            <button type="button" class="btn" onclick="showSection('reservations')"><i class="fas fa-list"></i> Voir mes réservations</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        const CURRENT_USER_ID = <?php echo json_encode($user_id); ?>;
        const CURRENT_USER_NAME = <?php echo json_encode($user_name); ?>;
        let cachedReservations = null;
        // Navigation entre sections
        function showSection(sectionName) {
            // Cacher toutes les sections
            const sections = ['overview', 'reservations', 'profile', 'new-reservation'];
            sections.forEach(section => {
                const element = document.getElementById(section + '-section');
                if (element) element.style.display = 'none';
            });

            // Afficher la section demandée
            const targetSection = document.getElementById(sectionName + '-section');
            if (targetSection) targetSection.style.display = 'block';

            // Mettre à jour la navigation
            document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
            const activeItem = document.querySelector(`.nav-item[onclick*="${sectionName}"]`);
            if (activeItem) activeItem.classList.add('active');

            // Persister l'onglet dans l'URL
            try { window.location.hash = sectionName; } catch (e) {}

            // Charger les données pour la section sélectionnée
            if (sectionName === 'reservations') {
                loadUserReservations();
            } else if (sectionName === 'profile') {
                loadProfileStats();
            } else if (sectionName === 'new-reservation') {
                loadEmplacements();
            } else if (sectionName === 'overview') {
                loadOverviewStats();
            }
        }

        // Fonction de déconnexion
        async function logout() {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                try {
                    const response = await fetch('../../api/logout.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        window.location.href = '../auth/login.php';
                    } else {
                        alert('Erreur lors de la déconnexion');
                    }
                } catch (error) {
                    console.error('Erreur de déconnexion:', error);
                    // Rediriger quand même en cas d'erreur
                    window.location.href = '../auth/login.php';
                }
            }
        }

        // Charger les réservations de l'utilisateur
        async function loadUserReservations() {
            try {
                const response = await fetch('../../api/reservations-real.php');
                const data = await response.json();

                const tbody = document.getElementById('reservations-table-body');
                tbody.innerHTML = '';

                if (data.success && Array.isArray(data.data)) {
                    // Mettre en cache toutes les réservations puis filtrer pour l'utilisateur courant si possible
                    cachedReservations = data.data;
                    let list = cachedReservations;
                    let myList = list.filter(r => r.utilisateur_nom && r.utilisateur_nom.toLowerCase() === String(CURRENT_USER_NAME || '').toLowerCase());
                    const rows = (myList.length > 0 ? myList : list);

                    if (myList.length === 0) {
                        console.warn('Aucune réservation spécifique à l’utilisateur trouvée, affichage de toutes les réservations.');
                    }

                    if (rows.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">Aucune réservation trouvée</td></tr>';
                        return;
                    }

                    rows.forEach(reservation => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${reservation.numero_reservation || '-'}</td>
                            <td>${reservation.emplacement_nom || '-'}</td>
                            <td>${reservation.date_debut || '-'}</td>
                            <td>${reservation.date_fin || '-'}</td>
                            <td><span class="status-badge status-${reservation.statut}">${reservation.statut}</span></td>
                            <td>${formatMoney(reservation.montant_total)}</td>
                        `;
                        tbody.appendChild(row);
                    });

                } else {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">Aucune réservation trouvée</td></tr>';
                }
            } catch (error) {
                console.error('Erreur lors du chargement des réservations:', error);
                document.getElementById('reservations-table-body').innerHTML =
                    '<tr><td colspan="6" style="text-align: center; padding: 2rem;">Erreur lors du chargement des réservations</td></tr>';
            }
        }

        // Fonctions utilitaires
        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }

        function formatMoney(amount) {
            if (amount === undefined || amount === null) return '-';
            if (typeof amount === 'string') {
                // Déjà formaté côté API (ex: "1,234.00") => ajouter l'unité
                return amount + ' MAD';
            }
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'MAD'
            }).format(Number(amount));
        }

        // Utilitaires pour la nouvelle réservation
        function parseMoneyStr(str) {
            if (!str) return 0;
            // Supprimer tout sauf chiffres, virgules et points
            let s = String(str).replace(/[^0-9.,]/g, '');
            // Si il y a plusieurs virgules/points, enlever les séparateurs de milliers
            // Stratégie: enlever les virgules de milliers, garder le dernier point comme décimal
            s = s.replace(/,/g, '');
            // Remplacer une éventuelle virgule décimale par un point
            s = s.replace(/,(?=\d{2}$)/, '.');
            const val = parseFloat(s);
            return isNaN(val) ? 0 : val;
        }

        function formatDateTimeForApi(dt) {
            // Convertir un input datetime-local en "YYYY-MM-DD HH:mm:ss"
            if (!dt) return '';
            const d = new Date(dt);
            const pad = n => String(n).padStart(2, '0');
            const y = d.getFullYear();
            const m = pad(d.getMonth() + 1);
            const da = pad(d.getDate());
            const h = pad(d.getHours());
            const mi = pad(d.getMinutes());
            const s = pad(d.getSeconds());
            return `${y}-${m}-${da} ${h}:${mi}:${s}`;
        }

        async function loadEmplacements() {
            const select = document.getElementById('emplacementSelect');
            const info = document.getElementById('emp-info');
            if (!select) return;
            try {
                // Ne recharger que si la liste est vide (hors placeholder)
                if (select.options.length > 1) return;
                const resp = await fetch('../../api/emplacements-real.php');
                const data = await resp.json();
                if (data.success && Array.isArray(data.data)) {
                    data.data.forEach(emp => {
                        const opt = document.createElement('option');
                        opt.value = emp.id;
                        opt.textContent = `${emp.nom} (${emp.code})`;
                        opt.dataset.dailyRate = parseMoneyStr(emp.tarif_journalier);
                        opt.dataset.zone = emp.zone || '';
                        opt.dataset.status = emp.etat_libelle || emp.etat || '';
                        select.appendChild(opt);
                    });
                    select.addEventListener('change', computeAmount);
                    ['startInput','endInput'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.addEventListener('change', computeAmount);
                    });
                } else {
                    info.textContent = 'Impossible de charger les emplacements.';
                }
            } catch (e) {
                console.error('Erreur chargement emplacements', e);
                if (info) info.textContent = 'Erreur lors du chargement des emplacements.';
            }
        }

        function computeAmount() {
            const select = document.getElementById('emplacementSelect');
            const info = document.getElementById('emp-info');
            const start = document.getElementById('startInput').value;
            const end = document.getElementById('endInput').value;
            const amountInput = document.getElementById('amountInput');
            if (!select || !amountInput) return;

            const daily = parseFloat(select.options[select.selectedIndex]?.dataset?.dailyRate || '0');
            let days = 0;
            if (start && end) {
                const s = new Date(start).getTime();
                const e = new Date(end).getTime();
                if (e > s) {
                    const diff = e - s;
                    days = Math.ceil(diff / (24 * 60 * 60 * 1000));
                    if (days <= 0) days = 1;
                }
            }
            const total = daily > 0 && days > 0 ? (daily * days) : 0;
            amountInput.value = total ? total.toFixed(2) : '';

            if (info && select.selectedIndex > 0) {
                const opt = select.options[select.selectedIndex];
                info.textContent = `Zone: ${opt.dataset.zone || '-'} | Statut: ${opt.dataset.status || '-'} | Tarif journalier: ${daily ? daily.toFixed(2) + ' MAD' : '-'}`;
            }
        }

        async function submitReservationForm(ev) {
            ev.preventDefault();
            const msg = document.getElementById('new-res-msg');
            const select = document.getElementById('emplacementSelect');
            const start = document.getElementById('startInput').value;
            const end = document.getElementById('endInput').value;
            const amount = parseFloat(document.getElementById('amountInput').value || '0');
            const comment = document.getElementById('commentInput').value || '';

            msg.textContent = '';

            if (!select.value) { msg.textContent = 'Veuillez choisir un emplacement.'; return false; }
            if (!start || !end) { msg.textContent = 'Veuillez saisir la période.'; return false; }
            if (new Date(end) <= new Date(start)) { msg.textContent = 'La date de fin doit être après la date de début.'; return false; }
            if (!amount || amount <= 0) { msg.textContent = 'Le montant total calculé est invalide.'; return false; }

            const payload = {
                user_id: CURRENT_USER_ID,
                emplacement_id: parseInt(select.value, 10),
                date_debut: formatDateTimeForApi(start),
                date_fin: formatDateTimeForApi(end),
                montant_total: amount,
                commentaire: comment
            };

            try {
                const resp = await fetch('../../api/reservations-real.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const res = await resp.json();
                if (res.success) {
                    msg.style.color = '#8ef58e';
                    msg.textContent = 'Réservation envoyée avec succès. Redirection...';
                    // Recharger les réservations
                    setTimeout(() => {
                        showSection('reservations');
                        loadUserReservations();
                    }, 800);
                    // Réinitialiser le formulaire
                    document.getElementById('new-reservation-form').reset();
                } else {
                    msg.style.color = '#ffb3b3';
                    msg.textContent = res.message || 'Erreur lors de la création de la réservation.';
                }
            } catch (e) {
                console.error('Erreur création réservation', e);
                msg.style.color = '#ffb3b3';
                msg.textContent = 'Erreur réseau lors de l\'envoi de la réservation.';
            }
            return false;
        }

        async function ensureReservationsCached() {
            if (Array.isArray(cachedReservations)) return cachedReservations;
            try {
                const resp = await fetch('../../api/reservations-real.php');
                const data = await resp.json();
                if (data.success && Array.isArray(data.data)) {
                    cachedReservations = data.data;
                    return cachedReservations;
                }
            } catch (e) { console.warn('Chargement réservations échoué', e); }
            cachedReservations = [];
            return cachedReservations;
        }

        async function loadOverviewStats() {
            const list = await ensureReservationsCached();
            const my = list.filter(r => r.utilisateur_nom && r.utilisateur_nom.toLowerCase() === String(CURRENT_USER_NAME || '').toLowerCase());
            const rows = my.length ? my : list;
            const total = rows.length;
            const countBy = (st) => rows.filter(r => String(r.statut).toLowerCase() === st).length;
            document.getElementById('count-total').textContent = total;
            document.getElementById('count-validee').textContent = countBy('validee');
            document.getElementById('count-en_attente').textContent = countBy('en_attente');
        }

        async function loadProfileStats() {
            const list = await ensureReservationsCached();
            const my = list.filter(r => r.utilisateur_nom && r.utilisateur_nom.toLowerCase() === String(CURRENT_USER_NAME || '').toLowerCase());
            const rows = my.length ? my : list;
            const total = rows.length;
            const val = rows.filter(r => String(r.statut).toLowerCase() === 'validee').length;
            const enAtt = rows.filter(r => String(r.statut).toLowerCase() === 'en_attente').length;
            const totalEl = document.getElementById('profile-total');
            if (totalEl) totalEl.textContent = total;
            const valEl = document.getElementById('profile-validees');
            if (valEl) valEl.textContent = val;
            const attEl = document.getElementById('profile-en-attente');
            if (attEl) attEl.textContent = enAtt;
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            const hash = (window.location.hash || '').replace('#','') || 'overview';
            showSection(hash);
            if (hash === 'new-reservation') {
                loadEmplacements();
            } else if (hash === 'profile') {
                loadProfileStats();
            } else if (hash === 'reservations') {
                loadUserReservations();
            } else {
                loadOverviewStats();
            }
        });
    </script>
</body>
</html>
