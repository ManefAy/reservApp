<?php require 'views/layout/header.php'; ?>

<div class="auth-container">
    <h2>Réinitialiser le mot de passe</h2>

    <?php if (!empty($erreur)): ?>
        <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>
    <?php if (!empty($succes)): ?>
        <p class="succes"><?= htmlspecialchars($succes) ?></p>
        <p class="lien"><a href="index.php?page=login">Se connecter</a></p>
    <?php endif; ?>

    <?php if ($tokenValide): ?>
    <form method="POST" action="index.php?page=reset_password">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <div class="form-group">
            <label>Nouveau mot de passe</label>
            <input type="password" name="mot_de_passe" required
                   placeholder="6 caractères minimum">
        </div>
        <div class="form-group">
            <label>Confirmer le mot de passe</label>
            <input type="password" name="confirmer_mdp" required
                   placeholder="Répétez le mot de passe">
        </div>
        <button type="submit" class="btn">Réinitialiser</button>
    </form>
    <?php elseif (empty($succes)): ?>
        <p class="lien"><a href="index.php?page=forgot_password">Faire une nouvelle demande</a></p>
    <?php endif; ?>

    <p class="lien">
        <a href="index.php?page=login">← Retour à la connexion</a>
    </p>
</div>

<?php require 'views/layout/footer.php'; ?>
