<?php
// dashboard.php

// 1. Sécurité et Session
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.html");
    exit();
}

// 2. Connexion BDD pour les stats
require_once './assets/php/config/Database.php';
$database = new Database();
$db = $database->conn;

// Récupérer quelques chiffres pour le dashboard
// Nombre de clients
$sqlClient = "SELECT COUNT(*) as total FROM CLIENT";
$resClient = mysqli_query($db, $sqlClient);
$nbClients = mysqli_fetch_assoc($resClient)['total'];

// Factures impayées ou en cours
$sqlDoc = "SELECT COUNT(*) as total FROM DOCUMENT WHERE status != 'PAYE'";
$resDoc = mysqli_query($db, $sqlDoc);
$nbFacturesAttente = mysqli_fetch_assoc($resDoc)['total'];

// Chiffre d'affaires (Total des règlements)
$sqlCA = "SELECT SUM(montant) as total FROM REGLEMENT";
$resCA = mysqli_query($db, $sqlCA);
$caTotal = mysqli_fetch_assoc($resCA)['total'] ?? 0;

// Récupérer les 5 dernières factures
$sqlLastDocs = "SELECT d.numero_d, d.date_creation, d.montant_total, d.status, c.nom, c.prenom 
                FROM DOCUMENT d 
                JOIN CLIENT c ON d.id_client = c.id 
                ORDER BY d.date_creation DESC LIMIT 5";
$resLastDocs = mysqli_query($db, $sqlLastDocs);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MobileMoney Facturation</title>

    <!-- Tes fichiers CSS -->
    <link rel="stylesheet" href="./assets/css/dashboard.css">

</head>

<body>
    <div class="app-container">

        <!-- ================= SIDEBAR (NAVIGATION) ================= -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2>FacturationApp</h2>
            </div>

            <?php 
            // On récupère la page actuelle pour savoir quel menu allumer
            // Si pas de page définie, on considère qu'on est sur 'home'
            $currentPage = $_GET['page'] ?? 'home'; 
            ?>

            <ul class="menu">
                <!-- Tableau de bord -->
                <li class="<?php echo ($currentPage == 'home') ? 'active' : ''; ?>">
                    <a href="dashboard.php?page=home"><i class="fa fa-home"></i> Tableau de bord</a>
                </li>

                <!-- Clients -->
                <li class="<?php echo ($currentPage == 'clients') ? 'active' : ''; ?>">
                    <a href="dashboard.php?page=clients"><i class="fa fa-users"></i> Clients</a>
                </li>

                <!-- Documents -->
                <li class="<?php echo ($currentPage == 'documents') ? 'active' : ''; ?>">
                    <a href="dashboard.php?page=documents"><i class="fa fa-file-invoice"></i> Documents</a>
                </li>

                <!-- Caisse -->
                <li class="<?php echo ($currentPage == 'reglements') ? 'active' : ''; ?>">
                    <a href="dashboard.php?page=reglements"><i class="fa fa-money-bill-wave"></i> Caisse</a>
                </li>

                <!-- Produits & Services (CORRIGÉ) -->
                <li class="<?php echo ($currentPage == 'services') ? 'active' : ''; ?>">
                    <a href="dashboard.php?page=services"><i class="fa fa-box"></i> Produits & Services</a>
                </li>

                <!-- Historique & Rapports (CORRIGÉ) -->
                <li class="<?php echo ($currentPage == 'historique') ? 'active' : ''; ?>">
                    <a href="dashboard.php?page=historique"><i class="fa fa-history"></i> Historique</a>
                </li>

                <!-- Admin (Visible seulement si SUPER_ADMIN) (CORRIGÉ) -->
                <?php if(isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'SUPER_ADMIN'): ?>
                <li class="<?php echo ($currentPage == 'admins') ? 'active' : ''; ?>">
                    <a href="dashboard.php?page=admins"><i class="fa fa-user-shield"></i> Administrateurs</a>
                </li>
                <?php endif; ?>

            </ul>

            <div class="sidebar-footer">
                <a href="./assets/php/logout.php" class="btn-logout">
                    <i class="fa fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </nav>
        <!-- ================= MAIN CONTENT ================= -->
        <main class="main-content">
            <?php
        // 1. Récupération de la page demandée via l'URL
        // Si $_GET['page'] n'existe pas, on met 'home' par défaut
        $page = isset($_GET['page']) ? $_GET['page'] : 'home';

        // 2. Aiguillage (Switch)
        switch ($page) {

            // CAS 1 : GESTION DES CLIENTS
            case 'clients':
                // Attention : Vérifiez si votre fichier s'appelle 'client.html' ou 'clients.html'
                // D'après votre arborescence, c'était 'client.html' (singulier)
                $fichier = 'assets/php/views/clients.html';
                
                if (file_exists($fichier)) {
                    include $fichier;
                } else {
                    echo "<h3 style='color:red'>Erreur : Le fichier $fichier est introuvable.</h3>";
                }
                break;

            // CAS 2 : DOCUMENTS
            case 'documents':
                // include 'assets/php/views/documents.html';
                echo "<h2>Page Documents (En construction)</h2>";
                break;

            // CAS 3 : CAISSE
            case 'reglements':
                // include 'assets/php/views/reglements.html';
                echo "<h2>Page Caisse (En construction)</h2>";
                break;

            // CAS 4 : SERVICES
            case 'services':
                // include 'assets/php/views/services.html';
                echo "<h2>Page Services (En construction)</h2>";
                break;

            // CAS 5 : HISTORIQUE
            case 'historique':
                // include 'assets/php/views/historique.html';
                echo "<h2>Page Historique (En construction)</h2>";
                break;

            // CAS PAR DÉFAUT : ACCUEIL / TABLEAU DE BORD
            case 'home':
            default:
        ?>
            <!-- --- DÉBUT DU CONTENU DU DASHBOARD (HOME) --- -->
            <h1>Tableau de bord</h1>

            <div class="cards-container">
                <div class="card">
                    <h3>Clients</h3>
                    <p class="number">0</p>
                </div>
                <div class="card">
                    <h3>Chiffre d'affaires</h3>
                    <p class="number">0 FCFA</p>
                </div>
                <!-- Ajoutez vos autres cartes ici -->
            </div>


            <div class="recent-orders">
                <h2>Dernières activités</h2>
                <p>Aucune activité pour le moment.</p>
            </div>
            <!-- --- FIN DU CONTENU DU DASHBOARD --- -->


            <?php
                break; // Fin du case 'home'
                } // Fin du switch
            ?>



        </main>
    </div>

    <!-- Script JS pour les interactions si nécessaire -->
    <script src="./assets/js/main.js"></script>
</body>

</html>