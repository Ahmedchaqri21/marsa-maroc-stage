<?php
/**
 * Session Check - Centralizes session verification logic
 * This file should be included at the beginning of all protected pages
 */

// Prévenir toute sortie avant les redirections
ob_start();

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirects to the login page
 * @param string $message Optional message to display on login page
 */
function redirectToLogin($message = null) {
    if ($message) {
        // Optionally store a message to display on login page
        $_SESSION['login_message'] = $message;
    }
    
    // Déterminer le chemin de base
    $baseDir = dirname($_SERVER['SCRIPT_NAME']);
    
    // Si le script est directement dans le dossier racine, $baseDir sera juste un slash
    if ($baseDir === '/') {
        $loginPath = '/login.php';
    } else {
        // Sinon, assurez-vous que le chemin est correctement formaté
        if (substr($baseDir, -1) !== '/') {
            $baseDir .= '/';
        }
        $loginPath = $baseDir . 'login.php';
    }
    
    header('Location: ' . $loginPath);
    exit;
}

/**
 * Checks if the user is logged in
 * @return bool True if user is authenticated, false otherwise
 */
function isAuthenticated() {
    return isset($_SESSION['logged_in']) && 
           $_SESSION['logged_in'] === true && 
           isset($_SESSION['user_id']) && 
           isset($_SESSION['role']);
}

/**
 * Checks if the session has expired
 * @param int $maxIdleTime Maximum idle time in seconds (default: 3600 = 1 hour)
 * @return bool True if session has expired, false otherwise
 */
function isSessionExpired($maxIdleTime = 3600) {
    return !isset($_SESSION['last_activity']) || 
           (time() - $_SESSION['last_activity'] > $maxIdleTime);
}

/**
 * Updates the last activity timestamp
 */
function updateLastActivity() {
    $_SESSION['last_activity'] = time();
}

/**
 * Checks if the user has the required role
 * @param string|array $roles Single role string or array of allowed roles
 * @return bool True if user has required role, false otherwise
 */
function hasRole($roles) {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    
    if (is_array($roles)) {
        return in_array($_SESSION['role'], $roles);
    }
    
    return $_SESSION['role'] === $roles;
}

// Main session verification logic
if (!isAuthenticated()) {
    // Nettoyer tout tampon de sortie avant la redirection
    ob_end_clean();
    redirectToLogin("Veuillez vous connecter pour accéder à cette page.");
}

// Check session expiration
if (isSessionExpired()) {
    session_unset();
    session_destroy();
    // Nettoyer tout tampon de sortie avant la redirection
    ob_end_clean();
    redirectToLogin("Votre session a expiré. Veuillez vous reconnecter.");
}

// Update last activity
updateLastActivity();

// Le script peut continuer normalement
ob_end_clean();
