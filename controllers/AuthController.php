<?php
require_once 'models/UtilisateurModel.php';
require_once 'models/OtpModel.php';
require_once 'config/mail.php';

class AuthController {

    /**
     * Page de login : affiche le formulaire (GET) ou traite (POST)
     */
    public static function login(): void {
    $erreur = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $login = trim($_POST['login'] ?? '');
        $mdp   = trim($_POST['mot_de_passe'] ?? '');

        if ($login === '' || $mdp === '') {
            $erreur = "Veuillez remplir tous les champs.";
        } else {
            $model = new UtilisateurModel();
            $user  = $model->trouverParLogin($login);

            if ($user && password_verify($mdp, $user['mot_de_passe'])) {
                // ── Suspicious IP detection (all users) ──
                $currentIp = $_SERVER['REMOTE_ADDR'];
                if (!empty($user['last_ip']) && $user['last_ip'] !== $currentIp) {
                    self::envoyerAlertIp($user['email'], $user['nom'], $currentIp);
                }
                $model->mettreAjourIp((int)$user['id'], $currentIp);

                // ── OTP for admin only ──
                if ($user['role'] === 'administrateur') {
                    $otpModel = new OtpModel();
                    $code     = $otpModel->creer($user['id']);
                    self::envoyerOtp($user['email'], $user['nom'], $code);
                    $_SESSION['otp_user_id'] = $user['id'];
                    header('Location: index.php?page=otp_verify');
                    return;
                } // ← closes the admin if

                // Normal login for non-admins
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_nom']   = $user['nom'];
                $_SESSION['user_role']  = $user['role'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_photo'] = $user['photo'] ?? null;

                self::redirigerSelonRole($user['role']);
                return;

            } else {
                $erreur = "Login ou mot de passe incorrect.";
            }
        }
    }

    require 'views/auth/login.php';
}
    
    /**
     * Inscription d'un membre (public)
     */
    public static function register(): void {
        $erreur  = '';
        $succes  = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom   = trim($_POST['nom']          ?? '');
            $email = trim($_POST['email']         ?? '');
            $mdp   = trim($_POST['mot_de_passe']  ?? '');

            // Validations simples
            if ($nom === '' || $email === '' || $mdp === '') {
                $erreur = "Tous les champs sont obligatoires.";
            } elseif (strlen($nom) < 3) {
                $erreur = "Le nom doit contenir au moins 3 caractères.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erreur = "Adresse e-mail invalide.";
            } elseif (strlen($mdp) < 6) {
                $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
            } else {
                $model = new UtilisateurModel();

                if ($model->trouverParEmail($email)) {
                    $erreur = "Cet e-mail est déjà utilisé.";
                } else {
                    $model->creerMembre($nom, $email, $mdp);
                    $succes = "Compte créé ! Vous pouvez vous connecter.";
                }
            }
        }

