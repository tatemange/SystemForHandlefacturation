<?php
// assets/php/migrations/01_add_id_document_to_reglement.php

require_once '../config/Database.php';

$database = new Database();
$db = $database->conn;

echo "Mise à jour de la table REGLEMENT...\n";

// Vérifier si la colonne existe déjà
$check = mysqli_query($db, "SHOW COLUMNS FROM REGLEMENT LIKE 'id_document'");
if(mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE REGLEMENT ADD COLUMN id_document INT(11) DEFAULT NULL";
    if(mysqli_query($db, $sql)) {
        echo "Succès : Colonne id_document ajoutée.\n";
        
        // Ajouter la clé étrangère pour la propreté (Optionnel mais recommandé)
        $sqlFK = "ALTER TABLE REGLEMENT ADD CONSTRAINT REGLEMENT_document_FK FOREIGN KEY (id_document) REFERENCES DOCUMENT(id_document) ON UPDATE CASCADE ON DELETE SET NULL";
        if(mysqli_query($db, $sqlFK)) {
            echo "Succès : Clé étrangère ajoutée.\n";
        } else {
            echo "Erreur lors de l'ajout de la FK : " . mysqli_error($db) . "\n";
        }

    } else {
        echo "Erreur : " . mysqli_error($db) . "\n";
    }
} else {
    echo "La colonne id_document existe déjà.\n";
}

echo "Terminé.\n";
?>
