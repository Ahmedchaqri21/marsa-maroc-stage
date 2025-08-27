<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$pdo = getDBConnection();

try {
    // Get total revenue
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(montant_total), 0) as total_revenue 
        FROM reservations 
        WHERE statut = 'validee'
    ");
    $total_revenue = $stmt->fetch()['total_revenue'];

    // Get monthly revenue for current year
    $stmt = $pdo->query("
        SELECT 
            MONTH(date_debut) as month,
            SUM(montant_total) as revenue
        FROM reservations 
        WHERE statut = 'validee' 
        AND YEAR(date_debut) = YEAR(CURRENT_DATE())
        GROUP BY MONTH(date_debut)
        ORDER BY month
    ");
    $monthly_revenue = $stmt->fetchAll();

    // Get occupation rate
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_emplacements,
            SUM(CASE WHEN etat = 'occupe' THEN 1 ELSE 0 END) as occupied_emplacements,
            SUM(CASE WHEN etat = 'disponible' THEN 1 ELSE 0 END) as available_emplacements,
            SUM(CASE WHEN etat = 'maintenance' THEN 1 ELSE 0 END) as maintenance_emplacements
        FROM emplacements
    ");
    $occupation_data = $stmt->fetch();
    
    $total_emplacements = $occupation_data['total_emplacements'];
    $occupied_emplacements = $occupation_data['occupied_emplacements'];
    $available_emplacements = $occupation_data['available_emplacements'];
    $maintenance_emplacements = $occupation_data['maintenance_emplacements'];
    
    $occupation_rate = $total_emplacements > 0 ? round(($occupied_emplacements / $total_emplacements) * 100, 2) : 0;

    // Get reservation status counts
    $stmt = $pdo->query("
        SELECT 
            statut,
            COUNT(*) as count
        FROM reservations 
        GROUP BY statut
    ");
    $reservation_status = $stmt->fetchAll();

    // Get top performing emplacements
    $stmt = $pdo->query("
        SELECT 
            e.nom,
            COUNT(r.id) as reservation_count,
            COALESCE(SUM(r.montant_total), 0) as total_revenue
        FROM emplacements e
        LEFT JOIN reservations r ON e.id = r.emplacement_id AND r.statut = 'validee'
        GROUP BY e.id, e.nom
        ORDER BY total_revenue DESC
        LIMIT 5
    ");
    $top_emplacements = $stmt->fetchAll();

    // Get recent reservations
    $stmt = $pdo->query("
        SELECT 
            r.id,
            r.date_debut,
            r.date_fin,
            r.statut,
            r.montant_total,
            u.full_name as user_name,
            e.nom as emplacement_nom
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        JOIN emplacements e ON r.emplacement_id = e.id
        ORDER BY r.created_at DESC
        LIMIT 10
    ");
    $recent_reservations = $stmt->fetchAll();

    // Compile statistics
    $statistics = [
        'overview' => [
            'total_revenue' => (float)$total_revenue,
            'occupation_rate' => $occupation_rate,
            'total_emplacements' => (int)$total_emplacements,
            'occupied_emplacements' => (int)$occupied_emplacements,
            'available_emplacements' => (int)$available_emplacements,
            'maintenance_emplacements' => (int)$maintenance_emplacements
        ],
        'monthly_revenue' => $monthly_revenue,
        'reservation_status' => $reservation_status,
        'top_emplacements' => $top_emplacements,
        'recent_reservations' => $recent_reservations
    ];

    echo json_encode($statistics);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()]);
}
?>

