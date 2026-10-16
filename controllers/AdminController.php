<?php
require_once 'models/SalleModel.php';
require_once 'models/ReservationModel.php';
require_once 'models/UtilisateurModel.php';
require_once 'models/EmployeModel.php';
require_once 'models/CreneauModel.php';      
require_once 'models/AvisModel.php';

class AdminController {

    private static function verifierAdmin(): void {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrateur') {
            header('Location: index.php?page=login');
            exit;
        }
    }

    public static function dashboard(): void {
        self::verifierAdmin();
        $salleModel      = new SalleModel();
        $resaModel       = new ReservationModel();
        $employeModel    = new EmployeModel();
        $nb_salles       = $salleModel->compter();
        $nb_reservations = $resaModel->compter();
        $nb_employes     = count($employeModel->listerTout());
        $nb_aujourdhui   = $resaModel->compterAujourdhui();

        // Données graphique : 7 derniers jours
        $statsMap = [];
        foreach ($resaModel->reservationsParJour(7) as $s) {
            $statsMap[$s['jour']] = (int) $s['total'];
        }
        $chartLabels = [];
        $chartData   = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $chartLabels[] = date('d/m', strtotime("-$i days"));
            $chartData[]   = $statsMap[$d] ?? 0;
        }
        // Stats avis
        $avisModel   = new AvisModel();
        $moyenne_avis = $avisModel->moyenneNote();
        $nb_avis      = count($avisModel->tousLesAvis());
        require 'views/admin/dashboard.php';
    }

    public static function salles(): void {
        self::verifierAdmin();
        $model  = new SalleModel();
        $erreur = '';
        $succes = '';

        if (isset($_GET['action']) && $_GET['action'] === 'supprimer') {
            $id = (int) ($_GET['id'] ?? 0);
            if ($model->aDesReservationsAvenir($id)) {
                $erreur = "Impossible : cette salle a des réservations à venir.";
            } else {
                $model->supprimer($id);
                $succes = "Salle supprimée avec succès.";
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ajouter') {
            $erreur = self::validerSalle($_POST);
            if ($erreur === '') {
                $model->ajouter([
                    'nom'         => trim($_POST['nom']),
                    'description' => trim($_POST['description']),
                    'capacite'    => (int) $_POST['capacite'],
                    'type_salle'  => trim($_POST['type_salle']),
                    'equipements' => trim($_POST['equipements']),
                ]);
                $succes = "Salle ajoutée avec succès.";
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier') {
            $id     = (int) ($_POST['id'] ?? 0);
            $erreur = self::validerSalle($_POST);
            if ($erreur === '') {
                $model->modifier($id, [
                    'nom'         => trim($_POST['nom']),
                    'description' => trim($_POST['description']),
                    'capacite'    => (int) $_POST['capacite'],
                    'type_salle'  => trim($_POST['type_salle']),
                    'equipements' => trim($_POST['equipements']),
                ]);
                $succes = "Salle modifiée avec succès.";
            }
        }

        $salleAModifier = null;
        if (isset($_GET['action']) && $_GET['action'] === 'editer') {
            $salleAModifier = $model->trouverParId((int) ($_GET['id'] ?? 0));
        }

        $salles = $model->listerTout();
        require 'views/admin/salles.php';
    }

    private static function validerSalle(array $data): string {
        $nom      = trim($data['nom']         ?? '');
        $capacite = trim($data['capacite']    ?? '');
        $type     = trim($data['type_salle']  ?? '');
        $desc     = trim($data['description'] ?? '');
        if (strlen($nom) < 3)                                            return "Le nom doit faire au moins 3 caractères.";
        if (!is_numeric($capacite) || $capacite < 1 || $capacite > 500) return "Capacité entre 1 et 500.";
        if ($type === '')                                                 return "Le type est obligatoire.";
        if ($desc !== '' && strlen($desc) < 20)                         return "La description doit faire au moins 20 caractères.";
        return '';
    }

    public static function employes(): void {
        self::verifierAdmin();
        $model  = new EmployeModel();
        $erreur = '';
        $succes = '';

        if (isset($_GET['action']) && $_GET['action'] === 'supprimer') {
            $id = (int) ($_GET['id'] ?? 0);
            if ($model->aDesReservationsAvenir($id)) {
                $erreur = "Impossible : cet employé a des réservations à venir.";
            } else {
                $model->supprimer($id);
                $succes = "Employé supprimé avec succès.";
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ajouter') {
            $erreur = self::validerEmploye($_POST);
            if ($erreur === '') {
                if ($model->loginExiste(trim($_POST['login']))) {
                    $erreur = "Ce login est déjà utilisé.";
                } elseif ($model->emailExiste(trim($_POST['email']))) {
                    $erreur = "Cet email est déjà utilisé.";
                } else {
                    $model->ajouter([
                        'nom'          => trim($_POST['nom']),
                        'email'        => trim($_POST['email']),
                        'login'        => trim($_POST['login']),
                        'mot_de_passe' => trim($_POST['mot_de_passe']),
                        'departement'  => trim($_POST['departement']),
                    ]);
                    $succes = "Employé ajouté avec succès.";
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier') {
            $id     = (int) ($_POST['id'] ?? 0);
            $erreur = self::validerEmploye($_POST, true);
            if ($erreur === '') {
                if ($model->loginExiste(trim($_POST['login']), $id)) {
                    $erreur = "Ce login est déjà utilisé.";
                } elseif ($model->emailExiste(trim($_POST['email']), $id)) {
                    $erreur = "Cet email est déjà utilisé.";
                } else {
                    $model->modifier($id, [
                        'nom'          => trim($_POST['nom']),
                        'email'        => trim($_POST['email']),
                        'login'        => trim($_POST['login']),
                        'mot_de_passe' => trim($_POST['mot_de_passe']),
                        'departement'  => trim($_POST['departement']),
                    ]);
                    $succes = "Employé modifié avec succès.";
                }
            }
        }

        $employeAModifier = null;
        if (isset($_GET['action']) && $_GET['action'] === 'editer') {
            $employeAModifier = $model->trouverParId((int) ($_GET['id'] ?? 0));
        }

        $employes = $model->listerTout();
        require 'views/admin/employes.php';
    }

    private static function validerEmploye(array $data, bool $mdpOptionel = false): string {
        $nom   = trim($data['nom']          ?? '');
        $email = trim($data['email']        ?? '');
        $login = trim($data['login']        ?? '');
        $mdp   = trim($data['mot_de_passe'] ?? '');
        if (strlen($nom) < 3)                                    return "Le nom doit faire au moins 3 caractères.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))          return "Email invalide.";
        if (strlen($login) < 3)                                  return "Le login doit faire au moins 3 caractères.";
        if (!$mdpOptionel && strlen($mdp) < 6)                   return "Le mot de passe doit faire au moins 6 caractères.";
        if ($mdpOptionel && $mdp !== '' && strlen($mdp) < 6)     return "Le mot de passe doit faire au moins 6 caractères.";
        return '';
    }

    // ── RÉSERVATIONS ────────────────────────────────────────────

    public static function reservations(): void {
        self::verifierAdmin();

        $resaModel  = new ReservationModel();
        $salleModel = new SalleModel();
        $userModel  = new UtilisateurModel();
        $erreur     = '';
        $succes     = '';

        // ── ANNULATION ───────────────────────────
        if (isset($_GET['action']) && $_GET['action'] === 'annuler') {
            $id = (int) ($_GET['id'] ?? 0);
            $resaModel->annuler($id);
            $succes = "Réservation annulée avec succès.";
        }

        // Récupère les filtres
        $filtres = [
            'salle_id'       => $_GET['salle_id']       ?? '',
            'date'           => $_GET['date']            ?? '',
            'utilisateur_id' => $_GET['utilisateur_id'] ?? '',
            'statut'         => $_GET['statut']          ?? '',
        ];

        $reservations = $resaModel->listerTout($filtres);
        $salles       = $salleModel->listerTout();
        $utilisateurs = $userModel->listerEmployes(); // pour le filtre

        require 'views/admin/reservations.php';
    }
}