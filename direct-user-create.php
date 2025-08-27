<?php
/**
 * Direct User Create API
 * Creates a user directly in the database
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

// Check for POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Get data from POST
$data = $_POST;

// Validate required fields
if (empty($data['username']) || empty($data['email']) || empty($data['password']) || 
    empty($data['full_name']) || empty($data['role']) || empty($data['status'])) {
    echo json_encode(['error' => 'Required fields are missing']);
    exit;
}

try {
    // Get database connection
    require_once __DIR__ . '/config/database.php';
    $pdo = getDBConnection();
    
    // Hash password
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Prepare and execute insert statement
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, full_name, phone, role, status) 
        VALUES (:username, :email, :password, :full_name, :phone, :role, :status)
    ");
    
    // Bind parameters
    $stmt->bindParam(':username', $data['username']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':full_name', $data['full_name']);
    $stmt->bindParam(':phone', $data['phone']);
    $stmt->bindParam(':role', $data['role']);
    $stmt->bindParam(':status', $data['status']);
    
    // Execute statement
    $success = $stmt->execute();
    
    if ($success) {
        $pdo->commit();
        echo json_encode([
            'success' => true, 
            'message' => 'User created successfully',
            'id' => $pdo->lastInsertId()
        ]);
    } else {
        $pdo->rollBack();
        echo json_encode(['error' => 'Failed to create user']);
    }
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    // Log error and return generic message
    error_log('Error in direct-user-create.php: ' . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
