<?php require 'views/layout/header.php'; ?>

<?php
$erreur = $erreur ?? '';
?>

<div class="auth-container">
    <h2>🔐 Vérification en deux étapes</h2>

    <p style="text-align:center; color: var(--text-muted); margin-bottom: 20px;">
        Un code à 6 chiffres a été envoyé à votre adresse email.<br>
        Ce code expire dans <strong>5 minutes</strong>.
    </p>

    <?php if ($erreur): ?>
        <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?page=otp_verify">
        <div class="form-group">
            <label>Code de vérification</label>
            <input type="text"
                   name="code"
                   required
                   maxlength="6"
                   placeholder="000000"
                   autocomplete="one-time-code"
                   style="text-align:center; font-size:1.5rem; letter-spacing:8px;">
        </div>
        <button type="submit" class="btn" style="width:100%;">Vérifier</button>
    </form>

    <p class="lien">
        <a href="index.php?page=login">← Retour à la connexion</a>
    </p>
</div>

<?php require 'views/layout/footer.php'; ?>