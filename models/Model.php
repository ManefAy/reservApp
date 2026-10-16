<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Classe mère dont héritent tous les Models.
 * Elle donne accès à la connexion PDO via $this->db
 */
class Model {
    protected PDO $db;

    public function __construct() {
        $this->db = getConnexion();
    }
}