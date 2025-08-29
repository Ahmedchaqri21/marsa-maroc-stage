<?php
// Inclusion du fichier de vérification de session
require_once '../../config/session_check.php';

// Vérification du rôle - seuls les administrateurs et managers peuvent accéder à cette page
if (!hasRole(['admin', 'manager'])) {
    if (hasRole('user')) {
        // Utiliser un chemin absolu pour la redirection
        $baseDir = dirname($_SERVER['SCRIPT_NAME']);
        if ($baseDir === '/') {
            $redirectPath = '/user-dashboard.php';
        } else {
            if (substr($baseDir, -1) !== '/') {
                $baseDir .= '/';
            }
            $redirectPath = $baseDir . 'user-dashboard.php';
        }
        header('Location: ' . $redirectPath);
    } else {
        redirectToLogin("Vous n'avez pas les autorisations nécessaires pour accéder à cette page.");
    }
    exit;
}

// Récupération des informations utilisateur pour l'affichage
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            padding: 1rem 1.25rem;
            margin-bottom: 0.5rem;
            border-radius: 10px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            font-weight: 500;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .nav-item:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: linear-gradient(135deg, #f05252, #ce0e2d);
            z-index: -1;
            transition: width 0.3s ease;
        }

        .nav-item:hover {
            color: #ffffff;
            transform: translateX(5px);
        }
        
        .nav-item:hover:before {
            width: 100%;
        }

        .nav-item.active {
            background: linear-gradient(135deg, #f05252, #ce0e2d);
            color: white;
            box-shadow: 0 4px 12px rgba(206, 14, 45, 0.3);
        }

        .nav-item i {
            width: 20px;
            margin-right: 12px;
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }
        
        .nav-item:hover i {
            transform: scale(1.2);
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
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }
        
        .header:after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 15%;
            height: 2px;
            background: linear-gradient(to right, #ce0e2d, transparent);
        }

        .header h1 {
            font-size: 2.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff, #b8c5d6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .header p {
            color: rgba(255, 255, 255, 0.7);
            margin-top: 0.5rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #ffffff;
        }

        .user-details {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            color: #ffffff;
        }

        .user-role {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .user-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3182ce, #1a365d); /* Bleu Marsa Maroc */
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
            color: white;
            border: 3px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .logout-btn {
            background: linear-gradient(135deg, #f05252, #ce0e2d); /* Rouge Marsa Maroc */
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 10px rgba(206, 14, 45, 0.25);
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, #e53e3e, #b50c26); /* Rouge Marsa Maroc plus foncé */
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(206, 14, 45, 0.3);
        }

        /* Sections and Cards */
        .section-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: #ffffff;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .card {
            background: linear-gradient(135deg, rgba(26, 32, 44, 0.8), rgba(45, 55, 72, 0.7));
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        .card-value {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #ffffff, #b8c5d6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-change {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .positive {
            color: #48bb78; /* Vert */
        }

        .negative {
            color: #f05252; /* Rouge */
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
            margin-bottom: 2rem;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            background: linear-gradient(135deg, rgba(26, 32, 44, 0.8), rgba(45, 55, 72, 0.7));
            backdrop-filter: blur(10px);
        }

        th, td {
            padding: 1.25rem 1rem;
            text-align: left;
            color: #ffffff;
        }

        th {
            background-color: rgba(49, 130, 206, 0.1); /* Bleu Marsa Maroc très clair */
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.95rem;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: rgba(49, 130, 206, 0.05); /* Bleu Marsa Maroc très clair */
        }

        .action-btn {
            padding: 0.6rem 0.75rem;
            margin: 0 0.25rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .action-btn.edit-btn {
            background: linear-gradient(135deg, #3182ce, #2c5282); /* Bleu Marsa Maroc moyen */
            color: white;
        }

        .action-btn.delete-btn {
            background: linear-gradient(135deg, #f05252, #ce0e2d); /* Rouge Marsa Maroc */
            color: white;
        }

        .action-btn.view-btn {
            background: linear-gradient(135deg, #63b3ed, #4299e1); /* Bleu Marsa Maroc clair */
            color: white;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .status-badge {
            padding: 0.3rem 0.85rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(5px);
            display: inline-block;
        }

        .status-badge.status-disponible,
        .status-badge.status-active {
            background: linear-gradient(135deg, #48bb78, #38a169); /* Vert */
            color: white;
        }

        .status-badge.status-occupe,
        .status-badge.status-en_attente {
            background: linear-gradient(135deg, #f05252, #ce0e2d); /* Rouge Marsa Maroc */
            color: white;
        }

        .status-badge.status-maintenance {
            background: linear-gradient(135deg, #ed8936, #dd6b20); /* Orange */
            color: white;
        }

        .status-badge.status-admin {
            background: linear-gradient(135deg, #2c5282, #1a365d); /* Bleu Marsa Maroc foncé */
            color: white;
        }

        .status-badge.status-user {
            background: #4299e1; /* Bleu Marsa Maroc clair */
            color: white;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .add-btn {
            background: linear-gradient(135deg, #f05252, #ce0e2d); /* Rouge Marsa Maroc */
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            box-shadow: 0 4px 10px rgba(206, 14, 45, 0.25);
        }

        .add-btn:hover {
            background: linear-gradient(135deg, #e53e3e, #b50c26); /* Rouge Marsa Maroc plus foncé */
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(206, 14, 45, 0.3);
        }

        /* Stats Overview */
        .stat-row {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(26, 32, 44, 0.8), rgba(45, 55, 72, 0.7));
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .stat-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.8);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .stat-icon.red {
            background: linear-gradient(135deg, #f05252, #ce0e2d);
        }

        .stat-icon.blue {
            background: linear-gradient(135deg, #3182ce, #1a365d);
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #48bb78, #38a169);
        }

        .stat-icon.purple {
            background: linear-gradient(135deg, #805ad5, #6b46c1);
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        .stat-change {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Charts */
        .charts-container {
            margin-top: 2rem;
        }
        
        .charts-row {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        
        .chart-card {
            flex: 1;
            min-width: 300px;
            background: linear-gradient(135deg, rgba(26, 32, 44, 0.8), rgba(45, 55, 72, 0.7));
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .chart-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }
        
        .chart-header {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .chart-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #f7fafc;
        }
        
        .chart-body {
            padding: 1.5rem;
            height: 300px;
            position: relative;
            background: rgba(0, 0, 0, 0.2);
        }
        
        .chart-placeholder {
            background: linear-gradient(135deg, rgba(26, 32, 44, 0.8), rgba(45, 55, 72, 0.7));
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 4rem 2rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Notifications */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(26, 32, 44, 0.9), rgba(45, 55, 72, 0.8));
            backdrop-filter: blur(10px);
            color: white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 1000;
            transition: transform 0.3s ease, opacity 0.3s ease;
            transform: translateY(-100px);
            opacity: 0;
        }

        .notification.show {
            transform: translateY(0);
            opacity: 1;
        }

        .notification-icon {
            font-size: 1.5rem;
        }

        .notification-message {
            flex: 1;
        }

        .notification-success {
            border-left: 4px solid #48bb78;
        }

        .notification-error {
            border-left: 4px solid #f05252;
        }

        .section-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .search-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input {
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border-radius: 8px;
            border: 1px solid rgba(49, 130, 206, 0.3);
            font-size: 0.875rem;
            width: 250px;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .search-input:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.2);
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            color: #718096;
            font-size: 0.875rem;
        }

        .export-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #3182ce, #2c5282);
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(49, 130, 206, 0.3);
        }

        .export-btn:hover {
            background: linear-gradient(135deg, #2c5282, #1a365d);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(49, 130, 206, 0.4);
        }

        .notification-info {
            border-left: 4px solid #3182ce;
        }

        /* Loading indicator */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #ffffff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Modal styles */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal-backdrop.active {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .modal.show {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-content {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(249, 250, 251, 0.95));
            backdrop-filter: blur(20px);
            border-radius: 20px;
            width: 90%;
            max-width: 650px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.25),
                0 0 0 1px rgba(255, 255, 255, 0.2);
            transform: translateY(30px) scale(0.95);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        
        .modal.show .modal-content {
            transform: translateY(0) scale(1);
        }

        .modal-backdrop.active .modal {
            transform: scale(1);
            opacity: 1;
        }

        .modal-header {
            background: linear-gradient(135deg, #1a365d, #2c5282);
            margin: -2rem -2rem 2rem -2rem;
            padding: 1.5rem 2rem;
            border-radius: 20px 20px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .modal-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.1), transparent);
            pointer-events: none;
        }

        .modal-title, .modal-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            z-index: 1;
            position: relative;
        }

        .close-modal {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 1;
            position: relative;
        }

        .close-modal:hover {
            background: rgba(248, 113, 113, 0.2);
            border-color: #f87171;
            transform: rotate(90deg);
        }
        
        .modal-body {
            padding: 0 2rem 2rem 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .form-group input:focus, 
        .form-group select:focus, 
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 
                0 0 0 3px rgba(59, 130, 246, 0.1),
                0 4px 6px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }
        
        .form-group.error input,
        .form-group.error select {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }
        
        .form-group.success input,
        .form-group.success select {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        
        .form-group .error-message {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }
        
        .form-group.error .error-message {
            opacity: 1;
            transform: translateY(0);
        }
        
        .form-group .success-message {
            color: #10b981;
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }
        
        .form-group.success .success-message {
            opacity: 1;
            transform: translateY(0);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .form-actions .add-btn,
        .form-actions .btn-submit {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            min-width: 120px;
        }
        
        .form-actions .add-btn:hover,
        .form-actions .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }
        
        .form-actions .add-btn:active,
        .form-actions .btn-submit:active {
            transform: translateY(0);
        }
        
        .form-actions .btn-cancel {
            background: #f3f4f6;
            color: #374151;
            border: 2px solid #e5e7eb;
            padding: 0.875rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 120px;
        }
        
        .form-actions .btn-cancel:hover {
            background: #e5e7eb;
            border-color: #d1d5db;
            transform: translateY(-1px);
        }
        
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #ffffff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 0.5rem;
        }
        
        .btn-loading .loading-spinner {
            display: inline-block;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .field-icon {
            position: absolute;
            right: 1rem;
            top: 2.5rem;
            color: #9ca3af;
            transition: color 0.3s ease;
        }
        
        .form-group.error .field-icon {
            color: #ef4444;
        }
        
        .form-group.success .field-icon {
            color: #10b981;
        }
        
        /* Responsive Design pour Mobile */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 1rem;
                max-height: 95vh;
            }
            
            .modal-header {
                margin: -1.5rem -1.5rem 1.5rem -1.5rem;
                padding: 1rem 1.5rem;
            }
            
            .modal-body {
                padding: 0 1.5rem 1.5rem 1.5rem;
            }
            
            .modal-title, .modal-header h2 {
                font-size: 1.25rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .form-actions {
                flex-direction: column-reverse;
                gap: 0.75rem;
            }
            
            .form-actions .add-btn,
            .form-actions .btn-submit,
            .form-actions .btn-cancel {
                width: 100%;
                justify-content: center;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .form-group input, 
            .form-group select {
                font-size: 16px; /* Évite le zoom sur iOS */
            }
        }
        
        @media (max-width: 480px) {
            .modal-content {
                width: 98%;
                margin: 0.5rem;
                border-radius: 15px;
            }
            
            .modal-header {
                border-radius: 15px 15px 0 0;
            }
            
            .close-modal {
                width: 35px;
                height: 35px;
                font-size: 1.25rem;
            }
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            font-size: 1rem;
            transition: border-color 0.3s ease, background-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #3182ce;
            background-color: rgba(255, 255, 255, 0.15);
        }

        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            font-size: 1rem;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23ffffff' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: calc(100% - 1rem) center;
            padding-right: 2.5rem;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-cancel {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.9);
        }

        .btn-primary {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            background: linear-gradient(135deg, #3182ce, #1a365d);
            color: white;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(49, 130, 206, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2b6cb0, #153e75);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(49, 130, 206, 0.35);
        }

        @media (max-width: 1024px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                max-height: 300px;
                overflow-y: auto;
            }
            
            .nav-section {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            
            .nav-item {
                flex: 1;
                min-width: 150px;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .user-info {
                width: 100%;
                justify-content: space-between;
            }
            
            .stat-row,
            .grid-container {
                grid-template-columns: 1fr;
            }
            
            .table-responsive {
                max-width: 100%;
                overflow-x: auto;
            }
        }
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            position: relative;
            background: linear-gradient(135deg, rgba(26, 32, 44, 0.95), rgba(45, 55, 72, 0.9));
            backdrop-filter: blur(20px);
            margin: 5% auto;
            width: 80%;
            max-width: 700px;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: slideIn 0.3s ease-in-out;
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Notification Styles */
        .notification-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .notification {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            border-radius: 10px;
            min-width: 300px;
            max-width: 400px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            animation: slideInNotification 0.3s ease-in, fadeOut 0.3s ease-out 4.7s forwards;
            background: linear-gradient(135deg, rgba(26, 32, 44, 0.95), rgba(45, 55, 72, 0.9));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .notification-hide {
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease-out;
        }
        
        @keyframes slideInNotification {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
        
        .notification-icon {
            font-size: 1.3rem;
            min-width: 24px;
        }
        
        .notification-message {
            flex-grow: 1;
            color: #ffffff;
            font-size: 0.95rem;
        }
        
        .notification-close {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.3s ease;
        }
        
        .notification-close:hover {
            color: #ffffff;
        }
        
        .notification-success .notification-icon {
            color: #48BB78; /* Vert */
        }
        
        .notification-error .notification-icon {
            color: #F56565; /* Rouge */
        }
        
        .notification-info .notification-icon {
            color: #4299E1; /* Bleu */
        }
        
        .notification-warning .notification-icon {
            color: #ECC94B; /* Jaune */
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .modal-header h2 {
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }
        
        .close-modal {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.8rem;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .close-modal:hover {
            color: #ffffff;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-row {
            display: flex;
            gap: 1rem;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(0, 0, 0, 0.2);
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 2px rgba(49, 130, 206, 0.3);
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn-cancel {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <h2>Marsa Maroc</h2>
                <p>Système de Gestion Portuaire</p>
            </div>
            
            <div class="nav-section">
                <div class="nav-title">Menu principal</div>
                <div class="nav-item active" onclick="showSection('overview')">
                    <i class="fas fa-tachometer-alt"></i> Vue d'ensemble
                </div>
                <div class="nav-item" onclick="showSection('emplacements')">
                    <i class="fas fa-map-marker-alt"></i> Emplacements
                </div>
                <div class="nav-item" onclick="showSection('reservations')">
                    <i class="fas fa-calendar-check"></i> Réservations
                </div>
                <div class="nav-item" onclick="showSection('users')">
                    <i class="fas fa-users"></i> Utilisateurs
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <div class="header">
                <div>
                    <h1>Tableau de Bord</h1>
                    <p>Gérez les emplacements, réservations et utilisateurs du port</p>
                </div>
                <div class="user-info">
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                        <div class="user-role"><?php echo ucfirst(htmlspecialchars($user_role)); ?></div>
                    </div>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                    <a href="../../api/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>

            <!-- Overview Section -->
            <div id="overview-section">
                <h2 class="section-title">Vue d'ensemble</h2>
                <div class="stat-row">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Emplacements Disponibles</div>
                            <div class="stat-icon green">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-value" id="stat-emplacements-disponibles">--</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up"></i>
                            <span>Mise à jour en temps réel</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Réservations en Attente</div>
                            <div class="stat-icon red">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="stat-value" id="stat-reservations-attente">--</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up"></i>
                            <span>Mise à jour en temps réel</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Revenus Mensuels</div>
                            <div class="stat-icon blue">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="stat-value">€542,800</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up"></i>
                            <span>+18% vs 2024</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Clients Actifs</div>
                            <div class="stat-icon purple">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="stat-value" id="stat-clients-actifs">--</div>
                        <div class="stat-change">
                            <i class="fas fa-arrow-up"></i>
                            <span>Mise à jour en temps réel</span>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Container -->
                <div class="charts-container">
                    <div class="charts-row">
                        <div class="chart-card">
                            <div class="chart-header">
                                <h3>Distribution des Types d'Emplacement</h3>
                            </div>
                            <div class="chart-body">
                                <canvas id="locationTypesChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="chart-card">
                            <div class="chart-header">
                                <h3>Occupation par Zone</h3>
                            </div>
                            <div class="chart-body">
                                <canvas id="zoneOccupancyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emplacements Section -->
            <div id="emplacements-section" style="display: none;">
                <div class="section-header">
                    <h2 class="section-title">Gestion des Emplacements</h2>
                    <div class="section-actions">
                        <div class="search-container">
                            <input type="text" id="search-emplacement-code" class="search-input" placeholder="Rechercher par code...">
                            <i class="fas fa-search search-icon"></i>
                        </div>
                        <button id="export-csv-btn" class="export-btn">
                            <i class="fas fa-download"></i> Exporter CSV
                        </button>
                        <button class="add-btn" id="add-emplacement-btn">
                            <i class="fas fa-plus"></i> Nouvel Emplacement
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Code</th>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Superficie</th>
                                <th>Tarif/jour</th>
                                <th>État</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2rem;">
                                    <div class="loading"></div> Chargement des données...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reservations Section -->
            <div id="reservations-section" style="display: none;">
                <div class="section-header">
                    <h2 class="section-title">Gestion des Réservations</h2>
                    <button class="add-btn" id="add-reservation-btn">
                        <i class="fas fa-plus"></i> Nouvelle Réservation
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>N° Réservation</th>
                                <th>Client</th>
                                <th>Emplacement</th>
                                <th>Date Début</th>
                                <th>Date Fin</th>
                                <th>Statut</th>
                                <th>Montant</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 2rem;">
                                    <div class="loading"></div> Chargement des données...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Users Section -->
            <div id="users-section" style="display: none;">
                <div class="section-header">
                    <h2 class="section-title">Gestion des Utilisateurs</h2>
                    <button class="add-btn" id="add-user-btn">
                        <i class="fas fa-plus"></i> Nouvel Utilisateur
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom d'utilisateur</th>
                                <th>Nom complet</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2rem;">
                                    <div class="loading"></div> Chargement des données...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
    
    <!-- Notification Container -->
    <div id="notification" class="notification">
        <div class="notification-icon">
            <i class="fas fa-info-circle"></i>
        </div>
        <div class="notification-message">Message</div>
    </div>
    
    <!-- Notification Container for Modal -->
    <div class="notification-container" id="notification-container"></div>

    <!-- JavaScript -->
    <script>
        // Afficher une notification
        function showNotification(message, type = 'info') {
            // Utiliser l'élément de notification existant
            const notification = document.getElementById('notification');
            const iconElement = notification.querySelector('.notification-icon i');
            const messageElement = notification.querySelector('.notification-message');
            
            console.log("Affichage notification:", message, type);
            
            // Définir l'icône et la classe en fonction du type
            notification.className = 'notification notification-' + type;
            
            switch (type) {
                case 'success':
                    iconElement.className = 'fas fa-check-circle';
                    break;
                case 'error':
                    iconElement.className = 'fas fa-exclamation-circle';
                    break;
                case 'warning':
                    iconElement.className = 'fas fa-exclamation-triangle';
                    break;
                case 'info':
                default:
                    iconElement.className = 'fas fa-info-circle';
                    break;
            }
            
            messageElement.textContent = message;
            
            notification.classList.add('show');
            
            // Retirer automatiquement après 5 secondes
            setTimeout(() => {
                notification.classList.remove('show');
            }, 5000);
        }

        // Navigation entre sections
        function showSection(sectionName) {
            // Cacher toutes les sections
            const sections = ['overview', 'emplacements', 'reservations', 'users'];
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

            // Charger les données pour la section sélectionnée
            switch(sectionName) {
                case 'overview':
                    loadStats();
                    break;
                case 'emplacements':
                    loadEmplacements();
                    break;
                case 'reservations':
                    loadReservations();
                    break;
                case 'users':
                    loadUsers();
                    break;
            }
        }

        // Variables globales pour les données d'emplacements
        let allEmplacements = [];
        let locationTypesData = [];
        let zoneOccupancyData = [];
        
        // Fonction pour charger les statistiques de la vue d'ensemble
        async function loadStats() {
            try {
                // Charger les stats des emplacements
                const emplacements = await fetchEmplacements();
                if (Array.isArray(emplacements)) {
                    allEmplacements = emplacements; // Stocker pour réutilisation dans les graphiques
                    
                    const disponibles = emplacements.filter(e => e.etat === 'disponible').length;
                    document.getElementById('stat-emplacements-disponibles').textContent = disponibles;
                    
                    // Préparer les données pour les graphiques
                    prepareChartData(emplacements);
                    
                    // Créer/mettre à jour les graphiques
                    createLocationTypesChart();
                    createZoneOccupancyChart();
                }
                
                // Charger les stats des réservations
                const reservations = await fetchReservations();
                if (Array.isArray(reservations)) {
                    const enAttente = reservations.filter(r => r.statut === 'en_attente').length;
                    document.getElementById('stat-reservations-attente').textContent = enAttente;
                }
                
                // Charger les stats des utilisateurs
                const users = await fetchUsers();
                if (Array.isArray(users)) {
                    const actifs = users.filter(u => u.status === 'active' && u.role === 'user').length;
                    document.getElementById('stat-clients-actifs').textContent = actifs;
                }
                
            } catch (error) {
                console.error("Erreur lors du chargement des statistiques:", error);
                showNotification("Erreur lors du chargement des statistiques: " + error.message, 'error');
            }
        }
        
        // Fonction pour préparer les données des graphiques
        function prepareChartData(emplacements) {
            // Préparer les données pour le graphique des types d'emplacements
            const typeCount = {};
            emplacements.forEach(emp => {
                const type = emp.type || 'Non spécifié';
                typeCount[type] = (typeCount[type] || 0) + 1;
            });
            
            // Transformer en format pour Chart.js
            locationTypesData = {
                labels: Object.keys(typeCount),
                datasets: [{
                    data: Object.values(typeCount),
                    backgroundColor: [
                        'rgba(72, 187, 120, 0.8)',
                        'rgba(66, 153, 225, 0.8)',
                        'rgba(237, 137, 54, 0.8)',
                        'rgba(128, 90, 213, 0.8)',
                        'rgba(49, 130, 206, 0.8)',
                        'rgba(245, 101, 101, 0.8)',
                        'rgba(34, 211, 238, 0.8)',
                        'rgba(232, 121, 249, 0.8)',
                    ],
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1
                }]
            };
            
            // Préparer les données pour le graphique d'occupation par zone
            // Regrouper par zone (extraite du code d'emplacement)
            const zoneData = {};
            emplacements.forEach(emp => {
                // Extraire la zone à partir du code (supposons que les 2 premiers caractères indiquent la zone)
                let zone = 'Non spécifié';
                if (emp.code && typeof emp.code === 'string') {
                    const match = emp.code.match(/^([A-Z0-9]{1,2})/i);
                    if (match) {
                        zone = match[1].toUpperCase();
                    }
                }
                
                if (!zoneData[zone]) {
                    zoneData[zone] = { disponible: 0, occupe: 0 };
                }
                
                // Incrémenter le compteur approprié
                if (emp.etat === 'disponible') {
                    zoneData[zone].disponible++;
                } else {
                    zoneData[zone].occupe++;
                }
            });
            
            // Transformer en format pour Chart.js
            const zones = Object.keys(zoneData);
            const disponibles = zones.map(zone => zoneData[zone].disponible);
            const occupes = zones.map(zone => zoneData[zone].occupe);
            
            zoneOccupancyData = {
                labels: zones,
                datasets: [
                    {
                        label: 'Disponibles',
                        data: disponibles,
                        backgroundColor: 'rgba(72, 187, 120, 0.7)',
                        borderColor: 'rgba(72, 187, 120, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Occupés',
                        data: occupes,
                        backgroundColor: 'rgba(237, 137, 54, 0.7)',
                        borderColor: 'rgba(237, 137, 54, 1)',
                        borderWidth: 1
                    }
                ]
            };
        }
        
        // Créer le graphique de types d'emplacements
        let locationTypeChart;
        function createLocationTypesChart() {
            const ctx = document.getElementById('locationTypesChart').getContext('2d');
            
            // Détruire le graphique existant s'il existe
            if (locationTypeChart) {
                locationTypeChart.destroy();
            }
            
            // Créer un nouveau graphique
            locationTypeChart = new Chart(ctx, {
                type: 'pie',
                data: locationTypesData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                color: 'rgba(255, 255, 255, 0.8)',
                                font: {
                                    family: 'Inter',
                                    size: 12
                                },
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            titleFont: {
                                family: 'Inter',
                                size: 14
                            },
                            bodyFont: {
                                family: 'Inter',
                                size: 13
                            },
                            padding: 12,
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1,
                            displayColors: true
                        }
                    }
                }
            });
        }
        
        // Créer le graphique d'occupation par zone
        let zoneOccupancyChart;
        function createZoneOccupancyChart() {
            const ctx = document.getElementById('zoneOccupancyChart').getContext('2d');
            
            // Détruire le graphique existant s'il existe
            if (zoneOccupancyChart) {
                zoneOccupancyChart.destroy();
            }
            
            // Créer un nouveau graphique
            zoneOccupancyChart = new Chart(ctx, {
                type: 'bar',
                data: zoneOccupancyData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)',
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.7)'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)',
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.7)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: 'rgba(255, 255, 255, 0.8)',
                                font: {
                                    family: 'Inter',
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            titleFont: {
                                family: 'Inter',
                                size: 14
                            },
                            bodyFont: {
                                family: 'Inter',
                                size: 13
                            },
                            padding: 12,
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1
                        }
                    }
                }
            });
        }

        // Fonction pour charger les emplacements
        async function loadEmplacements() {
            try {
                // Récupérer la valeur de recherche
                const searchValue = document.getElementById('search-emplacement-code').value.trim().toLowerCase();
                const allEmplacements = await fetchEmplacements();
                
                // Filtrer les emplacements par code si une recherche est effectuée
                let filteredEmplacements = allEmplacements;
                if (searchValue) {
                    filteredEmplacements = allEmplacements.filter(emp => 
                        emp.code.toLowerCase().includes(searchValue)
                    );
                }
                
                const tbody = document.querySelector('#emplacements-section table tbody');
                if (!tbody) return;
                
                tbody.innerHTML = '';
                
                if (Array.isArray(filteredEmplacements) && filteredEmplacements.length > 0) {
                    filteredEmplacements.forEach(emp => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${emp.id}</td>
                            <td>${emp.code || '-'}</td>
                            <td>${emp.nom || '-'}</td>
                            <td>${emp.type || '-'}</td>
                            <td>${emp.superficie || (emp.longueur * emp.largeur) || '-'} m²</td>
                            <td>${formatMoney(emp.tarif_journalier)}</td>
                            <td><span class="status-badge status-${emp.statut || emp.etat || 'disponible'}">${emp.statut || emp.etat || 'disponible'}</span></td>
                            <td>
                                <button data-action="edit-emplacement" data-id="${emp.id}" class="action-btn edit-btn" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button data-action="delete-emplacement" data-id="${emp.id}" class="action-btn delete-btn" title="Supprimer">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 2rem;">Aucun emplacement trouvé</td></tr>';
                }
            } catch (error) {
                console.error("Erreur lors du chargement des emplacements:", error);
                showNotification("Erreur lors du chargement des emplacements: " + error.message, 'error');
                
                const tbody = document.querySelector('#emplacements-section table tbody');
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 2rem;">Erreur: ${error.message}</td></tr>`;
                }
            }
        }
        
        // Fonction pour exporter les données en CSV
        function exportTableToCSV() {
            // En-têtes des colonnes
            const headers = ['ID', 'Code', 'Nom', 'Type', 'Superficie (m²)', 'Tarif Journalier', 'État'];
            
            // Récupérer les données actuelles du tableau
            const rows = Array.from(document.querySelectorAll('#emplacements-section table tbody tr'))
                .filter(row => row.cells.length > 1) // Ignorer les lignes de message "aucun emplacement trouvé"
                .map(row => [
                    row.cells[0].textContent, // ID
                    row.cells[1].textContent, // Code
                    row.cells[2].textContent, // Nom
                    row.cells[3].textContent, // Type
                    row.cells[4].textContent, // Superficie
                    row.cells[5].textContent, // Tarif
                    row.cells[6].textContent  // État
                ]);
                
            // Créer le contenu CSV
            let csvContent = headers.join(',') + '\n';
            
            // Ajouter chaque ligne
            rows.forEach(rowArray => {
                // Échapper les guillemets et entourer de guillemets si nécessaire
                const formattedRow = rowArray.map(cell => {
                    const content = String(cell).trim();
                    if (content.includes(',') || content.includes('"') || content.includes('\n')) {
                        return `"${content.replace(/"/g, '""')}"`;
                    }
                    return content;
                });
                csvContent += formattedRow.join(',') + '\n';
            });
            
            // Créer un blob et télécharger le fichier
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const now = new Date();
            const timestamp = now.toISOString().replace(/[:.]/g, '-');
            
            link.href = URL.createObjectURL(blob);
            link.download = `emplacements-marsa-maroc-${timestamp}.csv`;
            link.style.display = "none";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showNotification("Export CSV effectué avec succès", 'success');
        }
        
        // Fonction pour récupérer les emplacements
        async function fetchEmplacements() {
            try {
                console.log('Récupération des emplacements...');
                
                // Utiliser l'API fixée
                const response = await fetch('../../api/emplacements-fixed.php');
                
                console.log('Statut de la réponse:', response.status, response.statusText);
                
                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
                }
                
                const responseText = await response.text();
                console.log('Réponse brute:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    throw new Error(`Erreur de parsing JSON: ${e.message}`);
                }
                
                console.log('Résultat parsé:', result);
                
                // Normaliser le format de la réponse
                let emplacements = [];
                
                if (result && result.success === true && Array.isArray(result.data)) {
                    emplacements = result.data;
                } else if (Array.isArray(result)) {
                    emplacements = result;
                } else {
                    throw new Error('Format de données inattendu');
                }
                
                return emplacements;
            } catch (error) {
                console.error('Erreur lors de la récupération des emplacements:', error);
                throw error;
            }
        }

        // Fonction pour récupérer les utilisateurs
        async function fetchUsers() {
            try {
                console.log('Récupération des utilisateurs...');
                
                // Utiliser l'API users-real
                const response = await fetch('../../api/users-real.php');
                
                console.log('Statut de la réponse:', response.status, response.statusText);
                
                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
                }
                
                const responseText = await response.text();
                console.log('Réponse brute:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    throw new Error(`Erreur de parsing JSON: ${e.message}`);
                }
                
                console.log('Résultat parsé:', result);
                
                // Normaliser le format de la réponse
                let users = [];
                
                if (result && result.success === true && Array.isArray(result.data)) {
                    users = result.data;
                } else if (Array.isArray(result)) {
                    users = result;
                } else {
                    throw new Error('Format de données inattendu');
                }
                
                return users;
            } catch (error) {
                console.error('Erreur lors de la récupération des utilisateurs:', error);
                throw error;
            }
        }

        // Fonction pour charger les réservations
        async function loadReservations() {
            try {
                const reservations = await fetchReservations();
                
                const tbody = document.querySelector('#reservations-section table tbody');
                if (!tbody) return;
                
                tbody.innerHTML = '';
                
                if (Array.isArray(reservations) && reservations.length > 0) {
                    reservations.forEach(res => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${res.id}</td>
                            <td>${res.numero_reservation || '-'}</td>
                            <td>${res.utilisateur_nom || '-'}</td>
                            <td>${res.emplacement_nom || '-'}</td>
                            <td>${formatDate(res.date_debut)}</td>
                            <td>${formatDate(res.date_fin)}</td>
                            <td><span class="status-badge status-${res.statut}">${res.statut}</span></td>
                            <td>${formatMoney(res.montant_total)}</td>
                            <td>
                                ${res.statut === 'en_attente' ? `
                                    <button data-action="validate-reservation" data-id="${res.id}" class="action-btn edit-btn">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button data-action="reject-reservation" data-id="${res.id}" class="action-btn delete-btn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                ` : ''}
                                <button data-action="view-reservation" data-id="${res.id}" class="action-btn view-btn">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 2rem;">Aucune réservation trouvée</td></tr>';
                }
            } catch (error) {
                console.error("Erreur lors du chargement des réservations:", error);
                showNotification("Erreur lors du chargement des réservations: " + error.message, 'error');
                
                const tbody = document.querySelector('#reservations-section table tbody');
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; padding: 2rem;">Erreur: ${error.message}</td></tr>`;
                }
            }
        }
        
        // Fonction pour récupérer les réservations
        async function fetchReservations() {
            try {
                console.log('Récupération des réservations...');
                
                // Utiliser l'API real
                const response = await fetch('../../api/reservations-real.php');
                
                console.log('Statut de la réponse:', response.status, response.statusText);
                
                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
                }
                
                const responseText = await response.text();
                console.log('Réponse brute:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    throw new Error(`Erreur de parsing JSON: ${e.message}`);
                }
                
                console.log('Résultat parsé:', result);
                
                // Normaliser le format de la réponse
                let reservations = [];
                
                if (result && result.success === true && Array.isArray(result.data)) {
                    reservations = result.data;
                } else if (Array.isArray(result)) {
                    reservations = result;
                } else {
                    throw new Error('Format de données inattendu');
                }
                
                return reservations;
            } catch (error) {
                console.error('Erreur lors de la récupération des réservations:', error);
                throw error;
            }
        }

        // Fonction pour charger les utilisateurs
        async function loadUsers() {
            try {
                const users = await fetchUsers();
                
                const tbody = document.querySelector('#users-section table tbody');
                if (!tbody) return;
                
                tbody.innerHTML = '';
                
                if (Array.isArray(users) && users.length > 0) {
                    users.forEach(user => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${user.id}</td>
                            <td>${user.username || '-'}</td>
                            <td>${user.full_name || '-'}</td>
                            <td>${user.email || '-'}</td>
                            <td>${user.phone || '-'}</td>
                            <td><span class="status-badge status-${user.role}">${user.role}</span></td>
                            <td><span class="status-badge status-${user.status === 'active' ? 'disponible' : 'occupe'}">${user.status}</span></td>
                            <td>
                                <button data-action="edit-user" data-id="${user.id}" class="action-btn edit-btn">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button data-action="delete-user" data-id="${user.id}" class="action-btn delete-btn">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 2rem;">Aucun utilisateur trouvé</td></tr>';
                }
            } catch (error) {
                console.error("Erreur lors du chargement des utilisateurs:", error);
                showNotification("Erreur lors du chargement des utilisateurs: " + error.message, 'error');
                
                const tbody = document.querySelector('#users-section table tbody');
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 2rem;">Erreur: ${error.message}</td></tr>`;
                }
            }
        }
        
        // Fonction pour récupérer les utilisateurs
        async function fetchUsers() {
            try {
                console.log('Récupération des utilisateurs...');
                
                // Utiliser l'API fixée
                const response = await fetch('../../api/users-fixed.php');
                
                console.log('Statut de la réponse:', response.status, response.statusText);
                
                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
                }
                
                const responseText = await response.text();
                console.log('Réponse brute:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    throw new Error(`Erreur de parsing JSON: ${e.message}`);
                }
                
                console.log('Résultat parsé:', result);
                
                // Normaliser le format de la réponse
                let users = [];
                
                if (result && result.success === true && Array.isArray(result.data)) {
                    users = result.data;
                } else if (Array.isArray(result)) {
                    users = result;
                } else {
                    throw new Error('Format de données inattendu');
                }
                
                return users;
            } catch (error) {
                console.error('Erreur lors de la récupération des utilisateurs:', error);
                throw error;
            }
        }

        // Fonction pour formater une date
        function formatDate(dateStr) {
            if (!dateStr) return '-';
            
            const date = new Date(dateStr);
            if (isNaN(date.getTime())) return dateStr;
            
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Fonction pour formater un montant
        function formatMoney(amount) {
            if (amount === undefined || amount === null) return '-';
            
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'MAD'
            }).format(amount);
        }
        
        // Écouteurs d'événements pour les boutons d'action
        document.addEventListener('click', function(event) {
            const target = event.target.closest('[data-action]');
            if (!target) return;
            
            const action = target.dataset.action;
            const id = target.dataset.id;
            
            console.log(`Action déclenchée: ${action} pour ID: ${id}`);
            
            switch (action) {
                case 'edit-emplacement':
                    console.log(`Appel editEmplacement(${id})`);
                    editEmplacement(id);
                    break;
                case 'delete-emplacement':
                    console.log(`Appel deleteEmplacement(${id})`);
                    deleteEmplacement(id);
                    break;
                case 'validate-reservation':
                    showNotification(`Validation de la réservation ${id} (fonctionnalité à venir)`, 'info');
                    break;
                case 'reject-reservation':
                    showNotification(`Rejet de la réservation ${id} (fonctionnalité à venir)`, 'info');
                    break;
                case 'view-reservation':
                    showNotification(`Affichage de la réservation ${id} (fonctionnalité à venir)`, 'info');
                    break;
                case 'edit-user':
                    showNotification(`Édition de l'utilisateur ${id} (fonctionnalité à venir)`, 'info');
                    break;
                case 'delete-user':
                    showNotification(`Suppression de l'utilisateur ${id} (fonctionnalité à venir)`, 'info');
                    break;
                default:
                    console.log(`Action non reconnue: ${action}`);
            }
        });
        
        // Boutons d'ajout
        document.addEventListener('DOMContentLoaded', function() {
            const addButton = document.getElementById('add-emplacement-btn');
            if (addButton) {
                console.log("Bouton d'ajout trouvé");
                addButton.onclick = function() {
                    console.log("Bouton ajout emplacement cliqué via onclick");
                    showEmplacementModal();
                };
            } else {
                console.error("Bouton d'ajout non trouvé");
            }
        });
        
        document.getElementById('add-reservation-btn').addEventListener('click', function() {
            showReservationModal();
        });
        
        document.getElementById('add-user-btn').addEventListener('click', function() {
            showUserModal();
        });
        
        // Charger la vue d'ensemble au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            
            // Attacher l'écouteur d'événement pour la recherche
            document.getElementById('search-emplacement-code').addEventListener('input', function() {
                loadEmplacements();
            });
            
            // Attacher l'écouteur d'événement pour l'export CSV
            document.getElementById('export-csv-btn').addEventListener('click', function() {
                exportTableToCSV();
            });
        });
        
        // Fonctions pour la gestion des emplacements
        function showEmplacementModal(emplacement = null) {
            const modal = document.getElementById('emplacementModal');
            const form = document.getElementById('add-emplacement-form');
            const title = document.querySelector('#modalTitle');
            const submitBtn = document.getElementById('submitEmplacementBtn');
            
            if (!modal || !form) {
                console.error("Modal ou formulaire non trouvé");
                return;
            }
            
            // Réinitialiser le formulaire et les états de validation
            form.reset();
            clearFormValidation(form);
            
            if (emplacement) {
                // Mode édition
                title.textContent = 'Modifier un Emplacement';
                submitBtn.innerHTML = '<span class="loading-spinner"></span><i class="fas fa-edit"></i> Modifier';
                
                // Pré-remplir le formulaire avec les données de l'emplacement
                document.getElementById('code').value = emplacement.code || '';
                document.getElementById('nom').value = emplacement.nom || '';
                document.getElementById('type').value = emplacement.type || '';
                document.getElementById('longueur').value = emplacement.longueur || '';
                document.getElementById('largeur').value = emplacement.largeur || '';
                document.getElementById('tarif').value = emplacement.tarif_journalier || '';
                document.getElementById('capacite').value = emplacement.capacite_navire || '';
                document.getElementById('equipements').value = emplacement.equipements || '';
                document.getElementById('statut').value = emplacement.etat || emplacement.statut || 'disponible';
                
                // Ajouter l'ID caché pour l'édition
                let idInput = document.getElementById('emplacement-id');
                if (!idInput) {
                    idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.id = 'emplacement-id';
                    idInput.name = 'id';
                    form.appendChild(idInput);
                }
                idInput.value = emplacement.id;
            } else {
                // Mode création
                title.textContent = 'Ajouter un Nouvel Emplacement';
                submitBtn.innerHTML = '<span class="loading-spinner"></span><i class="fas fa-save"></i> Enregistrer';
                
                // Supprimer l'ID caché s'il existe
                const idInput = document.getElementById('emplacement-id');
                if (idInput) {
                    idInput.parentNode.removeChild(idInput);
                }
            }
            
            // Activer la validation en temps réel
            setupRealTimeValidation(form);
            
            // Afficher le modal avec animation
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }

        function closeEmplacementModal() {
            const modal = document.getElementById('emplacementModal');
            if (modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                    const form = document.getElementById('add-emplacement-form');
                    if (form) {
                        form.reset();
                        clearFormValidation(form);
                    }
                }, 300);
            }
        }
        
        function clearFormValidation(form) {
            const formGroups = form.querySelectorAll('.form-group');
            formGroups.forEach(group => {
                group.classList.remove('error', 'success');
            });
        }
        
        function validateField(field) {
            const formGroup = field.closest('.form-group');
            const value = field.value.trim();
            let isValid = true;
            let errorMessage = '';
            
            // Validation selon le type de champ
            switch(field.name) {
                case 'code':
                    if (!value) {
                        isValid = false;
                        errorMessage = 'Code requis';
                    } else if (value.length > 10) {
                        isValid = false;
                        errorMessage = 'Code trop long (max 10 caractères)';
                    }
                    break;
                    
                case 'nom':
                    if (!value) {
                        isValid = false;
                        errorMessage = 'Nom requis';
                    } else if (value.length > 100) {
                        isValid = false;
                        errorMessage = 'Nom trop long (max 100 caractères)';
                    }
                    break;
                    
                case 'type':
                    if (!value) {
                        isValid = false;
                        errorMessage = 'Type requis';
                    }
                    break;
                    
                case 'longueur':
                    const longueur = parseFloat(value);
                    if (!value) {
                        isValid = false;
                        errorMessage = 'Longueur requise';
                    } else if (longueur < 1 || longueur > 2000) {
                        isValid = false;
                        errorMessage = 'Longueur entre 1 et 2000 mètres';
                    }
                    break;
                    
                case 'largeur':
                    const largeur = parseFloat(value);
                    if (!value) {
                        isValid = false;
                        errorMessage = 'Largeur requise';
                    } else if (largeur < 1 || largeur > 500) {
                        isValid = false;
                        errorMessage = 'Largeur entre 1 et 500 mètres';
                    }
                    break;
                    
                case 'capacite':
                    const capacite = parseFloat(value);
                    if (!value) {
                        isValid = false;
                        errorMessage = 'Capacité requise';
                    } else if (capacite < 100 || capacite > 1000000) {
                        isValid = false;
                        errorMessage = 'Capacité entre 100 et 1,000,000 tonnes';
                    }
                    break;
                    
                case 'tarif':
                    const tarif = parseFloat(value);
                    if (!value) {
                        isValid = false;
                        errorMessage = 'Tarif requis';
                    } else if (tarif < 0 || tarif > 100000) {
                        isValid = false;
                        errorMessage = 'Tarif entre 0 et 100,000 euros';
                    }
                    break;
            }
            
            // Appliquer les classes de validation
            if (isValid) {
                formGroup.classList.remove('error');
                formGroup.classList.add('success');
            } else {
                formGroup.classList.remove('success');
                formGroup.classList.add('error');
                
                // Mettre à jour le message d'erreur
                const errorEl = formGroup.querySelector('.error-message span');
                if (errorEl) {
                    errorEl.textContent = errorMessage;
                }
            }
            
            return isValid;
        }
        
        function setupRealTimeValidation(form) {
            const fields = form.querySelectorAll('input[required], select[required]');
            
            fields.forEach(field => {
                // Validation sur blur (perte de focus)
                field.addEventListener('blur', () => {
                    validateField(field);
                });
                
                // Validation sur input (saisie)
                field.addEventListener('input', () => {
                    // Délai pour éviter la validation trop fréquente
                    clearTimeout(field.validationTimeout);
                    field.validationTimeout = setTimeout(() => {
                        validateField(field);
                    }, 500);
                });
            });
        }        // Fonction pour éditer un emplacement
        async function editEmplacement(id) {
            try {
                console.log(`Édition de l'emplacement ID: ${id}`);
                
                // Récupérer les détails de l'emplacement
                const response = await fetch(`../../api/emplacements-fixed.php?id=${id}`);
                
                if (!response.ok) {
                    throw new Error(`Erreur HTTP: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success && data.data) {
                    // Afficher le modal avec les données de l'emplacement
                    showEmplacementModal(data.data);
                } else {
                    throw new Error(data.message || 'Emplacement non trouvé');
                }
            } catch (error) {
                console.error("Erreur lors de la récupération de l'emplacement:", error);
                showNotification(`Erreur: ${error.message}`, 'error');
            }
        }
        
        // Fonction pour supprimer un emplacement
        async function deleteEmplacement(id) {
            console.log(`🗑️ deleteEmplacement appelée avec ID: ${id}`);
            
            if (!id) {
                console.error('❌ ID manquant pour la suppression');
                showNotification('Erreur: ID manquant pour la suppression', 'error');
                return;
            }
            
            if (confirm('Êtes-vous sûr de vouloir supprimer cet emplacement ? Cette action est irréversible.')) {
                try {
                    console.log(`🔄 Envoi de la requête DELETE pour l'emplacement ID: ${id}`);
                    
                    const response = await fetch(`../../api/emplacements-fixed.php?id=${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });
                    
                    console.log(`📡 Réponse reçue - Status: ${response.status}`);
                    
                    if (!response.ok) {
                        throw new Error(`Erreur HTTP: ${response.status} ${response.statusText}`);
                    }
                    
                    const data = await response.json();
                    console.log('📦 Données de réponse:', data);
                    
                    if (data.success) {
                        console.log('✅ Suppression réussie');
                        showNotification('Emplacement supprimé avec succès!', 'success');
                        // Recharger la liste des emplacements
                        await loadEmplacements();
                        await loadStats();
                    } else {
                        throw new Error(data.message || 'Erreur lors de la suppression');
                    }
                } catch (error) {
                    console.error("❌ Erreur lors de la suppression de l'emplacement:", error);
                    showNotification(`Erreur: ${error.message}`, 'error');
                }
            } else {
                console.log('🚫 Suppression annulée par l\'utilisateur');
            }
        }
        
        function validateForm(form) {
            const requiredFields = form.querySelectorAll('input[required], select[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });
            
            return isValid;
        }
        
        function setLoadingState(button, isLoading) {
            if (isLoading) {
                button.classList.add('btn-loading');
                button.disabled = true;
                const spinner = button.querySelector('.loading-spinner');
                if (spinner) {
                    spinner.style.display = 'inline-block';
                }
            } else {
                button.classList.remove('btn-loading');
                button.disabled = false;
                const spinner = button.querySelector('.loading-spinner');
                if (spinner) {
                    spinner.style.display = 'none';
                }
            }
        }
        
        function showSuccessAnimation() {
            // Créer une animation de succès
            const successDiv = document.createElement('div');
            successDiv.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: linear-gradient(135deg, #10b981, #059669);
                color: white;
                padding: 2rem;
                border-radius: 20px;
                box-shadow: 0 25px 50px rgba(16, 185, 129, 0.3);
                z-index: 10000;
                text-align: center;
                font-size: 1.2rem;
                font-weight: 600;
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            `;
            
            successDiv.innerHTML = `
                <i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                Emplacement enregistré avec succès!
            `;
            
            document.body.appendChild(successDiv);
            
            // Animation d'apparition
            setTimeout(() => {
                successDiv.style.opacity = '1';
                successDiv.style.transform = 'translate(-50%, -50%) scale(1)';
            }, 10);
            
            // Animation de disparition
            setTimeout(() => {
                successDiv.style.opacity = '0';
                successDiv.style.transform = 'translate(-50%, -50%) scale(0.8)';
                setTimeout(() => {
                    document.body.removeChild(successDiv);
                }, 400);
            }, 2000);
        }

        // Gestionnaire de soumission du formulaire d'ajout d'emplacement
        document.addEventListener('DOMContentLoaded', function() {
            // Attacher l'événement de soumission après que le DOM soit complètement chargé
            const form = document.getElementById('add-emplacement-form');
            if (form) {
                console.log("Formulaire trouvé, attachement de l'événement submit");
                
                form.onsubmit = async function(e) {
                    e.preventDefault();
                    console.log("Formulaire soumis via onsubmit");
                    
                    const submitBtn = document.getElementById('submitEmplacementBtn');
                    
                    try {
                        // Validation complète du formulaire
                        if (!validateForm(this)) {
                            showNotification('Veuillez corriger les erreurs dans le formulaire', 'error');
                            return;
                        }
                        
                        // Activer l'état de chargement
                        setLoadingState(submitBtn, true);
                        
                        // Récupérer les données du formulaire
                        const formData = new FormData(this);
                        
                        // Vérifier si c'est une création ou une modification
                        const idInput = document.getElementById('emplacement-id');
                        const emplacementId = idInput ? idInput.value : null;
                        
                        // Créer un objet avec les données du formulaire
                        const emplacementData = {
                            code: formData.get('code'),
                            nom: formData.get('nom'),
                            type: formData.get('type'),
                            superficie: parseFloat(formData.get('longueur')) * parseFloat(formData.get('largeur')),
                            longueur: parseFloat(formData.get('longueur')),
                            largeur: parseFloat(formData.get('largeur')),
                            tarif_journalier: parseFloat(formData.get('tarif')),
                            tarif_horaire: parseFloat(formData.get('tarif')) / 24, // Estimation du tarif horaire
                            tarif_mensuel: parseFloat(formData.get('tarif')) * 30, // Estimation du tarif mensuel
                            capacite_navire: formData.get('capacite'),
                            equipements: formData.get('equipements'),
                            etat: formData.get('statut') || 'disponible'
                        };
                        
                        console.log("Données à envoyer:", emplacementData);
                        console.log("ID de l'emplacement:", emplacementId);
                        
                        // Envoyer les données à l'API
                        const method = emplacementId ? 'PUT' : 'POST';
                        const url = emplacementId ? `../../api/emplacements-fixed.php?id=${emplacementId}` : '../../api/emplacements-fixed.php';
                        
                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(emplacementData)
                        });
                        
                        console.log("Réponse reçue, status:", response.status);
                        
                        if (!response.ok) {
                            throw new Error(`Erreur HTTP: ${response.status}`);
                        }
                        
                        const data = await response.json();
                        console.log("Réponse API:", data);
                        
                        if (data.success) {
                            // Afficher une animation de succès
                            showSuccessAnimation();
                            
                            // Afficher une notification de succès
                            const actionText = emplacementId ? 'modifié' : 'ajouté';
                            showNotification(`Emplacement ${actionText} avec succès!`, 'success');
                            
                            // Fermer le modal avec un délai pour l'animation
                            setTimeout(() => {
                                closeEmplacementModal();
                            }, 1000);
                            
                            // Rafraîchir la liste des emplacements et les statistiques
                            await loadEmplacements();
                            await loadStats();
                        } else {
                            throw new Error(data.message || 'Erreur lors de l\'opération sur l\'emplacement');
                        }
                    } catch (error) {
                        console.error("Erreur lors de l'opération sur l'emplacement:", error);
                        showNotification('Erreur: ' + error.message, 'error');
                    } finally {
                        // Désactiver l'état de chargement
                        setLoadingState(submitBtn, false);
                    }
                    
                    return false;
                };
            } else {
                console.error("Formulaire d'ajout d'emplacement non trouvé dans le DOM");
            }
            
            // Gérer la fermeture du modal en cliquant à l'extérieur
            window.addEventListener('click', function(event) {
                const modal = document.getElementById('emplacementModal');
                if (event.target === modal) {
                    closeEmplacementModal();
                }
            });
            
            // Gérer la fermeture du modal avec la touche Escape
            window.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    const modal = document.getElementById('emplacementModal');
                    if (modal && modal.style.display === 'block') {
                        closeEmplacementModal();
                    }
                }
            });
        });
        
        // Fermer le modal si on clique à l'extérieur du contenu
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('emplacementModal');
            if (event.target === modal) {
                closeEmplacementModal();
            }
        });
        
        // Fermer le modal avec Escape
        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeEmplacementModal();
            }
        });

        // Fonctions pour la gestion des réservations
        function showReservationModal(reservation = null) {
            const modal = document.getElementById('reservationModal');
            const form = document.getElementById('add-reservation-form');
            const title = document.querySelector('#reservationModalTitle');
            const submitBtn = document.getElementById('submitReservationBtn');
            
            if (!modal || !form) {
                console.error("Modal ou formulaire de réservation non trouvé");
                return;
            }
            
            // Réinitialiser le formulaire
            form.reset();
            
            // Charger les emplacements disponibles et les utilisateurs
            loadAvailableEmplacements();
            loadAvailableUsers();
            
            if (reservation) {
                // Mode édition
                title.textContent = 'Modifier une Réservation';
                submitBtn.innerHTML = '<span class="loading-spinner"></span><i class="fas fa-edit"></i> Modifier';
            } else {
                // Mode création
                title.textContent = 'Nouvelle Réservation';
                submitBtn.innerHTML = '<span class="loading-spinner"></span><i class="fas fa-save"></i> Enregistrer';
            }
            
            // Afficher le modal avec animation
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }

        function closeReservationModal() {
            const modal = document.getElementById('reservationModal');
            if (modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        }

        // Fonction pour charger les emplacements disponibles
        async function loadAvailableEmplacements() {
            try {
                const emplacements = await fetchEmplacements();
                const select = document.getElementById('reservation_emplacement_id');
                
                if (!select) return;
                
                // Vider le select
                select.innerHTML = '<option value="">Sélectionner un emplacement</option>';
                
                // Ajouter les emplacements disponibles
                emplacements.forEach(emplacement => {
                    if (emplacement.etat === 'disponible') {
                        const option = document.createElement('option');
                        option.value = emplacement.id;
                        option.textContent = `${emplacement.code} - ${emplacement.nom}`;
                        select.appendChild(option);
                    }
                });
                
            } catch (error) {
                console.error('Erreur lors du chargement des emplacements:', error);
                showNotification('Erreur lors du chargement des emplacements', 'error');
            }
        }

        // Fonction pour charger les utilisateurs disponibles
        async function loadAvailableUsers() {
            try {
                const users = await fetchUsers();
                const select = document.getElementById('reservation_user_id');
                
                if (!select) return;
                
                // Vider le select
                select.innerHTML = '<option value="">Sélectionner un client</option>';
                
                // Ajouter les utilisateurs actifs
                users.forEach(user => {
                    if (user.status === 'active' && user.role === 'user') {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = `${user.full_name} (${user.company_name || user.email})`;
                        select.appendChild(option);
                    }
                });
                
            } catch (error) {
                console.error('Erreur lors du chargement des utilisateurs:', error);
                showNotification('Erreur lors du chargement des utilisateurs', 'error');
            }
        }

        // Fonctions pour la gestion des utilisateurs
        function showUserModal(user = null) {
            const modal = document.getElementById('userModal');
            const form = document.getElementById('add-user-form');
            const title = document.querySelector('#userModalTitle');
            const submitBtn = document.getElementById('submitUserBtn');
            
            if (!modal || !form) {
                console.error("Modal ou formulaire d'utilisateur non trouvé");
                return;
            }
            
            // Réinitialiser le formulaire
            form.reset();
            
            if (user) {
                // Mode édition
                title.textContent = 'Modifier un Utilisateur';
                submitBtn.innerHTML = '<span class="loading-spinner"></span><i class="fas fa-edit"></i> Modifier';
                
                // Pré-remplir le formulaire avec les données de l'utilisateur
                document.getElementById('user_nom').value = user.nom || '';
                document.getElementById('user_prenom').value = user.prenom || '';
                document.getElementById('user_email').value = user.email || '';
                document.getElementById('user_username').value = user.username || '';
                document.getElementById('user_role').value = user.role || '';
                document.getElementById('user_entreprise').value = user.entreprise || '';
                document.getElementById('user_telephone').value = user.telephone || '';
                
                // Masquer le champ mot de passe en mode édition
                const passwordField = document.getElementById('user_password');
                const passwordGroup = passwordField.closest('.form-group');
                if (passwordGroup) {
                    passwordGroup.style.display = 'none';
                }
                passwordField.required = false;
                
                // Ajouter l'ID caché pour l'édition
                let idInput = document.getElementById('user-id');
                if (!idInput) {
                    idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.id = 'user-id';
                    idInput.name = 'id';
                    form.appendChild(idInput);
                }
                idInput.value = user.id;
            } else {
                // Mode création
                title.textContent = 'Nouvel Utilisateur';
                submitBtn.innerHTML = '<span class="loading-spinner"></span><i class="fas fa-save"></i> Enregistrer';
                
                // Afficher le champ mot de passe en mode création
                const passwordField = document.getElementById('user_password');
                const passwordGroup = passwordField.closest('.form-group');
                if (passwordGroup) {
                    passwordGroup.style.display = 'block';
                }
                passwordField.required = true;
                
                // Supprimer l'ID caché s'il existe
                const idInput = document.getElementById('user-id');
                if (idInput) {
                    idInput.parentNode.removeChild(idInput);
                }
            }
            
            // Afficher le modal avec animation
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        }

        function closeUserModal() {
            const modal = document.getElementById('userModal');
            if (modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        }
    </script>
    
    <div class="notification-container" id="notification-container"></div>
    
    <!-- Modal pour ajouter un emplacement -->
    <div class="modal" id="emplacementModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Ajouter un Nouvel Emplacement</h2>
                <span class="close-modal" onclick="closeEmplacementModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="add-emplacement-form">
                    <div class="form-group">
                        <label for="code">Code de l'emplacement</label>
                        <input type="text" id="code" name="code" required placeholder="Ex: QA100" maxlength="10">
                        <i class="field-icon fas fa-tag"></i>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Code requis (max 10 caractères)</span>
                        </div>
                        <div class="success-message">
                            <i class="fas fa-check-circle"></i>
                            <span>Code valide</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nom">Nom de l'emplacement</label>
                        <input type="text" id="nom" name="nom" required placeholder="Ex: Quai Alpha Terminal 1" maxlength="100">
                        <i class="field-icon fas fa-signature"></i>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Nom requis (max 100 caractères)</span>
                        </div>
                        <div class="success-message">
                            <i class="fas fa-check-circle"></i>
                            <span>Nom valide</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="type">Type d'emplacement</label>
                        <select id="type" name="type" required>
                            <option value="">-- Sélectionnez un type --</option>
                            <option value="quai">🚢 Quai</option>
                            <option value="digue">🌊 Digue</option>
                            <option value="bassin">🏊 Bassin</option>
                            <option value="zone_amarrage">⚓ Zone d'amarrage</option>
                        </select>
                        <i class="field-icon fas fa-list"></i>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Type requis</span>
                        </div>
                        <div class="success-message">
                            <i class="fas fa-check-circle"></i>
                            <span>Type sélectionné</span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="longueur">Longueur (mètres)</label>
                            <input type="number" id="longueur" name="longueur" required placeholder="200" min="1" max="2000">
                            <i class="field-icon fas fa-ruler-horizontal"></i>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>Longueur requise (1-2000m)</span>
                            </div>
                            <div class="success-message">
                                <i class="fas fa-check-circle"></i>
                                <span>Longueur valide</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="largeur">Largeur (mètres)</label>
                            <input type="number" id="largeur" name="largeur" required placeholder="30" min="1" max="500">
                            <i class="field-icon fas fa-ruler-vertical"></i>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>Largeur requise (1-500m)</span>
                            </div>
                            <div class="success-message">
                                <i class="fas fa-check-circle"></i>
                                <span>Largeur valide</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="capacite">Capacité navire (tonnes)</label>
                        <input type="number" id="capacite" name="capacite" required placeholder="50000" min="100" max="1000000">
                        <i class="field-icon fas fa-weight-hanging"></i>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Capacité requise (100-1,000,000 tonnes)</span>
                        </div>
                        <div class="success-message">
                            <i class="fas fa-check-circle"></i>
                            <span>Capacité valide</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tarif">Tarif journalier (€)</label>
                        <input type="number" id="tarif" name="tarif" step="0.01" required placeholder="1000.00" min="0" max="100000">
                        <i class="field-icon fas fa-euro-sign"></i>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Tarif requis (0-100,000€)</span>
                        </div>
                        <div class="success-message">
                            <i class="fas fa-check-circle"></i>
                            <span>Tarif valide</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="equipements">Équipements disponibles</label>
                        <input type="text" id="equipements" name="equipements" placeholder="Ex: Grue 50T, Éclairage LED, Alimentation électrique" maxlength="255">
                        <i class="field-icon fas fa-tools"></i>
                        <div class="success-message">
                            <i class="fas fa-check-circle"></i>
                            <span>Équipements renseignés</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="statut">Statut initial</label>
                        <select id="statut" name="statut" required>
                            <option value="disponible">✅ Disponible</option>
                            <option value="maintenance">🔧 En maintenance</option>
                            <option value="occupe">🚢 Occupé</option>
                            <option value="reserve">📋 Réservé</option>
                        </select>
                        <i class="field-icon fas fa-flag"></i>
                        <div class="success-message">
                            <i class="fas fa-check-circle"></i>
                            <span>Statut défini</span>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="closeEmplacementModal()">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button type="submit" class="add-btn" id="submitEmplacementBtn">
                            <span class="loading-spinner"></span>
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal pour ajouter une réservation -->
    <div class="modal" id="reservationModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="reservationModalTitle">Nouvelle Réservation</h2>
                <span class="close-modal" onclick="closeReservationModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="add-reservation-form">
                    <div class="form-group">
                        <label for="reservation_user_id">Client</label>
                        <select id="reservation_user_id" name="user_id" required>
                            <option value="">Sélectionner un client</option>
                        </select>
                        <i class="field-icon fas fa-user"></i>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Client requis</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reservation_emplacement_id">Emplacement</label>
                        <select id="reservation_emplacement_id" name="emplacement_id" required>
                            <option value="">Sélectionner un emplacement</option>
                        </select>
                        <i class="field-icon fas fa-map-marker-alt"></i>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Emplacement requis</span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reservation_date_debut">Date de début</label>
                            <input type="datetime-local" id="reservation_date_debut" name="date_debut" required>
                            <i class="field-icon fas fa-calendar-alt"></i>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>Date de début requise</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="reservation_date_fin">Date de fin</label>
                            <input type="datetime-local" id="reservation_date_fin" name="date_fin" required>
                            <i class="field-icon fas fa-calendar-alt"></i>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>Date de fin requise</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reservation_navire_nom">Nom du navire</label>
                        <input type="text" id="reservation_navire_nom" name="navire_nom" required placeholder="Ex: Marsa Express" maxlength="100">
                        <i class="field-icon fas fa-ship"></i>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Nom du navire requis</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reservation_type_marchandise">Type de marchandise</label>
                        <select id="reservation_type_marchandise" name="type_marchandise" required>
                            <option value="">Sélectionner le type</option>
                            <option value="conteneurs">Conteneurs</option>
                            <option value="vrac_solide">Vrac solide</option>
                            <option value="vrac_liquide">Vrac liquide</option>
                            <option value="roulier">Roulier</option>
                            <option value="general">Marchandise générale</option>
                        </select>
                        <i class="field-icon fas fa-boxes"></i>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Type de marchandise requis</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reservation_description">Description (optionnel)</label>
                        <textarea id="reservation_description" name="description" placeholder="Informations supplémentaires sur la réservation..." rows="3" maxlength="500"></textarea>
                        <i class="field-icon fas fa-comment"></i>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="closeReservationModal()">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button type="submit" class="add-btn" id="submitReservationBtn">
                            <span class="loading-spinner"></span>
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal pour ajouter un utilisateur -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="userModalTitle">Nouvel Utilisateur</h2>
                <span class="close-modal" onclick="closeUserModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="add-user-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="user_nom">Nom</label>
                            <input type="text" id="user_nom" name="nom" required placeholder="Ex: Benali" maxlength="50">
                            <i class="field-icon fas fa-user"></i>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>Nom requis</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="user_prenom">Prénom</label>
                            <input type="text" id="user_prenom" name="prenom" required placeholder="Ex: Ahmed" maxlength="50">
                            <i class="field-icon fas fa-user"></i>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>Prénom requis</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="user_email">Email</label>
                        <input type="email" id="user_email" name="email" required placeholder="Ex: ahmed.benali@entreprise.ma" maxlength="100">
                        <i class="field-icon fas fa-envelope"></i>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Email valide requis</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="user_username">Nom d'utilisateur</label>
                        <input type="text" id="user_username" name="username" required placeholder="Ex: abenali" maxlength="50">
                        <i class="field-icon fas fa-at"></i>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Nom d'utilisateur requis</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="user_password">Mot de passe</label>
                        <input type="password" id="user_password" name="password" required placeholder="Mot de passe sécurisé" minlength="6">
                        <i class="field-icon fas fa-lock"></i>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Mot de passe requis (min 6 caractères)</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="user_role">Rôle</label>
                        <select id="user_role" name="role" required>
                            <option value="">Sélectionner le rôle</option>
                            <option value="user">👤 Utilisateur</option>
                            <option value="manager">👨‍💼 Manager</option>
                            <option value="admin">👑 Administrateur</option>
                        </select>
                        <i class="field-icon fas fa-user-tag"></i>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Rôle requis</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="user_entreprise">Entreprise</label>
                        <input type="text" id="user_entreprise" name="entreprise" placeholder="Ex: Marsa Maroc" maxlength="100">
                        <i class="field-icon fas fa-building"></i>
                    </div>
                    <div class="form-group">
                        <label for="user_telephone">Téléphone</label>
                        <input type="tel" id="user_telephone" name="telephone" placeholder="Ex: +212 6 12 34 56 78" maxlength="20">
                        <i class="field-icon fas fa-phone"></i>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="closeUserModal()">
                            <i class="fas fa-times"></i> Annuler
                        </button>
                        <button type="submit" class="add-btn" id="submitUserBtn">
                            <span class="loading-spinner"></span>
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Gestionnaires d'événements pour les formulaires de réservation et utilisateur
        document.addEventListener('DOMContentLoaded', function() {
            // Gestionnaire pour le formulaire de réservation
            const reservationForm = document.getElementById('add-reservation-form');
            if (reservationForm) {
                reservationForm.addEventListener('submit', handleReservationSubmit);
            }

            // Gestionnaire pour le formulaire d'utilisateur
            const userForm = document.getElementById('add-user-form');
            if (userForm) {
                userForm.addEventListener('submit', handleUserSubmit);
            }

            // Gestionnaires pour les boutons d'action
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('action-btn') || e.target.closest('.action-btn')) {
                    const button = e.target.classList.contains('action-btn') ? e.target : e.target.closest('.action-btn');
                    const action = button.getAttribute('data-action');
                    const id = button.getAttribute('data-id');
                    
                    handleActionButton(action, id);
                }
            });
        });

        // Fonction pour gérer la soumission du formulaire de réservation
        async function handleReservationSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const submitBtn = document.getElementById('submitReservationBtn');
            
            try {
                // Afficher le loader
                submitBtn.querySelector('.loading-spinner').style.display = 'inline-block';
                submitBtn.disabled = true;

                // Collecter les données du formulaire
                const formData = new FormData(form);
                
                // Calculer le montant total basé sur l'emplacement et la durée
                const dateDebut = new Date(formData.get('date_debut'));
                const dateFin = new Date(formData.get('date_fin'));
                const dureeJours = Math.ceil((dateFin - dateDebut) / (1000 * 60 * 60 * 24));
                
                // Pour l'instant, on utilise un tarif fixe de 500 MAD par jour
                // Plus tard, on pourra récupérer le tarif réel de l'emplacement
                const montantTotal = dureeJours * 500;

                const reservationData = {
                    user_id: formData.get('user_id'),
                    emplacement_id: formData.get('emplacement_id'),
                    date_debut: formData.get('date_debut'),
                    date_fin: formData.get('date_fin'),
                    montant_total: montantTotal,
                    commentaire: formData.get('description') || ''
                };

                // Envoyer les données à l'API
                const response = await fetch('../../api/reservations-real.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(reservationData)
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Réservation créée avec succès', 'success');
                    closeReservationModal();
                    form.reset();
                    // Recharger les réservations si on est sur cette section
                    if (document.getElementById('reservations-section').style.display !== 'none') {
                        loadReservations();
                    }
                    // Mettre à jour les statistiques
                    loadStats();
                } else {
                    throw new Error(result.error || 'Erreur lors de la création de la réservation');
                }

            } catch (error) {
                console.error('Erreur:', error);
                showNotification(error.message, 'error');
            } finally {
                // Masquer le loader
                submitBtn.querySelector('.loading-spinner').style.display = 'none';
                submitBtn.disabled = false;
            }
        }

        // Fonction pour gérer la soumission du formulaire d'utilisateur
        async function handleUserSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const submitBtn = document.getElementById('submitUserBtn');
            
            try {
                // Afficher le loader
                submitBtn.querySelector('.loading-spinner').style.display = 'inline-block';
                submitBtn.disabled = true;

                // Collecter les données du formulaire
                const formData = new FormData(form);
                const userData = {
                    username: formData.get('username'),
                    email: formData.get('email'),
                    full_name: formData.get('full_name'),
                    password: formData.get('password'),
                    role: formData.get('role'),
                    company_name: formData.get('entreprise') || '',
                    phone: formData.get('telephone') || ''
                };

                // Envoyer les données à l'API
                const response = await fetch('../../api/users-real.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(userData)
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Utilisateur créé avec succès', 'success');
                    closeUserModal();
                    form.reset();
                    // Recharger les utilisateurs si on est sur cette section
                    if (document.getElementById('users-section').style.display !== 'none') {
                        loadUsers();
                    }
                    // Mettre à jour les statistiques
                    loadStats();
                } else {
                    throw new Error(result.message || 'Erreur lors de la création de l\'utilisateur');
                }

            } catch (error) {
                console.error('Erreur:', error);
                showNotification(error.message, 'error');
            } finally {
                // Masquer le loader
                submitBtn.querySelector('.loading-spinner').style.display = 'none';
                submitBtn.disabled = false;
            }
        }

        // Fonction pour gérer les boutons d'action
        async function handleActionButton(action, id) {
            switch (action) {
                case 'edit-emplacement':
                    await editEmplacement(id);
                    break;
                case 'delete-emplacement':
                    await deleteEmplacement(id);
                    break;
                case 'validate-reservation':
                    await validateReservation(id);
                    break;
                case 'reject-reservation':
                    await rejectReservation(id);
                    break;
                case 'view-reservation':
                    await viewReservation(id);
                    break;
                case 'edit-user':
                    await editUser(id);
                    break;
                case 'delete-user':
                    await deleteUser(id);
                    break;
                default:
                    console.warn('Action non reconnue:', action);
            }
        }

        // Fonctions spécifiques pour chaque action
        async function editEmplacement(id) {
            try {
                const response = await fetch(`../../api/emplacements-fixed.php?id=${id}`);
                const result = await response.json();
                
                if (result.success && result.data) {
                    showEmplacementModal(result.data);
                } else {
                    throw new Error('Impossible de charger les données de l\'emplacement');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur lors du chargement de l\'emplacement', 'error');
            }
        }

        async function deleteEmplacement(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet emplacement ?')) {
                return;
            }

            try {
                const response = await fetch(`../../api/emplacements-fixed.php?id=${id}`, {
                    method: 'DELETE'
                });
                const result = await response.json();

                if (result.success) {
                    showNotification('Emplacement supprimé avec succès', 'success');
                    loadEmplacements();
                    loadStats();
                } else {
                    throw new Error(result.error || 'Erreur lors de la suppression');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification(error.message, 'error');
            }
        }

        async function validateReservation(id) {
            try {
                const response = await fetch('../../api/reservations-real.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id,
                        statut: 'validee'
                    })
                });
                const result = await response.json();

                if (result.success) {
                    showNotification('Réservation validée avec succès', 'success');
                    loadReservations();
                    loadStats();
                } else {
                    throw new Error(result.error || 'Erreur lors de la validation');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification(error.message, 'error');
            }
        }

        async function rejectReservation(id) {
            if (!confirm('Êtes-vous sûr de vouloir rejeter cette réservation ?')) {
                return;
            }

            try {
                const response = await fetch('../../api/reservations-real.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id,
                        statut: 'refusee'
                    })
                });
                const result = await response.json();

                if (result.success) {
                    showNotification('Réservation rejetée', 'success');
                    loadReservations();
                    loadStats();
                } else {
                    throw new Error(result.error || 'Erreur lors du rejet');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification(error.message, 'error');
            }
        }

        async function viewReservation(id) {
            try {
                const response = await fetch(`../../api/reservations-real.php?id=${id}`);
                const result = await response.json();
                
                if (result.success && result.data) {
                    // Ouvrir le modal en mode lecture seule
                    showReservationModal(result.data, true);
                } else {
                    throw new Error('Impossible de charger les données de la réservation');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur lors du chargement de la réservation', 'error');
            }
        }

        async function editUser(id) {
            try {
                const response = await fetch(`../../api/users-real.php?id=${id}`);
                const result = await response.json();
                
                if (result.success && result.data) {
                    showUserModal(result.data);
                } else {
                    throw new Error('Impossible de charger les données de l\'utilisateur');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('Erreur lors du chargement de l\'utilisateur', 'error');
            }
        }

        async function deleteUser(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
                return;
            }

            try {
                const response = await fetch(`../../api/users-real.php?id=${id}`, {
                    method: 'DELETE'
                });
                const result = await response.json();

                if (result.success) {
                    showNotification('Utilisateur supprimé avec succès', 'success');
                    loadUsers();
                    loadStats();
                } else {
                    throw new Error(result.error || 'Erreur lors de la suppression');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification(error.message, 'error');
            }
        }
    </script>
</body>
</html>
