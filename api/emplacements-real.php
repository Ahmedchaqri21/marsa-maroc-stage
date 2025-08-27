<?php
// API Emplacements - Version complète avec vraie base de données
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
            // Récupérer tous les emplacements avec statistiques
            $sql = "SELECT 
                        e.id,
                        e.code,
                        e.nom,
                        e.type,
                        e.superficie,
                        e.longueur,
                        e.largeur,
                        e.profondeur,
                        e.tarif_horaire,
                        e.tarif_journalier,
                        e.tarif_mensuel,
                        e.etat,
                        e.capacite_navire,
                        e.equipements,
                        e.description,
                        e.zone,
                        e.created_at,
                        COUNT(r.id) as total_reservations,
                        COUNT(CASE WHEN r.statut = 'validee' THEN 1 END) as reservations_validees
                    FROM emplacements e
                    LEFT JOIN reservations r ON e.id = r.emplacement_id
                    GROUP BY e.id
                    ORDER BY e.zone, e.code";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $emplacements = $stmt->fetchAll();
            
            // Formater les données pour l'affichage
            $formatted_emplacements = [];
            foreach ($emplacements as $emp) {
                // Déterminer la couleur du statut
                $status_color = match($emp['etat']) {
                    'disponible' => 'success',
                    'occupe' => 'danger',
                    'maintenance' => 'warning',
                    'reserve' => 'info',
                    default => 'secondary'
                };
                
                $formatted_emplacements[] = [
                    'id' => $emp['id'],
                    'code' => $emp['code'],
                    'nom' => $emp['nom'],
                    'type' => ucfirst($emp['type']),
                    'superficie' => number_format($emp['superficie'], 2) . ' m²',
                    'dimensions' => $emp['longueur'] . 'm x ' . $emp['largeur'] . 'm',
                    'profondeur' => $emp['profondeur'] . 'm',
                    'tarif_horaire' => number_format($emp['tarif_horaire'], 2) . ' MAD',
                    'tarif_journalier' => number_format($emp['tarif_journalier'], 2) . ' MAD',
                    'tarif_mensuel' => number_format($emp['tarif_mensuel'], 2) . ' MAD',
                    'etat' => $emp['etat'],
                    'etat_libelle' => ucfirst(str_replace('_', ' ', $emp['etat'])),
                    'status_color' => $status_color,
                    'capacite_navire' => $emp['capacite_navire'] ?: 'Non spécifiée',
                    'equipements' => $emp['equipements'] ?: 'Aucun équipement spécifié',
                    'description' => $emp['description'] ?: '',
                    'zone' => $emp['zone'],
                    'total_reservations' => $emp['total_reservations'],
                    'reservations_validees' => $emp['reservations_validees'],
                    'taux_occupation' => $emp['total_reservations'] > 0 ? 
                        round(($emp['reservations_validees'] / $emp['total_reservations']) * 100, 1) : 0,
                    'created_at' => date('d/m/Y', strtotime($emp['created_at']))
                ];
            }
            
            $response = [
                'success' => true,
                'data' => $formatted_emplacements,
                'count' => count($formatted_emplacements),
                'message' => 'Emplacements récupérés avec succès'
            ];
            break;
            
        case 'POST':
            // Créer un nouvel emplacement
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Valider les données obligatoires
            if (!isset($input['nom']) || !isset($input['superficie']) || !isset($input['tarif_journalier'])) {
                throw new Exception('Données obligatoires manquantes (nom, superficie, tarif_journalier)');
            }
            
            // Utiliser des valeurs par défaut pour les champs non fournis
            $code = $input['code'] ?? ('EMP' . rand(1000, 9999));
            $type = $input['type'] ?? 'quai';
            $longueur = $input['longueur'] ?? 0;
            $largeur = $input['largeur'] ?? 0;
            $profondeur = $input['profondeur'] ?? 0;
            $tarif_horaire = $input['tarif_horaire'] ?? ($input['tarif_journalier'] / 24);
            $tarif_mensuel = $input['tarif_mensuel'] ?? ($input['tarif_journalier'] * 30);
            $zone = $input['zone'] ?? 'Zone Principale';
            
            $sql = "INSERT INTO emplacements 
                    (code, nom, type, superficie, longueur, largeur, profondeur, 
                     tarif_horaire, tarif_journalier, tarif_mensuel, etat, 
                     capacite_navire, equipements, description, zone) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $code,
                $input['nom'],
                $type,
                $input['superficie'],
                $longueur,
                $largeur,
                $profondeur,
                $tarif_horaire,
                $input['tarif_journalier'],
                $tarif_mensuel,
                $input['etat'] ?? 'disponible',
                $input['capacite_navire'] ?? '',
                $input['equipements'] ?? '',
                $input['description'] ?? '',
                $zone
            ]);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Emplacement créé avec succès',
                    'id' => $pdo->lastInsertId()
                ];
            } else {
                throw new Exception('Erreur lors de la création de l\'emplacement');
            }
            break;
            
        case 'PUT':
            // Mettre à jour un emplacement
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Récupérer l'ID soit de l'URL soit du corps de la requête
            $id = isset($_GET['id']) ? $_GET['id'] : ($input['id'] ?? null);
            
            if (!$id) {
                throw new Exception('ID d\'emplacement manquant');
            }
            
            // Vérifier que l'emplacement existe
            $checkStmt = $pdo->prepare("SELECT id FROM emplacements WHERE id = ?");
            $checkStmt->execute([$id]);
            if ($checkStmt->rowCount() === 0) {
                throw new Exception('Emplacement non trouvé');
            }
            
            // Préparer les données pour la mise à jour
            $updateFields = [];
            $params = [];
            
            // Liste des champs pouvant être mis à jour
            $allowedFields = ['code', 'nom', 'type', 'superficie', 'longueur', 'largeur', 
                             'profondeur', 'tarif_horaire', 'tarif_journalier', 'tarif_mensuel', 
                             'etat', 'capacite_navire', 'equipements', 'description', 'zone'];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $input[$field];
                }
            }
            
            // Ajouter la date de mise à jour
            $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
            
            if (empty($updateFields)) {
                throw new Exception('Aucune donnée fournie pour la mise à jour');
            }
            
            // Ajouter l'ID à la fin des paramètres
            $params[] = $id;
            
            $sql = "UPDATE emplacements SET " . implode(", ", $updateFields) . " WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Emplacement mis à jour avec succès'
                ];
            } else {
                throw new Exception('Erreur lors de la mise à jour');
            }
            break;
            
        case 'DELETE':
            // Supprimer un emplacement
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID d\'emplacement manquant');
            }
            
            // Vérifier les réservations associées
            $checkStmt = $pdo->prepare("SELECT COUNT(*) as count FROM reservations WHERE emplacement_id = ? AND statut IN ('en_attente', 'validee')");
            $checkStmt->execute([$id]);
            $result = $checkStmt->fetch();
            
            if ($result['count'] > 0) {
                throw new Exception('Impossible de supprimer cet emplacement car des réservations y sont associées');
            }
            
            $sql = "DELETE FROM emplacements WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Emplacement supprimé avec succès'
                ];
            } else {
                throw new Exception('Erreur lors de la suppression');
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
    
    // Log the error for debugging
    error_log("Emplacements API Error: " . $e->getMessage());
}

ob_clean();
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
ob_end_flush();
exit;
?>
