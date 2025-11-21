<?php 

/**
 * Contrôleur pour gérer les clients
 * Actions : liste, ajouter, modifier, supprimer
 * Interagit avec ClientModel et envoie les données à la vue
 */

include_once '../config/Database.php';
include_once '../models/ClientModel.php';

class ClientController {
    private $clientModel;
    private $db;

    public function __construct(){
        $this->db = new Database();
        $this->clientModel = new ClientModel($this->db->conn);
    }

    // Liste tous les clients (JSON pour AJAX)
    public function liste(){
        $clients = $this->clientModel->getAll();
        header('Content-Type: application/json');
        echo json_encode($clients);
    }

    // Ajouter un client via POST AJAX
    public function ajouter(){
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $telephone = $_POST['numero_telephone'] ?? '';
        $email = $_POST['email'] ?? '';

        $result = $this->clientModel->create($nom, $prenom, $telephone, $email);
        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
    }

    // Modifier un client via POST AJAX
    public function modifier(){
        $id = $_POST['id'] ?? 0;
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $telephone = $_POST['numero_telephone'] ?? '';
        $email = $_POST['email'] ?? '';

        $result = $this->clientModel->update($id, $nom, $prenom, $telephone, $email);
        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
    }

    // Supprimer un client via POST AJAX (suppression logique)
    public function supprimer(){
        $id = $_POST['id'] ?? 0;
        $result = $this->clientModel->delete($id);
        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
    }
}


// ---------------------------
// Point d'entrée simple pour tester le controller via URL
// Exemple : ?action=liste
// ---------------------------
$controller = new ClientController();
$action = $_GET['action'] ?? 'liste';

if(method_exists($controller, $action)){
    $controller->$action();
} else {
    echo json_encode(['error' => 'Action non trouvée']);
}
