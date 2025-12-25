<?php

class HistoriqueModel
{
    private $conn;
    private $table = "HISTORIQUE";

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function create($type_action, $commentaire, $solde, $id_reglement = null)
    {
        $sql = "INSERT INTO $this->table (type_action, commentaire, solde, id_reglement, date_action) VALUES (?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssdi', $type_action, $commentaire, $solde, $id_reglement);
        return mysqli_stmt_execute($stmt);
    }

    public function getByClient($id_client) {
        // Cette méthode nécessiterait une jointure complexe si on veut filtrer par client via Reglement
        // Pour l'instant, on liste tout ou on filtre par Reglement si besoin
        // TODO: Implémenter si requis par l'UI
        return [];
    }
    
    public function getAll() {
        $sql = "SELECT * FROM $this->table ORDER BY date_action DESC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}
