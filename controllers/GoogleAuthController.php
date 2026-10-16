<?php
require_once 'config/config.php';
require_once 'config/database.php'; 

class GoogleAuthController {
    public static function callback() {
        $code = $_GET['code'] ?? '';

        if (empty($code)) {
            header('Location: index.php?page=login');
            exit;
        }

        // 1. Exchange code for token
        $tokenResponse = file_get_contents('https://oauth2.googleapis.com/token', false,
            stream_context_create(['http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'code'          => $code,
                    'client_id'     => GOOGLE_CLIENT_ID,
                    'client_secret' => GOOGLE_CLIENT_SECRET,
                    'redirect_uri'  => GOOGLE_REDIRECT_URI,
                    'grant_type'    => 'authorization_code',
                ])
            ]])
        );

        $tokenData   = json_decode($tokenResponse, true);
        $accessToken = $tokenData['access_token'];

        // 2. Get Google user info
        $userResponse = file_get_contents('https://www.googleapis.com/oauth2/v2/userinfo', false,
            stream_context_create(['http' => [
                'header' => 'Authorization: Bearer ' . $accessToken
            ]])
        );

        $googleUser = json_decode($userResponse, true);
        $email      = $googleUser['email'];
        $nom        = $googleUser['name'];

        // 3. Check if user already exists in DB
        $pdo  = getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            // 4. Create new account
            $stmt = $pdo->prepare("
                INSERT INTO utilisateurs (nom, email, login, mot_de_passe, role)
                VALUES (?, ?, ?, ?, 'membre')
            ");
            $stmt->execute([
                $nom,
                $email,
                $email,                // login = email
                password_hash(uniqid(), PASSWORD_DEFAULT) // random password (they use Google to login)
            ]);
            $id   = $pdo->lastInsertId();
            $role = 'membre';
            $photo = null;
        } else {
            $id   = $user['id'];
            $role = $user['role'];
            $nom  = $user['nom'];
            $photo = $user['photo'] ?? null;
        }

        // Use Google photo if no local photo
        $googlePicture = $googleUser['picture'] ?? null;

        // 5. Save in session (same structure as your normal login)
        $_SESSION['user_id']    = $id;
        $_SESSION['user_nom']   = $nom;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role']  = $role;
        $_SESSION['user_photo'] = $photo ?? $googlePicture;

        // 6. Redirect based on role
        switch ($role) {
            case 'administrateur': header('Location: index.php?page=admin_dashboard');    break;
            case 'employe':        header('Location: index.php?page=employe_reservations'); break;
            default:               header('Location: index.php?page=membre_salles');       break;
        }
        exit;
    }
}