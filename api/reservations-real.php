<?php
// API Réservations - Version complète avec vraie base de données
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
    
    // Activer le mode d'affichage des erreurs PDO pour le débogage
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // Récupérer toutes les réservations avec informations utilisateur et emplacement
            $sql = "SELECT 
                        r.id,
                        r.numero_reservation,
                        r.date_debut,
                        r.date_fin,
                        r.duree_jours,
                        r.statut,
                        r.montant_total,
                        r.montant_acompte,
                        r.montant_restant,
                        r.mode_paiement,
                        r.statut_paiement,
                        r.commentaire,
                        r.created_at,
                        u.full_name as utilisateur_nom,
                        u.company_name,
                        e.nom as emplacement_nom,
                        e.code as emplacement_code,
                        e.zone,
                        e.type as emplacement_type
                    FROM reservations r
                    LEFT JOIN users u ON r.user_id = u.id
                    LEFT JOIN emplacements e ON r.emplacement_id = e.id
                    ORDER BY r.created_at DESC
                    LIMIT 50";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $reservations = $stmt->fetchAll();
            
            // Formater les données pour l'affichage
            $formatted_reservations = [];
            foreach ($reservations as $res) {
                $formatted_reservations[] = [
                    'id' => $res['id'],
                    'numero_reservation' => $res['numero_reservation'],
                    'utilisateur_nom' => $res['utilisateur_nom'] ?: 'N/A',
                    'company_name' => $res['company_name'] ?: 'N/A',
                    'emplacement_nom' => $res['emplacement_nom'] ?: 'N/A',
                    'emplacement_code' => $res['emplacement_code'] ?: 'N/A',
                    'zone' => $res['zone'] ?: 'N/A',
                    'emplacement_type' => $res['emplacement_type'] ?: 'N/A',
                    'date_debut' => date('d/m/Y H:i', strtotime($res['date_debut'])),
                    'date_fin' => date('d/m/Y H:i', strtotime($res['date_fin'])),
                    'duree_jours' => $res['duree_jours'] ?: 0,
                    'statut' => $res['statut'],
                    'montant_total' => number_format($res['montant_total'], 2),
                    'montant_acompte' => number_format($res['montant_acompte'], 2),
                    'montant_restant' => number_format($res['montant_restant'], 2),
                    'mode_paiement' => $res['mode_paiement'],
                    'statut_paiement' => $res['statut_paiement'],
                    'commentaire' => $res['commentaire'] ?: '',
                    'created_at' => date('d/m/Y H:i', strtotime($res['created_at']))
                ];
            }
            
            $response = [
                'success' => true,
                'data' => $formatted_reservations,
                'count' => count($formatted_reservations),
                'message' => 'Réservations récupérées avec succès'
            ];
            break;
            
        case 'POST':
            // Créer une nouvelle réservation
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Générer un numéro de réservation si non fourni
            if (empty($input['numero_reservation'])) {
                $date = date('ymd');
                $random = mt_rand(1000, 9999);
                $userId = $input['user_id'] ?? 0;
                $input['numero_reservation'] = "R{$date}-{$random}-{$userId}";
            }
            
            // Vérifier que le montant total est bien défini
            if (!isset($input['montant_total']) || $input['montant_total'] <= 0) {
                error_log('Montant total manquant ou invalide: ' . print_r($input, true));
                throw new Exception('Le montant total est requis et doit être supérieur à zéro');
            }
            
            // S'assurer que le montant total est un nombre
            $input['montant_total'] = floatval($input['montant_total']);
            
            $sql = "INSERT INTO reservations 
                    (numero_reservation, user_id, emplacement_id, date_debut, date_fin, 
                     montant_total, montant_acompte, mode_paiement, commentaire) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $input['numero_reservation'],
                $input['user_id'],
                $input['emplacement_id'],
                $input['date_debut'],
                $input['date_fin'],
                $input['montant_total'],
                $input['montant_acompte'] ?? 0,
                $input['mode_paiement'] ?? 'virement',
                $input['commentaire'] ?? ''
            ]);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Réservation créée avec succès',
                    'id' => $pdo->lastInsertId()
                ];
            } else {
                throw new Exception('Erreur lors de la création de la réservation');
            }
            break;
            
        case 'PUT':
            // Mettre à jour une réservation
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID de réservation manquant');
            }
            
            $sql = "UPDATE reservations SET 
                    statut = ?, 
                    montant_acompte = ?, 
                    statut_paiement = ?,
                    commentaire = ?,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $input['statut'],
                $input['montant_acompte'] ?? 0,
                $input['statut_paiement'] ?? 'en_attente',
                $input['commentaire'] ?? '',
                $id
            ]);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'Réservation mise à jour avec succès'
                ];
            } else {
                throw new Exception('Erreur lors de la mise à jour');
            }
            break;
            
        default:
            throw new Exception('Méthode non supportée');
    }

} catch (Exception $e) {
    // Log l'erreur pour le débogage
    error_log("Reservation API Error: " . $e->getMessage());
    
    // Préparer les détails de l'erreur
    $errorDetails = [];
    
    // Si c'est une exception PDO, récupérer les informations détaillées
    if ($e instanceof PDOException) {
        $errorDetails['sqlState'] = $e->errorInfo[0] ?? null;
        $errorDetails['sqlCode'] = $e->errorInfo[1] ?? null;
        $errorDetails['sqlMessage'] = $e->errorInfo[2] ?? null;
    }
    
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
        'message' => 'Erreur lors du traitement de la demande',
        'details' => $errorDetails
    ];
}

ob_clean();
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
ob_end_flush();
exit;
?>
