<?php
// Version corrigée de l'API emplacements - sans aucune sortie parasite
ob_start(); // Commencer la capture de sortie

// Supprimer toute sortie précédente
if (ob_get_level()) {
    ob_clean();
}

// Configuration des erreurs
error_reporting(0); // Désactiver l'affichage des erreurs
ini_set('display_errors', 0);

// Headers JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Inclure la configuration de base de données
    require_once __DIR__ . '/../config/database.php';
    
    // Obtenir la connexion à la base de données
    $pdo = getDBConnection();
    
    // Détecter la méthode HTTP
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Traiter selon la méthode HTTP
    switch ($method) {
        case 'GET':
            // Récupérer tous les emplacements de la base de données
            $stmt = $pdo->prepare("SELECT id, code, nom, type, superficie, longueur, largeur, profondeur, 
                            tarif_horaire, tarif_journalier, tarif_mensuel, etat as statut, 
                            capacite_navire, equipements, description, zone 
                            FROM emplacements ORDER BY id DESC");
            $stmt->execute();
            $emplacements = $stmt->fetchAll();
            
            $response = [
                'success' => true,
                'data' => $emplacements,
                'count' => count($emplacements),
                'message' => 'Emplacements récupérés avec succès'
            ];
            break;
            
        case 'POST':
            // Récupérer les données du corps de la requête
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Si le JSON est invalide, essayer avec les données POST normales
            if (json_last_error() !== JSON_ERROR_NONE) {
                $data = $_POST;
            }
            
            // Validation des données
            if (empty($data['code']) || empty($data['nom']) || empty($data['type']) || empty($data['superficie'])) {
                throw new Exception("Les champs code, nom, type et superficie sont obligatoires");
            }
            
            // Préparer la requête d'insertion
            $stmt = $pdo->prepare("INSERT INTO emplacements (
                code, nom, type, superficie, longueur, largeur, profondeur,
                tarif_horaire, tarif_journalier, tarif_mensuel, etat,
                capacite_navire, equipements, description, zone
            ) VALUES (
                :code, :nom, :type, :superficie, :longueur, :largeur, :profondeur,
                :tarif_horaire, :tarif_journalier, :tarif_mensuel, :etat,
                :capacite_navire, :equipements, :description, :zone
            )");
            
            // Exécution avec les valeurs
            $stmt->execute([
                ':code' => $data['code'],
                ':nom' => $data['nom'],
                ':type' => $data['type'] ?? 'quai',
                ':superficie' => $data['superficie'] ?? ($data['longueur'] * $data['largeur']),
                ':longueur' => $data['longueur'] ?? null,
                ':largeur' => $data['largeur'] ?? null,
                ':profondeur' => $data['profondeur'] ?? null,
                ':tarif_horaire' => $data['tarif_horaire'] ?? 0,
                ':tarif_journalier' => $data['tarif_journalier'] ?? 0,
                ':tarif_mensuel' => $data['tarif_mensuel'] ?? 0,
                ':etat' => $data['statut'] ?? 'disponible',
                ':capacite_navire' => $data['capacite_navire'] ?? null,
                ':equipements' => $data['equipements'] ?? null,
                ':description' => $data['description'] ?? null,
                ':zone' => $data['zone'] ?? 'Zone A'
            ]);
            
            // Récupérer l'ID de l'emplacement nouvellement créé
            $newId = $pdo->lastInsertId();
            
            $response = [
                'success' => true,
                'message' => 'Emplacement ajouté avec succès',
                'id' => $newId
            ];
            break;
            
        default:
            throw new Exception("Méthode HTTP non prise en charge");
    }

} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => 'Erreur de base de données',
        'message' => 'Impossible de récupérer les emplacements'
    ];
}

// Nettoyer toute sortie parasite
ob_clean();

// Sortir uniquement le JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE);

// Terminer proprement
ob_end_flush();
exit;
?>
