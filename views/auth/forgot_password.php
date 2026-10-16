<?php require 'views/layout/header.php'; ?>

<div class="auth-container">
    <h2>Mot de passe oublié</h2>
    <p style="color:var(--text-muted);margin-bottom:20px;font-size:0.95rem;">
        Saisissez votre adresse e-mail et nous vous enverrons un lien de réinitialisation.
    </p>

    <?php if (!empty($erreur)): ?>
        <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>
    <?php if (!empty($succes)): ?>
        <p class="succes"><?= htmlspecialchars($succes) ?></p>
    <?php endif; ?>

    <?php if (empty($succes)): ?>
    <form method="POST" action="index.php?page=forgot_password">
        <div class="form-group">
            <label>Adresse e-mail</label>
            <input type="email" name="email" required placeholder="votre@email.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <button type="submit" class="btn">Envoyer le lien</button>
    </form>
    <?php endif; ?>

    <p class="lien">
        <a href="index.php?page=login">← Retour à la connexion</a>
    </p>
</div>

<?php require 'views/layout/footer.php'; ?>
