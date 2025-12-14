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
        $sql = "SELECT * FROM $this->table";
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

    public function create($libelle, $prix_vente, $prix_achat, $est_service, $description)
    {
        $stmt = mysqli_prepare($this->conn, "INSERT INTO $this->table (libelle, prix_de_vente, prix_achat, est_service, description) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sddis', $libelle, $prix_vente, $prix_achat, $est_service, $description);
        return mysqli_stmt_execute($stmt);
    }

    public function update($id, $libelle, $prix_vente, $prix_achat, $est_service, $description)
    {
        $stmt = mysqli_prepare($this->conn, "UPDATE $this->table SET libelle=?, prix_de_vente=?, prix_achat=?, est_service=?, description=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'sddisi', $libelle, $prix_vente, $prix_achat, $est_service, $description, $id);
        return mysqli_stmt_execute($stmt);
    }

    public function delete($id)
    {
        $stmt = mysqli_prepare($this->conn, "DELETE FROM $this->table WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        return mysqli_stmt_execute($stmt);
    }
}

