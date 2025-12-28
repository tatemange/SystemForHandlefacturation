<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../php/models/HistoriqueModel.php';
require_once __DIR__ . '/../php/config/Database.php';

$database = new Database();
$db = $database->conn;
$historyModel = new HistoriqueModel($db);

$method = $_SERVER['REQUEST_METHOD'];

// SECURITY CHECK
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Accès refusé']);
    exit;
}

if ($method === 'GET') {
    // Paramètres de pagination
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    
    // Recherche
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $term = $_GET['search'];
        $logs = $historyModel->search($term);
    } 
    // Filtrage Type (si pas de recherche global active, ou combiné? User said search query DB. Usually search overrides filter or combines. Let's assume search overrides for now or filters are cleared)
    elseif (isset($_GET['entity_type']) && !empty($_GET['entity_type'])) {
        $logs = $historyModel->getByEntityType($_GET['entity_type'], $limit, $offset);
    } 
    // Défaut
    else {
        $logs = $historyModel->getAll($limit, $offset);
    }
    
    echo json_encode(['status' => 'success', 'data' => $logs, 'count' => count($logs)]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
