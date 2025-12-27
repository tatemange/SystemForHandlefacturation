<?php
// assets/php/controllers/CaisseController.php

header('Content-Type: application/json');
require_once '../config/Database.php';
require_once '../models/CaisseModel.php';
require_once '../models/ReglementModel.php';
require_once '../models/EnregistrerModel.php';
require_once '../models/HistoriqueModel.php'; // Included

session_start();

$database = new Database();
$db = $database->conn;

$caisseModel = new CaisseModel($db);
$reglementModel = new ReglementModel($db);
$enregistrerModel = new EnregistrerModel($db);
$historyModel = new HistoriqueModel($db); // Initialized

$action = $_GET['action'] ?? '';
$adminId = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 1;

// Sécurité basique
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

switch ($action) {
    case 'list_caisses':
        $caisses = $caisseModel->getAll();
        echo json_encode(['success' => true, 'data' => $caisses]);
        break;

    case 'details_caisse':
        $id_caisse = $_GET['id'] ?? 0;
        $reglements = $caisseModel->getReglements($id_caisse);
        $info = $caisseModel->getById($id_caisse);
        // Totaux
        $total = $reglementModel->getTotalEncaisseParCaisse($id_caisse);
        
        echo json_encode([
            'success' => true, 
            'info' => $info, 
            'reglements' => $reglements,
            'total_encaisse' => $total
        ]);
        break;

    case 'creer_reglement':
        // POST : id_caisse, id_client, montant, mode, reference, id_document (opt)
        $data = json_decode(file_get_contents('php://input'), true);
        
        $id_caisse = $data['id_caisse'];
        $id_client = $data['id_client'];
        $montant = $data['montant'];
        $mode = $data['mode_paiement']; // CASH, etc.
        $ref = $data['reference'] ?? '';
        $id_doc = $data['id_document'] ?? null;

        if($id_doc == 0 || $id_doc == "") $id_doc = null;

        $id_reglement = $reglementModel->create($id_client, $montant, $mode, $ref, $id_doc);

        if ($id_reglement) {
            // Lier à la caisse (EN_ATTENTE)
            $res = $enregistrerModel->enregistrerPaiement($id_caisse, $id_reglement);
            if($res) {
                // Log History
                $details = "Enregistrement règlement: $montant FCFA (Mode: $mode)";
                $historyModel->create('REGLEMENT', $id_reglement, 'CREATE', $details, $adminId);

                echo json_encode(['success' => true, 'message' => 'Règlement créé et en attente de validation']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement en caisse']);
            }
        } else {
             echo json_encode(['success' => false, 'message' => 'Erreur création règlement']);
        }
        break;

    case 'valider_reglement':
        $data = json_decode(file_get_contents('php://input'), true);
        $id_reglement = $data['id_reglement'];
        $id_caisse = $data['id_caisse'];

        $result = $enregistrerModel->validerReglement($id_reglement, $id_caisse);
        
        if ($result === true) {
            // Log History
            $historyModel->create('REGLEMENT', $id_reglement, 'UPDATE', "Validation règlement", $adminId);

            echo json_encode(['success' => true, 'message' => 'Règlement validé avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur validation: ' . $result]);
        }
        break;
        
    case 'annuler_reglement':
         $data = json_decode(file_get_contents('php://input'), true);
         $id_reglement = $data['id_reglement'];
         $id_caisse = $data['id_caisse'];
         
         $result = $enregistrerModel->annulerReglement($id_reglement, $id_caisse);
         if($result === true) {
             // Log History
             $historyModel->create('REGLEMENT', $id_reglement, 'UPDATE', "Annulation règlement", $adminId);

             echo json_encode(['success' => true, 'message' => 'Règlement annulé']);
         } else {
             echo json_encode(['success' => false, 'message' => 'Erreur annulation']);
         }
         break;

    case 'get_clients':
        // Récupérer liste clients pour le select
        require_once '../models/ClientModel.php';
        $clientModel = new ClientModel($db);
        $clients = $clientModel->getAll(); // Assumons que getAll existe
        echo json_encode(['success' => true, 'data' => $clients]);
        break;

    case 'get_client_invoices':
        // Récupérer factures impayées/en_cours pour un client
        require_once '../models/DocumentModel.php';
        $id_client = $_GET['id_client'];
        // On fait une requête directe ici pour simplifier si DocumentModel n'a pas la méthode
        // Mais idéalement on utiliserait DocumentModel.
        // SQL Ad-hoc pour aller vite et bien
        $sql = "SELECT id_document, numero_d, montant_total, status, date_creation 
                FROM DOCUMENT 
                WHERE id_client = ? AND status != 'PAYE'
                ORDER BY date_creation DESC";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id_client);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $docs = mysqli_fetch_all($res, MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $docs]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action inconnue']);
        break;
}
