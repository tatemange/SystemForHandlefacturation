<?php
// dashboard.php

// 1. On démarre la session (OBLIGATOIRE au tout début du fichier)
session_start();

// 2. Vérification de sécurité : Est-ce que l'admin est connecté ?
// Si la variable de session 'admin_id' n'existe pas, c'est un intrus.
if (!isset($_SESSION['admin_id'])) {
    // On le redirige vers la page de connexion
    header("Location: index.html");
    exit();
}

// Si on arrive ici, c'est que l'utilisateur est connecté.
// On peut récupérer ses infos stockées lors du login dans le Controller.
$nom_utilisateur = $_SESSION['admin_username'];
$role = $_SESSION['admin_role'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Facturation</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <style>
        body, body > * {
            display:flex;
            gap:.5rem;
            flex-direction: column;
        }
        /* Juste pour l'exemple, à mettre dans ton CSS */
        .dashboard-header { padding: 20px; background: #f4f4f4; display: flex; justify-content: space-between; align-items: center; }
        .btn-logout { background: red; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>

    <div class="dashboard-header">
        <div>
            <h1>Bienvenue, <?php echo htmlspecialchars($nom_utilisateur); ?> !</h1>
            <small>Rôle : <?php echo htmlspecialchars($role); ?></small>
        </div>
        
        <!-- Bouton de déconnexion -->
        <a href="./assets/php/logout.php" class="btn-logout">Se déconnecter</a>
    </div>

    <div class="conteneur">
        <h2>Gestion de la facturation</h2>
        <p>Ici viendra ton interface de gestion (Tableaux, Graphiques, etc.)</p>
        
        <!-- Exemple de contenu -->
        <div class="cards">
            <div class="card">Créer une facture</div>
            <div class="card">Voir les clients</div>
        </div>
    </div>

</body>
</html>