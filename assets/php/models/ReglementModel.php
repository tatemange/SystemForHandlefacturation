<?php

class ReglementModel {
    private $conn;
    private $table = "REGLEMENT";

    public function __construct($conn){ $this->conn = $conn; }

    public function getAll(){
        $sql = "SELECT * FROM $this->table WHERE is_deleted=0";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getById($id){
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM $this->table WHERE id_reglement=? AND is_deleted=0");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    }

    public function create($montant_r, $id_historique){
        $stmt = mysqli_prepare($this->conn, "INSERT INTO $this->table (montant_r, id_historique, is_deleted) VALUES (?, ?, 0)");
        mysqli_stmt_bind_param($stmt, 'di', $montant_r, $id_historique);
        return mysqli_stmt_execute($stmt);
    }

    public function update($id, $montant_r, $id_historique){
        $stmt = mysqli_prepare($this->conn, "UPDATE $this->table SET montant_r=?, id_historique=? WHERE id_reglement=?");
        mysqli_stmt_bind_param($stmt, 'dii', $montant_r, $id_historique, $id);
        return mysqli_stmt_execute($stmt);
    }

    public function delete($id){
        $stmt = mysqli_prepare($this->conn, "UPDATE $this->table SET is_deleted=1 WHERE id_reglement=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        return mysqli_stmt_execute($stmt);
    }
}
