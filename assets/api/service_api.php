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
    require_once __DIR__ . '/../php/models/HistoriqueModel.php'; // Included

    $db = new Database();
    $serviceModel = new ServiceProduitModel($db->conn);
    $historyModel = new HistoriqueModel($db->conn); // Initialized

    $method = $_SERVER['REQUEST_METHOD'];
    $adminId = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 1;

    // --- 3. TRAITEMENT GET (Récupérer la liste ou un item) ---
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $item = $serviceModel->getById($id);
            if ($item) {
                echo json_encode(['status' => 'success', 'data' => $item]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Non trouvé']);
            }
        } else {
            $services = $serviceModel->getAll();
            echo json_encode(['status' => 'success', 'data' => $services]);
        }
        exit;
    }

    // --- 4. TRAITEMENT POST (Création) ---
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $libelle = $data['libelle'] ?? '';
        $prix_vente = floatval($data['prix_de_vente'] ?? 0);
        $prix_achat = floatval($data['prix_achat'] ?? 0);
        $est_service = isset($data['est_service']) && $data['est_service'] == 1 ? 1 : 0;
        $description = $data['description'] ?? '';
        $quantite_stock = intval($data['quantite_stock'] ?? 0);

        if (empty($libelle)) {
            echo json_encode(['status' => 'error', 'message' => 'Le libellé est obligatoire']);
            exit;
        }
        
        // Note: create() returns boolean, ideally need ID.
        // Assuming create returns boolean for now, we miss ID logging unless we update Model.
        // But for ServiceProduitModel, let's assume valid Insert and perform a quick lookup or just log without ID?
        // Better: create logs without ID or 0 if we can't get it easily without changing Model.
        // But let's check ServiceProduitModel...
        // Actually, let's just log "CREATE" with 0 if true, or try to get InsertID if the Model uses the shared connection.
        // $db->conn->insert_id should work if executed on same connection!
        
        if ($serviceModel->create($libelle, $prix_vente, $prix_achat, $est_service, $description, $quantite_stock)) {
            $newId = $db->conn->insert_id; // Check if this works
            
            // Log History
            $historyModel->create('SERVICE', $newId, 'CREATE', "Création: $libelle", $adminId);

            echo json_encode(['status' => 'success', 'message' => 'Élément créé avec succès']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la création']);
        }
        exit;
    }

    // --- 5. TRAITEMENT PUT (Mise à jour) ---
    if ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $id = intval($data['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID invalide']);
            exit;
        }

        $libelle = $data['libelle'] ?? '';
        $prix_vente = floatval($data['prix_de_vente'] ?? 0);
        $prix_achat = floatval($data['prix_achat'] ?? 0);
        $est_service = isset($data['est_service']) && $data['est_service'] == 1 ? 1 : 0;
        $description = $data['description'] ?? '';
        $quantite_stock = intval($data['quantite_stock'] ?? 0);

        if ($serviceModel->update($id, $libelle, $prix_vente, $prix_achat, $est_service, $description, $quantite_stock)) {
            // Log History
            $historyModel->create('SERVICE', $id, 'UPDATE', "Mise à jour: $libelle", $adminId);

            echo json_encode(['status' => 'success', 'message' => 'Mise à jour réussie']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la mise à jour']);
        }
        exit;
    }

    // --- 6. TRAITEMENT DELETE ---
    if ($method === 'DELETE') {
        // On peut passer l'ID dans l'URL ou dans le body. Préférons l'URL pour DELETE standard
        // Mais PHP ne remplit pas $_GET pour DELETE parfois ? Si, query params marchent.
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        // Fallback: lire input si pas dans URL
        if ($id === 0) {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = intval($data['id'] ?? 0);
        }

        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID invalide']);
            exit;
        }

        if ($serviceModel->delete($id)) {
            // Log History
            $historyModel->create('SERVICE', $id, 'DELETE', "Suppression service/produit", $adminId);

            echo json_encode(['status' => 'success', 'message' => 'Suppression réussie']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la suppression']);
        }
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Méthode non supportée']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage()
    ]);
}
?>
