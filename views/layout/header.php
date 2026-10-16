<?php
$pageActuelle = $_GET['page'] ?? 'login';
$labelsRole = [
    'administrateur' => '🔴 Admin',
    'employe'        => '🔵 Employé',
    'membre'         => '🟢 Membre',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Réservations</title>
    <link rel="icon" href="public/img/logo.png" type="image/x-icon" />
    <script>document.documentElement.setAttribute('data-theme', localStorage.getItem('reservapp_theme') || 'light');</script>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<?php if (isset($_SESSION['user_id']) && !in_array($pageActuelle, ['login', 'register'])): ?>
<nav class="navbar">
    <span class="brand">📅 ReservApp</span>

    <div class="nav-links">

        <?php if ($_SESSION['user_role'] === 'administrateur'): ?>
            <a href="index.php?page=admin_dashboard"
                class="nav-link <?= $pageActuelle === 'admin_dashboard' ? 'active' : '' ?>">
                📊 Dashboard
            </a>
            <a href="index.php?page=admin_salles"
                class="nav-link <?= $pageActuelle === 'admin_salles' ? 'active' : '' ?>">
                🏢 Salles
            </a>
            <a href="index.php?page=admin_employes"
                class="nav-link <?= $pageActuelle === 'admin_employes' ? 'active' : '' ?>">
                👥 Employés
            </a>
            <a href="index.php?page=admin_reservations"
                class="nav-link <?= $pageActuelle === 'admin_reservations' ? 'active' : '' ?>">
                📅 Réservations
            </a>
            <a href="index.php?page=calendrier"
                class="nav-link <?= $pageActuelle === 'calendrier' ? 'active' : '' ?>">
                🗓️ Calendrier
            </a>
            <a href="index.php?page=avis"
                class="nav-link <?= $pageActuelle === 'avis' ? 'active' : '' ?>">
                ⭐ Avis
            </a>

        <?php elseif ($_SESSION['user_role'] === 'employe'): ?>
            <a href="index.php?page=employe_reservations"
                class="nav-link <?= $pageActuelle === 'employe_reservations' ? 'active' : '' ?>">
                📅 Mes réservations
            </a>
            <a href="index.php?page=employe_reserver"
                class="nav-link <?= $pageActuelle === 'employe_reserver' ? 'active' : '' ?>">
                ➕ Nouvelle
            </a>
            <a href="index.php?page=calendrier"
                class="nav-link <?= $pageActuelle === 'calendrier' ? 'active' : '' ?>">
                🗓️ Calendrier
            </a>
            <a href="index.php?page=avis"
                class="nav-link <?= $pageActuelle === 'avis' ? 'active' : '' ?>">
                ⭐ Avis
            </a>

        <?php elseif ($_SESSION['user_role'] === 'membre'): ?>
            <a href="index.php?page=membre_salles"
                class="nav-link <?= $pageActuelle === 'membre_salles' ? 'active' : '' ?>">
                🏢 Salles
            </a>
            <a href="index.php?page=membre_reservations"
                class="nav-link <?= $pageActuelle === 'membre_reservations' ? 'active' : '' ?>">
                📅 Mes réservations
            </a>
            <a href="index.php?page=calendrier"
                class="nav-link <?= $pageActuelle === 'calendrier' ? 'active' : '' ?>">
                🗓️ Calendrier
            </a>
            <a href="index.php?page=avis"
                class="nav-link <?= $pageActuelle === 'avis' ? 'active' : '' ?>">
                ⭐ Avis
            </a>
        <?php endif; ?>

        <!-- ── Bouton toggle mode sombre/clair ── -->
        <button id="theme-toggle" class="btn-theme" title="Changer le thème">
            <span class="theme-icon-moon">🌙</span>
            <span class="theme-icon-sun">☀️</span>
        </button>

        <!-- Infos utilisateur + déconnexion -->
        <div class="nav-user">
            <div class="nav-user-info">
                <a href="index.php?page=profil" style="text-decoration:none;
                display:flex; align-items:center; gap:8px;">

        <!-- Photo ou avatar selon le rôle -->
        <?php if ($_SESSION['user_role'] === 'membre' && !empty($_SESSION['user_photo'])): ?>
            <?php if (str_starts_with($_SESSION['user_photo'], 'http')): ?>
                <!-- Google photo -->
                <img src="<?= htmlspecialchars($_SESSION['user_photo']) ?>"
                    alt="Photo"
                    style="width:32px; height:32px; border-radius:50%;
                            object-fit:cover; border:2px solid rgba(255,255,255,0.5);">
            <?php else: ?>
                <!-- Local photo -->
                <img src="public/uploads/photos/<?= htmlspecialchars($_SESSION['user_photo']) ?>"
                    alt="Photo"
                    style="width:32px; height:32px; border-radius:50%;
                            object-fit:cover; border:2px solid rgba(255,255,255,0.5);">
            <?php endif; ?>
        <?php else: ?>
            <!-- Avatar initiales -->
            <div style="width:32px; height:32px; border-radius:50%;
                        background:rgba(255,255,255,0.2);
                        display:flex; align-items:center;
                        justify-content:center; font-size:0.85rem;
                        font-weight:bold; color:white;">
                <?= strtoupper(substr($_SESSION['user_nom'], 0, 2)) ?>
            </div>
        <?php endif; ?>

                    <span class="nav-user-nom">
                        <?= htmlspecialchars($_SESSION['user_nom']) ?>
                    </span>
                </a>
                <span class="nav-user-role">
                    <?= $labelsRole[$_SESSION['user_role']] ?? '' ?>
                </span>
            </div>
            <a href="index.php?page=logout" class="btn btn-small btn-danger">
                Déconnexion
            </a>
        </div>

    </div>
</nav>
<?php endif; ?>

<main class="container">