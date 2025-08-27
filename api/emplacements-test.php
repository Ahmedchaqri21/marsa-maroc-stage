<?php
// API Emplacements simplifiée avec gestion d'erreurs robuste
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
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            try {
                // Données de test statiques
                $emplacements = [
                    [
                        'id' => 1,
                        'code' => 'QA1',
                        'nom' => 'Quai Principal A1',
                        'superficie' => 500.00,
                        'tarif' => 150.00,
                        'etat' => 'disponible'
                    ],
                    [
                        'id' => 2,
                        'code' => 'QA2',
                        'nom' => 'Quai Principal A2',
                        'superficie' => 600.00,
                        'tarif' => 180.00,
                        'etat' => 'occupe'
                    ],
                    [
                        'id' => 3,
                        'code' => 'TB1',
                        'nom' => 'Terminal Conteneurs B1',
                        'superficie' => 800.00,
                        'tarif' => 200.00,
                        'etat' => 'disponible'
                    ],
                    [
                        'id' => 4,
                        'code' => 'ZS1',
                        'nom' => 'Zone Stockage C1',
                        'superficie' => 300.00,
                        'tarif' => 100.00,
                        'etat' => 'maintenance'
                    ]
                ];
                
                if (isset($_GET['id'])) {
                    $id = intval($_GET['id']);
                    $emplacement = array_filter($emplacements, fn($e) => $e['id'] === $id);
                    if (empty($emplacement)) {
                        http_response_code(404);
                        echo json_encode(['error' => 'Emplacement non trouvé']);
                    } else {
                        echo json_encode(array_values($emplacement)[0]);
                    }
                } else {
                    echo json_encode($emplacements);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la récupération: ' . $e->getMessage()]);
            }
            break;
            
        case 'POST':
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!$data || !isset($data['nom']) || !isset($data['superficie']) || !isset($data['tarif'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Données manquantes']);
                    break;
                }
                
                // Simulation de création réussie
                echo json_encode(['success' => true, 'message' => 'Emplacement créé avec succès', 'id' => rand(5, 100)]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la création: ' . $e->getMessage()]);
            }
            break;
            
        case 'DELETE':
            try {
                if (!isset($_GET['id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID manquant']);
                    break;
                }
                
                // Simulation de suppression réussie
                echo json_encode(['success' => true, 'message' => 'Emplacement supprimé avec succès']);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
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

if (ob_get_length()) {
    ob_end_clean();
}
?>
