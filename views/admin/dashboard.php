<?php require 'views/layout/header.php'; ?>

<div class="page-header">
    <h2>📊 Tableau de bord</h2>
    <span>Bienvenue, <?= htmlspecialchars($_SESSION['user_nom']) ?></span>
</div>

<!-- Cartes statistiques -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">🏢</div>
        <div class="stat-info">
            <h3><?= $nb_salles ?></h3>
            <p>Salles</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-info">
            <h3><?= $nb_reservations ?></h3>
            <p>Réservations actives</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <h3><?= $nb_employes ?></h3>
            <p>Employés</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🕐</div>
        <div class="stat-info">
            <h3><?= $nb_aujourdhui ?></h3>
            <p>Réservations aujourd'hui</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⭐</div>
        <div class="stat-info">
            <h3><?= $moyenne_avis ?> / 5</h3>
            <p><?= $nb_avis ?> Avis</p>
        </div>
    </div>
</div>

<!-- Menu rapide -->
<div class="quick-menu">
    <h3>Accès rapide</h3>
    <div class="quick-links">
        <a href="index.php?page=admin_salles" class="btn">🏢 Gérer les salles</a>
        <a href="index.php?page=admin_employes" class="btn">👥 Gérer les employés</a>
        <a href="index.php?page=admin_reservations" class="btn">📅 Gérer les réservations</a>
    </div>
</div>

<!-- Graphique des réservations (7 derniers jours) -->
<div class="card">
    <h3 style="margin-bottom:20px;">📈 Réservations des 7 derniers jours</h3>
    <canvas id="chartResa" height="90"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    var labels = <?= json_encode($chartLabels) ?>;
    var data   = <?= json_encode($chartData) ?>;

    var ctx = document.getElementById('chartResa');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Réservations confirmées',
                data: data,
                backgroundColor: 'rgba(44,62,80,0.6)',
                borderColor: '#2c3e50',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            return ' ' + ctx.parsed.y + ' réservation(s)';
                        }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
})();
</script>

<?php require 'views/layout/footer.php'; ?>