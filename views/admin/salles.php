<?php require 'views/layout/header.php'; ?>

<div class="page-header">
    <h2>🏢 Gestion des salles</h2>
    <a href="index.php?page=admin_dashboard" class="btn btn-small">← Dashboard</a>
</div>

<!-- Messages -->
<?php if (!empty($erreur)): ?>
    <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>
<?php if (!empty($succes)): ?>
    <p class="succes"><?= htmlspecialchars($succes) ?></p>
<?php endif; ?>

<!-- Formulaire Ajout / Modification -->
<div class="card">
    <h3><?= $salleAModifier ? '✏️ Modifier la salle' : '➕ Ajouter une salle' ?></h3>
    <form method="POST" action="index.php?page=admin_salles">
        <input type="hidden" name="action" value="<?= $salleAModifier ? 'modifier' : 'ajouter' ?>">
        <?php if ($salleAModifier): ?>
            <input type="hidden" name="id" value="<?= $salleAModifier['id'] ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label>Nom *</label>
                <input type="text" name="nom" required
                    value="<?= htmlspecialchars($salleAModifier['nom'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Capacité (1-500) *</label>
                <input type="number" name="capacite" min="1" max="500" required
                    value="<?= htmlspecialchars($salleAModifier['capacite'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Type de salle *</label>
                <select name="type_salle" required>
                    <option value="">-- Choisir --</option>
                    <?php
                    $types = ['Réunion', 'Coworking', 'Salle de conférence', 'Bureau individuel'];
                    foreach ($types as $t):
                        $selected = ($salleAModifier['type_salle'] ?? '') === $t ? 'selected' : '';
                    ?>
                        <option value="<?= $t ?>" <?= $selected ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Équipements</label>
                <input type="text" name="equipements" placeholder="Ex: Projecteur, WiFi, Tableau"
                    value="<?= htmlspecialchars($salleAModifier['equipements'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Description (min. 20 caractères si renseignée)</label>
            <textarea name="description" rows="3"
                placeholder="Décrivez la salle..."><?= htmlspecialchars($salleAModifier['description'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn">
            <?= $salleAModifier ? '💾 Enregistrer' : '➕ Ajouter' ?>
        </button>
        <?php if ($salleAModifier): ?>
            <a href="index.php?page=admin_salles" class="btn btn-small" style="margin-left:10px">
                Annuler
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Tableau des salles -->
<div class="card">
    <h3>📋 Liste des salles (<?= count($salles) ?>)</h3>
    <?php if (empty($salles)): ?>
        <p>Aucune salle enregistrée.</p>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Type</th>
                <th>Capacité</th>
                <th>Équipements</th>
                <th>Réservations</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($salles as $salle): ?>
            <tr>
                <td><strong><?= htmlspecialchars($salle['nom']) ?></strong></td>
                <td><?= htmlspecialchars($salle['type_salle']) ?></td>
                <td><?= $salle['capacite'] ?> pers.</td>
                <td><?= htmlspecialchars($salle['equipements'] ?? '—') ?></td>
                <td><?= $salle['nb_reservations'] ?></td>
                <td>
                    <a href="index.php?page=admin_salles&action=editer&id=<?= $salle['id'] ?>"
                       class="btn btn-small">✏️ Modifier</a>

                    <a href="index.php?page=admin_salles&action=supprimer&id=<?= $salle['id'] ?>"
                       class="btn btn-small btn-danger"
                       onclick="return confirm('Supprimer cette salle ?')">
                       🗑️ Supprimer
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require 'views/layout/footer.php'; ?>