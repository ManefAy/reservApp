<?php require 'views/layout/header.php'; ?>
<style>
    .container { max-width: 1400px; }
</style>

<div class="page-header">
    <h2>📅 Calendrier des réservations</h2>
    <!-- Bouton retour selon le rôle -->
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

<!-- Légende des couleurs -->
<div class="card" style="padding:15px 25px;">
    <div class="calendrier-legende">
        <span class="legende-item">
            <span class="legende-couleur" style="background:#27ae60;"></span>
            Confirmée
        </span>
        <span class="legende-item">
            <span class="legende-couleur" style="background:#e74c3c;"></span>
            Annulée
        </span>
        <?php if ($_SESSION['user_role'] === 'administrateur'): ?>
        <span class="legende-item" style="color:var(--text-muted); font-size:0.85rem;">
            👁️ Vous voyez toutes les réservations
        </span>
        <?php else: ?>
        <span class="legende-item" style="color:var(--text-muted); font-size:0.85rem;">
            👁️ Vous voyez uniquement vos réservations
        </span>
        <?php endif; ?>
    </div>
</div>

<!-- Conteneur du calendrier -->
<div class="card">
    <div id="calendrier"></div>
</div>

<!-- Modal détails réservation -->
<div id="modal-overlay" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>📋 Détails de la réservation</h3>
            <button onclick="fermerModal()" class="modal-fermer">✖</button>
        </div>
        <div class="modal-body" id="modal-contenu">
            <!-- Rempli dynamiquement par JavaScript -->
        </div>
        <div class="modal-footer">
            <button onclick="fermerModal()" class="btn btn-small">Fermer</button>
        </div>
    </div>
</div>

<!-- FullCalendar CSS + JS (CDN — pas d'installation) -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css"
      rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js">
</script>

<!-- Localisation française de FullCalendar -->
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales/fr.global.min.js">
</script>

<script>
/* ══════════════════════════════════════
   INITIALISATION DU CALENDRIER
   
   On utilise FullCalendar v6 avec :
   - Vue mois par défaut
   - Vue semaine disponible
   - Vue jour disponible
   - Clic sur événement → modal détails
   - Données PHP → JSON → FullCalendar
══════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', function () {

    // Récupère l'élément HTML du calendrier
    const el = document.getElementById('calendrier');

    /* ── Données des réservations ──
       Passées depuis PHP via json_encode()
    */
    const evenements = <?= $evenementsJson ?>;

    /* ── Initialise FullCalendar ── */
    const calendrier = new FullCalendar.Calendar(el, {

        // Langue française
        locale: 'fr',

        // Vue par défaut : mois
        initialView: 'dayGridMonth',

        // Barre d'outils : navigation + vues
        headerToolbar: {
            left  : 'prev,next today',    // Boutons navigation
            center: 'title',              // Titre (mois/semaine)
            right : 'dayGridMonth,timeGridWeek,timeGridDay', // Vues
        },

        // Labels des boutons en français
        buttonText: {
            today : "Aujourd'hui",
            month : 'Mois',
            week  : 'Semaine',
            day   : 'Jour',
        },

        // Hauteur automatique selon le contenu
        height: 'auto',

        // Affiche les numéros de semaine
        weekNumbers: true,

        // Permet de cliquer sur "voir plus" si trop d'événements
        dayMaxEvents: 3,

        // Les événements PHP
        events: evenements,

        /* ── Clic sur un événement ──
           Ouvre le modal avec les détails
        */
        eventClick: function (info) {
            ouvrirModal(info.event);
        },

        /* ── Survol d'un événement ──
           Change le curseur en pointer
        */
        eventMouseEnter: function (info) {
            info.el.style.cursor = 'pointer';
        },

        /* ── Style des événements ── */
        eventDisplay: 'block',
    });

    // Affiche le calendrier
    calendrier.render();
});

/* ══════════════════════════════════════
   MODAL DÉTAILS
   Affiche les détails d'une réservation
   quand on clique dessus
══════════════════════════════════════ */

/**
 * Ouvre le modal avec les détails
 * de la réservation cliquée
 */
function ouvrirModal(event) {
    const props  = event.extendedProps;

    /* ── Formate les dates/heures ── */
    const debut  = event.start
        ? event.start.toLocaleString('fr-FR', {
            weekday: 'long',
            day    : '2-digit',
            month  : 'long',
            year   : 'numeric',
            hour   : '2-digit',
            minute : '2-digit',
          })
        : '—';

    const fin = event.end
        ? event.end.toLocaleString('fr-FR', {
            hour  : '2-digit',
            minute: '2-digit',
          })
        : '—';

    /* ── Badge statut ── */
    const badgeStatut = props.statut === 'confirmee'
        ? '<span style="background:#d1e7dd; color:#0f5132; padding:3px 10px; border-radius:12px; font-size:0.85rem; font-weight:bold;">✅ Confirmée</span>'
        : '<span style="background:#f8d7da; color:#842029; padding:3px 10px; border-radius:12px; font-size:0.85rem; font-weight:bold;">❌ Annulée</span>';

    /* ── Contenu HTML du modal ── */
    document.getElementById('modal-contenu').innerHTML = `
        <div class="modal-info-ligne">
            <span class="modal-info-label">🏢 Salle</span>
            <span>${props.salle}</span>
        </div>
        <div class="modal-info-ligne">
            <span class="modal-info-label">👤 Réservé par</span>
            <span>${props.user}</span>
        </div>
        <div class="modal-info-ligne">
            <span class="modal-info-label">📅 Début</span>
            <span>${debut}</span>
        </div>
        <div class="modal-info-ligne">
            <span class="modal-info-label">🕐 Fin</span>
            <span>${fin}</span>
        </div>
        <div class="modal-info-ligne">
            <span class="modal-info-label">✅ Statut</span>
            <span>${badgeStatut}</span>
        </div>
    `;

    // Affiche le modal
    document.getElementById('modal-overlay').style.display = 'flex';
}

/**
 * Ferme le modal
 */
function fermerModal() {
    document.getElementById('modal-overlay').style.display = 'none';
}

/* ── Ferme le modal en cliquant en dehors ── */
document.getElementById('modal-overlay')
    ?.addEventListener('click', function (e) {
        if (e.target === this) fermerModal();
    });
</script>

<?php require 'views/layout/footer.php'; ?>