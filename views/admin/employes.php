<?php require 'views/layout/header.php'; ?>

<div class="page-header">
    <h2>👥 Gestion des employés</h2>
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
    <h3><?= $employeAModifier ? '✏️ Modifier l\'employé' : '➕ Ajouter un employé' ?></h3>
    <form method="POST" action="index.php?page=admin_employes">
        <input type="hidden" name="action"
               value="<?= $employeAModifier ? 'modifier' : 'ajouter' ?>">
        <?php if ($employeAModifier): ?>
            <input type="hidden" name="id" value="<?= $employeAModifier['id'] ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label>Nom complet *</label>
                <input type="text" name="nom" required
                       value="<?= htmlspecialchars($employeAModifier['nom'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Adresse e-mail *</label>
                <input type="email" name="email" required
                       value="<?= htmlspecialchars($employeAModifier['email'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Login *</label>
                <input type="text" name="login" required
                       value="<?= htmlspecialchars($employeAModifier['login'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Mot de passe <?= $employeAModifier ? '(laisser vide = inchangé)' : '*' ?></label>
                <input type="password" name="mot_de_passe"
                       <?= $employeAModifier ? '' : 'required' ?>
                       placeholder="<?= $employeAModifier ? 'Laisser vide pour ne pas changer' : 'Min. 6 caractères' ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Département</label>
                <input type="text" name="departement"
                       placeholder="Ex: Informatique, RH, Finance..."
                       value="<?= htmlspecialchars($employeAModifier['departement'] ?? '') ?>">
            </div>
        </div>

        <button type="submit" class="btn">
            <?= $employeAModifier ? '💾 Enregistrer' : '➕ Ajouter' ?>
        </button>
        <?php if ($employeAModifier): ?>
            <a href="index.php?page=admin_employes" class="btn btn-small" style="margin-left:10px">
                Annuler
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Tableau des employés -->
<div class="card">
    <h3>📋 Liste des employés (<?= count($employes) ?>)</h3>
    <?php if (empty($employes)): ?>
        <p>Aucun employé enregistré.</p>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Login</th>
                <th>Département</th>
                <th>Réservations à venir</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employes as $emp): ?>
            <tr>
                <td><strong><?= htmlspecialchars($emp['nom']) ?></strong></td>
                <td><?= htmlspecialchars($emp['email']) ?></td>
                <td><code><?= htmlspecialchars($emp['login']) ?></code></td>
                <td><?= htmlspecialchars($emp['departement'] ?? '—') ?></td>
                <td>
                    <?php if ($emp['nb_reservations'] > 0): ?>
                        <span class="badge badge-warning"><?= $emp['nb_reservations'] ?></span>
                    <?php else: ?>
                        <span class="badge badge-ok">0</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="index.php?page=admin_employes&action=editer&id=<?= $emp['id'] ?>"
                       class="btn btn-small">✏️ Modifier</a>

                    <?php if ($emp['nb_reservations'] == 0): ?>
                        <a href="index.php?page=admin_employes&action=supprimer&id=<?= $emp['id'] ?>"
                           class="btn btn-small btn-danger"
                           onclick="return confirm('Supprimer cet employé ?')">
                           🗑️ Supprimer
                        </a>
                    <?php else: ?>
                        <span class="btn btn-small btn-disabled"
                              title="A des réservations à venir">
                              🔒 Protégé
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require 'views/layout/footer.php'; ?>