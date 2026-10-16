<?php
/* ══════════════════════════════════════
   PROFIL MODEL
   
   Gère la modification des informations
   personnelles de l'utilisateur connecté.
   Méthodes :
   - trouverParId()     → récupère les infos
   - modifierInfos()    → change nom et email
   - modifierMotDePasse() → change le mot de passe
══════════════════════════════════════ */

require_once __DIR__ . '/Model.php';

class ProfilModel extends Model {

    /**
     * Récupère les infos d'un utilisateur par son ID
     * Utilisé pour afficher le profil
     */
    public function trouverParId(int $id): array|false {
        $stmt = $this->db->prepare(
            "SELECT id, nom, email, login, role, photo
             FROM utilisateurs
             WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Vérifie si l'email est déjà utilisé
     * par un autre utilisateur
     */
    public function emailExiste(string $email, int $excludeId): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM utilisateurs
             WHERE email = :email AND id != :id"
        );
        $stmt->execute([':email' => $email, ':id' => $excludeId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Modifie le nom et l'email de l'utilisateur
     */
    public function modifierInfos(int $id, string $nom, string $email): bool {
        $stmt = $this->db->prepare(
            "UPDATE utilisateurs
             SET nom = :nom, email = :email
             WHERE id = :id"
        );
        return $stmt->execute([
            ':nom'   => $nom,
            ':email' => $email,
            ':id'    => $id,
        ]);
    }

    /**
     * Modifie le mot de passe de l'utilisateur
     * Le nouveau mot de passe est hashé avec bcrypt
     */
    public function modifierMotDePasse(int $id, string $nouveauMotDePasse): bool {
        // Hash sécurisé du nouveau mot de passe
        $hash = password_hash($nouveauMotDePasse, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            "UPDATE utilisateurs
             SET mot_de_passe = :mdp
             WHERE id = :id"
        );
        return $stmt->execute([':mdp' => $hash, ':id' => $id]);
    }

    /**
     * Vérifie que l'ancien mot de passe est correct
     * avant d'autoriser le changement
     */
    public function verifierMotDePasse(int $id, string $motDePasse): bool {
        $stmt = $this->db->prepare(
            "SELECT mot_de_passe FROM utilisateurs WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        // Compare le mot de passe saisi avec le hash en base
        return $user && password_verify($motDePasse, $user['mot_de_passe']);
    }
    /**
     * Met à jour la photo de profil
     */
    public function modifierPhoto(int $id, string $photo): bool {
        $stmt = $this->db->prepare(
            "UPDATE utilisateurs SET photo = :photo WHERE id = :id"
        );
        return $stmt->execute([':photo' => $photo, ':id' => $id]);
    }

    /**
     * Supprime la photo de profil
     */
    public function supprimerPhoto(int $id): bool {
        $stmt = $this->db->prepare(
            "UPDATE utilisateurs SET photo = NULL WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }
    
}