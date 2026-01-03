<?php 
// assets/php/models/DocumentModel.php

class DocumentModel {
    private $conn;
    private $table = "DOCUMENT";

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAll(){
        // Use subquery to avoid ONLY_FULL_GROUP_BY issues
        $sql = "SELECT d.*, c.nom, c.prenom, 
                (SELECT COALESCE(SUM(montant), 0) FROM REGLEMENT WHERE id_document = d.id_document) as montant_regle
                FROM $this->table d
                JOIN CLIENT c ON d.id_client = c.id
                ORDER BY d.date_creation DESC";
        $result = mysqli_query($this->conn, $sql);
        if($result) {
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        return [];
    }

    public function getById($id){
        $sql = "SELECT d.*, 
                COALESCE((SELECT SUM(montant) FROM REGLEMENT WHERE id_document = d.id_document), 0) as montant_regle
                FROM $this->table d 
                WHERE id_document=?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    }

    public function create($numero_d, $montant_total, $status, $id_client){
        $sql = "INSERT INTO $this->table (numero_d, montant_total, status, id_client) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sdsi', $numero_d, $montant_total, $status, $id_client);
            if(mysqli_stmt_execute($stmt)) {
                $id = mysqli_insert_id($this->conn);
                return $id;
            }
        }
        return false;
    }

    public function updateStatus($id, $status){
        $sql = "UPDATE $this->table SET status=? WHERE id_document=?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $status, $id);
        return mysqli_stmt_execute($stmt);
    }
}
