<?php
// 1. CONFIGURATION DE LA CONNEXION (Le socle)
$host = 'localhost';
$db   = 'portfolio_db';
$user = 'phpmyadmin';
$pass = '200854';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // Sécurité : oblige SQL à préparer les requêtes
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Sécurité : on ne montre pas l'erreur SQL brute au visiteur
    die(json_encode(["status" => "error", "message" => "Erreur de connexion"]));
}

// 2. RÉCEPTION ET FILTRAGE (Le garde du corps)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Nettoyage contre les injections XSS et SQL
    $pseudo = filter_input(INPUT_POST, 'pseudo', FILTER_SANITIZE_SPECIAL_CHARS);
    $score  = filter_input(INPUT_POST, 'score', FILTER_VALIDATE_INT);

    if ($pseudo && $score !== false) {
        
        // --- 3. DISPOSITIF DE PROTECTION DES COOKIES ---
        // Ce cookie "verrouillé" mémorise le nom du joueur en toute sécurité
        setcookie('player_name', $pseudo, [
            'expires' => time() + (3600 * 24 * 30), // 30 jours
            'path' => '/',
            'domain' => 'localhost',
            'secure' => true,      // Activez ceci uniquement si vous avez le HTTPS
            'httponly' => true,    // Rend le cookie invisible pour les scripts pirates (Anti-XSS)
            'samesite' => 'Strict' // Empêche l'utilisation du cookie sur d'autres sites (Anti-CSRF)
        ]);

        // --- 4. REQUÊTE PRÉPARÉE (Le bouclier anti-SQL)
        // On utilise des jetons (:ps, :sc) pour que les données ne soient jamais exécutées
        $sql = "INSERT INTO scores (pseudo, score, date_record) VALUES (:ps, :sc, NOW())";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([':ps' => $pseudo, ':sc' => $score])) {
            echo json_encode(["status" => "success", "message" => "Score et Cookie sécurisés !"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Données invalides"]);
    }
}
?>