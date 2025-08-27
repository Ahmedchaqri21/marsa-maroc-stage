<?php
// Clean output buffer to prevent any unwanted output
ob_start();

session_start();

// Set JSON headers immediately
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Clean any output that might have occurred
ob_clean();

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['username']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Nom d\'utilisateur et mot de passe requis']);
    exit;
}

$username = trim($data['username']);
$password = $data['password'];

try {
    $pdo = getDBConnection();
    
    // Debug: Log the login attempt
    error_log("Login attempt for username: " . $username);
    
    // Check if user exists by username or email
    $stmt = $pdo->prepare("
        SELECT id, username, email, password, full_name, role, status, last_login 
        FROM users 
        WHERE (username = ? OR email = ?) AND status = 'active'
    ");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    // Debug: Log if user was found
    if (!$user) {
        error_log("User not found for: " . $username);
        http_response_code(401);
        echo json_encode(['error' => 'Nom d\'utilisateur ou mot de passe incorrect']);
        exit;
    }
    
    error_log("User found: " . $user['username'] . " with role: " . $user['role']);
    
    // Check password (plain text comparison)
    if ($password !== $user['password']) {
        error_log("Password verification failed for user: " . $username);
        http_response_code(401);
        echo json_encode(['error' => 'Nom d\'utilisateur ou mot de passe incorrect']);
        exit;
    }
    
    error_log("Password verification successful for user: " . $username);
    
    // Update last login
    $updateStmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
    $updateStmt->execute([$user['id']]);
    
    // Détruire toute session existante pour éviter les problèmes
    session_unset();
    session_destroy();
    
    // Redémarrer une session propre
    session_start();
    
    // Créer une nouvelle session avec les données utilisateur
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    
    // Log successful login
    $logStmt = $pdo->prepare("
        INSERT INTO audit_log (user_id, action, table_name, record_id, new_values, ip_address) 
        VALUES (?, 'LOGIN', 'users', ?, ?, ?)
    ");
    $logStmt->execute([
        $user['id'], 
        $user['id'], 
        json_encode(['login_time' => date('Y-m-d H:i:s')]),
        $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    
    // Déterminer les chemins absolus pour la redirection
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'], 2); // Remonte de deux niveaux (api/ -> racine)
    if ($scriptDir === '/') {
        $adminPath = '/admin-dashboard.php';
        $userPath = '/user-dashboard.php';
    } else {
        if (substr($scriptDir, -1) !== '/') {
            $scriptDir .= '/';
        }
        $adminPath = $scriptDir . 'admin-dashboard.php';
        $userPath = $scriptDir . 'user-dashboard.php';
    }
    
    $redirectUrl = (in_array($user['role'], ['admin', 'manager'])) ? $adminPath : $userPath;
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'last_login' => $user['last_login']
        ],
        'redirect_url' => $redirectUrl
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in auth.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de base de données']);
} catch (Exception $e) {
    error_log("General error in auth.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}
?>
