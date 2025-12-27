<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../php/models/HistoriqueModel.php';
require_once __DIR__ . '/../php/config/Database.php';

$database = new Database();
$db = $database->conn;
$historyModel = new HistoriqueModel($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Optional filtering
    if (isset($_GET['entity_type'])) {
        $logs = $historyModel->getByEntityType($_GET['entity_type']);
    } else {
        $logs = $historyModel->getAll();
    }
    
    echo json_encode(['status' => 'success', 'data' => $logs]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
