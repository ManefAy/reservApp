<?php
/* ══════════════════════════════════════
   PROFIL CONTROLLER
   Gère les infos, mot de passe
   et la photo de profil
══════════════════════════════════════ */

require_once 'models/ProfilModel.php';

class ProfilController {

    private static function verifierConnecte(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }
    }

    public static function index(): void {
        self::verifierConnecte();

        $model  = new ProfilModel();
        $userId = (int) $_SESSION['user_id'];

        $erreurInfos = '';
        $succesInfos = '';
        $erreurMdp   = '';
        $succesMdp   = '';
        $erreurPhoto = '';
        $succesPhoto = '';

        /* ── Formulaire 1 : Modifier nom + email ── */
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && ($_POST['action'] ?? '') === 'modifier_infos') {

            $nom   = trim($_POST['nom']   ?? '');
            $email = trim($_POST['email'] ?? '');

            if (strlen($nom) < 3) {
                $erreurInfos = "Le nom doit contenir au moins 3 caractères.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erreurInfos = "Adresse email invalide.";
            } elseif ($model->emailExiste($email, $userId)) {
                $erreurInfos = "Cet email est déjà utilisé.";
            } else {
                $model->modifierInfos($userId, $nom, $email);
                $_SESSION['user_nom']   = $nom;
                $_SESSION['user_email'] = $email;
                $succesInfos = "Informations mises à jour avec succès.";
            }
        }

        /* ── Formulaire 2 : Changer mot de passe ── */
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && ($_POST['action'] ?? '') === 'modifier_mdp') {

            $ancienMdp  = $_POST['ancien_mdp']    ?? '';
            $nouveauMdp = $_POST['nouveau_mdp']   ?? '';
            $confirmMdp = $_POST['confirmer_mdp'] ?? '';

            if ($ancienMdp === '' || $nouveauMdp === '' || $confirmMdp === '') {
                $erreurMdp = "Tous les champs sont obligatoires.";
            } elseif (!$model->verifierMotDePasse($userId, $ancienMdp)) {
                $erreurMdp = "L'ancien mot de passe est incorrect.";
            } elseif (strlen($nouveauMdp) < 6) {
                $erreurMdp = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
            } elseif ($nouveauMdp !== $confirmMdp) {
                $erreurMdp = "Les mots de passe ne correspondent pas.";
            } elseif ($ancienMdp === $nouveauMdp) {
                $erreurMdp = "Le nouveau mot de passe doit être différent de l'ancien.";
            } else {
                $model->modifierMotDePasse($userId, $nouveauMdp);
                $succesMdp = "Mot de passe changé avec succès.";
            }
        }

        /* ── Formulaire 3 : Upload photo de profil ──
           Seulement pour les membres
        */
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && ($_POST['action'] ?? '') === 'modifier_photo'
            && $_SESSION['user_role'] === 'membre') {

            $erreurPhoto = self::traiterPhoto($userId, $model);
            if ($erreurPhoto === '') {
                $succesPhoto = "Photo de profil mise à jour !";
            }
        }

        /* ── Suppression photo ── */
        if (isset($_GET['action']) && $_GET['action'] === 'supprimer_photo'
            && $_SESSION['user_role'] === 'membre') {

            // Récupère l'ancienne photo pour la supprimer du disque
            $user = $model->trouverParId($userId);
            if (!empty($user['photo'])) {
                $cheminFichier = 'public/uploads/photos/' . $user['photo'];
                if (file_exists($cheminFichier)) {
                    unlink($cheminFichier); // Supprime le fichier
                }
            }
            $model->supprimerPhoto($userId);
            $_SESSION['user_photo'] = null;
            $succesPhoto = "Photo supprimée.";
        }

        $user = $model->trouverParId($userId);

        // Met à jour la photo en session
        $_SESSION['user_photo'] = $user['photo'] ?? null;

        require 'views/profil/index.php';
    }

    /**
     * Traite l'upload de la photo de profil
     * Vérifie le type, la taille, renomme le fichier
     * Retourne '' si OK, ou un message d'erreur
     */
    private static function traiterPhoto(int $userId, ProfilModel $model): string {

        // Vérifie qu'un fichier a bien été envoyé
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
            return "Veuillez sélectionner une image.";
        }

        $fichier = $_FILES['photo'];

        // ── Vérification des erreurs d'upload ──
        if ($fichier['error'] !== UPLOAD_ERR_OK) {
            return "Erreur lors de l'upload. Code : " . $fichier['error'];
        }

        // ── Vérification de la taille (max 2 Mo) ──
        $maxTaille = 2 * 1024 * 1024; // 2 Mo en octets
        if ($fichier['size'] > $maxTaille) {
            return "L'image ne doit pas dépasser 2 Mo.";
        }

        /* ── Vérification du type MIME réel ──
           On vérifie le vrai type du fichier
           et pas juste l'extension (sécurité)
        */
        $typesAutorises = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo          = finfo_open(FILEINFO_MIME_TYPE);
        $typeMime       = finfo_file($finfo, $fichier['tmp_name']);
        finfo_close($finfo);

        if (!in_array($typeMime, $typesAutorises)) {
            return "Format non autorisé. Utilisez JPG, PNG, GIF ou WEBP.";
        }

        // ── Détermine l'extension selon le type MIME ──
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        $extension = $extensions[$typeMime];

        /* ── Crée le dossier si nécessaire ──
           public/uploads/photos/
        */
        $dossier = 'public/uploads/photos/';
        if (!is_dir($dossier)) {
            mkdir($dossier, 0755, true);
        }

        /* ── Supprime l'ancienne photo ──
           Pour éviter d'accumuler les fichiers
        */
        $userActuel = $model->trouverParId($userId);
        if (!empty($userActuel['photo'])) {
            $ancienFichier = $dossier . $userActuel['photo'];
            if (file_exists($ancienFichier)) {
                unlink($ancienFichier);
            }
        }

        /* ── Nom unique pour le fichier ──
           photo_ID_TIMESTAMP.ext
           Ex : photo_5_1714123456.jpg
        */
        $nomFichier = 'photo_' . $userId . '_' . time() . '.' . $extension;
        $destination = $dossier . $nomFichier;

        // ── Déplace le fichier uploadé ──
        if (!move_uploaded_file($fichier['tmp_name'], $destination)) {
            return "Erreur lors de la sauvegarde de l'image.";
        }

        // ── Sauvegarde le nom en base de données ──
        $model->modifierPhoto($userId, $nomFichier);

        // ── Met à jour la session ──
        $_SESSION['user_photo'] = $nomFichier;

        return ''; // Pas d'erreur
    }
}