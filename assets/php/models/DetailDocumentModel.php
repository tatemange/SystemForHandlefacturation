

<?php 

class DetailDocumentModel {
    private $conn;
    private $table = "DETAIL_DOCUMENT";

    public function __construct($conn){ $this->conn = $conn; }

    public function getAll(){
        $sql = "SELECT * FROM $this->table WHERE is_deleted=0";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getById($id){
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM $this->table WHERE id_detail=? AND is_deleted=0");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    }

    public function create($id_document, $id_service_produit, $quantite, $prix_unitaire, $montant){
        $stmt = mysqli_prepare($this->conn, "INSERT INTO $this->table (id_document, id_service_produit, quantite, prix_unitaire, montant, is_deleted) VALUES (?, ?, ?, ?, ?, 0)");
        mysqli_stmt_bind_param($stmt, 'iiidd', $id_document, $id_service_produit, $quantite, $prix_unitaire, $montant);
        return mysqli_stmt_execute($stmt);
    }

    public function update($id, $id_document, $id_service_produit, $quantite, $prix_unitaire, $montant){
        $stmt = mysqli_prepare($this->conn, "UPDATE $this->table SET id_document=?, id_service_produit=?, quantite=?, prix_unitaire=?, montant=? WHERE id_detail=?");
        mysqli_stmt_bind_param($stmt, 'iiiddi', $id_document, $id_service_produit, $quantite, $prix_unitaire, $montant, $id);
        return mysqli_stmt_execute($stmt);
    }

    public function delete($id){
        $stmt = mysqli_prepare($this->conn, "UPDATE $this->table SET is_deleted=1 WHERE id_detail=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        return mysqli_stmt_execute($stmt);
    }
}
