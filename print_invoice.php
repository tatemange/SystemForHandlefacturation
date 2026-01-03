<?php
// print_invoice.php
// Page dédiée à l'impression d'une facture

session_start();
if (!isset($_SESSION['admin_id'])) {
    // Redirection si tentative d'accès direct
    header("Location: index.html");
    exit();
}

if (!isset($_GET['id'])) {
    die("ID Facture manquant");
}

$id = intval($_GET['id']);

require_once './assets/php/config/Database.php';
require_once './assets/php/models/DocumentModel.php';
require_once './assets/php/models/DetailDocumentModel.php';
require_once './assets/php/models/ReglementModel.php';

$db = new Database();
$docModel = new DocumentModel($db->conn);
$detailModel = new DetailDocumentModel($db->conn);
// Not reusing ReglementModel methods directly because we need a specific query by document_id which might not be there cleanly
// But let's check manually via query if model lacks it. ReglementModel doesn't have getByDocumentId.
// We'll write a small query here.

// 1. Récupérer la facture
$doc = $docModel->getById($id);
if (!$doc) {
    die("Facture introuvable");
}

// 2. Récupérer les détails
$details = $detailModel->getByDocumentId($id);

// 2b. Récupérer le Client
require_once './assets/php/models/ClientModel.php';
$clientModel = new ClientModel($db->conn);
$client = $clientModel->getById($doc['id_client']);

// 3. Infos entreprise
$companyName = "CISCO informatique";
$companyAddress = "Bafoussam, Cameroun";
$companyPhone = ""; // Demande utilisateur: "tu ne met rien"

// 4. Calculs totaux
$total = floatval($doc['montant_total']);

// 5. Récupérer le mode de paiement si payé
$modePaiement = null;
if ($doc['status'] == 'PAYE' || $doc['status'] == 'EN_COURS') { // Check regs even if partial? Mostly PAYE.
    $sqlReg = "SELECT mode_paiement, reference FROM REGLEMENT WHERE id_document = ? ORDER BY id_reglement DESC LIMIT 1";
    $stmt = mysqli_prepare($db->conn, $sqlReg);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $resReg = mysqli_stmt_get_result($stmt);
    if ($r = mysqli_fetch_assoc($resReg)) {
        $modePaiement = $r['mode_paiement'];
    }
}

// Logique utilisateur: "si non cash... mobilemoney"
// On affiche le mode de paiement. Si c'est autre chose que CASH/Espèce, on affiche ce que c'est (ou MobileMoney si demandé)
// Interpret: "par mobilemoney dans la facture tu regarde le mode de peillement si non cash."
// -> "By mobilemoney: in the invoice check payment mode. If not cash..."
// -> Let's display "Mode de paiement: [Mode]"
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture <?php echo $doc['numero_d']; ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 20px; color: #333; }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            border: 1px solid #eee;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .company-info { text-align: left; }
        .invoice-info { text-align: right; }
        
        h1 { margin: 0; color: #333; font-size: 24px; text-transform: uppercase; }
        h2 { margin: 5px 0 0; font-size: 16px; color: #666; font-weight: normal; }
        
        .client-info { margin-bottom: 30px; border-top: 1px solid #eee; padding-top: 20px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th, table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background-color: #f8f9fa; font-weight: bold; }
        table td.right { text-align: right; }
        
        .total-section { text-align: right; margin-top: 20px; }
        .total-row { font-size: 18px; font-weight: bold; margin-top: 5px; }
        
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #aaa; border-top: 1px solid #eee; padding-top: 10px; }
        
        /* Badge Statut */
        .badge { padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; color: white; display: inline-block; }
        .paye { background-color: #2ecc71; color: white; border: 1px solid #27ae60; }
        .impaye { background-color: #e74c3c; color: white; }
        .en_cours { background-color: #f1c40f; color: black; }

        @media print {
            .invoice-box { box-shadow: none; border: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="invoice-box">
    
    <div class="header">
        <div class="company-info">
            <h1><?php echo $companyName; ?></h1>
            <div><?php echo $companyAddress; ?></div>
            <?php if(!empty($companyPhone)): ?>
            <div><?php echo $companyPhone; ?></div>
            <?php endif; ?>
        </div>
        <div class="invoice-info">
            <h1>Facture</h1>
            <h2>N°: <?php echo $doc['numero_d']; ?></h2>
            <div>Date: <?php echo date('d/m/Y', strtotime($doc['date_creation'])); ?></div>
            <div style="margin-top:5px">
                Statut: 
                <?php 
                    $st = $doc['status'];
                    $cls = 'en_cours';
                    if($st=='PAYE') $cls='paye';
                    if($st=='IMPAYE') $cls='impaye';
                ?>
                <span class="badge <?php echo $cls; ?>"><?php echo $st; ?></span>
            </div>
            <?php if($modePaiement): ?>
            <div style="margin-top:5px">
                <strong>Mode Paiement:</strong> 
                <?php 
                    // Logic: "si non cash... mobilemoney"
                    // If mode is NOT 'CASH' or 'ESPECES', display 'MobileMoney' or the actual mode?
                    // The user said: "par mobilemoney dans la facture tu regarde le mode de peillement si non cash"
                    // I will display the actual mode if it exists.
                    // If user meant "Show MobileMoney IF not cash", I can force it.
                    // "si non cash" -> if ($modePaiement != 'CASH' && $modePaiement != 'ESPECES') echo 'MobileMoney'; else echo $modePaiement;
                    // BUT let's just show the actual mode, usually 'OM', 'MOMO', 'VIREMENT'. 
                    echo htmlspecialchars($modePaiement); 
                ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="client-info">
        <strong>Facturé à :</strong> 
        <?php 
            if(isset($client) && $client) {
                echo $client['nom'] . ' ' . $client['prenom'] . '<br>';
                echo 'Ref Client ( ID ): ' . $client['id'] . '<br>';
                if(!empty($client['email'])) echo 'Email: '. $client['email'] . '<br>';
                if(!empty($client['numero_telephone'])) echo 'Telephone: ' . $client['numero_telephone'];
            } else {
                echo "Client Inconnu (ID " . $doc['id_client'] . ")";
            }
        ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="right">Prix Unitaire</th>
                <th class="right">Quantité</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($details as $line): ?>
            <tr>
                <td><?php echo $line['libelle']; ?></td> 
                <td class="right"><?php echo number_format($line['prix_unitaire'], 0, ',', ' '); ?></td>
                <td class="right"><?php echo $line['quantite']; ?></td>
                <td class="right"><?php echo number_format($line['montant_ligne'], 0, ',', ' '); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">Total à payer: <?php echo number_format($total, 0, ',', ' '); ?> FCFA</div>
    </div>

    <div class="footer">
        Grand merci de nous faire confiance.
    </div>

</div>

<script>
    window.onload = function() {
        window.print();
    }
</script>

</body>
</html>
