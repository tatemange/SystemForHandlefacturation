<?php
require_once 'assets/php/config/Database.php';
require_once 'assets/php/models/ServiceProduitModel.php';

$db = new Database();
$conn = $db->conn;

$sql = "SELECT id, libelle, est_service, quantite_stock FROM SERVICE_PRODUIT WHERE libelle LIKE '%reparation%'";
$result = mysqli_query($conn, $sql);
$items = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode($items, JSON_PRETTY_PRINT);
