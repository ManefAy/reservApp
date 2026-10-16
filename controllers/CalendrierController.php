<?php
/* ══════════════════════════════════════
   CALENDRIER CONTROLLER
   
   Affiche le calendrier visuel des
   réservations avec FullCalendar.
   
   Accessible par les 3 rôles.
   URL : index.php?page=calendrier
══════════════════════════════════════ */

require_once 'models/ReservationModel.php';

class CalendrierController {

    /**
     * Vérifie que l'utilisateur est connecté
     */
    private static function verifierConnecte(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }
    }

    /**
     * Affiche le calendrier
     * Les données sont passées en JSON à FullCalendar
     */
    public static function index(): void {
        self::verifierConnecte();

        $model  = new ReservationModel();
        $userId = (int) $_SESSION['user_id'];
        $role   = $_SESSION['user_role'];

        // Récupère les réservations formatées pour FullCalendar
        $evenements = $model->pourCalendrier($userId, $role);

        // Convertit en JSON pour JavaScript
        $evenementsJson = json_encode($evenements);

        require 'views/calendrier/index.php';
    }
}