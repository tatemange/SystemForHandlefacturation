
<?php 
require_once __DIR__ . '/../config/Database.php';

class DetailDocumentModel {
    private $db;
    private $conn;
    private $table = "DETAIL_DOCUMENT";

    public function __construct($db = null) {
        if ($db && $db instanceof Database) {
            $this->db = $db;
        } else {
            $this->db = new Database();
        }
        $this->conn = $this->db->conn;
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
    public function create($id_document, $id_service_produit, $quantite, $prix_unitaire, $montant, $status = 'EN_ATTENTE'){
        // id_detail est auto-incrémenté
        $sql = "INSERT INTO $this->table (id_document, id_service_produit, quantite, prix_unitaire, montant, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'iiidds', $id_document, $id_service_produit, $quantite, $prix_unitaire, $montant, $status);
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
