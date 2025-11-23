
<?php
// assets/php/controllers/AdminController.php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/AdminModel.php';

class AdminController
{
    private $model;
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->conn;
        $this->model = new AdminModel($this->db);
    }

    // Traitement de l'inscription
    public function register($data)
    {
        // Nettoyage des entrées
        $nom = htmlspecialchars(strip_tags($data['nom']));
        $prenom = htmlspecialchars(strip_tags($data['prenom']));
        $phone = htmlspecialchars(strip_tags($data['phone']));
        $username = htmlspecialchars(strip_tags($data['userName']));
        $password = $data['user_password'];

        // Vérifications basiques
        if (empty($nom) || empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Veuillez remplir les champs obligatoires.'];
        }

        // Vérifier doublons
        if ($this->model->exists($username, $phone)) {
            return ['success' => false, 'message' => 'Le nom d\'utilisateur ou le téléphone existe déjà.'];
        }

        // Hasher le mot de passe
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Création
        if ($this->model->create($nom, $prenom, $username, $phone, $passwordHash)) {
            return ['success' => true, 'message' => 'Compte créé avec succès. Vous pouvez vous connecter.'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la création du compte.'];
        }
    }

    // Traitement de la connexion
    public function login($data)
    {
        $login = htmlspecialchars(strip_tags($data['login']));
        $password = $data['login_password'];

        $admin = $this->model->getByLoginIdentifier($login);

        if ($admin) {
            // Vérifier le statut
            if ($admin['status'] !== 'ACTIF') {
                return ['success' => false, 'message' => 'Votre compte est ' . strtolower($admin['status']) . '.'];
            }

            // Vérifier le mot de passe
            if (password_verify($password, $admin['mot_de_passe'])) {
                // Démarrer la session
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION['admin_id'] = $admin['id_admin'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_role'] = $admin['role'];

                // Mettre à jour dernier login
                $this->model->updateLastLogin($admin['id_admin']);

                return ['success' => true, 'message' => 'Connexion réussie.', 'redirect' => 'dashboard.php']; // Adapte dashboard.php selon ta page d'accueil
            }
        }

        return ['success' => false, 'message' => 'Identifiants incorrects.'];
    }
}