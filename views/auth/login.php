<?php require 'views/layout/header.php';
require_once 'config/database.php';
$googleAuthUrl = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => 'email profile',
    'access_type'   => 'online',
]);
?>

<div class="auth-container">
    <h2>Connexion</h2>

    <?php if ($erreur): ?>
        <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?page=login">
        <div class="form-group">
            <label>Login</label>
            <input type="text" name="login" required placeholder="Votre login">
        </div>
        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="mot_de_passe" required placeholder="••••••">
        </div>
        <button type="submit" class="btn">Se connecter</button>
    </form>

    <!-- Séparateur -->
    <div class="separateur">
        <span>ou</span>
    </div>

    <!-- Bouton Google -->
    <a href="<?= $googleAuthUrl ?>" class="btn-google">
        <img src="https://developers.google.com/identity/images/g-logo.png" width="20" height="20" alt="G">
        Se connecter avec Google
    </a>


    <p class="lien">Pas encore de compte ?
        <a href="index.php?page=register">S'inscrire</a>
    </p>
    <p class="lien">
        <a href="index.php?page=forgot_password">Mot de passe oublié ?</a>
    </p>
</div>

<?php require 'views/layout/footer.php'; ?>