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
// --- 2. INCLUSION DES MODÈLES ---
require_once __DIR__ . '/../php/models/ClientModel.php';
require_once __DIR__ . '/../php/models/HistoriqueModel.php'; // Included
require_once __DIR__ . '/../php/config/Database.php';

$database = new Database();
$db = $database->conn;
$clientModel = new ClientModel($db);
$historyModel = new HistoriqueModel($db); // Initialized

$method = $_SERVER['REQUEST_METHOD'];

// SÉCURITÉ: Authentification requise
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Accès refusé.']);
    exit;
}
$adminId = $_SESSION['admin_id'];

// --- 3. TRAITEMENT GET (Récupérer la liste ou un client) ---
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $client = $clientModel->getById($id);
        if ($client) {
            echo json_encode(['status' => 'success', 'data' => $client]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Client introuvable']);
        }
    } else {
        $clients = $clientModel->getAllClients();
        echo json_encode(['status' => 'success', 'data' => $clients]);
    }
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

        // Note: ClientModel::addClient currently returns boolean. 
        // We need the ID for history. 
        // Ideally we should update ClientModel to return ID or use insert_id.
        // For now, let's try to update ClientModel first or do a workaround?
        // Actually, let's update ClientModel to return the ID instead of boolean in the next step.
        // Assuming ClientModel will be updated to return ID or we act on success.
        
        $newId = $clientModel->addClient($nom, $prenom, $tel, $email);

        if ($newId) {
            // Log History
            $details = "Nom: $nom, Prénom: $prenom";
            $historyModel->create('CLIENT', $newId, 'CREATE', $details, $adminId);

            echo json_encode(['status' => 'success', 'message' => 'Client ajouté avec succès']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'enregistrement en base de données']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Le champ NOM est obligatoire']);
    }
    exit;
}

// --- 5. TRAITEMENT PUT (Modifier un client) ---
if ($method === 'PUT') {
    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, true);

    if (isset($data['id']) && isset($data['nom'])) {
        $id = intval($data['id']);
        $nom = htmlspecialchars($data['nom']);
        $prenom = htmlspecialchars($data['prenom'] ?? '');
        $tel = htmlspecialchars($data['telephone'] ?? '');
        $email = htmlspecialchars($data['email'] ?? '');

        // We need a method updateClient in ClientModel
        if ($clientModel->updateClient($id, $nom, $prenom, $tel, $email)) {
             // Log History
             $details = "Mise à jour Client ID: $id. Nom: $nom";
             $historyModel->create('CLIENT', $id, 'UPDATE', $details, $adminId);
             
             echo json_encode(['status' => 'success', 'message' => 'Client modifié avec succès']);
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la modification']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID et Nom requis']);
    }
    exit;
}
?>