<?php
require_once 'models/ReservationModel.php';
require_once 'models/SalleModel.php';
require_once 'models/CreneauModel.php';
require_once 'models/MailModel.php';          // ← Ajoute

class EmployeController {

    private static function verifierEmploye(): void {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'employe') {
            header('Location: index.php?page=login');
            exit;
        }
    }

    public static function mesReservations(): void {
        self::verifierEmploye();
        $model        = new ReservationModel();
        $erreur       = '';
        $succes       = '';
        $userId       = (int) $_SESSION['user_id'];

        if (isset($_GET['action']) && $_GET['action'] === 'annuler') {
            $id   = (int) ($_GET['id'] ?? 0);
            $resa = self::trouverReservationEmploye($id, $userId);
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
        require 'views/employe/reservations.php';
    }

    public static function reserver(): void {
        self::verifierEmploye();

        $salleModel   = new SalleModel();
        $creneauModel = new CreneauModel();
        $resaModel    = new ReservationModel();
        $erreur       = '';
        $succes       = '';
        $userId       = (int) $_SESSION['user_id'];
        $creneauxPris = [];

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
            } elseif (!$resaModel->creneauDisponible($salleId, $date, $creneauId)) {
                $erreur = "Ce créneau est déjà réservé pour cette salle.";
            } else {
                $ok = $resaModel->creer($salleId, $userId, $date, $creneauId);

                if ($ok) {
                    $succes = "Réservation créée avec succès !";

                    /* ── Envoi email avec QR Code ────────
                       1. Récupère la dernière réservation
                       2. Génère le QR Code
                       3. Envoie l'email
                    */
                    $resas      = $resaModel->listerParUtilisateur($userId);
                    $derniereResa = $resas[0]; // La plus récente (ORDER BY DESC)

                    // Génère le QR Code et récupère son chemin
                    $qrPath = self::genererQrCode($derniereResa);

                    // Envoie l'email
                    $emailEnvoye = MailModel::envoyerConfirmation(
                        $derniereResa,
                        $_SESSION['user_email'] ?? '',  // Email de l'employé
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
        require 'views/employe/reserver.php';
    }

    /**
     * Génère le QR Code pour une réservation
     * et retourne le chemin du fichier PNG
     */
    private static function genererQrCode(array $resa): string {
        require_once 'libs/phpqrcoder/qrlib.php'; // ← Ta librairie QR

        $contenu = implode(' | ', [
            'ReservApp #'  . $resa['id'],
            'Salle: '      . $resa['salle_nom'],
            'Date: '       . date('d/m/Y', strtotime($resa['date_reservation'])),
            'Creneau: '    . $resa['creneau_libelle'],
        ]);

        // Dossier de stockage des QR Codes
        $dossier = 'public/qrcodes/';
        if (!is_dir($dossier)) {
            mkdir($dossier, 0755, true);
        }

        $fichier = $dossier . 'qr_reservation_' . $resa['id'] . '.png';

        // Génère le QR Code avec ta librairie
        QRcode::png($contenu, $fichier, QR_ECLEVEL_M, 6, 2);

        return $fichier;
    }

    private static function trouverReservationEmploye(int $resaId, int $userId): array|false {
        $model = new ReservationModel();
        $resas = $model->listerParUtilisateur($userId);
        foreach ($resas as $r) {
            if ((int)$r['id'] === $resaId) return $r;
        }
        return false;
    }
}