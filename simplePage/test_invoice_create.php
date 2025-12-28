<?php
// test_invoice_create.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'assets/php/config/Database.php';
require_once 'assets/php/models/DocumentModel.php';
require_once 'assets/php/models/DetailDocumentModel.php';
require_once 'assets/php/models/ServiceProduitModel.php';

echo "Testing Invoice Creation...\n";

$database = new Database();
$db = $database->conn;

$docModel = new DocumentModel($db);
$detailModel = new DetailDocumentModel($db);
$serviceModel = new ServiceProduitModel($db);

require_once 'assets/php/models/ClientModel.php';
$clientModel = new ClientModel($db);
$clients = $clientModel->getAll();
if (empty($clients)) {
    // Create one
    $clientModel->addClient("TestClient", "Prenom", "0000", "test@test.com");
    $clients = $clientModel->getAll();
}
$id_client = $clients[0]['id'];
echo "Using Client ID: $id_client\n";

$items = [
    ['id' => 1, 'qty' => 1, 'price' => 1000] 
];

// Vérif existence item 1
$itemCheck = $serviceModel->getById(1);
if(!$itemCheck) {
    // Créer un item pour le test si pas de 1
    echo "Item ID 1 not found. Please create a dummy product/service first.\n";
    // On va tenter de créer un produit de test
    $serviceModel->create("Test Product", 1000, 500, 0, "Desc", 10);
    $all = $serviceModel->getAll();
    $last = end($all);
    $items[0]['id'] = $last['id'];
    echo "Created temp product ID: " . $last['id'] . "\n";
} else {
    echo "Using existing Item ID 1.\n";
    // Si stock insuffisant, on rajoute
    if($itemCheck['est_service'] == 0 && $itemCheck['quantite_stock'] < 1) {
         echo "Stock too low, adding 10.\n";
         $serviceModel->update($itemCheck['id'], $itemCheck['libelle'], $itemCheck['prix_de_vente'], $itemCheck['prix_achat'], $itemCheck['est_service'], $itemCheck['description'], 10);
    }
}

$total = 0;
foreach ($items as $item) {
    $total += ($item['price'] * $item['qty']);
}

$prefix = "TEST-FAC-";
$numero_d = $prefix . rand(1000,9999);

echo "Starting Transaction...\n";
mysqli_begin_transaction($db);

try {
    echo "Creating Document header...\n";
    $docId = $docModel->create($numero_d, $total, 'EN_COURS', $id_client);

    if (!$docId) {
        throw new Exception("Erreur lors de la création de l'en-tête de facture: " . mysqli_error($db));
    }
    echo "Header created. ID: $docId\n";

    foreach ($items as $item) {
        $id_service = intval($item['id']);
        $qty = intval($item['qty']);
        $price = floatval($item['price']);
        $montantLigne = $qty * $price;
        
        echo "Processing Item ID $id_service (Qty: $qty)...\n";

        // --- GESTION STOCK ---
        if (!$serviceModel->checkStock($id_service, $qty)) {
            throw new Exception("Stock check failed");
        }
        echo "Stock check passed.\n";
        
        if (!$serviceModel->decrementStock($id_service, $qty)) {
             throw new Exception("Stock decrement failed: " . mysqli_error($db));
        }
        echo "Stock decremented.\n";
        // ---------------------

        echo "Creating Detail...\n";
        // REMARQUE: ici on teste la méthode qui posait problème
        $resDetail = $detailModel->create($docId, $id_service, $qty, $price, $montantLigne);
        if (!$resDetail) {
             throw new Exception("Detail create failed: " . mysqli_error($db));
        }
        echo "Detail created.\n";
    }

    mysqli_commit($db);
    echo "SUCCESS: Invoice created.\n";

} catch (Exception $e) {
    mysqli_rollback($db);
    echo "FAILED: " . $e->getMessage() . "\n";
}
