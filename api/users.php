<?php
/**
 * API for User Management - Complete Rewrite
 * Fixed version that correctly handles all CRUD operations
 */

// Buffer control to ensure clean output
ob_start();
if (ob_get_level()) {
    ob_clean();
}

// Error handling and reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display in production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/api_errors.log');

// Set appropriate headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Helper function for consistent response format
function sendResponse($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if (!$success && empty($message)) {
        $response['message'] = 'Une erreur est survenue';
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Helper function for logging
function logMessage($message, $type = 'INFO') {
    error_log("[" . date('Y-m-d H:i:s') . "] [$type] $message");
}

try {
    // Include database configuration
    require_once __DIR__ . '/../config/database.php';
    $pdo = getDBConnection();
    
    // Log database connection success
    logMessage("Database connection established");
    
    // Determine HTTP method
    $method = $_SERVER['REQUEST_METHOD'];
    logMessage("API Request: $method " . ($_SERVER['QUERY_STRING'] ?? ''));
    
    switch ($method) {
        case 'GET':
            // Check if a specific user is requested by ID
            if (isset($_GET['id'])) {
                $userId = intval($_GET['id']);
                logMessage("Fetching user with ID: $userId");
                
                $sql = "SELECT 
                            id, username, email, full_name, phone, address, 
                            company_name, tax_id, role, status, 
                            last_login, created_at
                        FROM users 
                        WHERE id = :id";
                
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    logMessage("User found");
                    sendResponse(true, $user, 'Utilisateur récupéré avec succès');
                } else {
                    logMessage("User not found", "WARNING");
                    sendResponse(false, null, 'Utilisateur non trouvé', 404);
                }
            }
            
            // Fetch all users if no specific ID
            logMessage("Fetching all users");
            
            // Use simpler query to avoid GROUP BY issues
            $sql = "SELECT 
                        u.id,
                        u.username,
                        u.email,
                        u.full_name,
                        u.phone,
                        u.address,
                        u.company_name,
                        u.tax_id,
                        u.role,
                        u.status,
                        u.last_login,
                        u.created_at
                    FROM users u
                    ORDER BY u.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Fetch reservations count in a separate query
            $userIds = array_column($users, 'id');
            $reservationCounts = [];
            
            if (!empty($userIds)) {
                $placeholders = implode(',', array_fill(0, count($userIds), '?'));
                $countSql = "SELECT 
                                user_id, 
                                COUNT(*) as total_reservations,
                                COUNT(CASE WHEN statut = 'validee' THEN 1 END) as validated_reservations,
                                SUM(CASE WHEN statut = 'validee' THEN montant_total ELSE 0 END) as total_amount
                            FROM reservations 
                            WHERE user_id IN ($placeholders)
                            GROUP BY user_id";
                
                $countStmt = $pdo->prepare($countSql);
                $countStmt->execute($userIds);
                $countResults = $countStmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($countResults as $result) {
                    $reservationCounts[$result['user_id']] = [
                        'total' => $result['total_reservations'],
                        'validated' => $result['validated_reservations'],
                        'amount' => $result['total_amount']
                    ];
                }
            }
            
            // Format users with reservation data
            $formattedUsers = [];
            foreach ($users as $user) {
                $userId = $user['id'];
                $reservationData = $reservationCounts[$userId] ?? [
                    'total' => 0,
                    'validated' => 0,
                    'amount' => 0
                ];
                
                $formattedUsers[] = array_merge($user, [
                    'total_reservations' => $reservationData['total'],
                    'reservations_validees' => $reservationData['validated'],
                    'chiffre_affaires' => number_format($reservationData['amount'] ?: 0, 2) . ' MAD'
                ]);
            }
            
            logMessage("Found " . count($formattedUsers) . " users");
            sendResponse(true, $formattedUsers, 'Utilisateurs récupérés avec succès');
            break;
            
        case 'POST':
            // Create a new user
            $input = json_decode(file_get_contents('php://input'), true);
            logMessage("Creating new user: " . json_encode($input));
            
            // Validate required fields
            $requiredFields = ['username', 'email', 'password', 'full_name'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    logMessage("Missing required field: $field", "ERROR");
                    sendResponse(false, null, "Le champ '$field' est obligatoire", 400);
                }
            }
            
            // Check if username or email already exists
            $checkSql = "SELECT id FROM users WHERE username = :username OR email = :email";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->bindParam(':username', $input['username']);
            $checkStmt->bindParam(':email', $input['email']);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                logMessage("Username or email already exists", "ERROR");
                sendResponse(false, null, 'Nom d\'utilisateur ou email déjà utilisé', 409);
            }
            
            // Insert new user
            $sql = "INSERT INTO users 
                    (username, email, password, full_name, phone, address, 
                     company_name, tax_id, role, status) 
                    VALUES (:username, :email, :password, :full_name, :phone, :address, 
                            :company_name, :tax_id, :role, :status)";
            
            // Hash password
            $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $input['username']);
            $stmt->bindParam(':email', $input['email']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':full_name', $input['full_name']);
            $stmt->bindParam(':phone', $input['phone'] ?? '');
            $stmt->bindParam(':address', $input['address'] ?? '');
            $stmt->bindParam(':company_name', $input['company_name'] ?? '');
            $stmt->bindParam(':tax_id', $input['tax_id'] ?? '');
            $stmt->bindParam(':role', $input['role'] ?? 'user');
            $stmt->bindParam(':status', $input['status'] ?? 'active');
            
            if ($stmt->execute()) {
                $newId = $pdo->lastInsertId();
                logMessage("User created with ID: $newId");
                sendResponse(true, ['id' => $newId], 'Utilisateur créé avec succès', 201);
            } else {
                logMessage("Failed to create user", "ERROR");
                sendResponse(false, null, 'Erreur lors de la création de l\'utilisateur', 500);
            }
            break;
            
        case 'PUT':
            // Update an existing user
            $input = json_decode(file_get_contents('php://input'), true);
            $id = isset($_GET['id']) ? intval($_GET['id']) : null;
            
            if (!$id) {
                logMessage("Missing user ID for update", "ERROR");
                sendResponse(false, null, 'ID d\'utilisateur manquant', 400);
            }
            
            logMessage("Updating user ID: $id with data: " . json_encode($input));
            
            // Check if user exists
            $checkSql = "SELECT id FROM users WHERE id = :id";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                logMessage("User not found for update", "ERROR");
                sendResponse(false, null, 'Utilisateur non trouvé', 404);
            }
            
            // Build update query dynamically
            $updateFields = [];
            $params = [':id' => $id];
            
            // Define allowed fields to update
            $allowedFields = [
                'username', 'email', 'full_name', 'phone', 'address',
                'company_name', 'tax_id', 'role', 'status'
            ];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updateFields[] = "$field = :$field";
                    $params[":$field"] = $input[$field];
                }
            }
            
            // Handle password separately (only if provided and not empty)
            if (isset($input['password']) && !empty($input['password'])) {
                $updateFields[] = "password = :password";
                $params[':password'] = password_hash($input['password'], PASSWORD_DEFAULT);
            }
            
            // Only proceed if there are fields to update
            if (count($updateFields) > 0) {
                // Add updated_at timestamp
                $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
                
                $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute($params)) {
                    logMessage("User updated successfully");
                    sendResponse(true, null, 'Utilisateur mis à jour avec succès');
                } else {
                    logMessage("Failed to update user", "ERROR");
                    sendResponse(false, null, 'Erreur lors de la mise à jour de l\'utilisateur', 500);
                }
            } else {
                logMessage("No fields to update");
                sendResponse(true, null, 'Aucune modification effectuée');
            }
            break;
            
        case 'DELETE':
            // Delete a user
            $id = isset($_GET['id']) ? intval($_GET['id']) : null;
            
            if (!$id) {
                logMessage("Missing user ID for deletion", "ERROR");
                sendResponse(false, null, 'ID d\'utilisateur manquant', 400);
            }
            
            logMessage("Deleting user ID: $id");
            
            // Check if user exists
            $checkSql = "SELECT id FROM users WHERE id = :id";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                logMessage("User not found for deletion", "ERROR");
                sendResponse(false, null, 'Utilisateur non trouvé', 404);
            }
            
            // Delete the user
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                logMessage("User deleted successfully");
                sendResponse(true, null, 'Utilisateur supprimé avec succès');
            } else {
                logMessage("Failed to delete user", "ERROR");
                sendResponse(false, null, 'Erreur lors de la suppression de l\'utilisateur', 500);
            }
            break;
            
        default:
            logMessage("Unsupported method: $method", "ERROR");
            sendResponse(false, null, 'Méthode non supportée', 405);
    }
    
} catch (Exception $e) {
    logMessage("Exception: " . $e->getMessage(), "ERROR");
    sendResponse(false, null, 'Erreur serveur: ' . $e->getMessage(), 500);
}

// Clean any output and end
ob_end_clean();
?>
