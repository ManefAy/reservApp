<?php
/* ══════════════════════════════════════
   QR CODE CONTROLLER
   Génère un QR Code PNG pour une
   réservation donnée via son ID.
   
   URL d'appel :
   index.php?page=qrcode&id=5
══════════════════════════════════════ */

require_once 'libs/phpqrcoder/qrlib.php';
require_once 'models/ReservationModel.php';

class QrCodeController {

    public static function generer(): void {

        // ── Vérifie que l'utilisateur est connecté ──
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }

        // ── Récupère l'ID de la réservation ──
        $id    = (int) ($_GET['id'] ?? 0);
        $model = new ReservationModel();

        // ── Récupère les données de la réservation ──
        $reservations = $model->listerParUtilisateur((int) $_SESSION['user_id']);
        $resa         = null;

        // Cherche la réservation appartenant à cet utilisateur
        foreach ($reservations as $r) {
            if ((int)$r['id'] === $id) {
                $resa = $r;
                break;
            }
        }

        // Si l'admin demande le QR → on cherche dans toutes les réservations
        if (!$resa && $_SESSION['user_role'] === 'administrateur') {
            $toutes = $model->listerTout();
            foreach ($toutes as $r) {
                if ((int)$r['id'] === $id) {
                    $resa = $r;
                    break;
                }
            }
        }

        // Réservation introuvable
        if (!$resa) {
            die("Réservation introuvable.");
        }

        /* ── Contenu encodé dans le QR Code ──
           On met toutes les infos importantes
           de la réservation dans le QR
        */
        $contenu = implode("\n", [
            "=== RESERVATION ReservApp ===",
            "ID        : #" . $resa['id'],
            "Salle     : " . $resa['salle_nom'],
            "Date      : " . date('d/m/Y', strtotime($resa['date_reservation'])),
            "Créneau   : " . $resa['creneau_libelle'],
            "Statut    : " . strtoupper($resa['statut']),
            "Réservé par : " . $_SESSION['user_nom'],
        ]);

        /* ── Dossier temporaire pour le QR ──
           Le fichier PNG est généré dans
           public/qrcodes/ puis affiché
        */
        $dossier = 'public/qrcodes/';

        // Crée le dossier s'il n'existe pas
        if (!is_dir($dossier)) {
            mkdir($dossier, 0755, true);
        }

        // Nom unique du fichier QR
        $fichier = $dossier . 'qr_reservation_' . $id . '.png';

        /* ── Génère le QR Code ──
           Paramètres :
           - $fichier  : chemin de sortie
           - $contenu  : texte à encoder
           - QR_ECLEVEL_M : niveau de correction d'erreur (M = moyen)
           - 6  : taille des pixels (plus grand = meilleur)
           - 2  : marge en pixels
        */
        QRcode::png($contenu, $fichier, QR_ECLEVEL_M, 6, 2);

        /* ── Affiche l'image directement ──
           Le navigateur reçoit une image PNG
        */
        header('Content-Type: image/png');
        readfile($fichier);
        exit;
    }
}