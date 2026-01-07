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
    
    // Collect Filters
    $filters = [];
    if (isset($_GET['entity_type']) && !empty($_GET['entity_type'])) {
        $filters['entity_type'] = $_GET['entity_type'];
    }
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $filters['search'] = $_GET['search'];
    }
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $filters['start_date'] = $_GET['start_date'];
    }
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $filters['end_date'] = $_GET['end_date'];
    }

    $logs = $historyModel->getFilteredLogs($filters, $limit, $offset);
    
    echo json_encode(['status' => 'success', 'data' => $logs, 'count' => count($logs)]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
