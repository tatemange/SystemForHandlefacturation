<?php

class ServiceProduitModel
{
    private $conn;
    private $table = "SERVICE_PRODUIT";

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getAll()
    {
        // On récupère tout
        $sql = "SELECT * FROM $this->table ORDER BY libelle ASC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getById($id)
    {
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM $this->table WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    }

    public function checkStock($id, $qtyRequested) {
        $item = $this->getById($id);
        if (!$item) return false;
        
        // Si c'est un service (est_service = 1), pas de stock -> OK
        if ($item['est_service'] == 1) return true;

        // Si c'est un produit, on vérifie
        if ($item['quantite_stock'] >= $qtyRequested) {
            return true;
        }
        
        return false;
    }

    public function decrementStock($id, $qty) {
        $item = $this->getById($id);
        if (!$item || $item['est_service'] == 1) return true; // Rien à faire si Service

        // Décrémentation
        $sql = "UPDATE $this->table SET quantite_stock = quantite_stock - ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $qty, $id);
        return mysqli_stmt_execute($stmt);
    }

    // Gestion Création / Update avec Stock
    public function create($libelle, $prix_vente, $prix_achat, $est_service, $description, $quantite_stock = 0)
    {
        $sql = "INSERT INTO $this->table (libelle, prix_de_vente, prix_achat, est_service, description, quantite_stock) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sddisi', $libelle, $prix_vente, $prix_achat, $est_service, $description, $quantite_stock);
        return mysqli_stmt_execute($stmt);
    }

    public function update($id, $libelle, $prix_vente, $prix_achat, $est_service, $description, $quantite_stock)
    {
        $sql = "UPDATE $this->table SET libelle=?, prix_de_vente=?, prix_achat=?, est_service=?, description=?, quantite_stock=? WHERE id=?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sddisii', $libelle, $prix_vente, $prix_achat, $est_service, $description, $quantite_stock, $id);
        return mysqli_stmt_execute($stmt);
    }

    public function delete($id)
    {
        $stmt = mysqli_prepare($this->conn, "DELETE FROM $this->table WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        return mysqli_stmt_execute($stmt);
    }
}
