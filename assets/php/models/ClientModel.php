<?php
// On inclut la connexion à la base de données
// Assurez-vous que Database.php existe bien dans assets/php/config/
require_once __DIR__ . '/../config/Database.php';

class ClientModel {
    private $pdo;

    public function __construct() {
        // On récupère l'instance de connexion
        $this->pdo = Database::getInstance();
    }

    // Récupérer tous les clients
    public function getAllClients() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM CLIENT ORDER BY id DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return []; // Retourne un tableau vide en cas d'erreur
        }
    }

    // Ajouter un client
    public function addClient($nom, $prenom, $tel, $email) {
        try {
            $sql = "INSERT INTO CLIENT (nom, prenom, numero_telephone, email, solde, dette) 
                    VALUES (:nom, :prenom, :tel, :email, 0, 0)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':tel' => $tel,
                ':email' => $email
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>