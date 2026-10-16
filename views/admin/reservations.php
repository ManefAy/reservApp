<?php require 'views/layout/header.php'; ?>

<div class="page-header">
    <h2>📅 Gestion des réservations</h2>
    <a href="index.php?page=admin_dashboard" class="btn btn-small">← Dashboard</a>
</div>

<!-- Messages -->
<?php if (!empty($erreur)): ?>
    <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>
<?php if (!empty($succes)): ?>
    <p class="succes"><?= htmlspecialchars($succes) ?></p>
<?php endif; ?>

<!-- Filtres -->
<div class="card">
    <h3>🔍 Filtrer les réservations</h3>
    <form method="GET" action="index.php">
        <input type="hidden" name="page" value="admin_reservations">
        <div class="form-row">

            <div class="form-group">
                <label>Salle</label>
                <select name="salle_id">
                    <option value="">-- Toutes --</option>
                    <?php foreach ($salles as $s): ?>
                        <option value="<?= $s['id'] ?>"
                            <?= ($filtres['salle_id'] == $s['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date"
                       value="<?= htmlspecialchars($filtres['date']) ?>">
            </div>

            <div class="form-group">
                <label>Statut</label>
                <select name="statut">
                    <option value="">-- Tous --</option>
                    <option value="confirmee"  <?= $filtres['statut'] === 'confirmee'  ? 'selected' : '' ?>>Confirmée</option>
                    <option value="annulee"    <?= $filtres['statut'] === 'annulee'    ? 'selected' : '' ?>>Annulée</option>
                </select>
            </div>

        </div>
        <button type="submit" class="btn">🔍 Filtrer</button>
        <a href="index.php?page=admin_reservations" class="btn btn-small" style="margin-left:10px">
            ✖ Réinitialiser
        </a>
    </form>
</div>

<!-- Tableau des réservations -->
<div class="card">
    <h3>📋 Liste des réservations (<?= count($reservations) ?>)</h3>

    <?php if (empty($reservations)): ?>
        <p>Aucune réservation trouvée.</p>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Salle</th>
                <th>Utilisateur</th>
                <th>Rôle</th>
                <th>Date</th>
                <th>Créneau</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservations as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><strong><?= htmlspecialchars($r['salle_nom']) ?></strong></td>
                <td><?= htmlspecialchars($r['utilisateur_nom']) ?></td>
                <td>
                    <span class="badge <?= $r['utilisateur_role'] === 'employe' ? 'badge-role-emp' : 'badge-role-mem' ?>">
                        <?= $r['utilisateur_role'] ?>
                    </span>
                </td>
                <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                <td><?= htmlspecialchars($r['creneau_libelle']) ?></td>
                <td>
                    <?php if ($r['statut'] === 'confirmee'): ?>
                        <span class="badge badge-ok">✅ Confirmée</span>
                    <?php else: ?>
                        <span class="badge badge-danger">❌ Annulée</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($r['statut'] === 'confirmee'): ?>
                        <a href="index.php?page=admin_reservations&action=annuler&id=<?= $r['id'] ?>"
                           class="btn btn-small btn-danger"
                           onclick="return confirm('Annuler cette réservation ?')">
                           ❌ Annuler
                        </a>
                    <?php else: ?>
                        <span class="btn btn-small btn-disabled">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require 'views/layout/footer.php'; ?>