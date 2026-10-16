<?php
require_once __DIR__ . '/Model.php';

class SalleModel extends Model {

    /** Liste toutes les salles avec le nombre de réservations */
    public function listerTout(): array {
        $stmt = $this->db->query(
            "SELECT s.*, 
                COUNT(r.id) AS nb_reservations
             FROM salles s
             LEFT JOIN reservations r ON r.salle_id = s.id
             GROUP BY s.id
             ORDER BY s.nom"
        );
        return $stmt->fetchAll();
    }

    /** Trouve une salle par son ID */
    public function trouverParId(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM salles WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /** Ajoute une nouvelle salle */
    public function ajouter(array $data): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO salles (nom, description, capacite, type_salle, equipements)
             VALUES (:nom, :description, :capacite, :type_salle, :equipements)"
        );
        return $stmt->execute([
            ':nom'         => $data['nom'],
            ':description' => $data['description'],
            ':capacite'    => $data['capacite'],
            ':type_salle'  => $data['type_salle'],
            ':equipements' => $data['equipements'],
        ]);
    }

    /** Modifie une salle existante */
    public function modifier(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE salles 
             SET nom = :nom, description = :description,
                 capacite = :capacite, type_salle = :type_salle,
                 equipements = :equipements
             WHERE id = :id"
        );
        return $stmt->execute([
            ':id'          => $id,
            ':nom'         => $data['nom'],
            ':description' => $data['description'],
            ':capacite'    => $data['capacite'],
            ':type_salle'  => $data['type_salle'],
            ':equipements' => $data['equipements'],
        ]);
    }

    /** Supprime une salle */
    public function supprimer(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM salles WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /** Vérifie si une salle a des réservations à venir */
    public function aDesReservationsAvenir(int $id): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM reservations
             WHERE salle_id = :id 
             AND date_reservation >= CURDATE()
             AND statut = 'confirmee'"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    /** Compte total des salles */
    public function compter(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM salles")->fetchColumn();
    }
    /** Recherche des salles par nom et/ou type */
public function rechercherSalles(string $search = '', string $type = ''): array {
    $sql    = "SELECT s.*, COUNT(r.id) AS nb_reservations
               FROM salles s
               LEFT JOIN reservations r ON r.salle_id = s.id
               WHERE 1=1";
    $params = [];

    if ($search !== '') {
        $sql .= " AND (s.nom LIKE :search OR s.equipements LIKE :search2)";
        $params[':search']  = "%$search%";
        $params[':search2'] = "%$search%";
    }

    if ($type !== '') {
        $sql .= " AND s.type_salle = :type";
        $params[':type'] = $type;
    }

    $sql .= " GROUP BY s.id ORDER BY s.nom";
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
}