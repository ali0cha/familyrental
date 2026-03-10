<?php
session_start();
require 'config.php';
require 'mailer.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    die("Accès non autorisé.");
}

// Action : Valider un utilisateur
if (isset($_GET['validate_user'])) {
    $userId = (int) $_GET['validate_user'];
    $stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
    $stmt->execute([$userId]);

    // Récupérer l'email et le pseudo de l'utilisateur pour lui envoyer un email
    $user = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
    $user->execute([$userId]);
    $userData = $user->fetch();
    if ($userData && !empty($userData['email'])) {
        mailCompteValide($userData['username'], $userData['email']);
    }

    header("Location: admin.php");
    exit();
}

// Action : Gérer une réservation (approuver / refuser)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id     = (int) $_GET['id'];
    $action = $_GET['action'];
    $status = ($action === 'approuver') ? 'approuvée' : 'refusée';

    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    // Récupérer les infos de la réservation + l'email du membre concerné
    $stmt = $pdo->prepare("
        SELECT b.start_date, b.end_date, u.username, u.email
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        WHERE b.id = ?
    ");
    $stmt->execute([$id]);
    $booking = $stmt->fetch();

    if ($booking && !empty($booking['email'])) {
        if ($action === 'approuver') {
            mailReservationApprouvee($booking['username'], $booking['email'], $booking['start_date'], $booking['end_date']);
        } else {
            mailReservationRefusee($booking['username'], $booking['email'], $booking['start_date'], $booking['end_date']);
        }
    }

    header("Location: admin.php");
    exit();
}

$pending_users = $pdo->query("SELECT * FROM users WHERE is_active = 0")->fetchAll();
$bookings = $pdo->query("
    SELECT b.*, u.username
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    ORDER BY b.start_date DESC
")->fetchAll();

include 'header.php';
?>
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm" style="border-color: #f7b971 !important;">
            <div class="card-header bg-warning text-dark fw-bold">Nouveaux membres en attente</div>
            <div class="card-body">
                <?php if (empty($pending_users)): ?>
                    <p class="mb-0 text-muted">Aucun compte à valider pour le moment.</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($pending_users as $u): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($u['username']) ?>
                                <span class="text-muted small me-auto ms-3"><?= htmlspecialchars($u['email'] ?? '') ?></span>
                                <a href="?validate_user=<?= $u['id'] ?>" class="btn btn-sm btn-success">✅ Autoriser l'accès</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white fw-bold">Gestion des séjours</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Membre</th>
                            <th>Dates demandées</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($b['username']) ?></strong></td>
                            <td>Du <?= date('d/m/Y', strtotime($b['start_date'])) ?> au <?= date('d/m/Y', strtotime($b['end_date'])) ?></td>
                            <td>
                                <?php
                                $badge = ($b['status'] == 'approuvée') ? 'success' : (($b['status'] == 'en attente') ? 'warning text-dark' : 'danger');
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= ucfirst($b['status']) ?></span>
                            </td>
                            <td>
                                <?php if ($b['status'] == 'en attente'): ?>
                                    <a href="?action=approuver&id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-success">Approuver</a>
                                    <a href="?action=refuser&id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger">Refuser</a>
                                <?php else: ?>
                                    <span class="text-muted small">Traité</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
