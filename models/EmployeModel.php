<?php
require_once __DIR__ . '/Model.php';

class EmployeModel extends Model {

    /** Liste tous les employés avec leurs infos */
    public function listerTout(): array {
        $stmt = $this->db->query(
            "SELECT u.id, u.nom, u.email, u.login, e.departement,
                COUNT(r.id) AS nb_reservations
             FROM utilisateurs u
             LEFT JOIN employes e ON e.utilisateur_id = u.id
             LEFT JOIN reservations r ON r.utilisateur_id = u.id 
                AND r.date_reservation >= CURDATE()
                AND r.statut = 'confirmee'
             WHERE u.role = 'employe'
             GROUP BY u.id, u.nom, u.email, u.login, e.departement
             ORDER BY u.nom"
        );
        return $stmt->fetchAll();
    }

    /** Trouve un employé par son ID */
    public function trouverParId(int $id): array|false {
        $stmt = $this->db->prepare(
            "SELECT u.*, e.departement
             FROM utilisateurs u
             LEFT JOIN employes e ON e.utilisateur_id = u.id
             WHERE u.id = :id AND u.role = 'employe'"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /** Vérifie si le login existe déjà */
    public function loginExiste(string $login, int $excludeId = 0): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM utilisateurs 
             WHERE login = :login AND id != :id"
        );
        $stmt->execute([':login' => $login, ':id' => $excludeId]);
        return $stmt->fetchColumn() > 0;
    }

    /** Vérifie si l'email existe déjà */
    public function emailExiste(string $email, int $excludeId = 0): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM utilisateurs 
             WHERE email = :email AND id != :id"
        );
        $stmt->execute([':email' => $email, ':id' => $excludeId]);
        return $stmt->fetchColumn() > 0;
    }

    /** Ajoute un employé (utilisateur + ligne employes) */
    public function ajouter(array $data): bool {
        // 1. Insère dans utilisateurs
        $hash = password_hash($data['mot_de_passe'], PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            "INSERT INTO utilisateurs (nom, email, login, mot_de_passe, role)
             VALUES (:nom, :email, :login, :mdp, 'employe')"
        );
        $stmt->execute([
            ':nom'   => $data['nom'],
            ':email' => $data['email'],
            ':login' => $data['login'],
            ':mdp'   => $hash,
        ]);

        // 2. Récupère l'ID et insère dans employes
        $userId = (int) $this->db->lastInsertId();
        $stmt2  = $this->db->prepare(
            "INSERT INTO employes (utilisateur_id, departement)
             VALUES (:uid, :dept)"
        );
        return $stmt2->execute([
            ':uid'  => $userId,
            ':dept' => $data['departement'],
        ]);
    }

    /** Modifie un employé */
    public function modifier(int $id, array $data): void {
        // Mise à jour utilisateurs
        $stmt = $this->db->prepare(
            "UPDATE utilisateurs 
             SET nom = :nom, email = :email, login = :login
             WHERE id = :id"
        );
        $stmt->execute([
            ':nom'   => $data['nom'],
            ':email' => $data['email'],
            ':login' => $data['login'],
            ':id'    => $id,
        ]);

        // Mise à jour mot de passe si renseigné
        if (!empty($data['mot_de_passe'])) {
            $hash = password_hash($data['mot_de_passe'], PASSWORD_BCRYPT);
            $stmt2 = $this->db->prepare(
                "UPDATE utilisateurs SET mot_de_passe = :mdp WHERE id = :id"
            );
            $stmt2->execute([':mdp' => $hash, ':id' => $id]);
        }

        // Mise à jour département
        $stmt3 = $this->db->prepare(
            "UPDATE employes SET departement = :dept WHERE utilisateur_id = :id"
        );
        $stmt3->execute([':dept' => $data['departement'], ':id' => $id]);
    }

    /** Vérifie si l'employé a des réservations à venir */
    public function aDesReservationsAvenir(int $id): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM reservations
             WHERE utilisateur_id = :id
             AND date_reservation >= CURDATE()
             AND statut = 'confirmee'"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    /** Supprime un employé */
    public function supprimer(int $id): void {
        // La FK ON DELETE CASCADE supprime aussi la ligne dans employes
        $stmt = $this->db->prepare("DELETE FROM utilisateurs WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}