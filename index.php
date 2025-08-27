<?php
/**
 * Page d'accueil - Marsa Maroc Port Management System
 * Redirige automatiquement vers la page de connexion
 */

// Vérifier si l'utilisateur est déjà connecté
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    // Rediriger vers le dashboard approprié selon le rôle
    switch ($_SESSION['role']) {
        case 'admin':
        case 'manager':
            header('Location: pages/admin/dashboard.php');
            break;
        case 'user':
            header('Location: pages/user/dashboard.php');
            break;
        default:
            // Rôle non reconnu, déconnecter
            session_destroy();
            header('Location: pages/auth/login.php');
            break;
    }
} else {
    // Pas connecté, rediriger vers la page de connexion
    header('Location: pages/auth/login.php');
}

exit;
?>