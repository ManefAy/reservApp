<?php require 'views/layout/header.php'; ?>

<div class="page-header">
    <h2>📅 Réserver une salle</h2>
    <a href="index.php?page=membre_salles" class="btn btn-small">← Retour aux salles</a>
</div>

<!-- Messages -->
<?php if (!empty($erreur)): ?>
    <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>
<?php if (!empty($succes)): ?>
    <p class="succes"><?= htmlspecialchars($succes) ?></p>
    <a href="index.php?page=membre_reservations" class="btn" style="margin-bottom:20px">
        📋 Voir mes réservations
    </a>
<?php endif; ?>

<div class="card">
    <form method="POST" action="index.php?page=membre_reserver">

        <!-- Salle -->
        <div class="form-group">
            <label>Salle *</label>
            <select name="salle_id" required>
                <option value="">-- Choisir une salle --</option>
                <?php foreach ($salles as $s): ?>
                    <option value="<?= $s['id'] ?>"
                        <?= ($salleId == $s['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nom']) ?>
                        (<?= $s['type_salle'] ?> — <?= $s['capacite'] ?> pers.)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Salle choisie : détail -->
        <?php if ($salleChoisie): ?>
        <div class="salle-detail-mini">
            <strong>🏢 <?= htmlspecialchars($salleChoisie['nom']) ?></strong>
            <span>👥 <?= $salleChoisie['capacite'] ?> personnes</span>
            <?php if (!empty($salleChoisie['equipements'])): ?>
                <span>🔧 <?= htmlspecialchars($salleChoisie['equipements']) ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Date -->
        <div class="form-group">
            <label>Date *</label>
            <input type="date" name="date" required
                   min="<?= date('Y-m-d') ?>"
                   value="<?= htmlspecialchars($_POST['date'] ?? '') ?>">
        </div>

        <!-- Créneaux -->
        <div class="form-group">
            <label>Créneau horaire *</label>
            <div class="creneaux-grid">
                <?php
                $dateChoisie      = $_POST['date'] ?? '';
                $estAujourdHui    = ($dateChoisie === date('Y-m-d'));
                $minutesActuelles = (int)date('H') * 60 + (int)date('i');
                ?>
                <?php foreach ($creneaux as $c): ?>
                    <?php
                    $pris          = in_array($c['id'], $creneauxPris);
                    $parts         = explode(':', $c['heure_debut']);
                    $minutesCreneau = (int)$parts[0] * 60 + (int)($parts[1] ?? 0);
                    $passe         = $estAujourdHui && ($minutesCreneau <= $minutesActuelles);
                    $bloque        = $pris || $passe;
                    ?>
                    <label class="creneau-item <?= $bloque ? 'creneau-pris' : '' ?>">
                        <input type="radio" name="creneau_id"
                               value="<?= $c['id'] ?>"
                               <?= $bloque ? 'disabled' : 'required' ?>
                               <?= (($_POST['creneau_id'] ?? '') == $c['id'] && !$bloque) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($c['libelle']) ?>
                        <?php if ($pris): ?>
                            <span class="pris-label">Pris</span>
                        <?php elseif ($passe): ?>
                            <span class="pris-label">Passé</span>
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <p class="hint">💡 Choisissez une salle et une date — les créneaux disponibles s'affichent automatiquement.</p>
        </div>

        <button type="submit" class="btn">✅ Confirmer la réservation</button>
    </form>
</div>

<?php require 'views/layout/footer.php'; ?>