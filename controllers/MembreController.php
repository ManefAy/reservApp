<?php
require_once 'models/SalleModel.php';
require_once 'models/CreneauModel.php';
require_once 'models/ReservationModel.php';

class MembreController {

    /** Vérifie que l'utilisateur est bien un membre */
    private static function verifierMembre(): void {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'membre') {
            header('Location: index.php?page=login');
            exit;
        }
    }

    /** Liste les salles disponibles */
    public static function salles(): void {
        self::verifierMembre();

        $model  = new SalleModel();
        $search = trim($_GET['search'] ?? '');
        $type   = trim($_GET['type']   ?? '');

        // Filtres de recherche
        $salles = $model->rechercherSalles($search, $type);

        require 'views/membre/salles.php';
    }

    /** Créer une réservation */
    public static function reserver(): void {
        self::verifierMembre();

        $salleModel   = new SalleModel();
        $creneauModel = new CreneauModel();
        $resaModel    = new ReservationModel();
        $erreur       = '';
        $succes       = '';
        $userId       = (int) $_SESSION['user_id'];
        $creneauxPris = [];

        // Salle sélectionnée (depuis la liste ou le formulaire)
        $salleId    = (int) ($_GET['salle_id'] ?? $_POST['salle_id'] ?? 0);
        $salleChoisie = $salleId ? $salleModel->trouverParId($salleId) : null;

        // ── TRAITEMENT FORMULAIRE ─────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $salleId   = (int) ($_POST['salle_id']   ?? 0);
            $date      = trim($_POST['date']          ?? '');
            $creneauId = (int) ($_POST['creneau_id'] ?? 0);

            if ($salleId === 0 || $date === '' || $creneauId === 0) {
                $erreur = "Tous les champs sont obligatoires.";
            } elseif (strtotime($date) < strtotime('today')) {
                $erreur = "La date doit être aujourd'hui ou dans le futur.";
            } elseif ($date === date('Y-m-d') && ($creneau = $creneauModel->trouverParId($creneauId)) && $creneau['heure_debut'] <= date('H:i:s')) {
                $erreur = "Ce créneau est déjà passé.";
            }  elseif (!$resaModel->creneauDisponible($salleId, $date, $creneauId)) {
                $erreur = "Ce créneau est déjà réservé pour cette salle.";
            } elseif( $date === date ('Y-m-d') && ($creneau = $creneauModel->trouverParId($creneauId)) && $creneau['heure_debut'] >= date('H:i:s') ) {
                $ok = $resaModel->creer($salleId, $userId, $date, $creneauId);
            }
            else {
                $ok = $resaModel->creer($salleId, $userId, $date, $creneauId);
                if ($ok) {
                    $succes = "Réservation créée avec succès !";

                    /* ── Envoi email avec QR Code ──── */
                    $resas        = $resaModel->listerParUtilisateur($userId);
                    $derniereResa = $resas[0];

                    // Génère le QR Code
                    require_once 'libs/phpqrcoder/qrlib.php';
                    $dossier = 'public/qrcodes/';
                    if (!is_dir($dossier)) mkdir($dossier, 0755, true);
                    $qrPath  = $dossier . 'qr_reservation_' . $derniereResa['id'] . '.png';
                    QRcode::png(
                        'ReservApp #' . $derniereResa['id'] . ' | ' . $derniereResa['salle_nom'],
                        $qrPath,
                        QR_ECLEVEL_M, 6, 2
                    );

                    // Envoie l'email
                    require_once 'models/MailModel.php';
                    $emailEnvoye = MailModel::envoyerConfirmation(
                        $derniereResa,
                        $_SESSION['user_email'] ?? '',
                        $_SESSION['user_nom'],
                        $qrPath
                    );

                    if ($emailEnvoye) {
                        $succes .= " Un email de confirmation vous a été envoyé. 📧";
                    }
                } else {
                    $erreur = "Erreur lors de la création.";
                }
            }

            if ($salleId && $date) {
                $creneauxPris = $resaModel->creneauxPris($salleId, $date);
            }
        }

        $salles   = $salleModel->listerTout();
        $creneaux = $creneauModel->listerTout();
        require 'views/membre/reserver.php';
    }

    /** Mes réservations (membre) */
    public static function mesReservations(): void {
        self::verifierMembre();

        $model  = new ReservationModel();
        $erreur = '';
        $succes = '';
        $userId = (int) $_SESSION['user_id'];

        // ── ANNULATION ───────────────────────────
        if (isset($_GET['action']) && $_GET['action'] === 'annuler') {
            $id   = (int) ($_GET['id'] ?? 0);
            $resas = $model->listerParUtilisateur($userId);
            $resa  = null;

            foreach ($resas as $r) {
                if ((int)$r['id'] === $id) { $resa = $r; break; }
            }

            if (!$resa) {
                $erreur = "Réservation introuvable.";
            } elseif ($resa['statut'] !== 'confirmee') {
                $erreur = "Cette réservation est déjà annulée.";
            } else {
                $diff = strtotime($resa['date_reservation']) - time();
                if ($diff < 86400) {
                    $erreur = "Annulation impossible : moins de 24h avant la réservation.";
                } else {
                    $model->annuler($id);
                    $succes = "Réservation annulée avec succès.";
                }
            }
        }

        $reservations = $model->listerParUtilisateur($userId);
        require 'views/membre/mes_reservations.php';
    }
}