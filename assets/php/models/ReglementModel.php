<?php

class ReglementModel {
    private $conn;
    private $table = "REGLEMENT";

    public function __construct($conn){ $this->conn = $conn; }

    public function getAll(){
        $sql = "SELECT r.*, c.nom, c.prenom, d.numero_d 
                FROM $this->table r
                JOIN CLIENT c ON r.id_client = c.id
                LEFT JOIN DOCUMENT d ON r.id_document = d.id_document
                ORDER BY r.date_reglement DESC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getById($id){
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM $this->table WHERE id_reglement=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    }

    // Création d'un règlement. 
    // IMPORTANT : Cette méthode doit retourner l'ID du règlement créé pour l'utiliser dans ENREGISTRER
    public function create($id_client, $montant, $mode_paiement, $reference, $id_document = null){
        $sql = "INSERT INTO $this->table (id_client, montant, mode_paiement, reference, id_document) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'idssi', $id_client, $montant, $mode_paiement, $reference, $id_document);
        
        if(mysqli_stmt_execute($stmt)) {
            return mysqli_insert_id($this->conn);
        }
        return false;
    }

    // Statistiques pour les rapports
    public function getTotalEncaisseParCaisse($id_caisse) {
        // Nécessite une jointure avec ENREGISTRER validé
        $sql = "SELECT SUM(r.montant) as total 
                FROM $this->table r
                JOIN ENREGISTRER e ON r.id_reglement = e.id_reglement
                WHERE e.id_caisse = ? AND e.status = 'VALIDE'";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id_caisse);
        mysqli_stmt_execute($stmt);
        $res = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        return $res['total'] ?? 0;
    }

    public function getTotalEncaisseParJour($date) {
        // $date au format 'Y-m-d'
        $sql = "SELECT SUM(r.montant) as total 
                FROM $this->table r
                JOIN ENREGISTRER e ON r.id_reglement = e.id_reglement
                WHERE DATE(r.date_reglement) = ? AND e.status = 'VALIDE'";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $date);
        mysqli_stmt_execute($stmt);
        $res = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        return $res['total'] ?? 0;
    }

    public function getTotalParModePaiement() {
        $sql = "SELECT mode_paiement, SUM(r.montant) as total 
                FROM $this->table r
                JOIN ENREGISTRER e ON r.id_reglement = e.id_reglement
                WHERE e.status = 'VALIDE'
                GROUP BY mode_paiement";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}
