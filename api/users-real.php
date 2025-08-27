<?php
// API Utilisateurs - Version complète avec vraie base de données
ob_start();
if (ob_get_level()) {
    ob_clean();
}

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once __DIR__ . '/../config/database.php';
    $pdo = getDBConnection();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // Vérifier si on demande un utilisateur spécifique
            if (isset($_GET['id'])) {
                $userId = intval($_GET['id']);
                
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
                            u.created_at,
                            COUNT(r.id) as total_reservations,
                            COUNT(CASE WHEN r.statut = 'validee' THEN 1 END) as reservations_validees,
                            SUM(CASE WHEN r.statut = 'validee' THEN r.montant_total ELSE 0 END) as chiffre_affaires
                        FROM users u
                        LEFT JOIN reservations r ON u.id = r.user_id
                        WHERE u.id = :id
                        GROUP BY u.id";
                
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                $user = $stmt->fetch();
                
                if ($user) {
                    // Formater les données pour l'utilisateur unique
                    $status_color = match($user['status']) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'suspended' => 'danger',
                        default => 'secondary'
                    };
                    
                    $role_color = match($user['role']) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        'user' => 'primary',
                        default => 'secondary'
                    };
                    
                    $formatted_user = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'full_name' => $user['full_name'],
                        'phone' => $user['phone'] ?: '',
                        'address' => $user['address'] ?: '',
                        'company_name' => $user['company_name'] ?: '',
                        'tax_id' => $user['tax_id'] ?: '',
                        'role' => $user['role'],
                        'role_libelle' => match($user['role']) {
                            'admin' => 'Administrateur',
                            'manager' => 'Gestionnaire',
                            'user' => 'Utilisateur',
                            default => 'Inconnu'
                        },
                        'role_color' => $role_color,
                        'status' => $user['status'],
                        'status_libelle' => match($user['status']) {
                            'active' => 'Actif',
                            'inactive' => 'Inactif',
                            'suspended' => 'Suspendu',
                            default => 'Inconnu'
                        },
                        'status_color' => $status_color,
                        'last_login' => $user['last_login'] ? 
                            date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais connecté',
                        'total_reservations' => $user['total_reservations'],
                        'reservations_validees' => $user['reservations_validees'],
                        'chiffre_affaires' => number_format($user['chiffre_affaires'] ?: 0, 2) . ' MAD',
                        'created_at' => $user['created_at'] ? date('d/m/Y H:i', strtotime($user['created_at'])) : ''
                    ];
                    
                    // Log pour le débogage
                    error_log("Données utilisateur formatées: " . json_encode($formatted_user));
                    
                    $response = [
                        'success' => true,
                        'data' => $formatted_user,
                        'message' => 'Utilisateur récupéré avec succès'
                    ];
                    
                    echo json_encode($response);
                    exit;
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Utilisateur non trouvé'
                    ]);
                    exit;
                }
            }
            
            // Log for debugging
            error_log("Récupération de tous les utilisateurs");
            
            // Récupérer tous les utilisateurs avec statistiques
            // Modifié pour éviter les problèmes de GROUP BY avec MySQL strict mode
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
                        u.created_at,
                        IFNULL((SELECT COUNT(r.id) FROM reservations r WHERE r.user_id = u.id), 0) as total_reservations,
                        IFNULL((SELECT COUNT(r.id) FROM reservations r WHERE r.user_id = u.id AND r.statut = 'validee'), 0) as reservations_validees,
                        IFNULL((SELECT SUM(r.montant_total) FROM reservations r WHERE r.user_id = u.id AND r.statut = 'validee'), 0) as chiffre_affaires
                    FROM users u
                    ORDER BY u.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll();
            
            // Log the count for debugging
            error_log("Nombre d'utilisateurs récupérés: " . count($users));
            
            // Formater les données pour l'affichage
            $formatted_users = [];
            foreach ($users as $user) {
                // Déterminer la couleur du statut
                $status_color = match($user['status']) {
                    'active' => 'success',
                    'inactive' => 'warning',
                    'suspended' => 'danger',
                    default => 'secondary'
                };
                
                // Déterminer la couleur du rôle
                $role_color = match($user['role']) {
                    'admin' => 'danger',
                    'manager' => 'warning',
                    'user' => 'primary',
                    default => 'secondary'
                };
                
                $formatted_users[] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'full_name' => $user['full_name'],
                    'phone' => $user['phone'] ?: 'Non renseigné',
                    'address' => $user['address'] ?: 'Non renseignée',
                    'company_name' => $user['company_name'] ?: 'Particulier',
                    'tax_id' => $user['tax_id'] ?: 'N/A',
                    'role' => $user['role'],
                    'role_libelle' => match($user['role']) {
                        'admin' => 'Administrateur',
                        'manager' => 'Gestionnaire',
                        'user' => 'Utilisateur',
                        default => 'Inconnu'
                    },
                    'role_color' => $role_color,
                    'status' => $user['status'],
                    'status_libelle' => match($user['status']) {
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        'suspended' => 'Suspendu',
                        default => 'Inconnu'
                    },
                    'status_color' => $status_color,
                    'last_login' => $user['last_login'] ? 
                        date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais connecté',
                    'total_reservations' => $user['total_reservations'],
                    'reservations_validees' => $user['reservations_validees'],
                    'chiffre_affaires' => number_format($user['chiffre_affaires'] ?: 0, 2) . ' MAD',
                    'created_at' => date('d/m/Y H:i', strtotime($user['created_at']))
                ];
            }
            
            $response = [
                'success' => true,
                'data' => $formatted_users,
                'count' => count($formatted_users),
                'message' => 'Utilisateurs récupérés avec succès'
            ];
            break;
            
        case 'POST':
            // Créer un nouvel utilisateur
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Log for debugging
            error_log("Création d'un nouvel utilisateur: " . json_encode($input));
            
            // Vérifier les données obligatoires
            if (empty($input['username']) || empty($input['email']) || empty($input['password']) || empty($input['full_name'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Données obligatoires manquantes (username, email, password, full_name)'
                ]);
                exit;
            }
            
            // Vérifier si l'utilisateur existe déjà
            $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $check->execute([$input['username'], $input['email']]);
            if ($check->rowCount() > 0) {
                http_response_code(409); // Conflict
                echo json_encode([
                    'success' => false,
                    'message' => 'Un utilisateur avec ce nom d\'utilisateur ou cet email existe déjà'
                ]);
                exit;
            }
            
            // Hasher le mot de passe
            $hashed_password = password_hash($input['password'], PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users 
                    (username, email, password, full_name, phone, address, 
                     company_name, tax_id, role, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $input['username'],
                $input['email'],
                $hashed_password,
                $input['full_name'],
                $input['phone'] ?? '',
                $input['address'] ?? '',
                $input['company_name'] ?? '',
                $input['tax_id'] ?? '',
                $input['role'] ?? 'user',
                $input['status'] ?? 'active'
            ]);
            
            if ($result) {
                $newId = $pdo->lastInsertId();
                $response = [
                    'success' => true,
                    'message' => 'Utilisateur créé avec succès',
                    'id' => $newId
                ];
                error_log("Utilisateur créé avec ID: $newId");
            } else {
                throw new Exception('Erreur lors de la création de l\'utilisateur');
            }
            break;
            
        case 'PUT':
            // Mettre à jour un utilisateur
            $input = json_decode(file_get_contents('php://input'), true);
            // Get ID from the URL parameter instead of the JSON body
            $id = isset($_GET['id']) ? intval($_GET['id']) : null;
            
            if (!$id) {
                throw new Exception('ID d\'utilisateur manquant dans l\'URL');
            }
            
            // Log debug information
            error_log("Mise à jour utilisateur ID: $id avec données: " . json_encode($input));
            
            // Construire la requête de mise à jour
            $fields = [];
            $values = [];
            
            if (isset($input['username'])) {
                $fields[] = "username = ?";
                $values[] = $input['username'];
            }
            if (isset($input['full_name'])) {
                $fields[] = "full_name = ?";
                $values[] = $input['full_name'];
            }
            if (isset($input['email'])) {
                $fields[] = "email = ?";
                $values[] = $input['email'];
            }
            if (isset($input['phone'])) {
                $fields[] = "phone = ?";
                $values[] = $input['phone'];
            }
            if (isset($input['role'])) {
                $fields[] = "role = ?";
                $values[] = $input['role'];
            }
            if (isset($input['status'])) {
                $fields[] = "status = ?";
                $values[] = $input['status'];
            }
            if (isset($input['password']) && !empty($input['password'])) {
                $fields[] = "password = ?";
                $values[] = password_hash($input['password'], PASSWORD_DEFAULT);
            }
            
            // Add the ID at the end for the WHERE clause
            $values[] = $id;
            
            // Only proceed if we have fields to update
            if (count($fields) > 0) {
                $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
                
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute($values);
            } else {
                $result = true; // Nothing to update
            }
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Utilisateur mis à jour avec succès'
                ];
            } else {
                throw new Exception('Erreur lors de la mise à jour');
            }
            break;
            
        case 'DELETE':
            // Supprimer un utilisateur
            $id = isset($_GET['id']) ? intval($_GET['id']) : null;
            
            if (!$id) {
                throw new Exception('ID d\'utilisateur manquant dans l\'URL');
            }
            
            // Log the deletion request
            error_log("Demande de suppression d'utilisateur ID: $id");
            
            // Vérifier si l'utilisateur existe
            $check = $pdo->prepare("SELECT id FROM users WHERE id = ?");
            $check->execute([$id]);
            if ($check->rowCount() === 0) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ]);
                exit;
            }
            
            // Supprimer l'utilisateur
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Utilisateur supprimé avec succès'
                ];
            } else {
                throw new Exception('Erreur lors de la suppression de l\'utilisateur');
            }
            break;
            
        default:
            throw new Exception('Méthode non supportée');
    }

} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
        'message' => 'Erreur lors du traitement de la demande'
    ];
}

ob_clean();
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
ob_end_flush();
exit;
?>
