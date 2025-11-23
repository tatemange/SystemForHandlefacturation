<?php
// assets/php/logout.php

session_start(); // On récupère la session actuelle

// On supprime toutes les variables de session
$_SESSION = array();

// On détruit le cookie de session (sécurité supplémentaire)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// On détruit la session
session_destroy();

// On redirige vers la page de connexion (racine)
header("Location: ../../index.html");
exit;