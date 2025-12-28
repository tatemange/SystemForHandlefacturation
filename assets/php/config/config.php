<?php
// assets/php/config/config.php

// Identifiants base de données
// En production, ces valeurs peuvent venir de variables d'environnement (getenv)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'facturation');

// Timezone
date_default_timezone_set('Africa/Douala'); // Adaptez selon votre zone
?>
