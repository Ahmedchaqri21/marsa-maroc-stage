<?php
// Inclusion du fichier de vérification de session
require_once 'config/session_check.php';

// Vérification du rôle - seuls les utilisateurs réguliers peuvent accéder à cette page
if (!hasRole('user')) {
    // Si ce n'est pas un utilisateur régulier, rediriger en fonction du rôle
    if (hasRole(['admin', 'manager'])) {
        // Utiliser un chemin absolu pour la redirection
        $baseDir = dirname($_SERVER['SCRIPT_NAME']);
        if ($baseDir === '/') {
            $redirectPath = '/admin-dashboard.php';
        } else {
            if (substr($baseDir, -1) !== '/') {
                $baseDir .= '/';
            }
            $redirectPath = $baseDir . 'admin-dashboard.php';
        }
        header('Location: ' . $redirectPath);
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
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            color: #ffffff;
            overflow-x: hidden;
            line-height: 1.6;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(15px);
            border-right: 1px solid rgba(56, 189, 248, 0.1);
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 50;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        .logo {
            margin-bottom: 3rem;
            text-align: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(56, 189, 248, 0.1);
        }

        .logo img {
            width: 180px;
            height: auto;
            transition: transform 0.3s ease;
        }
        
        .logo img:hover {
            transform: scale(1.05);
        }

        .menu {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .menu-item {
            margin-bottom: 0.5rem;
        }

        .menu-link {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 12px;
            color: rgba(203, 213, 225, 0.8);
            text-decoration: none;
            transition: all 0.25s ease;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .menu-link:hover {
            background: rgba(56, 189, 248, 0.1);
            color: rgba(255, 255, 255, 1);
            box-shadow: 0 2px 10px rgba(56, 189, 248, 0.15);
        }
        
        .menu-link.active {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.2) 0%, rgba(49, 130, 206, 0.2) 100%);
            color: #38bdf8;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(56, 189, 248, 0.25);
        }
        
        .menu-link.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #38bdf8;
            border-radius: 0 3px 3px 0;
        }

        .menu-link i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
            color: rgba(56, 189, 248, 0.8);
            transition: all 0.25s ease;
        }
        
        .menu-link:hover i,
        .menu-link.active i {
            color: #38bdf8;
            transform: scale(1.1);
        }

        .menu-category {
            margin-top: 2rem;
            margin-bottom: 1rem;
            padding-left: 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(56, 189, 248, 0.5);
            position: relative;
        }
        
        .menu-category::after {
            content: '';
            position: absolute;
            width: 30px;
            height: 1px;
            background: rgba(56, 189, 248, 0.3);
            bottom: -6px;
            left: 1rem;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 2rem 2.5rem;
            margin-left: 280px;
            width: calc(100% - 280px);
            transition: all 0.3s ease;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(56, 189, 248, 0.1);
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .menu-toggle {
            display: none;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .menu-toggle:hover {
            background: rgba(56, 189, 248, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(56, 189, 248, 0.15);
        }
        
        .menu-toggle::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 40%;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px 12px 0 0;
        }
        
        .menu-toggle.active {
            background: rgba(56, 189, 248, 0.25);
            color: white;
            transform: translateY(0);
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .menu-toggle.active::after {
            opacity: 0;
        }
        
        @media (max-width: 992px) {
            .menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #f8fafc;
            position: relative;
            padding-left: 1rem;
            letter-spacing: 0.5px;
        }
        
        .page-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 24px;
            background: linear-gradient(to bottom, #38bdf8, #1e40af);
            border-radius: 2px;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-size: 1rem;
            font-weight: 600;
            color: #f8fafc;
            margin-bottom: 0.2rem;
        }

        .user-role {
            font-size: 0.85rem;
            color: rgba(148, 163, 184, 0.9);
            font-weight: 500;
        }

        .user-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #38bdf8, #1e40af);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.3rem;
            color: white;
            border: 3px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .user-avatar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 50%, rgba(255, 255, 255, 0.1) 50%);
            z-index: 1;
        }

        .logout-btn {
            background: linear-gradient(135deg, #f43f5e, #9f1239);
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
            gap: 0.6rem;
            box-shadow: 0 8px 15px rgba(244, 63, 94, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .logout-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px 12px 0 0;
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, #e11d48, #881337);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(244, 63, 94, 0.3);
        }
        
        .logout-btn:active {
            transform: translateY(-1px);
        }

        /* Dashboard Content */
        .section-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 1.8rem;
            color: #e0f2fe;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .section-title i {
            color: #38bdf8;
            font-size: 1.2rem;
            background: rgba(56, 189, 248, 0.15);
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .section-content {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2.5rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(56, 189, 248, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .section-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, #38bdf8, #1e40af);
        }

        /* Profile Section */
        .profile-header {
            display: flex;
            gap: 3rem;
            margin-bottom: 2.5rem;
            align-items: center;
            padding-bottom: 2rem;
            border-bottom: 1px solid rgba(56, 189, 248, 0.1);
        }

        .profile-avatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: linear-gradient(135deg, #38bdf8, #1e40af);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            color: white;
            border: 5px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }
        
        .profile-avatar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 50%, rgba(255, 255, 255, 0.1) 50%);
            z-index: 1;
        }

        .profile-info {
            flex: 1;
        }

        .profile-name {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.7rem;
            color: white;
            letter-spacing: 0.5px;
        }

        .profile-role {
            display: inline-block;
            padding: 0.35rem 1.2rem;
            border-radius: 30px;
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            margin-bottom: 1.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 5px 15px rgba(56, 189, 248, 0.15);
        }

        .profile-data {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .profile-item {
            margin-bottom: 1rem;
            background: rgba(30, 41, 59, 0.5);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid rgba(56, 189, 248, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .profile-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border-color: rgba(56, 189, 248, 0.1);
        }
        
        .profile-item::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 40%;
            height: 40%;
            background: linear-gradient(45deg, transparent, rgba(56, 189, 248, 0.03));
            border-radius: 0 0 0 100%;
        }

        .profile-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .profile-label i {
            color: #38bdf8;
            font-size: 0.85rem;
        }

        .profile-value {
            font-size: 1.1rem;
            color: #e0f2fe;
            font-weight: 500;
        }

        /* Reservations Section */
        .data-table-container {
            position: relative;
            overflow-x: auto;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            margin-bottom: 2rem;
            border: 1px solid rgba(56, 189, 248, 0.05);
        }
        
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0;
        }
        
        .data-table thead th {
            background: linear-gradient(180deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.9) 100%);
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .data-table th, 
        .data-table td {
            padding: 1.2rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid rgba(56, 189, 248, 0.08);
        }

        .data-table th {
            font-weight: 600;
            color: #94a3b8;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .data-table td {
            font-weight: 500;
            color: #e2e8f0;
        }

        .data-table tbody tr {
            transition: all 0.2s ease;
        }

        .data-table tbody tr:hover {
            background: rgba(56, 189, 248, 0.05);
            transform: scale(1.01);
        }
        
        .data-table tbody tr:not(:last-child) {
            box-shadow: 0 1px 0 rgba(56, 189, 248, 0.05);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 1rem;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
            gap: 0.4rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .status-badge::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .status-validee {
            background: rgba(34, 197, 94, 0.15);
            color: #4ade80;
        }
        
        .status-validee::before {
            background: #4ade80;
            box-shadow: 0 0 5px #4ade80;
        }

        .status-en_attente {
            background: rgba(251, 191, 36, 0.15);
            color: #fbbf24;
        }
        
        .status-en_attente::before {
            background: #fbbf24;
            box-shadow: 0 0 5px #fbbf24;
        }

        .status-refusee {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
        }
        
        .status-refusee::before {
            background: #f87171;
            box-shadow: 0 0 5px #f87171;
        }

        .status-terminee {
            background: rgba(148, 163, 184, 0.15);
            color: #cbd5e1;
        }
        
        .status-terminee::before {
            background: #cbd5e1;
            box-shadow: 0 0 5px #cbd5e1;
        }
        
        .status-disponible {
            background: rgba(34, 197, 94, 0.15);
            color: #4ade80;
        }
        
        .status-disponible::before {
            background: #4ade80;
            box-shadow: 0 0 5px #4ade80;
        }
        
        .status-occupe {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
        }
        
        .status-occupe::before {
            background: #f87171;
            box-shadow: 0 0 5px #f87171;
        }
        
        .status-maintenance {
            background: rgba(251, 191, 36, 0.15);
            color: #fbbf24;
        }
        
        .status-maintenance::before {
            background: #fbbf24;
            box-shadow: 0 0 5px #fbbf24;
        }
        
        .status-reserve {
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
        }
        
        .status-reserve::before {
            background: #818cf8;
            box-shadow: 0 0 5px #818cf8;
        }

        /* Action buttons */
        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.25s ease;
            margin-right: 0.7rem;
            font-size: 1rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
        }
        
        .action-btn:active {
            transform: translateY(-1px);
        }

        .view-btn {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
        }

        .view-btn:hover {
            background: rgba(56, 189, 248, 0.25);
            box-shadow: 0 7px 20px rgba(56, 189, 248, 0.2);
        }
        
        .edit-btn {
            background: rgba(34, 197, 94, 0.15);
            color: #4ade80;
        }
        
        .edit-btn:hover {
            background: rgba(34, 197, 94, 0.25);
            box-shadow: 0 7px 20px rgba(34, 197, 94, 0.2);
        }
        
        .delete-btn {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
        }
        
        .delete-btn:hover {
            background: rgba(239, 68, 68, 0.25);
            box-shadow: 0 7px 20px rgba(239, 68, 68, 0.2);
        }

        /* Modals */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.85);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            overflow: auto;
            padding: 1rem;
            animation: modalFadeIn 0.3s ease;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .modal-content {
            background: linear-gradient(135deg, rgb(30, 41, 59), rgb(15, 23, 42));
            border-radius: 20px;
            padding: 2.5rem;
            min-width: 550px;
            max-width: 90%;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(56, 189, 248, 0.1);
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        
        .modal-content::-webkit-scrollbar {
            width: 8px;
        }
        
        .modal-content::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.8);
            border-radius: 10px;
        }
        
        .modal-content::-webkit-scrollbar-thumb {
            background: rgba(56, 189, 248, 0.3);
            border-radius: 10px;
        }
        
        .modal-content::-webkit-scrollbar-thumb:hover {
            background: rgba(56, 189, 248, 0.5);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(56, 189, 248, 0.1);
        }

        .modal-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #e0f2fe;
            position: relative;
            padding-left: 1rem;
            letter-spacing: 0.5px;
        }
        
        .modal-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 20px;
            background: #38bdf8;
            border-radius: 3px;
        }

        .close-modal {
            background: rgba(239, 68, 68, 0.1);
            border: none;
            color: #f87171;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.25s ease;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-modal:hover {
            background: rgba(239, 68, 68, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }
        
        .close-modal:active {
            transform: translateY(0);
        }

        /* Form styles */
        .form-group {
            margin-bottom: 2rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.8rem;
            font-size: 0.95rem;
            font-weight: 600;
            color: #94a3b8;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            border: 1px solid rgba(56, 189, 248, 0.1);
            background: rgba(15, 23, 42, 0.5);
            color: #e0f2fe;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            letter-spacing: 0.3px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .form-control:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.3);
            background: rgba(30, 41, 59, 0.7);
        }
        
        .form-control::placeholder {
            color: #64748b;
        }
        
        .form-control:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2.5rem;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 1rem;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 40%;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px 12px 0 0;
        }
        
        .btn i {
            font-size: 1.1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #38bdf8, #0369a1);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(56, 189, 248, 0.25);
        }
        
        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: rgba(71, 85, 105, 0.2);
            color: #cbd5e1;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(71, 85, 105, 0.1);
        }

        .btn-secondary:hover {
            background: rgba(71, 85, 105, 0.3);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .btn-secondary:active {
            transform: translateY(-1px);
        }
        
        /* Notification toast */
        .notification {
            position: fixed;
            top: 30px;
            right: 30px;
            padding: 1.25rem 1.75rem;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            z-index: 1100;
            transform: translateY(-100px);
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }
        
        .notification i {
            font-size: 1.2rem;
        }

        .notification.show {
            transform: translateY(0);
            opacity: 1;
        }

        .notification.success {
            background: linear-gradient(135deg, #22c55e, #15803d);
            border-left: 5px solid #4ade80;
        }

        .notification.error {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            border-left: 5px solid #f87171;
        }

        .notification.info {
            background: linear-gradient(135deg, #38bdf8, #0369a1);
            border-left: 5px solid #7dd3fc;
        }
        
        /* Responsive Styles */
        @media (max-width: 1200px) {
            .profile-data {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 240px;
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 2rem;
            }
            
            .modal-content {
                min-width: 95%;
            }
        }
        
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 1.5rem;
            }
            
            .data-table {
                display: block;
                overflow-x: auto;
            }
            
            .data-table th, 
            .data-table td {
                padding: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .header {
                flex-direction: column;
                gap: 1.5rem;
                align-items: flex-start;
            }
            
            .user-menu {
                width: 100%;
                justify-content: space-between;
            }
            
            .btn {
                padding: 0.75rem 1.5rem;
            }
            
            .section-content {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <img src="assets/images/unnamed.png" alt="Marsa Maroc Logo">
            </div>
            
            <ul class="menu">
                <li class="menu-item">
                    <a href="#" class="menu-link active" data-section="profile-section">
                        <i class="fas fa-user"></i> Mon Profil
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link" data-section="reservations-section">
                        <i class="fas fa-calendar-check"></i> Mes Réservations
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link" data-section="emplacements-section">
                        <i class="fas fa-map-marker-alt"></i> Emplacements
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link" data-section="new-reservation-section">
                        <i class="fas fa-plus-circle"></i> Nouvelle Réservation
                    </a>
                </li>
            </ul>
            
            <div class="menu-category">Aide</div>
            
            <ul class="menu">
                <li class="menu-item">
                    <a href="#" class="menu-link" data-section="support-section">
                        <i class="fas fa-question-circle"></i> Support
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="header-left">
                    <button id="menuToggle" class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">Tableau de Bord</h1>
                </div>
                
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                        <div class="user-role">Utilisateur</div>
                    </div>
                    
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                    
                    <a href="login.php" class="logout-btn" onclick="return logout()">
                        <i class="fas fa-sign-out-alt"></i>
                        Déconnexion
                    </a>
                </div>
            </div>
            
            <!-- Profile Section -->
            <div id="profile-section">
                <h2 class="section-title"><i class="fas fa-user-circle"></i> Mon Profil</h2>
                
                <div class="section-content">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                        </div>
                        
                        <div class="profile-info">
                            <h3 class="profile-name"><?php echo htmlspecialchars($user_name); ?></h3>
                            <div class="profile-role">Client</div>
                            <button class="btn btn-primary" onclick="openModal('editProfileModal')">
                                <i class="fas fa-edit"></i> Modifier mon profil
                            </button>
                        </div>
                    </div>
                    
                    <div class="profile-data" id="user-profile-data">
                        <div class="profile-item">
                            <div class="profile-label">Email</div>
                            <div class="profile-value" id="profile-email">Chargement...</div>
                        </div>
                        
                        <div class="profile-item">
                            <div class="profile-label">Téléphone</div>
                            <div class="profile-value" id="profile-phone">Chargement...</div>
                        </div>
                        
                        <div class="profile-item">
                            <div class="profile-label">Entreprise</div>
                            <div class="profile-value" id="profile-company">Chargement...</div>
                        </div>
                        
                        <div class="profile-item">
                            <div class="profile-label">ID Fiscal</div>
                            <div class="profile-value" id="profile-tax">Chargement...</div>
                        </div>
                        
                        <div class="profile-item">
                            <div class="profile-label">Adresse</div>
                            <div class="profile-value" id="profile-address">Chargement...</div>
                        </div>
                        
                        <div class="profile-item">
                            <div class="profile-label">Membre depuis</div>
                            <div class="profile-value" id="profile-created">Chargement...</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reservations Section -->
            <div id="reservations-section" style="display: none;">
                <h2 class="section-title"><i class="fas fa-calendar-alt"></i> Mes Réservations</h2>
                
                <div class="section-content">
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Numéro</th>
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
                                    <td colspan="8" style="text-align: center; color: #b8c5d6; padding: 2rem;">
                                        Chargement des réservations...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Emplacements Section -->
            <div id="emplacements-section" style="display: none;">
                <h2 class="section-title"><i class="fas fa-map-marker-alt"></i> Emplacements Disponibles</h2>
                
                <div class="section-content">
                    <div class="data-table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Code</th>
                                    <th>Nom</th>
                                    <th>Type</th>
                                    <th>Superficie (m²)</th>
                                    <th>Tarif (€/jour)</th>
                                    <th>État</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" style="text-align: center; color: #b8c5d6; padding: 2rem;">
                                    Chargement des emplacements...
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- New Reservation Section -->
            <div id="new-reservation-section" style="display: none;">
                <h2 class="section-title"><i class="fas fa-plus-circle"></i> Nouvelle Réservation</h2>                <div class="section-content">
                    <form id="reservationForm" onsubmit="createReservation(event)">
                        <div class="form-group">
                            <label class="form-label" for="emplacement_id">Emplacement</label>
                            <select class="form-control" id="emplacement_id" name="emplacement_id" required>
                                <option value="">Sélectionnez un emplacement</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="date_debut">Date de début</label>
                            <input type="datetime-local" class="form-control" id="date_debut" name="date_debut" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="date_fin">Date de fin</label>
                            <input type="datetime-local" class="form-control" id="date_fin" name="date_fin" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="montant_total">Montant total estimé</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="montant_total" name="montant_total" readonly required>
                                <div class="input-group-append">
                                    <span class="input-group-text">MAD</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Le montant est calculé automatiquement en fonction de l'emplacement et de la durée.</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="commentaire">Commentaire (optionnel)</label>
                            <textarea class="form-control" id="commentaire" name="commentaire" rows="3"></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Soumettre la demande</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Support Section -->
            <div id="support-section" style="display: none;">
                <h2 class="section-title"><i class="fas fa-life-ring"></i> Support</h2>
                
                <div class="section-content">
                    <h3 style="color: white; margin-bottom: 1rem;">Besoin d'aide ?</h3>
                    <p style="color: #b8c5d6; margin-bottom: 2rem;">
                        Si vous avez des questions ou rencontrez des difficultés, n'hésitez pas à contacter notre équipe support.
                    </p>
                    
                    <div class="profile-data">
                        <div class="profile-item">
                            <div class="profile-label">Email Support</div>
                            <div class="profile-value">support@marsamaroc.co.ma</div>
                        </div>
                        
                        <div class="profile-item">
                            <div class="profile-label">Téléphone Support</div>
                            <div class="profile-value">+212 522 77 30 30</div>
                        </div>
                        
                        <div class="profile-item">
                            <div class="profile-label">Heures d'ouverture</div>
                            <div class="profile-value">Du lundi au vendredi, 8h30 - 17h00</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Profile Modal -->
    <div class="modal" id="editProfileModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Modifier mon Profil</h3>
                <button class="close-modal" onclick="closeModal('editProfileModal')">&times;</button>
            </div>
            
            <form id="profileForm" onsubmit="updateProfile(event)">
                <div class="form-group">
                    <label class="form-label" for="full_name">Nom complet</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="phone">Téléphone</label>
                    <input type="text" class="form-control" id="phone" name="phone">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="company_name">Entreprise</label>
                    <input type="text" class="form-control" id="company_name" name="company_name">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="tax_id">ID Fiscal</label>
                    <input type="text" class="form-control" id="tax_id" name="tax_id">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="address">Adresse</label>
                    <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Nouveau mot de passe (laisser vide pour ne pas modifier)</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editProfileModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary">Sauvegarder</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- View Reservation Modal -->
    <div class="modal" id="viewReservationModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Détails de la Réservation</h3>
                <button class="close-modal" onclick="closeModal('viewReservationModal')">&times;</button>
            </div>
            
            <div id="reservationDetails" style="color: white;">
                <div class="profile-data">
                    <div class="profile-item">
                        <div class="profile-label">Numéro de réservation</div>
                        <div class="profile-value" id="res-numero">Chargement...</div>
                    </div>
                    
                    <div class="profile-item">
                        <div class="profile-label">Emplacement</div>
                        <div class="profile-value" id="res-emplacement">Chargement...</div>
                    </div>
                    
                    <div class="profile-item">
                        <div class="profile-label">Date de début</div>
                        <div class="profile-value" id="res-debut">Chargement...</div>
                    </div>
                    
                    <div class="profile-item">
                        <div class="profile-label">Date de fin</div>
                        <div class="profile-value" id="res-fin">Chargement...</div>
                    </div>
                    
                    <div class="profile-item">
                        <div class="profile-label">Durée</div>
                        <div class="profile-value" id="res-duree">Chargement...</div>
                    </div>
                    
                    <div class="profile-item">
                        <div class="profile-label">Statut</div>
                        <div class="profile-value" id="res-statut">Chargement...</div>
                    </div>
                    
                    <div class="profile-item">
                        <div class="profile-label">Montant total</div>
                        <div class="profile-value" id="res-montant">Chargement...</div>
                    </div>
                    
                    <div class="profile-item">
                        <div class="profile-label">Mode de paiement</div>
                        <div class="profile-value" id="res-paiement">Chargement...</div>
                    </div>
                    
                    <div class="profile-item">
                        <div class="profile-label">Commentaire</div>
                        <div class="profile-value" id="res-commentaire">-</div>
                    </div>
                    
                    <div id="res-refus-container" class="profile-item" style="display: none;">
                        <div class="profile-label">Motif de refus</div>
                        <div class="profile-value" id="res-refus">-</div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('viewReservationModal')">Fermer</button>
            </div>
        </div>
    </div>
    
    <!-- Notification -->
    <div class="notification" id="notification"></div>
    
    <script>
        // Variable pour stocker l'ID utilisateur
        const USER_ID = <?php echo json_encode($user_id); ?>;
        
        // Fonctions de navigation
        document.addEventListener('DOMContentLoaded', function() {
            // Afficher une notification de bienvenue
            showNotification(`Bienvenue sur votre tableau de bord, ${document.querySelector('.user-name').textContent}`, 'info');
            
            // Gestion du menu et sections
            const menuLinks = document.querySelectorAll('.menu-link');
            const sections = document.querySelectorAll('[id$="-section"]');
            
            menuLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Désactiver tous les liens
                    menuLinks.forEach(item => item.classList.remove('active'));
                    
                    // Activer le lien cliqué
                    this.classList.add('active');
                    
                    // Cacher toutes les sections
                    sections.forEach(section => section.style.display = 'none');
                    
                    // Afficher la section correspondante
                    const targetSection = document.getElementById(this.dataset.section);
                    if (targetSection) {
                        targetSection.style.display = 'block';
                        
                        // Fermer le menu mobile si ouvert
                        if (window.innerWidth < 992) {
                            document.querySelector('.sidebar').classList.remove('show');
                        }
                        
                        // Charger les données si nécessaire
                        if (this.dataset.section === 'profile-section') {
                            loadProfile();
                        } else if (this.dataset.section === 'reservations-section') {
                            loadUserReservations();
                        } else if (this.dataset.section === 'emplacements-section') {
                            loadEmplacements();
                        } else if (this.dataset.section === 'new-reservation-section') {
                            loadEmplacementsForSelect();
                        }
                    }
                });
            });
            
            // Charger le profil au démarrage
            loadProfile();
        });
        
        // Fonction pour charger le profil de l'utilisateur
        async function loadProfile() {
            try {
                console.log('Chargement du profil...');
                const response = await fetch(`api/users-real.php?id=${USER_ID}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                console.log('Profil reçu:', result);
                
                if (!result.success) {
                    throw new Error(result.message || 'Erreur lors du chargement du profil');
                }
                
                // Récupérer l'objet utilisateur depuis la réponse
                const userData = result.data;
                
                // Débogage - Afficher les données brutes
                console.log('Données utilisateur:', {
                    email: userData.email,
                    phone: userData.phone,
                    company: userData.company_name,
                    tax: userData.tax_id,
                    address: userData.address,
                    created: userData.created_at
                });
                
                // Mettre à jour les données du profil
                document.getElementById('profile-email').textContent = userData.email || '-';
                document.getElementById('profile-phone').textContent = userData.phone || '-';
                document.getElementById('profile-company').textContent = userData.company_name || '-';
                document.getElementById('profile-tax').textContent = userData.tax_id || '-';
                document.getElementById('profile-address').textContent = userData.address || '-';
                
                // Formater la date de création
                if (userData.created_at) {
                    const createdDate = new Date(userData.created_at.replace(/(\d+)\/(\d+)\/(\d+)/, '$3-$2-$1'));
                    document.getElementById('profile-created').textContent = createdDate.toLocaleDateString('fr-FR');
                } else {
                    document.getElementById('profile-created').textContent = '-';
                }
                
                // Remplir le formulaire de profil
                document.getElementById('full_name').value = userData.full_name || '';
                document.getElementById('email').value = userData.email || '';
                document.getElementById('phone').value = userData.phone || '';
                document.getElementById('company_name').value = userData.company_name || '';
                document.getElementById('tax_id').value = userData.tax_id || '';
                document.getElementById('address').value = userData.address || '';
                
            } catch (error) {
                console.error('Erreur lors du chargement du profil:', error);
                showNotification(`Erreur: ${error.message}`, 'error');
            }
        }
        
        // Fonction pour mettre à jour le profil
        async function updateProfile(event) {
            event.preventDefault();
            
            try {
                const formData = new FormData(document.getElementById('profileForm'));
                const data = Object.fromEntries(formData.entries());
                
                // Si le mot de passe est vide, le supprimer
                if (!data.password) {
                    delete data.password;
                }
                
                console.log('Mise à jour du profil:', data);
                
                const response = await fetch(`api/users-real.php?id=${USER_ID}`, {
                    method: 'PUT',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`HTTP Error: ${response.status} - ${errorText}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Profil mis à jour avec succès', 'success');
                    closeModal('editProfileModal');
                    loadProfile();
                } else {
                    throw new Error(result.error || 'Une erreur est survenue');
                }
                
            } catch (error) {
                console.error('Erreur lors de la mise à jour du profil:', error);
                showNotification(`Erreur: ${error.message}`, 'error');
            }
        }
        
        // Fonction pour charger les réservations de l'utilisateur
        async function loadUserReservations() {
            try {
                console.log('Chargement des réservations...');
                const response = await fetch(`api/reservations-real.php?user_id=${USER_ID}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
                }
                
                const text = await response.text();
                
                let reservations;
                try {
                    reservations = JSON.parse(text);
                    // Check if data is wrapped in an object
                    if (!Array.isArray(reservations)) {
                        if (reservations.data && Array.isArray(reservations.data)) {
                            reservations = reservations.data;
                        } else {
                            throw new Error('Format de données inattendu');
                        }
                    }
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    throw new Error('Response is not valid JSON: ' + text.substring(0, 100));
                }
                
                console.log('Réservations reçues:', reservations);
                
                const tbody = document.querySelector('#reservations-section table tbody');
                if (!tbody) {
                    console.error('Table des réservations introuvable');
                    return;
                }
                
                tbody.innerHTML = '';
                
                if (Array.isArray(reservations) && reservations.length > 0) {
                    reservations.forEach(res => {
                        // Safe access to properties
                        const id = res.id || '-';
                        const numero = res.numero_reservation || '-';
                        const emplacement = res.emplacement_nom || '-';
                        const dateDebut = res.date_debut || '-';
                        const dateFin = res.date_fin || '-';
                        const statut = res.statut || 'en_attente';
                        const montant = res.montant_total || '0';
                        
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${id}</td>
                            <td>${numero}</td>
                            <td>${emplacement}</td>
                            <td>${dateDebut}</td>
                            <td>${dateFin}</td>
                            <td><span class="status-badge status-${statut}">${statut}</span></td>
                            <td>€${montant}</td>
                            <td>
                                <button data-action="view-reservation" data-id="${id}" class="action-btn view-btn">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                        
                        // Ajouter l'événement de clic pour voir les détails
                        row.querySelector('[data-action="view-reservation"]').addEventListener('click', function() {
                            viewReservation(id);
                        });
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: #b8c5d6;">Aucune réservation trouvée</td></tr>';
                }
            } catch (error) {
                console.error('Erreur lors du chargement des réservations:', error);
                const tbody = document.querySelector('#reservations-section table tbody');
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: #fa709a;">Erreur: ${error.message}</td></tr>`;
                }
                showNotification(`Erreur: ${error.message}`, 'error');
            }
        }
        
        // Fonction pour charger les emplacements disponibles
        async function loadEmplacements() {
            try {
                console.log('Chargement des emplacements...');
                const response = await fetch('api/emplacements-real.php?etat=disponible');
                
                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
                }
                
                const text = await response.text();
                
                let emplacements;
                try {
                    emplacements = JSON.parse(text);
                    // Check if data is wrapped in an object
                    if (!Array.isArray(emplacements)) {
                        if (emplacements.data && Array.isArray(emplacements.data)) {
                            emplacements = emplacements.data;
                        } else {
                            throw new Error('Format de données inattendu');
                        }
                    }
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    throw new Error('Response is not valid JSON: ' + text.substring(0, 100));
                }
                
                console.log('Emplacements reçus:', emplacements);
                
                const tbody = document.querySelector('#emplacements-section table tbody');
                if (!tbody) {
                    console.error('Table des emplacements introuvable');
                    return;
                }
                
                tbody.innerHTML = '';
                
                if (Array.isArray(emplacements) && emplacements.length > 0) {
                    emplacements.forEach(emp => {
                        // Safe access to properties
                        const id = emp.id || '-';
                        const code = emp.code || '-';
                        const nom = emp.nom || '-';
                        const type = emp.type || '-';
                        const superficie = emp.superficie || '0';
                        const tarif = emp.tarif_journalier || '0';
                        const etat = emp.etat || 'disponible';
                        
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${id}</td>
                            <td>${code}</td>
                            <td>${nom}</td>
                            <td>${type}</td>
                            <td>${superficie}</td>
                            <td>€${tarif}</td>
                            <td><span class="status-badge status-${etat}">${etat}</span></td>
                            <td>
                                <button data-action="reserve-emplacement" data-id="${id}" class="action-btn view-btn">
                                    <i class="fas fa-calendar-plus"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                        
                        // Ajouter l'événement de clic pour réserver
                        row.querySelector('[data-action="reserve-emplacement"]').addEventListener('click', function() {
                            // Rediriger vers la section de nouvelle réservation et pré-sélectionner l'emplacement
                            document.querySelector('.menu-link[data-section="new-reservation-section"]').click();
                            document.getElementById('emplacement_id').value = id;
                        });
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: #b8c5d6;">Aucun emplacement disponible trouvé</td></tr>';
                }
            } catch (error) {
                console.error('Erreur lors du chargement des emplacements:', error);
                const tbody = document.querySelector('#emplacements-section table tbody');
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: #fa709a;">Erreur: ${error.message}</td></tr>`;
                }
                showNotification(`Erreur: ${error.message}`, 'error');
            }
        }
        
        // Stocker les informations des emplacements pour le calcul du montant
        let emplacementsData = {};
        
        // Fonction pour calculer le montant total en fonction de l'emplacement et des dates
        function calculateTotalAmount() {
            const emplacementId = document.getElementById('emplacement_id').value;
            const dateDebut = document.getElementById('date_debut').value;
            const dateFin = document.getElementById('date_fin').value;
            
            if (!emplacementId || !dateDebut || !dateFin) {
                document.getElementById('montant_total').value = '';
                return;
            }
            
            // Récupérer les informations de l'emplacement
            const emplacement = emplacementsData[emplacementId];
            if (!emplacement) {
                console.error('Informations de l\'emplacement non trouvées');
                return;
            }
            
            // Calculer le nombre de jours
            const debut = new Date(dateDebut);
            const fin = new Date(dateFin);
            
            if (isNaN(debut.getTime()) || isNaN(fin.getTime()) || fin <= debut) {
                document.getElementById('montant_total').value = '';
                return;
            }
            
            // Calculer la différence en jours
            const diffTime = Math.abs(fin - debut);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            // Si moins d'un jour, compter au minimum 1 jour
            const days = Math.max(1, diffDays);
            
            // Calculer le montant
            const montantTotal = emplacement.tarif_journalier * days;
            
            // Afficher le montant
            document.getElementById('montant_total').value = montantTotal.toFixed(2);
        }
        
        // Fonction pour charger les emplacements dans le formulaire de réservation
        async function loadEmplacementsForSelect() {
            try {
                const response = await fetch('api/emplacements-real.php?etat=disponible');
                
                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
                }
                
                let emplacements = await response.json();
                
                // Check if data is wrapped in an object
                if (!Array.isArray(emplacements)) {
                    if (emplacements.data && Array.isArray(emplacements.data)) {
                        emplacements = emplacements.data;
                    } else {
                        throw new Error('Format de données inattendu');
                    }
                }
                
                const select = document.getElementById('emplacement_id');
                
                // Vider le select sauf la première option
                while (select.options.length > 1) {
                    select.remove(1);
                }
                
                // Réinitialiser les données des emplacements
                emplacementsData = {};
                
                // Ajouter les options
                if (Array.isArray(emplacements) && emplacements.length > 0) {
                    emplacements.forEach(emp => {
                        const option = document.createElement('option');
                        option.value = emp.id;
                        option.textContent = `${emp.nom} (${emp.type}) - ${emp.superficie}m² - ${emp.tarif_journalier} MAD/jour`;
                        select.appendChild(option);
                        
                        // Stocker les informations de l'emplacement pour le calcul du montant
                        emplacementsData[emp.id] = {
                            id: emp.id,
                            nom: emp.nom,
                            type: emp.type,
                            superficie: emp.superficie,
                            tarif_journalier: parseFloat(emp.tarif_journalier),
                            tarif_horaire: parseFloat(emp.tarif_horaire),
                            tarif_mensuel: parseFloat(emp.tarif_mensuel)
                        };
                    });
                    
                    // Ajouter des écouteurs d'événements pour calculer le montant
                    document.getElementById('emplacement_id').addEventListener('change', calculateTotalAmount);
                    document.getElementById('date_debut').addEventListener('change', calculateTotalAmount);
                    document.getElementById('date_fin').addEventListener('change', calculateTotalAmount);
                } else {
                    const option = document.createElement('option');
                    option.textContent = 'Aucun emplacement disponible';
                    option.disabled = true;
                    select.appendChild(option);
                }
                
            } catch (error) {
                console.error('Erreur lors du chargement des emplacements:', error);
                showNotification(`Erreur: ${error.message}`, 'error');
            }
        }
        
        // Fonction pour créer une nouvelle réservation
        async function createReservation(event) {
            event.preventDefault();
            
            try {
                const formData = new FormData(document.getElementById('reservationForm'));
                const data = Object.fromEntries(formData.entries());
                
                // Ajouter l'ID utilisateur
                data.user_id = USER_ID;
                
                // Générer un numéro de réservation unique
                const date = new Date();
                const dateStr = date.getFullYear().toString().substr(-2) + 
                                 (date.getMonth() + 1).toString().padStart(2, '0') + 
                                 date.getDate().toString().padStart(2, '0');
                const randomNum = Math.floor(1000 + Math.random() * 9000); // 4 chiffres aléatoires
                data.numero_reservation = `R${dateStr}-${randomNum}-${USER_ID}`;
                
                // Vérifier que le montant total est bien défini
                if (!data.montant_total || isNaN(parseFloat(data.montant_total))) {
                    // Recalculer le montant total au cas où
                    calculateTotalAmount();
                    // Récupérer à nouveau la valeur
                    data.montant_total = document.getElementById('montant_total').value;
                    
                    if (!data.montant_total || isNaN(parseFloat(data.montant_total))) {
                        throw new Error('Le montant total n\'a pas pu être calculé. Veuillez vérifier les dates et l\'emplacement sélectionné.');
                    }
                }
                
                console.log('Création de réservation:', data);
                console.log('Montant total:', data.montant_total);
                
                // Convertir le montant total en nombre pour être sûr
                data.montant_total = parseFloat(data.montant_total);
                
                const response = await fetch('api/reservations-real.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });
                
                if (!response.ok) {
                    let errorMessage = `HTTP Error: ${response.status}`;
                    
                    try {
                        // Essayer de parser la réponse comme JSON
                        const errorJson = await response.json();
                        console.error('Erreur détaillée:', errorJson);
                        
                        if (errorJson.error) {
                            errorMessage = errorJson.error;
                            
                            // Si nous avons des détails SQL, les afficher
                            if (errorJson.details && errorJson.details.sqlMessage) {
                                errorMessage += ` - ${errorJson.details.sqlMessage}`;
                            }
                        }
                    } catch (e) {
                        // Si ce n'est pas du JSON, obtenir le texte brut
                        const errorText = await response.text();
                        errorMessage += ` - ${errorText}`;
                    }
                    
                    throw new Error(errorMessage);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Demande de réservation soumise avec succès', 'success');
                    document.getElementById('reservationForm').reset();
                    
                    // Rediriger vers la liste des réservations
                    document.querySelector('.menu-link[data-section="reservations-section"]').click();
                } else {
                    console.error('Erreur de réservation:', result);
                    let errorMessage = result.message || 'Une erreur est survenue';
                    
                    // Ajouter des détails supplémentaires si disponibles
                    if (result.error) {
                        errorMessage += `: ${result.error}`;
                    }
                    
                    throw new Error(errorMessage);
                }
                
            } catch (error) {
                console.error('Erreur lors de la création de la réservation:', error);
                showNotification(`Erreur: ${error.message}`, 'error');
            }
        }
        
        // Fonction pour voir les détails d'une réservation
        async function viewReservation(id) {
            try {
                const response = await fetch(`api/reservations-real.php?id=${id}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
                }
                
                const data = await response.json();
                console.log('Détails de la réservation:', data);
                
                // Afficher les détails dans la modal
                document.getElementById('res-numero').textContent = data.numero_reservation || 'N/A';
                document.getElementById('res-emplacement').textContent = data.emplacement_nom || 'N/A';
                document.getElementById('res-debut').textContent = data.date_debut || 'N/A';
                document.getElementById('res-fin').textContent = data.date_fin || 'N/A';
                document.getElementById('res-duree').textContent = `${data.duree_jours || '0'} jours`;
                document.getElementById('res-statut').textContent = data.statut || 'N/A';
                document.getElementById('res-montant').textContent = `€${data.montant_total || '0'}`;
                document.getElementById('res-paiement').textContent = data.mode_paiement || 'N/A';
                document.getElementById('res-commentaire').textContent = data.commentaire || '-';
                
                // Afficher le motif de refus si la réservation est refusée
                if (data.statut === 'refusee' && data.motif_refus) {
                    document.getElementById('res-refus-container').style.display = 'block';
                    document.getElementById('res-refus').textContent = data.motif_refus;
                } else {
                    document.getElementById('res-refus-container').style.display = 'none';
                }
                
                openModal('viewReservationModal');
                
            } catch (error) {
                console.error('Erreur lors du chargement des détails:', error);
                showNotification(`Erreur: ${error.message}`, 'error');
            }
        }
        
        // Fonction de déconnexion
        function logout() {
            // Déconnexion directe sans confirmation
            fetch('api/logout.php', {
                method: 'POST',
                credentials: 'include'
            })
            .then(response => {
                console.log('Déconnexion réussie');
                // Rediriger vers la page de connexion
                window.location.replace('login.php');
            })
            .catch(error => {
                console.error('Erreur lors de la déconnexion:', error);
                // Rediriger quand même en cas d'erreur
                window.location.replace('login.php');
            });
            
            // Empêcher le comportement par défaut du lien
            return false;
        }
        
        // Fonctions pour gérer les modals
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Fonction pour afficher les notifications
        function showNotification(message, type = 'info') {
            const notification = document.getElementById('notification');
            
            // Ajout d'une icône basée sur le type de notification
            let icon = '';
            if (type === 'success') {
                icon = '<i class="fas fa-check-circle"></i>';
            } else if (type === 'error') {
                icon = '<i class="fas fa-exclamation-circle"></i>';
            } else if (type === 'info') {
                icon = '<i class="fas fa-info-circle"></i>';
            }
            
            notification.innerHTML = `${icon} ${message}`;
            notification.className = 'notification ' + type;
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 5000);
        }
        
        // Initialiser les fonctionnalités UI
        initializeUI();
        
        // Fonction pour initialiser les éléments d'interface
        function initializeUI() {
            // Gestion du menu mobile
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (menuToggle && sidebar) {
                menuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('show');
                    
                    // Animation du bouton de menu
                    this.classList.toggle('active');
                });
                
                // Fermer le menu lorsqu'on clique en dehors
                document.addEventListener('click', function(e) {
                    if (window.innerWidth < 992 && 
                        !sidebar.contains(e.target) && 
                        e.target !== menuToggle && 
                        !menuToggle.contains(e.target) &&
                        sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                        menuToggle.classList.remove('active');
                    }
                });
                
                // Gérer le redimensionnement de la fenêtre
                window.addEventListener('resize', function() {
                    if (window.innerWidth >= 992 && sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                        if (menuToggle.classList.contains('active')) {
                            menuToggle.classList.remove('active');
                        }
                    }
                });
            }
            
            // Amélioration des liens du menu avec effets hover
            document.querySelectorAll('.menu-link').forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.3s ease';
                });
                
                link.addEventListener('mouseleave', function() {
                    this.style.transition = 'all 0.5s ease';
                });
            });
        }
    </script>
</body>
</html>
