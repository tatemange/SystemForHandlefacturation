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

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tes fichiers CSS -->
    <link rel="stylesheet" href="./assets/css/variableCSS.css">
    <link rel="stylesheet" href="./assets/css/dashboard.css">
    <link rel="stylesheet" href="./assets/css/form.css">
    <link rel="stylesheet" href="./assets/css/documents.css">
    <link rel="stylesheet" href="./assets/css/caisse.css">

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
            case 'documents':
                $fichier = 'assets/php/views/documents.html';
                if (file_exists($fichier)) {
                    include $fichier;
                } else {
                    echo "<h3 style='color:red'>Erreur : Le fichier $fichier est introuvable.</h3>";
                }
                break;

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
                include 'assets/php/views/reglements.html';
                // echo "<h2>Page Caisse (En construction)</h2>";
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

            <!-- Stats Cards Grid -->
            <div class="stats-grid">
                <!-- Card 1: Clients -->
                <div class="card">
                    <div class="stat-card">
                        <div class="stat-icon client-color">
                            <i class="fa fa-users"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Clients</h3>
                            <p class="number"><?php echo $nbClients; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Chiffre d'affaires -->
                <div class="card">
                    <div class="stat-card">
                        <div class="stat-icon money-color">
                            <i class="fa fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Chiffre d'affaires</h3>
                            <p class="number"><?php echo number_format($caTotal, 0, ',', ' '); ?> FCFA</p>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Factures en attente -->
                <div class="card">
                    <div class="stat-card">
                        <div class="stat-icon invoice-color">
                            <i class="fa fa-file-invoice"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Factures en attente</h3>
                            <p class="number"><?php echo $nbFacturesAttente; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="recent-transactions">
                <div class="section-header">
                    <h2>Dernières factures</h2>
                    <a href="dashboard.php?page=documents" class="view-all">Voir tout</a>
                </div>

                <?php if (mysqli_num_rows($resLastDocs) > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>N° Facture</th>
                                    <th>Client</th>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($doc = mysqli_fetch_assoc($resLastDocs)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($doc['numero_d']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($doc['nom'] . ' ' . $doc['prenom']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($doc['date_creation'])); ?></td>
                                    <td><?php echo number_format($doc['montant_total'], 0, ',', ' '); ?> FCFA</td>
                                    <td>
                                        <?php
                                        $statusClass = strtolower($doc['status']);
                                        $statusLabel = $doc['status'];
                                        if ($doc['status'] === 'PAYE') {
                                            $statusClass = 'payee';
                                            $statusLabel = 'Payée';
                                        } elseif ($doc['status'] === 'EN_COURS') {
                                            $statusClass = 'en_attente';
                                            $statusLabel = 'En cours';
                                        } elseif ($doc['status'] === 'IMPAYE') {
                                            $statusClass = 'annulee';
                                            $statusLabel = 'Impayée';
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <?php echo $statusLabel; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-small view" onclick="alert('Voir facture')">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <button class="btn-small print" onclick="alert('Imprimer')">
                                            <i class="fa fa-print"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--label-second); padding: 2rem;">Aucune facture pour le moment.</p>
                <?php endif; ?>
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
    <script src="./assets/js/documents.js"></script>
    <script src="./assets/js/reglements.js"></script>
</body>

</html>