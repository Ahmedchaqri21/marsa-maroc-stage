<?php
/**
 * Get User Data API
 * Returns a single user's data for editing
 */

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors to client

// Set JSON content type
header('Content-Type: application/json');

// Check authentication
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Validate request
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'Invalid user ID']);
    exit;
}

try {
    // Get database connection
    require_once __DIR__ . '/config/database.php';
    $pdo = getDBConnection();
    
    // Prepare query
    $stmt = $pdo->prepare("SELECT id, username, email, full_name, role, status FROM users WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    
    // Get user data
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Return user data
        echo json_encode($user);
    } else {
        // User not found
        echo json_encode(['error' => 'User not found']);
    }
} catch (Exception $e) {
    // Log error and return generic message
    error_log('Error in get-user.php: ' . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
}
?>
