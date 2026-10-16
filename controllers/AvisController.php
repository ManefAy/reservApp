<?php
require_once 'models/AvisModel.php';

class AvisController {

    // Afficher la page publique des avis
    public static function index(): void {
        $model    = new AvisModel();
        $avis     = $model->tousLesAvis();
        $moyenne  = $model->moyenneNote();
        $erreur   = '';
        $succes = isset($_GET['succes']) ? "Votre avis a été publié !" : '';

        // Soumission d'un nouvel avis (membres uniquement)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $note        = (int) ($_POST['note'] ?? 0);
            $commentaire = trim($_POST['commentaire'] ?? '');

            if ($note < 1 || $note > 5) {
                $erreur = "Veuillez sélectionner une note entre 1 et 5.";
            } else {
                $model->ajouter($_SESSION['user_id'], $note, $commentaire);
                // Redirect to avoid form resubmission on refresh (PRG pattern)
                header('Location: index.php?page=avis&succes=1');
                exit;
}
        }

        require 'views/avis/index.php';
    }

    // Supprimer un avis (admin uniquement)
    public static function supprimer(): void {
        if (($_SESSION['user_role'] ?? '') !== 'administrateur') {
            header('Location: index.php?page=avis');
            exit;
        }

        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $model = new AvisModel();
            $model->supprimer($id);
        }

        header('Location: index.php?page=avis');
        exit;
    }
}