<?php
require_once __DIR__ . '/Model.php';

class ReservationModel extends Model {

    /** Liste toutes les réservations avec filtres optionnels */
    public function listerTout(array $filtres = []): array {
        $sql = "SELECT r.*, 
                    s.nom AS salle_nom,
                    u.nom AS utilisateur_nom,
                    u.role AS utilisateur_role,
                    c.libelle AS creneau_libelle
                FROM reservations r
                JOIN salles s ON s.id = r.salle_id
                JOIN utilisateurs u ON u.id = r.utilisateur_id
                JOIN creneaux c ON c.id = r.creneau_id
                WHERE 1=1";

        $params = [];

        // Filtre par salle
        if (!empty($filtres['salle_id'])) {
            $sql .= " AND r.salle_id = :salle_id";
            $params[':salle_id'] = $filtres['salle_id'];
        }

        // Filtre par date
        if (!empty($filtres['date'])) {
            $sql .= " AND r.date_reservation = :date";
            $params[':date'] = $filtres['date'];
        }

        // Filtre par utilisateur
        if (!empty($filtres['utilisateur_id'])) {
            $sql .= " AND r.utilisateur_id = :utilisateur_id";
            $params[':utilisateur_id'] = $filtres['utilisateur_id'];
        }

        // Filtre par statut
        if (!empty($filtres['statut'])) {
            $sql .= " AND r.statut = :statut";
            $params[':statut'] = $filtres['statut'];
        }

        $sql .= " ORDER BY r.date_reservation DESC, c.heure_debut ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Liste les réservations d'un utilisateur spécifique */
    public function listerParUtilisateur(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT r.*,
                s.nom AS salle_nom,
                c.libelle AS creneau_libelle
             FROM reservations r
             JOIN salles s ON s.id = r.salle_id
             JOIN creneaux c ON c.id = r.creneau_id
             WHERE r.utilisateur_id = :uid
             ORDER BY r.date_reservation DESC"
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    /** Crée une nouvelle réservation */
    public function creer(int $salleId, int $userId, string $date, int $creneauId): bool {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO reservations 
                    (salle_id, utilisateur_id, date_reservation, creneau_id, statut)
                 VALUES 
                    (:salle_id, :user_id, :date, :creneau_id, 'confirmee')"
            );
            return $stmt->execute([
                ':salle_id'  => $salleId,
                ':user_id'   => $userId,
                ':date'      => $date,
                ':creneau_id'=> $creneauId,
            ]);
        } catch (PDOException) {
            // Violation de la contrainte UNIQUE (créneau déjà pris)
            return false;
        }
    }

    /** Annule une réservation */
    public function annuler(int $id): bool {
        $stmt = $this->db->prepare(
            "UPDATE reservations SET statut = 'annulee' WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    /** Vérifie si un créneau est déjà pris */
    public function creneauDisponible(int $salleId, string $date, int $creneauId): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM reservations
             WHERE salle_id = :salle_id
             AND date_reservation = :date
             AND creneau_id = :creneau_id
             AND statut = 'confirmee'"
        );
        $stmt->execute([
            ':salle_id'   => $salleId,
            ':date'       => $date,
            ':creneau_id' => $creneauId,
        ]);
        return $stmt->fetchColumn() == 0;
    }

    /** Retourne les créneaux déjà réservés pour une salle + date */
    public function creneauxPris(int $salleId, string $date): array {
        $stmt = $this->db->prepare(
            "SELECT creneau_id FROM reservations
             WHERE salle_id = :salle_id
             AND date_reservation = :date
             AND statut = 'confirmee'"
        );
        $stmt->execute([':salle_id' => $salleId, ':date' => $date]);
        return array_column($stmt->fetchAll(), 'creneau_id');
    }

    /** Compte total des réservations confirmées */
    public function compter(): int {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM reservations WHERE statut = 'confirmee'"
        )->fetchColumn();
    }

    /** Compte les réservations confirmées par jour sur les N derniers jours */
    public function reservationsParJour(int $jours = 7): array {
        $stmt = $this->db->prepare(
            "SELECT DATE(date_reservation) AS jour, COUNT(*) AS total
             FROM reservations
             WHERE date_reservation >= DATE_SUB(CURDATE(), INTERVAL :jours DAY)
             AND statut = 'confirmee'
             GROUP BY DATE(date_reservation)
             ORDER BY jour ASC"
        );
        $stmt->execute([':jours' => $jours]);
        return $stmt->fetchAll();
    }

    /** Compte les réservations du jour */
    public function compterAujourdhui(): int {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM reservations
             WHERE date_reservation = CURDATE() AND statut = 'confirmee'"
        )->fetchColumn();
    }
    /**
 * Retourne les réservations au format JSON
 * pour FullCalendar
 * Filtre selon le rôle :
 * - Admin    → toutes les réservations
 * - Employé  → ses réservations
 * - Membre   → ses réservations
 */
public function pourCalendrier(int $userId, string $role): array {
    if ($role === 'administrateur') {
        // L'admin voit tout
        $stmt = $this->db->query(
            "SELECT r.id, r.date_reservation, r.statut,
                    s.nom AS salle_nom,
                    u.nom AS user_nom,
                    c.heure_debut, c.heure_fin
             FROM reservations r
             JOIN salles s ON s.id = r.salle_id
             JOIN utilisateurs u ON u.id = r.utilisateur_id
             JOIN creneaux c ON c.id = r.creneau_id
             ORDER BY r.date_reservation"
        );
    } else {
        // Employé ou membre → seulement ses réservations
        $stmt = $this->db->prepare(
            "SELECT r.id, r.date_reservation, r.statut,
                    s.nom AS salle_nom,
                    u.nom AS user_nom,
                    c.heure_debut, c.heure_fin
            FROM reservations r
            JOIN salles s ON s.id = r.salle_id
            JOIN utilisateurs u ON u.id = r.utilisateur_id
            JOIN creneaux c ON c.id = r.creneau_id
            WHERE r.utilisateur_id = :uid
            ORDER BY r.date_reservation"
        );
        $stmt->execute([':uid' => $userId]);
    }

    $reservations = $stmt->fetchAll();
    $evenements   = [];

    foreach ($reservations as $r) {
        /* ── Couleur selon le statut ──
            Confirmée → vert
            Annulée   → rouge
        */
        $couleur      = $r['statut'] === 'confirmee' ? '#27ae60' : '#e74c3c';
        $labelStatut  = $r['statut'] === 'confirmee' ? '✅ Confirmée' : '❌ Annulée';

        $evenements[] = [
            'id'    => $r['id'],
            'title' => $labelStatut . ' — 🏢 ' . $r['salle_nom']
            . ' — ' . $r['user_nom'],
            'start' => $r['date_reservation'] . 'T' . $r['heure_debut'],
            'end'   => $r['date_reservation'] . 'T' . $r['heure_fin'],
            'color' => $couleur,
            'extendedProps' => [
                'salle'   => $r['salle_nom'],
                'user'    => $r['user_nom'],
                'statut'  => $r['statut'],
            ],
        ];
    }

    return $evenements;
}
}