<?php
// Paramètres de connexion à la base de données
define('DB_HOST', '127.0.0.1:3308');
define('DB_NAME', 'gestion_reservations');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8');

function getConnexion(): PDO {
    static $pdo = null; // On garde une seule connexion (singleton)

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }
    return $pdo;
}