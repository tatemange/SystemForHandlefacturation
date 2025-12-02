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
            
            <ul class="menu">
                <!-- Accueil -->
                <li class="active">
                    <a href="dashboard.php"><i class="fa fa-home"></i> Tableau de bord</a>
                </li>

                <!-- Gestion Clients -->
                <li>
                    <a href="./php/views/clients.php"><i class="fa fa-users"></i> Clients</a>
                </li>

                <!-- Gestion Documents (Factures/Devis) -->
                <li>
                    <a href="./php/views/documents.php"><i class="fa fa-file-invoice"></i> Factures & Devis</a>
                </li>

                <!-- Gestion Règlements (Caisse) -->
                <li>
                    <a href="./php/views/reglements.php"><i class="fa fa-money-bill-wave"></i> Paiements & Caisse</a>
                </li>

                <!-- Catalogue Produits/Services -->
                <li>
                    <a href="./php/views/services.php"><i class="fa fa-box"></i> Produits & Services</a>
                </li>

                <!-- Historique & Rapports -->
                <li>
                    <a href="./php/views/historique.php"><i class="fa fa-history"></i> Historique</a>
                </li>
                
                <!-- Admin (Visible seulement si SUPER_ADMIN par exemple) -->
                <?php if($_SESSION['admin_role'] === 'SUPER_ADMIN'): ?>
                <li>
                    <a href="./php/views/admin_users.php"><i class="fa fa-user-shield"></i> Administrateurs</a>
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
            
            <!-- Top Header -->
            <header class="top-bar">
                <div class="page-title">
                    <h1>Tableau de bord</h1>
                </div>
                <div class="user-info">
                    <span>Bonjour, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></span>
                    <span class="role-badge"><?php echo $_SESSION['admin_role']; ?></span>
                </div>
            </header>

            <!-- Section 1 : Cartes Statistiques (KPI) -->
            <section class="stats-grid">
                <!-- Carte Clients -->
                <div class="card stat-card">
                    <div class="stat-icon client-color"><i class="fa fa-users"></i></div>
                    <div class="stat-details">
                        <h3>Clients Total</h3>
                        <p class="number"><?php echo $nbClients; ?></p>
                    </div>
                </div>

                <!-- Carte Factures en attente -->
                <div class="card stat-card">
                    <div class="stat-icon invoice-color"><i class="fa fa-file-invoice-dollar"></i></div>
                    <div class="stat-details">
                        <h3>Factures en cours</h3>
                        <p class="number"><?php echo $nbFacturesAttente; ?></p>
                    </div>
                </div>

                <!-- Carte Chiffre d'affaires -->
                <div class="card stat-card">
                    <div class="stat-icon money-color"><i class="fa fa-wallet"></i></div>
                    <div class="stat-details">
                        <h3>Total Encaissé</h3>
                        <p class="number"><?php echo number_format($caTotal, 2, ',', ' '); ?> FCFA</p>
                    </div>
                </div>
            </section>

            <!-- Section 2 : Actions Rapides -->
            <section class="quick-actions">
                <h2>Actions Rapides</h2>
                <div class="buttons-grid">
                    <button onclick="window.location.href='./php/views/add_client.php'" class="btn-action">
                        <i class="fa fa-user-plus"></i> Nouveau Client
                    </button>
                    
                    <button onclick="window.location.href='./php/views/add_invoice.php'" class="btn-action">
                        <i class="fa fa-plus-circle"></i> Nouvelle Facture
                    </button>
                    
                    <button onclick="window.location.href='./php/views/add_payment.php'" class="btn-action">
                        <i class="fa fa-hand-holding-usd"></i> Encaisser un Paiement
                    </button>
                    
                    <button onclick="window.location.href='./php/views/add_product.php'" class="btn-action">
                        <i class="fa fa-tags"></i> Ajouter un Service
                    </button>
                </div>
            </section>

            <!-- Section 3 : Tableau des dernières factures -->
            <section class="recent-transactions">
                <div class="section-header">
                    <h2>Dernières Factures Émises</h2>
                    <a href="./php/views/documents.php" class="view-all">Tout voir</a>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>N° Document</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($resLastDocs) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($resLastDocs)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['numero_d']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['date_creation'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['nom'] . ' ' . $row['prenom']); ?></td>
                                        <td><?php echo number_format($row['montant_total'], 2, ',', ' '); ?> FCFA</td>
                                        <td>
                                            <span class="status-badge <?php echo strtolower($row['status']); ?>">
                                                <?php echo $row['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn-small view" title="Voir">Voir</button>
                                            <button class="btn-small print" title="Imprimer">Imprimer</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align:center;">Aucune facture récente.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>

    <!-- Script JS pour les interactions si nécessaire -->
    <script src="./assets/js/main.js"></script>
</body>
</html>