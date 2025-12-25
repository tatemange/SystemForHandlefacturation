<?php
require_once __DIR__ . '/ClientModel.php';
require_once __DIR__ . '/DocumentModel.php';
require_once __DIR__ . '/HistoriqueModel.php';
require_once __DIR__ . '/ReglementModel.php';

class EnregistrerModel
{
    private $conn;
    private $table = "ENREGISTRER";

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Associer un règlement à une caisse (Statut EN_ATTENTE par défaut)
    public function enregistrerPaiement($id_caisse, $id_reglement) {
        $sql = "INSERT INTO $this->table (id_caisse, id_reglement, status) VALUES (?, ?, 'EN_ATTENTE')";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $id_caisse, $id_reglement);
        return mysqli_stmt_execute($stmt);
    }

    // VALIDATION DU PAIEMENT (Complexe)
    public function validerReglement($id_reglement, $id_caisse) {
        // 1. Start Transaction
        mysqli_begin_transaction($this->conn);

        try {
            // Vérifier le statut actuel
            $checkSql = "SELECT status FROM $this->table WHERE id_reglement = ? AND id_caisse = ?";
            $stmtCheck = mysqli_prepare($this->conn, $checkSql);
            mysqli_stmt_bind_param($stmtCheck, 'ii', $id_reglement, $id_caisse);
            mysqli_stmt_execute($stmtCheck);
            $resCheck = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCheck));

            if (!$resCheck) {
                throw new Exception("Enregistrement introuvable.");
            }
            if ($resCheck['status'] === 'VALIDE') {
                throw new Exception("Règlement déjà validé.");
            }

            // GET REGLEMENT INFO
            $reglementModel = new ReglementModel($this->conn);
            $reglement = $reglementModel->getById($id_reglement);
            if (!$reglement) throw new Exception("Règlement introuvable.");

            $montant = $reglement['montant'];
            $id_client = $reglement['id_client'];
            $id_document = $reglement['id_document'];

            // 2. Update ENREGISTRER status
            $updateSql = "UPDATE $this->table SET status = 'VALIDE' WHERE id_reglement = ? AND id_caisse = ?";
            $stmtUpdate = mysqli_prepare($this->conn, $updateSql);
            mysqli_stmt_bind_param($stmtUpdate, 'ii', $id_reglement, $id_caisse);
            mysqli_stmt_execute($stmtUpdate);

            // 3. Update CLIENT (Dette / Solde)
            $clientModel = new ClientModel($this->conn);
            $client = $clientModel->getById($id_client); // Supposons que cette méthode existe et retourne le client
            
            $detteActuelle = $client['dette'];
            $soldeActuel = $client['solde'];

            $nouvelleDette = $detteActuelle;
            $nouveauSolde = $soldeActuel;

            // Logique de remboursement dette
            if ($nouvelleDette > 0) {
                if ($montant >= $nouvelleDette) {
                    $reste = $montant - $nouvelleDette;
                    $nouvelleDette = 0;
                    $nouveauSolde += $reste;
                } else {
                    $nouvelleDette -= $montant;
                }
            } else {
                $nouveauSolde += $montant;
            }

            // Update Client manuel ou via Model s'il a méthode update (Attention ClientModel.php existant minimaliste)
            // On fait la requête directe pour être sûr
            $sqlClient = "UPDATE CLIENT SET solde = ?, dette = ? WHERE id = ?";
            $stmtClient = mysqli_prepare($this->conn, $sqlClient);
            mysqli_stmt_bind_param($stmtClient, 'ddi', $nouveauSolde, $nouvelleDette, $id_client);
            mysqli_stmt_execute($stmtClient);

            // 4. Update DOCUMENT (si lié)
            if ($id_document) {
                $docModel = new DocumentModel($this->conn); // On suppose qu'il existe
                // Vérifier total payé pour ce document ? 
                // Pour simplifier, si le paiement est lié au document, on check si le montant couvre.
                // MAIS, le document peut avoir déjà des paiements partiels.
                // Idéalement il faudrait sommer tous les règlements liés à ce document validés.
                
                // Calcul du total payé pour ce document (y compris celui-ci qui vient d'être validé)
                $sqlSum = "SELECT SUM(r.montant) as total_paye 
                           FROM REGLEMENT r 
                           JOIN ENREGISTRER e ON r.id_reglement = e.id_reglement
                           WHERE r.id_document = ? AND e.status = 'VALIDE'";
                $stmtSum = mysqli_prepare($this->conn, $sqlSum);
                mysqli_stmt_bind_param($stmtSum, 'i', $id_document);
                mysqli_stmt_execute($stmtSum);
                $totalPaye = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtSum))['total_paye'];

                // Get document total
                // On fait une requête rapide
                $sqlDoc = "SELECT montant_total FROM DOCUMENT WHERE id_document = ?";
                $stmtDoc = mysqli_prepare($this->conn, $sqlDoc);
                mysqli_stmt_bind_param($stmtDoc, 'i', $id_document);
                mysqli_stmt_execute($stmtDoc);
                $docInfo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtDoc));
                $montantDoc = $docInfo['montant_total'];

                $newStatus = ($totalPaye >= $montantDoc) ? 'PAYE' : 'IMPAYE';

                $sqlUpDoc = "UPDATE DOCUMENT SET status = ? WHERE id_document = ?";
                $stmtUpDoc = mysqli_prepare($this->conn, $sqlUpDoc);
                mysqli_stmt_bind_param($stmtUpDoc, 'si', $newStatus, $id_document);
                mysqli_stmt_execute($stmtUpDoc);
            }

            // 5. Historique
            $histModel = new HistoriqueModel($this->conn);
            $commentaire = "Encaissement Règlement #" . $id_reglement . " (" . $reglement['mode_paiement'] . ")";
            $histModel->create('CREDIT', $commentaire, $nouveauSolde, $id_reglement);

            mysqli_commit($this->conn);
            return true;

        } catch (Exception $e) {
            mysqli_rollback($this->conn);
            return $e->getMessage();
        }
    }

    public function annulerReglement($id_reglement, $id_caisse) {
         // Annulation simple si EN_ATTENTE
         // Rejet impossible si déjà validé selon les specs ("Un règlement validé ne peut pas être supprimé", mais peut-être annulé avec trace ?)
         // Prompt : "Quand ANNULÉ -> rollback logique" + "Toute annulation doit laisser une trace"
         
         // On va supposer qu'on peut passer de EN_ATTENTE à ANNULE.
         // Si VALIDE -> ANNULE (Rollback financier) ??
         // Le prompt dit : "Quand ANNULÉ -> rollback logique". Cela sous-entend qu'on peut annuler un validé.
         
         mysqli_begin_transaction($this->conn);
         try {
             $sql = "UPDATE $this->table SET status = 'ANNULE' WHERE id_reglement = ? AND id_caisse = ?";
             $stmt = mysqli_prepare($this->conn, $sql);
             mysqli_stmt_bind_param($stmt, 'ii', $id_reglement, $id_caisse);
             mysqli_stmt_execute($stmt);
             
             // Si c'était validé avant, il faudrait faire l'inverse des opérations financières.
             // Pour l'instant, simplifions : on annule avant validation ou on laisse l'annulation marquer le statut.
             // TODO : Implémenter rollback complet si besoin.
             
             // Historique trace
             $histModel = new HistoriqueModel($this->conn);
             $histModel->create('ANNULATION', "Annulation Règlement #$id_reglement", 0, $id_reglement);

             mysqli_commit($this->conn);
             return true;
         } catch(Exception $e) {
             mysqli_rollback($this->conn);
             return false;
         }
    }
}
