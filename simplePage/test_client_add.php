<?php
// test_client_add.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'assets/php/config/Database.php';
require_once 'assets/php/models/ClientModel.php';

echo "Testing Database Connection...\n";
try {
    $database = new Database();
    $db = $database->conn;
    if ($db) {
        echo "Database Connected.\n";
    } else {
        echo "Database Connection Failed (null).\n";
        exit;
    }
} catch (Exception $e) {
    echo "Database Exception: " . $e->getMessage() . "\n";
    exit;
}

echo "Testing ClientModel Instantiation...\n";
$clientModel = new ClientModel($db);

echo "Testing addClient...\n";
$nom = "TestUser_" . rand(1000, 9999);
$prenom = "TestPrenom";
$tel = "0102030405";
$email = "test@example.com";

$result = $clientModel->addClient($nom, $prenom, $tel, $email);

if ($result) {
    echo "SUCCESS: Client '$nom' added.\n";
    
    // Verify insertion
    $sql = "SELECT * FROM CLIENT WHERE nom = '$nom'";
    $res = mysqli_query($db, $sql);
    if ($row = mysqli_fetch_assoc($res)) {
        echo "VERIFICATION: Found in DB with ID: " . $row['id'] . "\n";
        
        // Clean up
        mysqli_query($db, "DELETE FROM CLIENT WHERE id = " . $row['id']);
        echo "CLEANUP: Deleted test client.\n";
    } else {
        echo "VERIFICATION FAILED: Not found in DB.\n";
    }
    
} else {
    echo "FAILURE: addClient returned false.\n";
    echo "MySQL Error: " . mysqli_error($db) . "\n";
}
?>
