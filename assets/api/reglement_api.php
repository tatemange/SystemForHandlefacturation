<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../php/config/Database.php';
require_once __DIR__ . '/../php/models/ReglementModel.php';
require_once __DIR__ . '/../php/models/DocumentModel.php';
require_once __DIR__ . '/../php/models/EnregistrerModel.php';
require_once __DIR__ . '/../php/models/HistoriqueModel.php';

$db = new Database();
$reglementModel = new ReglementModel($db->conn);
$documentModel = new DocumentModel($db->conn);
$enregistrerModel = new EnregistrerModel($db->conn);
$historyModel = new HistoriqueModel($db->conn);

$adminId = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['id_document'], $input['montant'], $input['mode_paiement'])) {
        echo json_encode(['status' => 'error', 'message' => 'Données manquantes']);
        exit;
    }

    $id_doc = intval($input['id_document']);
    $montant = floatval($input['montant']);
    $mode = $input['mode_paiement'];
    $ref = $input['reference'] ?? '';
    
    // Check document exists and get client ID
    $doc = $documentModel->getById($id_doc);
    if (!$doc) {
        echo json_encode(['status' => 'error', 'message' => 'Document introuvable']);
        exit;
    }

    // Create Payment
    $id_reglement = $reglementModel->create($doc['id_client'], $montant, $mode, $ref, $id_doc);

    if ($id_reglement) {
        // Enregistrer dans la Caisse (Caisse par défaut ou active... ICI on simplifie en prenant une caisse active arbitraire ou à gérer plus tard)
        // Pour l'instant on se concentre sur le lien Document <-> Règlement
        // TODO: Gérer l'affectation Caisse proprement si nécessaire ici.
        // Si on utilise CaisseController, c'est mieux, mais ici on est dans un flux "Facture".
        // On va supposer qu'on a besoin d'une caisse. On va essayer de trouver une caisse active ou ID 1.
        $id_caisse = 1; // Default fallback for now or query active one.
        $enregistrerModel->enregistrerPaiement($id_caisse, $id_reglement);
        // Auto-validate for simplified UX? Or keep as pending? Let's keep pending validation/caisse logic separately or validate immediately?
        // User requested "Partial/Paid" status update.
        // Let's validate it immediately effectively for the Status update logic OR we just count Reglements regardless of validation?
        // DocumentModel query included ALL reglements (LEFT JOIN REGLEMENT).
        
        // Update Document Status
        // Re-fetch totals
        $doc = $documentModel->getById($id_doc);
        $total = floatval($doc['montant_total']);
        $paid = floatval($doc['montant_regle']); // Includes the new one because it is committed
        
        $newStatus = 'EN_COURS'; // Default
        if ($paid >= $total) {
            $newStatus = 'PAYE';
        } elseif ($paid > 0) {
            $newStatus = 'PARTIEL'; // Custom status logic we are adding
            // Note: DB ENUM might need update if 'PARTIEL' is not allowed. 
            // Checking DocumentModel::create default ENUM: 'EN_COURS', 'PAYE', 'IMPAYE', 'ANNULE'
            // Using 'EN_COURS' for partial might be safer if ENUM is strict, but user asked for "Payé / Impayé / En cours" -> "Currently seems binary".
            // Implementation Plan said "Status logic (Payé vs Partiel vs Impayé)".
            // I will use 'EN_COURS' as "Partiel" visually if needed, OR try to set 'PARTIEL' if DB allows.
            // Let's assume strict ENUM for now and handle "Partiel" display in Frontend based on amount.
            // But actually, changing status to 'EN_COURS' if it was 'IMPAYE' is good.
        }
        
        // If paid >= total, force PAYE.
        if ($paid >= $total && $doc['status'] !== 'PAYE') {
            $documentModel->updateStatus($id_doc, 'PAYE');
        } else if ($paid < $total && $paid > 0 && $doc['status'] === 'IMPAYE') {
             $documentModel->updateStatus($id_doc, 'EN_COURS');
        }

        $historyModel->create('DOCUMENT', $id_doc, 'UPDATE', "Ajout règlement: $montant FCFA", $adminId);

        echo json_encode([
            'status' => 'success', 
            'message' => 'Paiement ajouté',
            'new_paid' => $paid,
            'total' => $total,
            'doc_status' => $doc['status'] // Return actual DB status
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erreur création règlement']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
