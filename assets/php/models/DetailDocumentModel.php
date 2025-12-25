<?php 
require_once __DIR__ . '/../config/Database.php';

class DetailDocumentModel {
    private $conn;
    private $table = "DETAIL_DOCUMENT";

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Récupérer les détails d'un document
    public function getByDocumentId($id_document){
        $sql = "SELECT d.*, s.libelle 
                FROM $this->table d
                JOIN SERVICE_PRODUIT s ON d.id_service_produit = s.id
                WHERE d.id_document = ?";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id_document);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // Créer une ligne de détail
    public function create($id_document, $id_service_produit, $quantite, $prix_unitaire, $montant){
        // id_detail est auto-incrémenté
        // Note: La colonne 'status' n'existe pas dans DETAIL_DOCUMENT, on l'a retirée
        $sql = "INSERT INTO $this->table (id_document, id_service_produit, quantite, prix_unitaire, montant) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'iiidd', $id_document, $id_service_produit, $quantite, $prix_unitaire, $montant);
            $exec = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $exec;
        }
        return false;
    }

    // Supprimer une ligne
    public function delete($id){
        $sql = "DELETE FROM $this->table WHERE id_detail=?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        return mysqli_stmt_execute($stmt);
    }
}
