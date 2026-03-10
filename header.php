<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?> - Réservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ── Palette ── */
        :root {
            --cognac:  #b07d5a;
            --cognac-dk: #8f5f3d;
            --sage:    #d1ce9c;
            --muted:   #94a88e;
            --dark:    #212e36;
            --cream:   #f8eabb;
            --coral:   #fb6c47;
            --gold:    #f7b971;
            --white:   #ffffff;

            /* Bootstrap overrides */
            --bs-primary:        #b07d5a;
            --bs-primary-rgb:    176,125,90;
            --bs-success:        #94a88e;
            --bs-success-rgb:    148,168,142;
            --bs-warning:        #f7b971;
            --bs-warning-rgb:    247,185,113;
            --bs-danger:         #fb6c47;
            --bs-danger-rgb:     251,108,71;
            --bs-dark:           #212e36;
            --bs-dark-rgb:       33,46,54;
            --bs-body-bg:        #f5f3ea;
            --bs-link-color:     #b07d5a;
            --bs-link-hover-color: #8f5f3d;
        }

        body {
            background-color: #f5f3ea;
            background-image: radial-gradient(circle at 80% 10%, #d1ce9c33 0%, transparent 50%),
                              radial-gradient(circle at 10% 90%, #b07d5a18 0%, transparent 45%);
        }

        /* ── Cards ── */
        .card {
            border-radius: 14px;
            border: 1px solid #d1ce9c88;
        }

        /* ── Navbar ── */
        .navbar.bg-primary {
            background-color: var(--dark) !important;
            border-bottom: 3px solid var(--cognac);
        }
        .navbar-brand {
            color: var(--cognac) !important;
            letter-spacing: .5px;
        }
        .navbar-brand:hover { color: var(--gold) !important; }
        .nav-link { color: #d1ce9ccc !important; transition: color .2s; }
        .nav-link:hover { color: var(--cognac) !important; }
        .nav-link.text-warning { color: var(--gold) !important; }

        /* ── Buttons ── */
        .btn-primary {
            background-color: var(--cognac);
            border-color: var(--cognac);
            color: #fff;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--cognac-dk);
            border-color: var(--cognac-dk);
            color: #fff;
        }
        .btn-outline-primary {
            color: var(--cognac);
            border-color: var(--cognac);
        }
        .btn-outline-primary:hover {
            background-color: var(--cognac);
            border-color: var(--cognac);
            color: #fff;
        }
        .btn-success {
            background-color: var(--muted);
            border-color: var(--muted);
            color: #fff;
        }
        .btn-success:hover {
            background-color: #7a9275;
            border-color: #7a9275;
            color: #fff;
        }
        .btn-outline-success {
            color: var(--muted);
            border-color: var(--muted);
        }
        .btn-outline-success:hover {
            background-color: var(--muted);
            border-color: var(--muted);
            color: #fff;
        }
        .btn-outline-danger {
            color: var(--coral);
            border-color: var(--coral);
        }
        .btn-outline-danger:hover {
            background-color: var(--coral);
            border-color: var(--coral);
            color: #fff;
        }
        .btn-outline-light {
            color: var(--sage);
            border-color: var(--sage);
        }
        .btn-outline-light:hover {
            background-color: var(--sage);
            border-color: var(--sage);
            color: var(--dark);
        }
        .btn-outline-secondary {
            color: var(--muted);
            border-color: var(--muted);
        }
        .btn-outline-secondary:hover {
            background-color: var(--muted);
            color: #fff;
        }

        /* ── Badges ── */
        .badge.bg-success  { background-color: var(--muted)  !important; }
        .badge.bg-danger   { background-color: var(--coral)  !important; }
        .badge.bg-warning  { background-color: var(--gold)   !important; color: var(--dark) !important; }
        .badge.bg-primary  { background-color: var(--cognac)   !important; }

        /* ── Card headers ── */
        .card-header.bg-warning {
            background-color: var(--gold) !important;
            color: var(--dark) !important;
        }
        .card-header.bg-dark {
            background-color: var(--dark) !important;
            border-bottom: 2px solid var(--cognac);
        }
        .card-header.bg-primary {
            background-color: var(--cognac) !important;
        }

        /* ── Tables ── */
        .table-light th { background-color: #ece9d5 !important; color: var(--dark); }
        .table-hover tbody tr:hover { background-color: #f0eddc; }

        /* ── Form controls ── */
        .form-control:focus {
            border-color: var(--cognac);
            box-shadow: 0 0 0 .2rem #b07d5a30;
        }

        /* ── Alerts ── */
        .alert-success {
            background-color: #e8f0e6;
            border-color: var(--muted);
            color: #3a5e35;
        }
        .alert-danger {
            background-color: #fdeee9;
            border-color: var(--coral);
            color: #9c3018;
        }
        .alert-info {
            background-color: #e0f4f5;
            border-color: var(--cognac);
            color: #1a6b70;
        }

        /* ── Footer ── */
        footer.bg-white {
            background-color: var(--dark) !important;
            border-top: none !important;
        }
        footer .text-muted, footer .text-secondary {
            color: var(--sage) !important;
        }

        /* ── List groups ── */
        .list-group-item {
            border-color: #d1ce9c88;
        }
    </style>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#b07d5a">
    <link rel="apple-touch-icon" href="/icon.png">
    <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars(APP_NAME) ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="planning.php">🏖️ <?= htmlspecialchars(APP_NAME) ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto align-items-center">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a class="nav-link" href="planning.php">Planning</a>
                    <a class="nav-link" href="reserver.php">Réserver</a>
                    <?php if($_SESSION['role'] === 'admin'): ?>
                        <a class="nav-link text-warning fw-bold" href="admin.php">Administration</a>
                    <?php endif; ?>
                    <span class="navbar-text ms-3 me-2 text-light small">
                        👤 <?= htmlspecialchars($_SESSION['username']) ?>
                    </span>
                    <a class="btn btn-sm btn-outline-light" href="logout.php">Déconnexion</a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">Connexion</a>
                    <a class="nav-link" href="register.php">Inscription</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<div class="container flex-grow-1">
