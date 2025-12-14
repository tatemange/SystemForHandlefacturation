<?php
// On inclut la connexion à la base de données
// Assurez-vous que Database.php existe bien dans assets/php/config/
require_once __DIR__ . '/../config/Database.php';

class ClientModel {
    private $db;
    private $conn;

    public function __construct() {
        // On instancie la classe Database
        $this->db = new Database();
        $this->conn = $this->db->conn;
    }

    // Récupérer tous les clients
    public function getAllClients() {
        $sql = "SELECT * FROM CLIENT ORDER BY id DESC";
        $result = mysqli_query($this->conn, $sql);
        
        if ($result) {
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            return [];
        }
    }

    // Ajouter un client
    public function addClient($nom, $prenom, $tel, $email) {
        $sql = "INSERT INTO CLIENT (nom, prenom, numero_telephone, email, solde, dette) 
                VALUES (?, ?, ?, ?, 0, 0)";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssss", $nom, $prenom, $tel, $email);
            $exec = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $exec;
        } else {
            return false;
        }
    }
}
?>