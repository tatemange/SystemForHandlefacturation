<?php

class ClientModel {
    private $conn;
    private $table = "CLIENT";

    public function __construct($conn){
        $this->conn = $conn;
    }

    // Récupérer tous les clients non supprimés
    public function getAll(){
        $sql = "SELECT * FROM $this->table WHERE is_deleted = 0";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // Récupérer un client par ID
    public function getById($id){
        $stmt = mysqli_prepare($this->conn, "SELECT * FROM $this->table WHERE id = ? AND is_deleted = 0");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    // Ajouter un client
    public function create($nom, $prenom, $telephone, $email){
        $stmt = mysqli_prepare($this->conn, "INSERT INTO $this->table (nom, prenom, numero_telephone, email, is_deleted) VALUES (?, ?, ?, ?, 0)");
        mysqli_stmt_bind_param($stmt, 'ssss', $nom, $prenom, $telephone, $email);
        return mysqli_stmt_execute($stmt);
    }

    // Mettre à jour un client
    public function update($id, $nom, $prenom, $telephone, $email){
        $stmt = mysqli_prepare($this->conn, "UPDATE $this->table SET nom=?, prenom=?, numero_telephone=?, email=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'ssssi', $nom, $prenom, $telephone, $email, $id);
        return mysqli_stmt_execute($stmt);
    }

    // Suppression logique
    public function delete($id){
        $stmt = mysqli_prepare($this->conn, "UPDATE $this->table SET is_deleted=1 WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        return mysqli_stmt_execute($stmt);
    }
}


