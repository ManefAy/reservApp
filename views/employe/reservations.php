<?php require 'views/layout/header.php'; ?>

<div class="page-header">
    <h2>📅 Mes réservations</h2>
    <a href="index.php?page=employe_reserver" class="btn">➕ Nouvelle réservation</a>
</div>

<!-- Messages -->
<?php if (!empty($erreur)): ?>
    <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>
<?php if (!empty($succes)): ?>
    <p class="succes"><?= htmlspecialchars($succes) ?></p>
<?php endif; ?>

<div class="card">
    <?php if (empty($reservations)): ?>
        <p>Vous n'avez aucune réservation pour le moment.</p>
        <a href="index.php?page=employe_reserver" class="btn" style="margin-top:15px">
            ➕ Créer une réservation
        </a>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Salle</th>
                <th>Date</th>
                <th>Créneau</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservations as $r): ?>
            <?php
                $estPassee  = strtotime($r['date_reservation']) < strtotime('today');
                $estAnnulee = $r['statut'] === 'annulee';
                // Vérifie la règle 24h
                $diff       = strtotime($r['date_reservation']) - time();
                $peutAnnuler = !$estPassee && !$estAnnulee && $diff >= 86400;
            ?>
            <tr class="<?= $estPassee || $estAnnulee ? 'row-inactive' : '' ?>">
                <td><strong><?= htmlspecialchars($r['salle_nom']) ?></strong></td>
                <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                <td><?= htmlspecialchars($r['creneau_libelle']) ?></td>
                <td>
                    <?php if ($estAnnulee): ?>
                        <span class="badge badge-danger">❌ Annulée</span>
                    <?php elseif ($estPassee): ?>
                        <span class="badge badge-warning">⏰ Passée</span>
                    <?php else: ?>
                        <span class="badge badge-ok">✅ Confirmée</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($peutAnnuler): ?>
                    <a href="index.php?page=employe_reservations&action=annuler&id=<?= $r['id'] ?>"
                        class="btn btn-small btn-danger"
                        onclick="return confirm('Annuler cette réservation ?')">
                        ❌ Annuler
                    </a>
                    <?php elseif (!$estAnnulee && !$estPassee): ?>
                    <span class="btn btn-small btn-disabled" title="Moins de 24h">
                    🔒 -24h
                    </span>
                    <?php else: ?>
                        <span>—</span>
                    <?php endif; ?>
                    <?php if (!$estAnnulee): ?>
                        <!-- Bouton QR Code — ouvre dans un nouvel onglet -->
                        <a href="index.php?page=qrcode&id=<?= $r['id'] ?>"
                        target="_blank"
                        class="btn btn-small"
                        title="Voir le QR Code de cette réservation">
                        📷 QR Code
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require 'views/layout/footer.php'; ?>