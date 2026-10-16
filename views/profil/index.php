<?php require 'views/layout/header.php'; ?>

<div class="page-header">
    <h2>👤 Mon profil</h2>
    <?php
    $retour = match($_SESSION['user_role']) {
        'administrateur' => 'index.php?page=admin_dashboard',
        'employe'        => 'index.php?page=employe_reservations',
        'membre'         => 'index.php?page=membre_salles',
        default          => 'index.php?page=login',
    };
    ?>
    <a href="<?= $retour ?>" class="btn btn-small">← Retour</a>
</div>

<!-- ══ SECTION PHOTO (membres uniquement) ══ -->
<?php if ($_SESSION['user_role'] === 'membre'): ?>
<div class="card">
    <h3>🖼️ Photo de profil</h3>

    <!-- Messages photo -->
    <?php if (!empty($erreurPhoto)): ?>
        <p class="erreur"><?= htmlspecialchars($erreurPhoto) ?></p>
    <?php endif; ?>
    <?php if (!empty($succesPhoto)): ?>
        <p class="succes"><?= htmlspecialchars($succesPhoto) ?></p>
    <?php endif; ?>

    <div class="photo-profil-container">

        <!-- Aperçu de la photo actuelle -->
        <div class="photo-apercu">
            <?php 
                $photoUrl = null;
                if (!empty($user['photo'])) {
                    $photoUrl = 'public/uploads/photos/' . htmlspecialchars($user['photo']);
                } elseif (!empty($_SESSION['user_photo']) && str_starts_with($_SESSION['user_photo'], 'http')) {
                    $photoUrl = htmlspecialchars($_SESSION['user_photo']);
                }
                ?>

                <?php if ($photoUrl): ?>
                    <!-- Photo existante -->
                    <img src="<?= $photoUrl ?>"
                    alt="Photo de profil"
                    id="apercu-photo"
                    class="photo-img">
                <!-- Bouton supprimer -->
                <a href="index.php?page=profil&action=supprimer_photo"
                    class="btn btn-small btn-danger photo-supprimer"
                    onclick="return confirm('Supprimer votre photo ?')">
                    🗑️ Supprimer
                </a>
            <?php else: ?>
                <!-- Avatar par défaut (initiales) -->
                <div class="photo-avatar" id="apercu-photo">
                    <?= strtoupper(substr($_SESSION['user_nom'], 0, 2)) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Formulaire upload -->
        <div class="photo-upload-zone">
            <form method="POST" action="index.php?page=profil"
                enctype="multipart/form-data"> <!-- enctype obligatoire pour les fichiers -->
                <input type="hidden" name="action" value="modifier_photo">

                <!-- Zone de drop visuelle -->
                <label for="photo-input" class="photo-drop-zone" id="photo-drop-zone">
                    <span class="photo-drop-icon">📸</span>
                    <span class="photo-drop-texte">
                        Cliquez ou glissez une image ici
                    </span>
                    <span class="photo-drop-hint">
                        JPG, PNG, GIF, WEBP — Max 2 Mo
                    </span>
                    <!-- Input caché stylisé par le label -->
                    <input type="file" name="photo" id="photo-input"
                        accept="image/jpeg,image/png,image/gif,image/webp"
                        style="display:none;">
                </label>

                <!-- Nom du fichier sélectionné -->
                <p id="photo-nom-fichier"
                   style="font-size:0.85rem; color:var(--text-muted);
                          margin:10px 0; text-align:center;">
                    Aucun fichier sélectionné
                </p>

                <button type="submit" class="btn" style="width:100%;">
                    💾 Enregistrer la photo
                </button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ══ SECTION INFOS ══ -->
<div class="card">
    <h3>📋 Informations du compte</h3>

    <div class="profil-role">
        <?php
        $badgeClass = match($_SESSION['user_role']) {
            'administrateur' => 'badge-role-admin',
            'employe'        => 'badge-role-emp',
            'membre'         => 'badge-role-mem',
            default          => '',
        };
        $labelRole = match($_SESSION['user_role']) {
            'administrateur' => '🔴 Administrateur',
            'employe'        => '🔵 Employé',
            'membre'         => '🟢 Membre',
            default          => $_SESSION['user_role'],
        };
        ?>
        <span class="badge <?= $badgeClass ?>"><?= $labelRole ?></span>
    </div>

    <?php if (!empty($erreurInfos)): ?>
        <p class="erreur"><?= htmlspecialchars($erreurInfos) ?></p>
    <?php endif; ?>
    <?php if (!empty($succesInfos)): ?>
        <p class="succes"><?= htmlspecialchars($succesInfos) ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?page=profil">
        <input type="hidden" name="action" value="modifier_infos">
        <div class="form-row">
            <div class="form-group">
                <label>Nom complet *</label>
                <input type="text" name="nom" required
                       value="<?= htmlspecialchars($user['nom']) ?>">
            </div>
            <div class="form-group">
                <label>Adresse email *</label>
                <input type="email" name="email" required
                       value="<?= htmlspecialchars($user['email']) ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Login <span class="text-muted">(non modifiable)</span></label>
            <input type="text" value="<?= htmlspecialchars($user['login']) ?>"
                   disabled style="opacity:0.6; cursor:not-allowed;">
        </div>
        <button type="submit" class="btn">💾 Sauvegarder</button>
    </form>
</div>

