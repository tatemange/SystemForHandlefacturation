<?php

class CaisseModel
{
    private $conn;
    private $table = "CAISSE";

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
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM $this->table WHERE id_caisse=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    }

    public function create($intitule_caisse, $responsable)
    {
        $stmt = mysqli_prepare($this->conn, "INSERT INTO $this->table (intitule_caisse, responsable) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, 'ss', $intitule_caisse, $responsable);
        return mysqli_stmt_execute($stmt);
    }

    public function update($id, $intitule_caisse, $responsable)
    {
        $stmt = mysqli_prepare($this->conn, "UPDATE $this->table SET intitule_caisse=?, responsable=? WHERE id_caisse=?");
        mysqli_stmt_bind_param($stmt, 'ssi', $intitule_caisse, $responsable, $id);
        return mysqli_stmt_execute($stmt);
    }

    // Récupérer les règlements associés à une caisse via la table ENREGISTRER
    public function getReglements($id_caisse) {
        $sql = "SELECT r.*, e.status as statut_validation, c.nom, c.prenom
                FROM REGLEMENT r
                JOIN ENREGISTRER e ON r.id_reglement = e.id_reglement
                JOIN CLIENT c ON r.id_client = c.id
                WHERE e.id_caisse = ?
                ORDER BY r.date_reglement DESC";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id_caisse);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}
