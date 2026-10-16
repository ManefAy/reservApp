<?php require 'views/layout/header.php'; ?>


<?php
// Silence editor warnings — these are set by the controller
$avis    = $avis    ?? [];
$moyenne = $moyenne ?? 0;
$erreur  = $erreur  ?? '';
$succes  = $succes  ?? '';
?>



<div class="container">

    <!-- ══ EN-TÊTE ══ -->
    <div class="page-header">
        <h2>⭐ Avis & Évaluations</h2>
        <span style="color: var(--text-muted); font-size: 0.95rem;">
            Note moyenne :
            <strong style="color: var(--text-main); font-size: 1.2rem;">
                <?= $moyenne ?> / 5
            </strong>
        </span>
    </div>

    <!-- ══ FORMULAIRE (membres uniquement) ══ -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'membre'): ?>
    <div class="card">
        <h3>Laisser un avis</h3>

        <?php if ($erreur): ?>
            <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>
        <?php if ($succes): ?>
            <p class="succes"><?= htmlspecialchars($succes) ?></p>
        <?php endif; ?>

        <form method="POST" action="index.php?page=avis">

            <!-- Étoiles -->
            <div class="form-group">
                <label>Votre note</label>
                <div class="stars-input">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" name="note" id="star<?= $i ?>" value="<?= $i ?>">
                        <label for="star<?= $i ?>" title="<?= $i ?> étoile(s)">★</label>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Commentaire -->
            <div class="form-group">
                <label>Commentaire (optionnel)</label>
                <textarea name="commentaire" rows="3"
                    placeholder="Partagez votre expérience..."></textarea>
            </div>

            <button type="submit" class="btn">Publier mon avis</button>
        </form>
    </div>
    <?php elseif (!isset($_SESSION['user_id'])): ?>
        <p class="hint" style="margin-bottom: 20px;">
            <a href="index.php?page=login">Connectez-vous</a> pour laisser un avis.
        </p>
    <?php endif; ?>

    <!-- ══ LISTE DES AVIS ══ -->
    <?php if (empty($avis)): ?>
        <p class="hint">Aucun avis pour le moment. Soyez le premier !</p>
    <?php else: ?>
        <div class="avis-grid">
            <?php foreach ($avis as $a): ?>
            <div class="avis-card">
                <div class="avis-header">
                    <!-- Avatar -->
                    <?php if (!empty($a['photo'])): ?>
                        <img src="public/uploads/photos/<?= htmlspecialchars($a['photo']) ?>"
                             class="avis-avatar" alt="photo">
                    <?php else: ?>
                        <div class="avis-avatar-initiales">
                            <?= strtoupper(substr($a['nom'], 0, 2)) ?>
                        </div>
                    <?php endif; ?>

                    <div>
                        <strong><?= htmlspecialchars($a['nom']) ?></strong>
                        <div class="avis-date">
                            <?= date('d/m/Y à H:i', strtotime($a['created_at'])) ?>
                        </div>
                    </div>

                    <!-- Étoiles affichées -->
                    <div class="avis-etoiles">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="<?= $i <= $a['note'] ? 'etoile-on' : 'etoile-off' ?>">★</span>
                        <?php endfor; ?>
                    </div>
                </div>

                <?php if (!empty($a['commentaire'])): ?>
                    <p class="avis-commentaire">
                        <?= htmlspecialchars($a['commentaire']) ?>
                    </p>
                <?php endif; ?>

                <!-- Bouton supprimer (admin) -->
                <?php if (($_SESSION['user_role'] ?? '') === 'administrateur'): ?>
                    <a href="index.php?page=avis_supprimer&id=<?= $a['id'] ?>"
                       class="btn btn-small btn-danger"
                       onclick="return confirm('Supprimer cet avis ?')">
                        🗑 Supprimer
                    </a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require 'views/layout/footer.php'; ?>