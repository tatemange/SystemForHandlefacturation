<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/api_errors.log');
error_reporting(E_ALL);

require_once __DIR__ . '/../php/models/DocumentModel.php';
require_once __DIR__ . '/../php/models/DetailDocumentModel.php';
require_once __DIR__ . '/../php/models/HistoriqueModel.php'; // Included
require_once __DIR__ . '/../php/config/Database.php';

$db = new Database();
$docModel = new DocumentModel($db->conn);
$detailModel = new DetailDocumentModel($db->conn);
$historyModel = new HistoriqueModel($db->conn); // Initialized

$method = $_SERVER['REQUEST_METHOD'];

// SECURITY CHECK
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Accès refusé. Veuillez vous connecter.']);
    exit;
}
$adminId = $_SESSION['admin_id'];

try {

// --- GET: LISTER LES DOCUMENTS ---
if ($method === 'GET') {
    // Si un ID est fourni, on renvoie les détails
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $doc = $docModel->getById($id);
        if ($doc) {
            $details = $detailModel->getByDocumentId($id);
            $doc['details'] = $details;
            
            // Log View
            $historyModel->create('DOCUMENT', $id, 'READ', "Consultation de la facture " . $doc['numero_d'], $adminId);
            
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

    require_once __DIR__ . '/../php/models/ServiceProduitModel.php';
    $serviceModel = new ServiceProduitModel($db->conn);

    // Transaction manuelle (si supporté par le driver/table Engine InnoDB)
    mysqli_begin_transaction($db->conn);

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

            // --- GESTION STOCK ---
            // Vérifier
            if (!$serviceModel->checkStock($id_service, $qty)) {
                // Récupérer infos produit pour message clair
                $prod = $serviceModel->getById($id_service);
                $nomProd = $prod ? $prod['libelle'] : "ID $id_service";
                $stockDispo = $prod ? $prod['quantite_stock'] : '?';
                throw new Exception("Stock insuffisant pour '$nomProd'. Demandé: $qty, Dispo: $stockDispo");
            }
            // Décrémenter
            if (!$serviceModel->decrementStock($id_service, $qty)) {
                throw new Exception("Erreur technique lors de la mise à jour du stock pour ID $id_service");
            }
            // ---------------------

            // BUGFIX: "status" n'est pas une colonne de DETAIL_DOCUMENT
            $resDetail = $detailModel->create($docId, $id_service, $qty, $price, $montantLigne);
            if (!$resDetail) {
                throw new Exception("Erreur lors de l'ajout de la ligne produit ID: $id_service");
            }
        }

        // Log Creation
        $details = "Création facture N° $numero_d. Montant: $total";
        $historyModel->create('DOCUMENT', $docId, 'CREATE', $details, $adminId);

        mysqli_commit($db->conn);
        echo json_encode(['status' => 'success', 'message' => 'Facture créée avec succès', 'id' => $docId, 'numero' => $numero_d]);

    } catch (Throwable $e) {
        mysqli_rollback($db->conn);
        $msg = $e->getMessage();
        error_log("API Error: $msg"); // Log to server log if possible
        echo json_encode(['status' => 'error', 'message' => "Erreur Serveur: " . $msg]);
    }
    exit;
}

// --- PUT: MISE À JOUR (STATUS OU CONTENU) ---
if ($method === 'PUT') {
    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, true);

    if (!isset($data['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'ID requis']);
        exit;
    }

    $id = intval($data['id']);

    // CASE 1: FULL UPDATE (Modification contenu)
    if (isset($data['items']) && isset($data['client_id'])) {
        require_once __DIR__ . '/../php/models/ServiceProduitModel.php';
        $serviceModel = new ServiceProduitModel($db->conn);

        $id_client = intval($data['client_id']);
        $items = $data['items'];
        
        // Calcul nouveau total
        $total = 0;
        foreach ($items as $item) {
            $total += (floatval($item['price']) * intval($item['qty']));
        }

        mysqli_begin_transaction($db->conn);
        try {
            // A. Récupérer les anciennes lignes pour restaurer le stock
            $oldDetails = $detailModel->getByDocumentId($id);
            foreach ($oldDetails as $old) {
                // Restaurer stock
                if (!$serviceModel->incrementStock($old['id_service_produit'], $old['quantite'])) {
                     throw new Exception("Erreur lors de la restauration du stock (Produit ID: " . $old['id_service_produit'] . ")");
                }
                // Supprimer ligne
                if (!$detailModel->delete($old['id_detail'])) {
                    throw new Exception("Erreur lors de la suppression de l'ancienne ligne de détail");
                }
            }

            // B. Mettre à jour l'entête du document
            // Note: DocumentModel needs an update method. Assuming direct query or helper if not present.
            // Let's use direct SQL here if model misses it, or check model. DocumentModel often misses generic update.
            // Documents table: id_document, numero_d, montant_total, status, date_creation, id_client
            // We update client and total.
            $sqlUpdate = "UPDATE DOCUMENT SET id_client = ?, montant_total = ? WHERE id_document = ?";
            $stmtUpdate = mysqli_prepare($db->conn, $sqlUpdate);
            mysqli_stmt_bind_param($stmtUpdate, 'idi', $id_client, $total, $id);
            if (!mysqli_stmt_execute($stmtUpdate)) {
                throw new Exception("Erreur lors de la mise à jour de la facture");
            }

            // C. Insérer les nouvelles lignes
            foreach ($items as $item) {
                $id_service = intval($item['id']);
                $qty = intval($item['qty']);
                $price = floatval($item['price']);
                $montantLigne = $qty * $price;

                // Vérifier Stock (Nouveau)
                if (!$serviceModel->checkStock($id_service, $qty)) {
                    $prod = $serviceModel->getById($id_service);
                    $nomProd = $prod ? $prod['libelle'] : "ID $id_service";
                    $stockDispo = $prod ? $prod['quantite_stock'] : '?';
                    throw new Exception("Stock insuffisant pour '$nomProd'. Demandé: $qty, Dispo: $stockDispo");
                }
                
                // Décrémenter Stock
                if (!$serviceModel->decrementStock($id_service, $qty)) {
                    throw new Exception("Erreur stock pour ID $id_service");
                }

                // Créer détail
                if (!$detailModel->create($id, $id_service, $qty, $price, $montantLigne)) {
                    throw new Exception("Erreur ajout ligne ID $id_service");
                }
            }

            // Log
            $historyModel->create('DOCUMENT', $id, 'UPDATE', "Modification facture (Nouveau total: $total)", $adminId);

            mysqli_commit($db->conn);
            echo json_encode(['status' => 'success', 'message' => 'Facture mise à jour avec succès']);
        } catch (Throwable $e) {
            mysqli_rollback($db->conn);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // CASE 2: STATUS UPDATE ONLY
    if (isset($data['status'])) {
        $status = $data['status'];
        if (!in_array($status, ['EN_COURS', 'PAYE', 'IMPAYE', 'ANNULE'])) {
             echo json_encode(['status' => 'error', 'message' => 'Statut invalide']);
             exit;
        }

        if ($docModel->updateStatus($id, $status)) {
            $historyModel->create('DOCUMENT', $id, 'UPDATE', "Changement statut: $status", $adminId);
            echo json_encode(['status' => 'success', 'message' => 'Statut mis à jour']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de la mise à jour']);
        }
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Données invalides pour mise à jour']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Méthode non supportée']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Exception Globale: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
    ]);
}
?>
