<?php require 'views/layout/header.php'; ?>

<div class="page-header">
    <h2>🏢 Salles disponibles</h2>
    <a href="index.php?page=membre_reservations" class="btn btn-small">📅 Mes réservations</a>
</div>

<!-- Barre de recherche -->
<div class="card">
    <form method="GET" action="index.php">
        <input type="hidden" name="page" value="membre_salles">
        <div class="form-row">
            <div class="form-group">
                <label>Rechercher par nom / équipement</label>
                <input type="text" name="search"
                       placeholder="Ex: Projecteur, WiFi..."
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="form-group">
                <label>Type de salle</label>
                <select name="type">
                    <option value="">-- Tous les types --</option>
                    <?php
                    $types = ['Réunion', 'Coworking', 'Salle de conférence', 'Bureau individuel'];
                    foreach ($types as $t):
                        $selected = ($type === $t) ? 'selected' : '';
                    ?>
                        <option value="<?= $t ?>" <?= $selected ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn">🔍 Rechercher</button>
        <a href="index.php?page=membre_salles" class="btn btn-small" style="margin-left:10px">
            ✖ Réinitialiser
        </a>
    </form>
</div>

<!-- Grille des salles -->
<?php if (empty($salles)): ?>
    <div class="card">
        <p>Aucune salle trouvée.</p>
    </div>
<?php else: ?>
<div class="salles-grid">
    <?php foreach ($salles as $s): ?>
    <div class="salle-card">
        <div class="salle-header">
            <h3><?= htmlspecialchars($s['nom']) ?></h3>
            <span class="badge badge-type"><?= htmlspecialchars($s['type_salle']) ?></span>
        </div>
        <div class="salle-body">
            <?php if (!empty($s['description'])): ?>
                <p class="salle-desc"><?= htmlspecialchars($s['description']) ?></p>
            <?php endif; ?>
            <div class="salle-infos">
                <span>👥 <?= $s['capacite'] ?> personnes</span>
                <?php if (!empty($s['equipements'])): ?>
                    <span>🔧 <?= htmlspecialchars($s['equipements']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="salle-footer">
            <a href="index.php?page=membre_reserver&salle_id=<?= $s['id'] ?>"
               class="btn" style="width:100%; text-align:center">
               📅 Réserver cette salle
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require 'views/layout/footer.php'; ?>