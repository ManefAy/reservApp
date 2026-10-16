<?php require 'views/layout/header.php'; ?>

<div class="auth-container">
    <h2>Créer un compte</h2>

    <?php if (!empty($erreur)): ?>
        <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

    <?php if (!empty($succes)): ?>
        <p class="succes"><?= htmlspecialchars($succes) ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?page=register">
        <div class="form-group">
            <label>Nom complet</label>
            <input type="text" name="nom" required placeholder="Ex: Manef Ayouni">
        </div>
        <div class="form-group">
            <label>Adresse e-mail</label>
            <input type="email" name="email" required placeholder="ex@email.com">
        </div>
        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="mot_de_passe" required placeholder="Min. 6 caractères">
        </div>
        <button type="submit" class="btn">S'inscrire</button>
    </form>

    <p class="lien">Déjà un compte ?
        <a href="index.php?page=login">Se connecter</a>
    </p>
</div>

<?php require 'views/layout/footer.php'; ?>