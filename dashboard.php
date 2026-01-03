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
$sqlLastDocs = "SELECT d.id_document, d.numero_d, d.date_creation, d.montant_total, d.status, c.nom, c.prenom 
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
    <!-- Font Awesome Icons (Local) -->
    <link rel="stylesheet" href="./assets/css/all.min.css">

    <!-- Tes fichiers CSS -->
    <link rel="stylesheet" href="./assets/css/variableCSS.css">
    <link rel="stylesheet" href="./assets/css/dashboard.css">
    <link rel="stylesheet" href="./assets/css/form.css">
    <link rel="stylesheet" href="./assets/css/documents.css">
    <link rel="stylesheet" href="./assets/css/caisse.css">
    <link rel="stylesheet" href="./assets/css/services.css">

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
                include 'assets/php/views/services.html';
                // echo "<h2>Page Services (En construction)</h2>";
                break;

            // CAS 5 : HISTORIQUE
            case 'historique':
                include 'assets/php/views/historique.html';
                // echo "<h2>Page Historique (En construction)</h2>";
                break;

            // CAS PAR DÉFAUT : ACCUEIL / TABLEAU DE BORD
            case 'home':
            default:
        ?>
            <!-- --- DÉBUT DU CONTENU DU DASHBOARD (HOME) --- -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="margin: 0;">Tableau de bord</h1>
                <button onclick="openAnalyticsModal()" class="btn-primary" style="background-color: var(--btn-bg); color: var(--btn-color); border: none; border-radius: 40px; height: 38px; display: flex; align-items: center; justify-content: center; padding: 0 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                    <i class="fa fa-chart-line" style="margin-right: 8px;"></i> Statistiques Avancées
                </button>
            </div>

            <!-- ANALYTICS MODAL -->
            <div id="analyticsModal" class="modal" style="display:none;">
                <!-- ... content ... -->
                <div class="modal-content" style="width: 90%; max-width: 1200px; background: var(--background); color: var(--label); border-radius: 20px; padding: 2rem;">
                    <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
                        <h2><i class="fa fa-chart-pie"></i> Analyses Financières & Activités</h2>
                        <span class="close-modal" onclick="closeAnalyticsModal()" style="font-size: 2rem; cursor:pointer;">&times;</span>
                    </div>
                    
                    <div class="charts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem;">
                        <!-- Chart 1: Evolution CA -->
                        <div class="chart-box" style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border-color); min-height: 400px;">
                            <h3 style="margin-bottom: 1rem; font-size: 1.1rem; opacity: 0.9;">Évolution du Chiffre d'Affaires (Année en cours)</h3>
                            <div style="position: relative; height: 300px; width: 100%;">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Chart 2: Top Products -->
                        <div class="chart-box" style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border-color); min-height: 400px;">
                            <h3 style="margin-bottom: 1rem; font-size: 1.1rem; opacity: 0.9;">Top 5 Produits / Services</h3>
                            <div style="position: relative; height: 300px; width: 100%;">
                                <canvas id="productsChart"></canvas>
                            </div>
                        </div>
                        
                        <!-- Chart 3: Top Clients -->
                        <div class="chart-box" style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border-color); min-height: 400px;">
                            <h3 style="margin-bottom: 1rem; font-size: 1.1rem; opacity: 0.9;">Top 5 Meilleurs Clients</h3>
                            <div style="position: relative; height: 300px; width: 100%;">
                                <canvas id="clientsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 2rem; text-align: right;">
                         <button class="btn-primary" onclick="closeAnalyticsModal()" style="background-color: var(--btn-bg); color: var(--btn-color); border: var(--border); padding: 0.5rem 1.5rem; border-radius: 30px; cursor: pointer;">Fermer</button>
                    </div>
                </div>
            </div>

            <!-- PAYMENT MODAL -->
            <div id="paymentModal" class="modal" style="display:none;">
                <div class="modal-content" style="background: var(--background); color: var(--label); border-radius: 16px; padding: 2rem; width: 90%; max-width: 500px; border: 1px solid var(--border-color);">
                    <div class="modal-header" style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                        <h2><i class="fa fa-hand-holding-usd"></i> Enregistrer un Paiement</h2>
                    </div>
                    <form id="paymentForm" onsubmit="event.preventDefault(); submitPayment();">
                        <input type="hidden" id="pay_doc_id">
                        
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label>Facture N°</label>
                            <input type="text" id="pay_doc_num" disabled style="width:100%; padding:0.8rem; border-radius:8px; border:1px solid var(--border-color); background:rgba(255,255,255,0.05); color:var(--label);">
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem; display:flex; gap:1rem;">
                            <div style="flex:1">
                                <label>Total Facture</label>
                                <input type="text" id="pay_doc_total" disabled style="width:100%; padding:0.8rem; border-radius:8px; border:1px solid var(--border-color); background:rgba(255,255,255,0.05); color:var(--label); font-weight:bold;">
                            </div>
                            <div style="flex:1">
                                <label>Déjà Réglé</label>
                                <input type="text" id="pay_doc_paid" disabled style="width:100%; padding:0.8rem; border-radius:8px; border:1px solid var(--border-color); background:rgba(255,255,255,0.05); color:var(--label);">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label>Reste à Payer</label>
                            <input type="text" id="pay_doc_left" disabled style="width:100%; padding:0.8rem; border-radius:8px; border:1px solid var(--border-color); background:rgba(255,255,255,0.05); color:#ff6b6b; font-weight:bold;">
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label>Montant du Paiement (FCFA)</label>
                            <input type="number" id="pay_amount" required min="1" step="1" style="width:100%; padding:0.8rem; border-radius:8px; border:1px solid var(--first-color); background:var(--background); color:var(--label); font-size:1.1rem; font-weight:bold;">
                        </div>

                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label>Mode de Paiement</label>
                            <select id="pay_mode" style="width:100%; padding:0.8rem; border-radius:8px; border:1px solid var(--border-color); background:var(--background); color:var(--label);">
                                <option value="CASH">Espèces</option>
                                <option value="MOBILE_MONEY">Mobile Money</option>
                                <option value="CHEQUE">Chèque</option>
                                <option value="VIREMENT">Virement</option>
                            </select>
                        </div>
                        
                        <div class="form-actions" style="display:flex; justify-content:flex-end; gap:1rem;">
                            <button type="button" onclick="document.getElementById('paymentModal').style.display='none'" style="padding:0.8rem 1.5rem; border-radius:30px; border:1px solid var(--border-color); background:transparent; color:var(--label); cursor:pointer;">Annuler</button>
                            <button type="submit" class="btn-primary" style="padding:0.8rem 2rem; border-radius:30px; border:none; background:var(--first-color); color:white; cursor:pointer; font-weight:bold;">Valider</button>
                        </div>
                    </form>
                </div>
            </div>

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
                                        <button class="btn-small view" onclick="window.location.href='dashboard.php?page=documents&open_id=<?php echo $doc['id_document']; ?>'" title="Voir / Modifier">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <button class="btn-small print" onclick="window.open('print_invoice.php?id=<?php echo $doc['id_document']; ?>', '_blank')" title="Imprimer">
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
    <script src="./assets/js/services.js"></script>
    <!-- Chart JS v4.5.1 -->
    <script src="./assets/js/chart.umd.min.js"></script>
    
    <script>
        // ANALYTICS LOGIC
        const modalAnalytics = document.getElementById('analyticsModal');
        let chartsInitialized = false;
        let chartInstances = {};

        function openAnalyticsModal() {
            if (typeof Chart === 'undefined') {
                alert("Erreur: La librairie Chart.js n'est pas chargée. Vérifiez votre connexion ou les fichiers.");
                return;
            }

            modalAnalytics.style.display = 'flex';
            modalAnalytics.style.alignItems = 'center';
            modalAnalytics.style.justifyContent = 'center';
            document.body.style.overflow = 'hidden';
            
            // Re-render every time to avoid sizing issues or just once?
            // Better to load once but ensure resize
            if (!chartsInitialized) {
                // Short timeout to ensure modal is rendered
                setTimeout(() => {
                    loadAnalyticsData();
                }, 100);
                chartsInitialized = true;
            }
        }

        function closeAnalyticsModal() {
            modalAnalytics.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        async function loadAnalyticsData() {
            try {
                const response = await fetch('assets/api/analytics_api.php');
                const data = await response.json();
                
                if (data.status === 'success') {
                    // Check if data exists
                    console.log("Analytics Data:", data);
                    
                    if (document.getElementById('revenueChart')) renderRevenueChart(data.monthly_revenue);
                    if (document.getElementById('productsChart')) renderProductsChart(data.top_products);
                    if (document.getElementById('clientsChart')) renderClientsChart(data.top_clients);
                } else {
                    console.error("API Error:", data.message);
                }
            } catch (e) {
                console.error("Error loading analytics", e);
            }
        }

        function renderRevenueChart(monthlyData) { 
            const canvas = document.getElementById('revenueChart');
            if(!canvas) return;

            const ctx = canvas.getContext('2d');
            const labels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
            const values = labels.map((_, index) => monthlyData[index + 1] || 0);

            const styles = getComputedStyle(document.body);
            const mainColor = styles.getPropertyValue('--first-color').trim() || '#3e64ff';
            const labelColor = styles.getPropertyValue('--label').trim() || '#fff';

            if(chartInstances.revenue) chartInstances.revenue.destroy();

            chartInstances.revenue = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Chiffre d\'Affaires (FCFA)',
                        data: values,
                        borderColor: mainColor,
                        backgroundColor: mainColor + '33', 
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Important for flex containers
                    plugins: { 
                        legend: { labels: { color: labelColor } } 
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            grid: { color: 'rgba(255,255,255,0.05)' },
                            ticks: { color: labelColor }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: labelColor }
                        }
                    }
                }
            });
        }

        function renderProductsChart(products) {
            const canvas = document.getElementById('productsChart');
            if(!canvas) return;

            const ctx = canvas.getContext('2d');
            const labels = products.map(p => p.label);
            const values = products.map(p => p.value);
            
            const styles = getComputedStyle(document.body);
            const labelColor = styles.getPropertyValue('--label').trim() || '#fff';

            if(chartInstances.products) chartInstances.products.destroy();

            chartInstances.products = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { 
                            position: 'bottom',
                            labels: { color: labelColor } 
                        } 
                    }
                }
            });
        }

        function renderClientsChart(clients) {
            const canvas = document.getElementById('clientsChart');
            if(!canvas) return;
            
            const ctx = canvas.getContext('2d');
            const labels = clients.map(c => c.name);
            const values = clients.map(c => c.value);
            
            const styles = getComputedStyle(document.body);
            const mainColor = styles.getPropertyValue('--first-color').trim() || '#3e64ff';
            const labelColor = styles.getPropertyValue('--label').trim() || '#fff';

            if(chartInstances.clients) chartInstances.clients.destroy();

            chartInstances.clients = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenus Générés',
                        data: values,
                        backgroundColor: mainColor,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false } 
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            grid: { color: 'rgba(255,255,255,0.05)' },
                            ticks: { color: labelColor }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: labelColor }
                        }
                    }
                }
            });
        }
        
        window.onclick = function(event) {
            if (event.target == modalAnalytics) {
                closeAnalyticsModal();
            }
        }
    </script>
</body>

</html>