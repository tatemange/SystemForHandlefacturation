<?php
// assets/php/models/AdminModel.php

class AdminModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Vérifier si un utilisateur existe déjà (username ou tel)
    public function exists($username, $phone)
    {
        $sql = "SELECT id_admin FROM ADMIN WHERE username = ? OR telephone = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $username, $phone);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $count = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);
        return $count > 0;
    }

    // Créer un nouvel admin
    public function create($nom, $prenom, $username, $phone, $passwordHash)
    {
        $sql = "INSERT INTO ADMIN (nom, prenom, username, telephone, mot_de_passe) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssss", $nom, $prenom, $username, $phone, $passwordHash);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    // Récupérer un admin par username ou téléphone (pour le login)
    public function getByLoginIdentifier($identifier)
    {
        $sql = "SELECT * FROM ADMIN WHERE username = ? OR telephone = ? LIMIT 1";
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $identifier, $identifier);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $admin = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            return $admin;
        }
        return null;
    }

    // Mettre à jour la date de dernier login
    public function updateLastLogin($id_admin)
    {
        $sql = "UPDATE ADMIN SET dernier_login = NOW() WHERE id_admin = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id_admin);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}