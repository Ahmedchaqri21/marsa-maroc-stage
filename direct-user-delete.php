<?php
/**
 * Direct User Delete API
 * Deletes a user directly from the database
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

// Check for DELETE method (or POST with _method=DELETE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
    $method = 'DELETE';
    $id = $_POST['id'] ?? null;
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $method = 'DELETE';
    parse_str(file_get_contents('php://input'), $data);
    $id = $data['id'] ?? null;
} else {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Validate user ID
if (!$id || !is_numeric($id)) {
    echo json_encode(['error' => 'Valid user ID is required']);
    exit;
}

try {
    // Get database connection
    require_once __DIR__ . '/config/database.php';
    $pdo = getDBConnection();
    
    // Prevent deleting the current user
    if ((int)$id === (int)$_SESSION['user_id']) {
        echo json_encode(['error' => 'Cannot delete your own account']);
        exit;
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Prepare and execute delete statement
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $success = $stmt->execute();
    
    if ($success && $stmt->rowCount() > 0) {
        $pdo->commit();
        echo json_encode([
            'success' => true, 
            'message' => 'User deleted successfully'
        ]);
    } else {
        $pdo->rollBack();
        echo json_encode(['error' => 'User not found or could not be deleted']);
    }
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    // Log error and return generic message
    error_log('Error in direct-user-delete.php: ' . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
}
?>
