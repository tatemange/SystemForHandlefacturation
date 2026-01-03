<?php
// debug_doc_query.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'assets/php/config/Database.php';
require_once 'assets/php/models/DocumentModel.php';

echo "Testing Database Connection...\n";
$db = new Database();
if($db->conn) {
    echo "Connected.\n";
} else {
    echo "Connection Failed.\n";
    exit;
}

echo "Testing DocumentModel::getAll()...\n";
$model = new DocumentModel($db->conn);
$docs = $model->getAll();

if(empty($docs)) {
    echo "Result is empty (or failed).\n";
    
    // Manually run the query to capture the error
    $sql = "SELECT d.*, c.nom, c.prenom, 
            (SELECT COALESCE(SUM(montant), 0) FROM REGLEMENT WHERE id_document = d.id_document) as montant_regle
            FROM DOCUMENT d
            JOIN CLIENT c ON d.id_client = c.id
            ORDER BY d.date_creation DESC";
            
     $result = mysqli_query($db->conn, $sql);
     if(!$result) {
         echo "SQL Error: " . mysqli_error($db->conn) . "\n";
     } else {
         echo "Direct Query Success. Count: " . mysqli_num_rows($result) . "\n";
     }
} else {
    echo "Success. Count: " . count($docs) . "\n";
    print_r($docs[0]);
}
?>