<!-- ══ SECTION MOT DE PASSE ══ -->
<div class="card">
    <h3>🔒 Changer le mot de passe</h3>

    <?php if (!empty($erreurMdp)): ?>
        <p class="erreur"><?= htmlspecialchars($erreurMdp) ?></p>
    <?php endif; ?>
    <?php if (!empty($succesMdp)): ?>
        <p class="succes"><?= htmlspecialchars($succesMdp) ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?page=profil">
        <input type="hidden" name="action" value="modifier_mdp">
        <div class="form-group">
            <label>Ancien mot de passe *</label>
            <div class="input-password">
                <input type="password" name="ancien_mdp" id="ancien_mdp"
                       required placeholder="Votre mot de passe actuel">
                <button type="button" class="btn-eye"
                        onclick="togglePassword('ancien_mdp')">👁️</button>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Nouveau mot de passe *</label>
                <div class="input-password">
                    <input type="password" name="nouveau_mdp" id="nouveau_mdp"
                           required placeholder="Min. 6 caractères">
                    <button type="button" class="btn-eye"
                            onclick="togglePassword('nouveau_mdp')">👁️</button>
                </div>
            </div>
            <div class="form-group">
                <label>Confirmer *</label>
                <div class="input-password">
                    <input type="password" name="confirmer_mdp" id="confirmer_mdp"
                           required placeholder="Répétez le mot de passe">
                    <button type="button" class="btn-eye"
                            onclick="togglePassword('confirmer_mdp')">👁️</button>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="mdp-force" id="mdp-force" style="display:none;">
                <span>Force : </span>
                <div class="mdp-force-barre">
                    <div class="mdp-force-niveau" id="mdp-force-niveau"></div>
                </div>
                <span id="mdp-force-label"></span>
            </div>
        </div>
        <button type="submit" class="btn">🔒 Changer le mot de passe</button>
    </form>
</div>

<script>
/* ── Afficher/masquer mot de passe ── */
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type  = (input.type === 'password') ? 'text' : 'password';
}

/* ── Indicateur force mot de passe ── */
document.getElementById('nouveau_mdp')
    ?.addEventListener('input', function () {
        const mdp       = this.value;
        const barre     = document.getElementById('mdp-force-niveau');
        const label     = document.getElementById('mdp-force-label');
        const conteneur = document.getElementById('mdp-force');

        if (mdp.length === 0) { conteneur.style.display = 'none'; return; }
        conteneur.style.display = 'flex';

        let score = 0;
        if (mdp.length >= 6)           score++;
        if (mdp.length >= 10)          score++;
        if (/[A-Z]/.test(mdp))         score++;
        if (/[0-9]/.test(mdp))         score++;
        if (/[^A-Za-z0-9]/.test(mdp))  score++;

        const niveaux = [
            { pct: '20%',  color: '#e74c3c', texte: 'Très faible' },
            { pct: '40%',  color: '#e67e22', texte: 'Faible'      },
            { pct: '60%',  color: '#f1c40f', texte: 'Moyen'       },
            { pct: '80%',  color: '#2ecc71', texte: 'Fort'        },
            { pct: '100%', color: '#27ae60', texte: 'Très fort'   },
        ];
        const niveau           = niveaux[Math.min(score, 4)];
        barre.style.width      = niveau.pct;
        barre.style.background = niveau.color;
        label.textContent      = niveau.texte;
        label.style.color      = niveau.color;
    });

/* ── Aperçu photo avant upload ──
   Affiche l'image choisie avant
   de soumettre le formulaire
*/
document.getElementById('photo-input')
    ?.addEventListener('change', function () {
        const fichier = this.files[0];
        if (!fichier) return;

        // Affiche le nom du fichier
        document.getElementById('photo-nom-fichier').textContent =
            '📎 ' + fichier.name + ' (' + (fichier.size / 1024).toFixed(1) + ' Ko)';

        // Aperçu de l'image via FileReader
        const reader  = new FileReader();
        reader.onload = function (e) {
            const apercu = document.getElementById('apercu-photo');
            // Remplace l'avatar ou l'ancienne photo par l'aperçu
            apercu.outerHTML =
                `<img src="${e.target.result}"
                      id="apercu-photo"
                      class="photo-img"
                      alt="Aperçu">`;
        };
        reader.readAsDataURL(fichier); // Lit le fichier comme Data URL
    });

/* ── Drag & Drop sur la zone ── */
const dropZone = document.getElementById('photo-drop-zone');
if (dropZone) {

    // Survol → change l'apparence
    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault(); // Nécessaire pour autoriser le drop
        dropZone.classList.add('photo-drop-active');
    });

    // Quitte la zone → remet l'apparence normale
    dropZone.addEventListener('dragleave', function () {
        dropZone.classList.remove('photo-drop-active');
    });

    // Drop → déclenche la sélection du fichier
    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropZone.classList.remove('photo-drop-active');

        // Récupère le fichier déposé
        const fichier = e.dataTransfer.files[0];
        if (fichier && fichier.type.startsWith('image/')) {
            // Injecte le fichier dans l'input
            const input      = document.getElementById('photo-input');
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(fichier);
            input.files = dataTransfer.files;

            // Déclenche l'événement change pour l'aperçu
            input.dispatchEvent(new Event('change'));
        }
    });
}
</script>

<?php require 'views/layout/footer.php'; ?>