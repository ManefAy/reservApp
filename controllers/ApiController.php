<?php
class ApiController {

    /**
     * Retourne en JSON les créneaux d'une salle pour une date donnée,
     * avec leur statut (pris / libre). Utilisé par le fetch() côté JS.
     *
     * GET params : salle_id (int), date (Y-m-d)
     */
    public static function creneaux(): void {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Non autorisé']);
            exit;
        }

        $salleId = (int) ($_GET['salle_id'] ?? 0);
        $date    = trim($_GET['date'] ?? '');

        if (!$salleId || !$date) {
            echo json_encode([]);
            exit;
        }

        require_once 'models/CreneauModel.php';
        require_once 'models/ReservationModel.php';

        $creneauModel  = new CreneauModel();
        $resaModel     = new ReservationModel();

        $creneaux     = $creneauModel->listerTout();
        $creneauxPris = $resaModel->creneauxPris($salleId, $date);

        $result = [];
        foreach ($creneaux as $c) {
            $result[] = [
                'id'      => $c['id'],
                'libelle' => $c['libelle'],
                'pris'    => in_array($c['id'], $creneauxPris),
            ];
        }

        echo json_encode($result);
        exit;
    }
}
