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
        $sql = "SELECT * FROM $this->table WHERE is_deleted=0";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getById($id)
    {
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM $this->table WHERE id_caisse=? AND is_deleted=0");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    }

    public function create($intitulle_caisse, $responsable)
    {
        $stmt = mysqli_prepare($this->conn, "INSERT INTO $this->table (intitulle_caisse, responsable, is_deleted) VALUES (?, ?, 0)");
        mysqli_stmt_bind_param($stmt, 'ss', $intitulle_caisse, $responsable);
        return mysqli_stmt_execute($stmt);
    }

    public function update($id, $intitulle_caisse, $responsable)
    {
        $stmt = mysqli_prepare($this->conn, "UPDATE $this->table SET intitulle_caisse=?, responsable=? WHERE id_caisse=?");
        mysqli_stmt_bind_param($stmt, 'ssi', $intitulle_caisse, $responsable, $id);
        return mysqli_stmt_execute($stmt);
    }

    public function delete($id)
    {
        $stmt = mysqli_prepare($this->conn, "UPDATE $this->table SET is_deleted=1 WHERE id_caisse=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        return mysqli_stmt_execute($stmt);
    }
}
