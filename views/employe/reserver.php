<?php require 'views/layout/header.php'; ?>

<div class="page-header">
    <h2>➕ Nouvelle réservation</h2>
    <a href="index.php?page=employe_reservations" class="btn btn-small">← Mes réservations</a>
</div>

<!-- Messages -->
<?php if (!empty($erreur)): ?>
    <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>
<?php if (!empty($succes)): ?>
    <p class="succes"><?= htmlspecialchars($succes) ?></p>
<?php endif; ?>

<div class="card">
    <h3>Choisissez votre salle et créneau</h3>
    <form method="POST" action="index.php?page=employe_reserver">

        <div class="form-row">
            <!-- Salle -->
            <div class="form-group">
                <label>Salle *</label>
                <select name="salle_id" id="salle_id" required>
                    <option value="">-- Choisir une salle --</option>
                    <?php foreach ($salles as $s): ?>
                        <option value="<?= $s['id'] ?>"
                            <?= (($_POST['salle_id'] ?? '') == $s['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nom']) ?>
                            (<?= $s['type_salle'] ?> — <?= $s['capacite'] ?> pers.)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date -->
            <div class="form-group">
                <label>Date *</label>
                <input type="date" name="date" id="date" required
                       min="<?= date('Y-m-d') ?>"
                       value="<?= htmlspecialchars($_POST['date'] ?? '') ?>">
            </div>
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
                    $pris           = in_array($c['id'], $creneauxPris);
                    $parts          = explode(':', $c['heure_debut']);
                    $minutesCreneau = (int)$parts[0] * 60 + (int)($parts[1] ?? 0);
                    $passe          = $estAujourdHui && ($minutesCreneau <= $minutesActuelles);
                    $bloque         = $pris || $passe;
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
        </div>

        <button type="submit" class="btn">✅ Confirmer la réservation</button>
    </form>
</div>

<!-- Info : créneaux mis à jour dynamiquement -->
<div class="card" style="background:#f8f9fa;">
    <p>💡 <strong>Astuce :</strong> Sélectionnez une salle et une date — les créneaux disponibles s'affichent automatiquement.</p>
</div>

<?php require 'views/layout/footer.php'; ?>