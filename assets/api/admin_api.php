<?php
// assets/api/admin_api.php

// Headers pour autoriser les requêtes JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Inclure le contrôleur
require_once __DIR__ . '/../php/controllers/AdminController.php';

// Lire les données JSON envoyées
$json = file_get_contents("php://input");
$data = json_decode($json, true);

$response = [];

if (!empty($data) && isset($data['action'])) {
    
    $controller = new AdminController();

    switch ($data['action']) {
        case 'register':
            $response = $controller->register($data);
            break;

        case 'login':
            $response = $controller->login($data);
            break;

        default:
            $response = ['success' => false, 'message' => 'Action non reconnue.'];
            break;
    }
} else {
    $response = ['success' => false, 'message' => 'Aucune donnée reçue.'];
}

// Renvoyer la réponse JSON
echo json_encode($response);
exit;