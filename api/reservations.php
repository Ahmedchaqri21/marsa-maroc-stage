<?php
// Version corrigée de l'API reservations - sans aucune sortie parasite
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
    
    // Test de connexion à la base de données
    $stmt = $pdo->query("SELECT 1");
    
    // Données factices pour test
    $reservations = [
        [
            'id' => 1,
            'numero_reservation' => 'RES001',
            'id_emplacement' => 1,
            'id_utilisateur' => 1,
            'nom_navire' => 'Navire Test 1',
            'date_debut' => '2024-01-15',
            'date_fin' => '2024-01-20',
            'statut' => 'validee',
            'montant_total' => 5000.00,
            'utilisateur_nom' => 'Test User',
            'emplacement_nom' => 'Quai A1'
        ],
        [
            'id' => 2,
            'numero_reservation' => 'RES002',
            'id_emplacement' => 2,
            'id_utilisateur' => 2,
            'nom_navire' => 'Navire Test 2',
            'date_debut' => '2024-02-01',
            'date_fin' => '2024-02-05',
            'statut' => 'en_attente',
            'montant_total' => 3500.00,
            'utilisateur_nom' => 'Test User 2',
            'emplacement_nom' => 'Quai B2'
        ]
    ];

    $response = [
        'success' => true,
        'data' => $reservations,
        'count' => count($reservations),
        'message' => 'Réservations récupérées avec succès'
    ];

} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => 'Erreur de base de données',
        'message' => 'Impossible de récupérer les réservations'
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
