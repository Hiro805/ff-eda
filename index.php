<?php
// 1. On définit le cookie AVANT tout le reste
setcookie(
    "user_preference", 
    "dark_mode", 
    [
        'expires' => time() + 3600 * 24 * 30,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]
);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    </head>
<body>
    </body>
</html>