        require 'views/auth/register.php';
    }

    /**
     * Formulaire "mot de passe oublié" : envoie un lien de réinitialisation
     */
    public static function forgotPassword(): void {
        $erreur = '';
        $succes = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erreur = "Adresse e-mail invalide.";
            } else {
                $model = new UtilisateurModel();
                $user  = $model->trouverParEmail($email);

                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    $model->creerTokenReset((int) $user['id'], $token);

                    require_once 'models/MailModel.php';
                    MailModel::envoyerReinitialisation($email, $user['nom'], $token);
                }

                // Message générique : ne révèle pas si l'email existe
                $succes = "Si cet email est enregistré, un lien de réinitialisation vous a été envoyé.";
            }
        }

        require 'views/auth/forgot_password.php';
    }

    /**
     * Page de réinitialisation : vérifie le token et met à jour le mot de passe
     */
    public static function resetPassword(): void {
        $token  = trim($_GET['token'] ?? $_POST['token'] ?? '');
        $erreur = '';
        $succes = '';

        if ($token === '') {
            header('Location: index.php?page=login');
            exit;
        }

        $model       = new UtilisateurModel();
        $user        = $model->trouverParTokenReset($token);
        $tokenValide = (bool) $user;

        if (!$tokenValide) {
            $erreur = "Ce lien est invalide ou a expiré. Faites une nouvelle demande.";
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mdp     = $_POST['mot_de_passe']  ?? '';
            $confirm = $_POST['confirmer_mdp'] ?? '';

            if (strlen($mdp) < 6) {
                $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
            } elseif ($mdp !== $confirm) {
                $erreur = "Les mots de passe ne correspondent pas.";
            } elseif (password_verify($mdp, $user['mot_de_passe'])) {
                $erreur = "Le nouveau mot de passe doit être différent de l'ancien.";
            } else {
                $model->reinitialiserMotDePasse($token, $mdp);
                $succes      = "Mot de passe réinitialisé ! Vous pouvez vous connecter.";
                $tokenValide = false;
            }
        }

        require 'views/auth/reset_password.php';
    }

    /**
     * Déconnexion
     */
    public static function logout(): void {
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    }

    /**
     * Redirige selon le rôle après connexion
     */
    private static function redirigerSelonRole(string $role): void {
        $destinations = [
            'administrateur' => 'index.php?page=admin_dashboard',
            'employe'        => 'index.php?page=employe_reservations',
            'membre'         => 'index.php?page=membre_salles',
        ];
        header('Location: ' . ($destinations[$role] ?? 'index.php?page=login'));
        exit;
    }

// ── Send OTP email ──
private static function envoyerOtp(string $email, string $nom, string $code): void {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($email, $nom);
        $mail->Subject = 'Votre code de vérification ReservApp';
        $mail->isHTML(true);
        $mail->Body    = "
            <h2>Vérification en deux étapes</h2>
            <p>Bonjour <strong>$nom</strong>,</p>
            <p>Votre code de connexion est :</p>
            <h1 style='letter-spacing:8px; color:#2c3e50;'>$code</h1>
            <p>Ce code expire dans <strong>5 minutes</strong>.</p>
            <p>Si vous n'êtes pas à l'origine de cette connexion, ignorez cet email.</p>
        ";
        $mail->send();
    } catch (Exception $e) {
        // Silently fail
    }
}

// ── Send suspicious IP alert email ──
private static function envoyerAlertIp(string $email, string $nom, string $ip): void {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($email, $nom);
        $mail->Subject = '⚠️ Connexion depuis une nouvelle adresse IP';
        $mail->isHTML(true);
        $mail->Body    = "
            <h2>⚠️ Alerte de sécurité</h2>
            <p>Bonjour <strong>$nom</strong>,</p>
            <p>Une connexion à votre compte a été détectée depuis une nouvelle adresse IP :</p>
            <p><strong style='color:#e74c3c;'>$ip</strong></p>
            <p>Date : <strong>" . date('d/m/Y à H:i') . "</strong></p>
            <p>Si c'est vous, ignorez cet email. Sinon, changez votre mot de passe immédiatement.</p>
        ";
        $mail->send();
    } catch (Exception $e) {
            // Silently fail
        }
    }

    public static function otpVerify(): void {
        if (!isset($_SESSION['otp_user_id'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $erreur = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code     = trim($_POST['code'] ?? '');
            $userId   = (int) $_SESSION['otp_user_id'];
            $otpModel = new OtpModel();

            if ($otpModel->verifier($userId, $code)) {
                unset($_SESSION['otp_user_id']);

                $model = new UtilisateurModel();
                $user  = $model->trouverParId($userId);

                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_nom']   = $user['nom'];
                $_SESSION['user_role']  = $user['role'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_photo'] = $user['photo'] ?? null;

                self::redirigerSelonRole($user['role']);
                return;
            } else {
                $erreur = "Code incorrect ou expiré. Veuillez réessayer.";
            }
        }

        require 'views/auth/otp_verify.php';
    }
} // ← closing brace of the class