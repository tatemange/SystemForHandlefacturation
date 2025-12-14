<?php
session_start();
header('Content-Type: application/json');

// --- 1. SÉCURITÉ (Optionnel) ---
// if (!isset($_SESSION['admin_id'])) {
//     echo json_encode(['status' => 'error', 'message' => 'Non autorisé']);
//     exit;
// }

// --- 2. INCLUSION DU MODÈLE ---
// --- 2. INCLUSION DES FICHIERS ---
try {
    require_once __DIR__ . '/../php/config/Database.php';
    require_once __DIR__ . '/../php/models/ServiceProduitModel.php';

    $db = new Database();
    $serviceModel = new ServiceProduitModel($db->conn);

    $method = $_SERVER['REQUEST_METHOD'];

    // --- 3. TRAITEMENT GET (Récupérer la liste) ---
    if ($method === 'GET') {
        $services = $serviceModel->getAll();
        echo json_encode(['status' => 'success', 'data' => $services]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Méthode non supportée']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
