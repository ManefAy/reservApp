/* ══════════════════════════════════════
   INITIALISATION AU CHARGEMENT
   On applique le thème AVANT que la page
   s'affiche pour éviter un flash blanc
══════════════════════════════════════ */

// Récupère le thème sauvegardé dans localStorage
// Si aucun thème sauvegardé → "light" par défaut
const themeSauvegarde = localStorage.getItem('reservapp_theme') || 'light';

// Applique immédiatement le thème sur <html>
// IMPORTANT : ce code s'exécute avant le DOMContentLoaded
// pour éviter le "flash" de thème au chargement
document.documentElement.setAttribute('data-theme', themeSauvegarde);

/* ══════════════════════════════════════
   APRÈS CHARGEMENT COMPLET DU DOM
══════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function () {

    /* ── TOGGLE THÈME ──────────────────
       Gère le bouton 🌙 / ☀️ dans la navbar
    ────────────────────────────────── */

    // Sélectionne le bouton toggle
    const toggleBtn = document.getElementById('theme-toggle');

    // Écoute le clic sur le bouton toggle
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            const themeActuel  = document.documentElement.getAttribute('data-theme') || 'light';
            const nouveauTheme = (themeActuel === 'light') ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', nouveauTheme);
            localStorage.setItem('reservapp_theme', nouveauTheme);
        });
    }

    /* ── MESSAGES FLASH ────────────────
       Les messages .erreur et .succes
       disparaissent automatiquement
       après 4 secondes
    ────────────────────────────────── */

    // Sélectionne tous les messages flash
    const messages = document.querySelectorAll('.erreur, .succes');

    messages.forEach(function (msg) {

        // Après 4 secondes → réduit l'opacité
        setTimeout(function () {
            msg.style.transition = 'opacity 0.8s ease';
            msg.style.opacity    = '0';

            // Après la transition (0.8s) → supprime l'élément
            setTimeout(function () {
                msg.remove();
            }, 800);

        }, 4000); // 4000ms = 4 secondes
    });

    /* ── AJAX CRÉNEAUX ────────────────────────────────────────────
       Sur les pages de réservation, recharge la grille des créneaux
       automatiquement quand la salle ou la date change.
       Endpoint : index.php?page=api_creneaux&salle_id=X&date=Y
    ────────────────────────────────────────────────────────────── */

    const salleSelect  = document.querySelector('select[name="salle_id"]');
    const dateInput    = document.querySelector('input[name="date"]');
    const creneauxGrid = document.querySelector('.creneaux-grid');

    if (salleSelect && dateInput && creneauxGrid) {

        function htmlEscape(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function rendreCreneaux(creneaux) {
            if (!creneaux.length) {
                creneauxGrid.innerHTML = '<p style="padding:10px;color:var(--text-muted);">Aucun créneau trouvé.</p>';
                return;
            }
            creneauxGrid.innerHTML = creneaux.map(function (c) {
                return '<label class="creneau-item ' + (c.pris ? 'creneau-pris' : '') + '">'
                    + '<input type="radio" name="creneau_id" value="' + c.id + '" '
                    + (c.pris ? 'disabled' : 'required') + '>'
                    + htmlEscape(c.libelle)
                    + (c.pris ? '<span class="pris-label">Pris</span>' : '')
                    + '</label>';
            }).join('');
        }

        function chargerCreneaux() {
            var salleId = salleSelect.value;
            var date    = dateInput.value;
            if (!salleId || !date) return;

            creneauxGrid.innerHTML = '<p style="padding:10px;color:var(--text-muted);">⏳ Chargement...</p>';

            fetch('index.php?page=api_creneaux&salle_id=' + encodeURIComponent(salleId) + '&date=' + encodeURIComponent(date))
                .then(function (r) { return r.json(); })
                .then(rendreCreneaux)
                .catch(function () {
                    creneauxGrid.innerHTML = '<p class="erreur">Erreur lors du chargement des créneaux.</p>';
                });
        }

        salleSelect.addEventListener('change', chargerCreneaux);
        dateInput.addEventListener('change', chargerCreneaux);

        // Déclenche si les deux champs sont déjà remplis (retour après POST)
        if (salleSelect.value && dateInput.value) {
            chargerCreneaux();
        }
    }

    /* ── CONFIRMATION SUPPRESSION ──────
       Remplace le confirm() natif du
       navigateur par une meilleure UX
       (optionnel — fonctionne déjà avec
       onclick="return confirm(...)")
    ────────────────────────────────── */

    // Tous les liens de suppression
    const liensSuppression = document.querySelectorAll('.btn-danger[href*="supprimer"]');

    liensSuppression.forEach(function (lien) {
        lien.addEventListener('click', function (e) {
            // Annule le comportement par défaut
            e.preventDefault();

            // Demande confirmation
            if (confirm('Êtes-vous sûr de vouloir supprimer ?')) {
                // Confirme → redirige vers le lien
                window.location.href = lien.href;
            }
            // Annule → ne fait rien
        });
    });

});