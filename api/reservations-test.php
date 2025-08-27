<?php
// API Reservations simplifiée avec gestion d'erreurs robuste
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

ob_clean();

try {
    require_once '../config/database.php';
    
    // Test de connexion à la base de données
    $pdo = getDBConnection();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            try {
                // Données de test statiques pour commencer
                $reservations = [
                    [
                        'id' => 1,
                        'numero_reservation' => 'RES001',
                        'user_name' => 'Mohammed Alami',
                        'emplacement_nom' => 'Quai Principal A1',
                        'date_debut' => '2025-01-15',
                        'date_fin' => '2025-01-20',
                        'statut' => 'en_attente',
                        'montant_total' => 750.00
                    ],
                    [
                        'id' => 2,
                        'numero_reservation' => 'RES002',
                        'user_name' => 'Fatima Benjelloun',
                        'emplacement_nom' => 'Terminal Conteneurs B1',
                        'date_debut' => '2025-01-18',
                        'date_fin' => '2025-01-25',
                        'statut' => 'validee',
                        'montant_total' => 1400.00
                    ]
                ];
                
                echo json_encode($reservations);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la récupération des données: ' . $e->getMessage()]);
            }
            break;
            
        case 'PUT':
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!$data || !isset($data['statut'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Statut manquant']);
                    break;
                }
                
                // Simulation de mise à jour réussie
                echo json_encode(['success' => true, 'message' => 'Statut mis à jour avec succès']);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur du serveur: ' . $e->getMessage()]);
}

// Nettoyer le buffer de sortie
if (ob_get_length()) {
    ob_end_clean();
}
?>
