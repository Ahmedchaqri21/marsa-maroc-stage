<?php
/**
 * Direct Users Data Endpoint
 * Provides direct database access for fetching user data
 */

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors to client

// Set JSON content type
header('Content-Type: application/json');

// Check authentication
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Get database connection
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT id, username, email, full_name, phone, role, status FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($users);
} catch (Exception $e) {
    // Log error and return generic message
    error_log('Error in direct-users.php: ' . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
}
?>
