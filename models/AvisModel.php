<?php 
require_once __DIR__ . '/Model.php';

class AvisModel extends Model {

    /** Lister tous les avis */
    public function tousLesAvis():array {
        $stmt = $this->db->query(
            "SELECT a.*, u.nom, u.photo FROM avis a 
            JOIN utilisateurs u 
            on a.utilisateur_id = u.id
            ORDER BY a.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    /** Ajouter un avis */
    public function ajouter (int $userId, int $note, string $comm):void {
        $stmt = $this->db->prepare(
            "INSERT INTO avis (utilisateur_id, note, commentaire)
            VALUES (?, ?, ?)"
        );
        $stmt->execute([$userId, $note, $comm]);
    }

    /** Supprimer un avis */ 
    public function supprimer (int $avisId):void {
        $stmt = $this->db->prepare(
            "DELETE FROM avis WHERE id = ?"
        );
        $stmt->execute([$avisId]);
    }
    
    /** Moyenne des notes */
    public function moyenneNote(): float {
        $stmt = $this->db->query(
            "SELECT AVG(note) AS moyenne FROM avis"
        );
        $res=$stmt->fetch();
        return round($res['moyenne']?? 0, 1);
    }
    }