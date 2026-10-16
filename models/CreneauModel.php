<?php
require_once __DIR__ . '/Model.php';

class CreneauModel extends Model {

    /** Liste tous les créneaux */
    public function listerTout(): array {
        $stmt = $this->db->query(
            "SELECT * FROM creneaux ORDER BY heure_debut"
        );
        return $stmt->fetchAll();
    }

    /** Retourne un créneau par son id */
    public function trouverParId(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM creneaux WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}