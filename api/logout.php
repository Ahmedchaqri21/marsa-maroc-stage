<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy session
session_destroy();

// Clear session cookies
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Si c'est une requête AJAX, renvoyer un JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    echo json_encode([
        'success' => true,
        'message' => 'Déconnexion réussie'
    ]);
} else {
    // Sinon, rediriger vers la page de connexion
    header('Location: ../login.php');
    exit;
}
?>

