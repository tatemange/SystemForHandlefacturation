<?php
// assets/php/models/ClientModel.php

class ClientModel {
    private $conn;
    private $table = "CLIENT";

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAll() {
        $sql = "SELECT * FROM $this->table ORDER BY nom ASC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    // Alias pour compatibilité si besoin, mais on préfère getAll()
    public function getAllClients() {
        return $this->getAll();
    }

    public function getById($id) {
        $sql = "SELECT * FROM $this->table WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    }

    public function updateSoldeDette($id, $solde, $dette) {
        $sql = "UPDATE $this->table SET solde = ?, dette = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ddi', $solde, $dette, $id);
        return mysqli_stmt_execute($stmt);
    }

    // Méthode restaurée pour compatibilité avec client_api.php
    public function addClient($nom, $prenom, $tel, $email) {
        // Validation basique
        if(empty($nom)) return false;

        $sql = "INSERT INTO $this->table (nom, prenom, numero_telephone, email, solde, dette) 
                VALUES (?, ?, ?, ?, 0, 0)";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssss", $nom, $prenom, $tel, $email);
            $exec = mysqli_stmt_execute($stmt);
            $newId = $exec ? mysqli_stmt_insert_id($stmt) : false;
            mysqli_stmt_close($stmt);
            return $newId;
        } else {
            return false;
        }
    }

    public function updateClient($id, $nom, $prenom, $tel, $email) {
        $sql = "UPDATE $this->table SET nom = ?, prenom = ?, numero_telephone = ?, email = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssssi', $nom, $prenom, $tel, $email, $id);
        return mysqli_stmt_execute($stmt);
    }
}