<?php
// API Statistiques - Version complète avec vraie base de données
ob_start();
if (ob_get_level()) {
    ob_clean();
}

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once __DIR__ . '/../config/database.php';
    $pdo = getDBConnection();
    
    // Statistiques générales
    $stats = [];
    
    // 1. Nombre total d'utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
    $stats['total_users'] = $stmt->fetch()['total'];
    
    // 2. Nombre total d'emplacements
    $stmt = $pdo->query("SELECT 
                            COUNT(*) as total,
                            COUNT(CASE WHEN etat = 'disponible' THEN 1 END) as disponibles,
                            COUNT(CASE WHEN etat = 'occupe' THEN 1 END) as occupes,
                            COUNT(CASE WHEN etat = 'maintenance' THEN 1 END) as maintenance
                         FROM emplacements");
    $emplacements_stats = $stmt->fetch();
    $stats['total_emplacements'] = $emplacements_stats['total'];
    $stats['emplacements_disponibles'] = $emplacements_stats['disponibles'];
    $stats['emplacements_occupes'] = $emplacements_stats['occupes'];
    $stats['emplacements_maintenance'] = $emplacements_stats['maintenance'];
    
    // 3. Statistiques des réservations
    $stmt = $pdo->query("SELECT 
                            COUNT(*) as total,
                            COUNT(CASE WHEN statut = 'en_attente' THEN 1 END) as en_attente,
                            COUNT(CASE WHEN statut = 'validee' THEN 1 END) as validees,
                            COUNT(CASE WHEN statut = 'terminee' THEN 1 END) as terminees,
                            SUM(CASE WHEN statut = 'validee' THEN montant_total ELSE 0 END) as revenus_valides,
                            SUM(montant_acompte) as acomptes_recus
                         FROM reservations");
    $reservations_stats = $stmt->fetch();
    $stats['total_reservations'] = $reservations_stats['total'];
    $stats['reservations_en_attente'] = $reservations_stats['en_attente'];
    $stats['reservations_validees'] = $reservations_stats['validees'];
    $stats['reservations_terminees'] = $reservations_stats['terminees'];
    $stats['revenus_total'] = $reservations_stats['revenus_valides'];
    $stats['acomptes_recus'] = $reservations_stats['acomptes_recus'];
    
    // 4. Revenus par mois (12 derniers mois)
    $stmt = $pdo->query("SELECT 
                            YEAR(created_at) as annee,
                            MONTH(created_at) as mois,
                            SUM(montant_total) as revenus,
                            COUNT(*) as nb_reservations
                         FROM reservations 
                         WHERE statut = 'validee' 
                           AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                         GROUP BY YEAR(created_at), MONTH(created_at)
                         ORDER BY annee DESC, mois DESC");
    $revenus_mensuels = $stmt->fetchAll();
    
    // 5. Top 5 des emplacements les plus réservés
    $stmt = $pdo->query("SELECT 
                            e.nom,
                            e.code,
                            e.zone,
                            COUNT(r.id) as nb_reservations,
                            SUM(r.montant_total) as revenus_generes
                         FROM emplacements e
                         LEFT JOIN reservations r ON e.id = r.emplacement_id
                         WHERE r.statut = 'validee'
                         GROUP BY e.id
                         ORDER BY nb_reservations DESC
                         LIMIT 5");
    $top_emplacements = $stmt->fetchAll();
    
    // 6. Top 5 des clients
    $stmt = $pdo->query("SELECT 
                            u.full_name,
                            u.company_name,
                            COUNT(r.id) as nb_reservations,
                            SUM(r.montant_total) as chiffre_affaires
                         FROM users u
                         LEFT JOIN reservations r ON u.id = r.user_id
                         WHERE r.statut = 'validee'
                         GROUP BY u.id
                         ORDER BY chiffre_affaires DESC
                         LIMIT 5");
    $top_clients = $stmt->fetchAll();
    
    // 7. Occupation par zone
    $stmt = $pdo->query("SELECT 
                            e.zone,
                            COUNT(e.id) as total_emplacements,
                            COUNT(CASE WHEN e.etat = 'occupe' THEN 1 END) as emplacements_occupes,
                            ROUND((COUNT(CASE WHEN e.etat = 'occupe' THEN 1 END) / COUNT(e.id)) * 100, 1) as taux_occupation
                         FROM emplacements e
                         GROUP BY e.zone
                         ORDER BY taux_occupation DESC");
    $occupation_zones = $stmt->fetchAll();
    
    // 8. Activité récente (dernières 10 réservations)
    $stmt = $pdo->query("SELECT 
                            r.numero_reservation,
                            r.statut,
                            r.created_at,
                            u.full_name as client,
                            e.nom as emplacement
                         FROM reservations r
                         LEFT JOIN users u ON r.user_id = u.id
                         LEFT JOIN emplacements e ON r.emplacement_id = e.id
                         ORDER BY r.created_at DESC
                         LIMIT 10");
    $activite_recente = $stmt->fetchAll();
    
    $response = [
        'success' => true,
        'data' => [
            'statistiques_generales' => $stats,
            'revenus_mensuels' => $revenus_mensuels,
            'top_emplacements' => $top_emplacements,
            'top_clients' => $top_clients,
            'occupation_zones' => $occupation_zones,
            'activite_recente' => $activite_recente
        ],
        'message' => 'Statistiques récupérées avec succès'
    ];

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
