<?php
require_once __DIR__ . '/Model.php';

class UtilisateurModel extends Model {

    /**
     * Trouve un utilisateur par son login (pour la connexion)
     */
    public function 
    trouverParLogin(string $login): array|false {
        $stmt = $this->db->prepare(
            "SELECT * FROM utilisateurs WHERE login = :login LIMIT 1"
        );
        $stmt->execute([':login' => $login]);
        return $stmt->fetch(); // Retourne un tableau ou false
    }

    /**
     * Trouve un utilisateur par son e-mail (pour vérifier les doublons)
     */
    public function trouverParEmail(string $email): array|false {
        $stmt = $this->db->prepare(
            "SELECT * FROM utilisateurs WHERE email = :email LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Crée un nouveau membre (inscription publique)
     */
    public function creerMembre(string $nom, string $email, string $motDePasse): bool {
        $hash = password_hash($motDePasse, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            "INSERT INTO utilisateurs (nom, email, login, mot_de_passe, role)
             VALUES (:nom, :email, :login, :mdp, 'membre')"
        );
        return $stmt->execute([
            ':nom'   => $nom,
            ':email' => $email,
            ':login' => $email, // Le login = e-mail pour les membres
            ':mdp'   => $hash,
        ]);
    }

    /**
     * Crée un employé (par l'admin uniquement)
     */
    public function creerEmploye(string $nom, string $email, string $login, string $motDePasse): int {
        $hash = password_hash($motDePasse, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            "INSERT INTO utilisateurs (nom, email, login, mot_de_passe, role)
             VALUES (:nom, :email, :login, :mdp, 'employe')"
        );
        $stmt->execute([
            ':nom'   => $nom,
            ':email' => $email,
            ':login' => $login,
            ':mdp'   => $hash,
        ]);
        return (int) $this->db->lastInsertId(); // Retourne l'ID du nouvel utilisateur
    }

    /**
     * Liste tous les employés
     */
    public function listerEmployes(): array {
        $stmt = $this->db->query(
            "SELECT u.*, e.departement
             FROM utilisateurs u
             LEFT JOIN employes e ON e.utilisateur_id = u.id
             WHERE u.role = 'employe'
             ORDER BY u.nom"
        );
        return $stmt->fetchAll();
    }

    /**
     * Supprime un utilisateur par son ID
     */
    public function supprimer(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM utilisateurs WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ── RÉINITIALISATION MOT DE PASSE ───────────────────────────

    /**
     * Crée un token de réinitialisation valable 1 heure.
     * Supprime d'abord les anciens tokens de l'utilisateur.
     */
    public function creerTokenReset(int $userId, string $token): bool {
        $this->db->prepare(
            "DELETE FROM password_resets WHERE utilisateur_id = :uid"
        )->execute([':uid' => $userId]);

        $stmt = $this->db->prepare(
            "INSERT INTO password_resets (utilisateur_id, token, expires_at)
             VALUES (:uid, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR))"
        );
        return $stmt->execute([':uid' => $userId, ':token' => $token]);
    }

    /**
     * Trouve l'utilisateur associé à un token valide (non expiré).
     */
    public function trouverParTokenReset(string $token): array|false {
        $stmt = $this->db->prepare(
            "SELECT u.id, u.nom, u.email, u.mot_de_passe
             FROM password_resets pr
             JOIN utilisateurs u ON u.id = pr.utilisateur_id
             WHERE pr.token = :token AND pr.expires_at > NOW()"
        );
        $stmt->execute([':token' => $token]);
        return $stmt->fetch();
    }

    /**
     * Met à jour le mot de passe et supprime le token utilisé.
     */
    public function reinitialiserMotDePasse(string $token, string $nouveauMdp): bool {
        $user = $this->trouverParTokenReset($token);
        if (!$user) return false;

        $this->db->prepare(
            "UPDATE utilisateurs SET mot_de_passe = :mdp WHERE id = :id"
        )->execute([':mdp' => password_hash($nouveauMdp, PASSWORD_BCRYPT), ':id' => $user['id']]);

        $this->db->prepare(
            "DELETE FROM password_resets WHERE token = :token"
        )->execute([':token' => $token]);

        return true;
    }
    public function mettreAjourIp(int $id, string $ip): void {
    $stmt = $this->db->prepare("UPDATE utilisateurs SET last_ip = ? WHERE id = ?");
    $stmt->execute([$ip, $id]);
    }

    public function trouverParId(int $id): array|false {
    $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}
}