<?php
// assets/php/migrations/02_add_stock_to_service_produit.php

require_once __DIR__ . '/../config/Database.php';

$database = new Database();
$db = $database->conn;

echo "Mise à jour de la table SERVICE_PRODUIT...\n";

// Vérifier si la colonne existe déjà
$check = mysqli_query($db, "SHOW COLUMNS FROM SERVICE_PRODUIT LIKE 'quantite_stock'");
if(mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE SERVICE_PRODUIT ADD COLUMN quantite_stock INT(11) DEFAULT 0";
    if(mysqli_query($db, $sql)) {
        echo "Succès : Colonne quantite_stock ajoutée.\n";
    } else {
        echo "Erreur : " . mysqli_error($db) . "\n";
    }
} else {
    echo "La colonne quantite_stock existe déjà.\n";
}

echo "Terminé.\n";
?>
