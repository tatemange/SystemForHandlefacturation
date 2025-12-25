<?php
session_start();
header('Content-Type: application/json');

// --- 1. SÉCURITÉ ---
// Si l'utilisateur n'est pas connecté, on bloque (optionnel, à activer selon vos besoins)
// if (!isset($_SESSION['admin_id'])) {
//     echo json_encode(['status' => 'error', 'message' => 'Non autorisé']);
//     exit;
// }

// --- 2. INCLUSION DU MODÈLE ---
require_once __DIR__ . '/../php/models/ClientModel.php';
require_once __DIR__ . '/../php/config/Database.php';

$database = new Database();
$db = $database->conn;
$clientModel = new ClientModel($db);

$method = $_SERVER['REQUEST_METHOD'];

// --- 3. TRAITEMENT GET (Récupérer la liste) ---
if ($method === 'GET') {
    $clients = $clientModel->getAllClients();
    echo json_encode(['status' => 'success', 'data' => $clients]);
    exit;
}

// --- 4. TRAITEMENT POST (Ajouter un client) ---
if ($method === 'POST') {
    // On lit le JSON envoyé par le Javascript
    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, true);

    if (isset($data['nom']) && !empty($data['nom'])) {
        $nom = htmlspecialchars($data['nom']);
        $prenom = htmlspecialchars($data['prenom'] ?? '');
        $tel = htmlspecialchars($data['telephone'] ?? '');
        $email = htmlspecialchars($data['email'] ?? '');

        if ($clientModel->addClient($nom, $prenom, $tel, $email)) {
            echo json_encode(['status' => 'success', 'message' => 'Client ajouté avec succès']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'enregistrement en base de données']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Le champ NOM est obligatoire']);
    }
    exit;
}
?>