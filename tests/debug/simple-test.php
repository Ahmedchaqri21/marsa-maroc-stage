<?php
// Test ultra-simple pour identifier le problème
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers pour JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

echo "TEST DE BASE - Si vous voyez ce message, PHP fonctionne";

// Test de sortie JSON basique
$response = array(
    'status' => 'success',
    'message' => 'Test simple réussi',
    'timestamp' => date('Y-m-d H:i:s'),
    'test_data' => array(
        'item1' => 'valeur1',
        'item2' => 'valeur2'
    )
);

echo json_encode($response, JSON_PRETTY_PRINT);
?>
