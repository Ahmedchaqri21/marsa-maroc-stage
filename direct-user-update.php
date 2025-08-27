<?php
/**
 * Direct User Update API
 * Updates a user directly in the database
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

// Check for PUT method (or POST with _method=PUT)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
    $method = 'PUT';
    $data = $_POST;
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $method = 'PUT';
    parse_str(file_get_contents('php://input'), $data);
} else {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Validate required fields
if (!isset($data['id']) || !is_numeric($data['id'])) {
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

if (empty($data['username']) || empty($data['email']) || empty($data['full_name']) || empty($data['role']) || empty($data['status'])) {
    echo json_encode(['error' => 'Required fields are missing']);
    exit;
}

try {
    // Get database connection
    require_once __DIR__ . '/config/database.php';
    $pdo = getDBConnection();
    
    // Start transaction
    $pdo->beginTransaction();
    
    if (!empty($data['password'])) {
        // Update with password
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            UPDATE users 
            SET username = :username, 
                email = :email, 
                password = :password,
                full_name = :full_name,
                phone = :phone,
                role = :role,
                status = :status
            WHERE id = :id
        ");
        $stmt->bindParam(':password', $password);
    } else {
        // Update without password
        $stmt = $pdo->prepare("
            UPDATE users 
            SET username = :username, 
                email = :email,
                full_name = :full_name,
                phone = :phone,
                role = :role,
                status = :status
            WHERE id = :id
        ");
    }
    
    // Bind parameters
    $stmt->bindParam(':username', $data['username']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':full_name', $data['full_name']);
    $stmt->bindParam(':phone', $data['phone']);
    $stmt->bindParam(':role', $data['role']);
    $stmt->bindParam(':status', $data['status']);
    $stmt->bindParam(':id', $data['id']);
    
    // Execute statement
    $success = $stmt->execute();
    
    if ($success) {
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        $pdo->rollBack();
        echo json_encode(['error' => 'Failed to update user']);
    }
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    // Log error and return generic message
    error_log('Error in direct-user-update.php: ' . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
}
?>
