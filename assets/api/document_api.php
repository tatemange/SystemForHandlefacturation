<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../php/models/DocumentModel.php';
require_once __DIR__ . '/../php/models/DetailDocumentModel.php';
require_once __DIR__ . '/../php/config/Database.php';

$db = new Database();
$docModel = new DocumentModel($db);
$detailModel = new DetailDocumentModel($db);

$method = $_SERVER['REQUEST_METHOD'];

// --- GET: LISTER LES DOCUMENTS ---
if ($method === 'GET') {
    // Si un ID est fourni, on renvoie les détails
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $doc = $docModel->getById($id);
        if ($doc) {
            $details = $detailModel->getByDocumentId($id);
            $doc['details'] = $details;
            echo json_encode(['status' => 'success', 'data' => $doc]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Document introuvable']);
        }
    } else {
        // Liste globale
        $docs = $docModel->getAll();
        echo json_encode(['status' => 'success', 'data' => $docs]);
    }
    exit;
}

// --- POST: CRÉER UN DOCUMENT (FACTURE) ---
if ($method === 'POST') {
    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, true);

    if (!isset($data['client_id']) || empty($data['items'])) {
        echo json_encode(['status' => 'error', 'message' => 'Données incomplètes (Client ou Articles manquants)']);
        exit;
    }

    $id_client = intval($data['client_id']);
    $items = $data['items'];
    
    // Calcul du total
    $total = 0;
    foreach ($items as $item) {
        $total += (floatval($item['price']) * intval($item['qty']));
    }

    // Génération Numéro Facture (Format: FAC-YYYYMMDD-XXXX)
    $prefix = "FAC-" . date('Ymd') . "-";
    $rand = strtoupper(substr(uniqid(), -4));
    $numero_d = $prefix . $rand;

    // Transaction manuelle (si supporté par le driver/table Engine InnoDB)
    mysqli_begin_transaction($docModel->conn);

    try {
        // 1. Créer l'en-tête
        $docId = $docModel->create($numero_d, $total, 'EN_COURS', $id_client);

        if (!$docId) {
            throw new Exception("Erreur lors de la création de l'en-tête de facture");
        }

        // 2. Créer les lignes
        foreach ($items as $item) {
            $id_service = intval($item['id']);
            $qty = intval($item['qty']);
            $price = floatval($item['price']);
            $montantLigne = $qty * $price;

            $status = isset($item['status']) ? $item['status'] : 'EN_ATTENTE';
            $resDetail = $detailModel->create($docId, $id_service, $qty, $price, $montantLigne, $status);
            if (!$resDetail) {
                throw new Exception("Erreur lors de l'ajout de la ligne produit ID: $id_service");
            }
        }

        mysqli_commit($docModel->conn);
        echo json_encode(['status' => 'success', 'message' => 'Facture créée avec succès', 'id' => $docId, 'numero' => $numero_d]);

    } catch (Exception $e) {
        mysqli_rollback($docModel->conn);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// --- PUT: MISE À JOUR (STATUS) ---
if ($method === 'PUT') {
    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, true);

    if (!isset($data['id']) || !isset($data['status'])) {
        echo json_encode(['status' => 'error', 'message' => 'ID et Status requis']);
        exit;
    }

    $id = intval($data['id']);
    $status = $data['status'];
    
    // Validation simple du statut
    if (!in_array($status, ['EN_COURS', 'PAYE', 'IMPAYE', 'ANNULE'])) {
         echo json_encode(['status' => 'error', 'message' => 'Statut invalide']);
         exit;
    }

    if ($docModel->updateStatus($id, $status)) {
        echo json_encode(['status' => 'success', 'message' => 'Statut mis à jour']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la mise à jour']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Méthode non supportée']);
?>
