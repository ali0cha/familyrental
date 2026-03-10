<?php
session_start();
require 'config.php';
require 'mailer.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ── Action : Annuler une réservation ──────────────────────────────────────────
if (isset($_GET['cancel'])) {
    $cancel_id = (int) $_GET['cancel'];

    // Vérifier que la réservation appartient bien à l'utilisateur connecté
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$cancel_id, $user_id]);
    $booking = $stmt->fetch();

    if ($booking) {
        $pdo->prepare("DELETE FROM bookings WHERE id = ?")->execute([$cancel_id]);
        mailReservationAnnuleeAdmin(
            $_SESSION['username'],
            $booking['start_date'],
            $booking['end_date']
        );
        $cancel_success = "Votre réservation a bien été annulée.";
    } else {
        $cancel_error = "Réservation introuvable ou non autorisée.";
    }
}

// ── Récupérer toutes les réservations à venir (hors refusées) ─────────────────
$stmt = $pdo->query("
    SELECT b.start_date, b.end_date, b.status, u.username
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    WHERE b.status != 'refusée' AND b.end_date >= CURDATE()
    ORDER BY b.start_date ASC
");
$occupations = $stmt->fetchAll();

// ── Récupérer les réservations de l'utilisateur connecté ──────────────────────
$stmt = $pdo->prepare("
    SELECT * FROM bookings
    WHERE user_id = ? AND end_date >= CURDATE()
    ORDER BY start_date ASC
");
$stmt->execute([$user_id]);
$mes_reservations = $stmt->fetchAll();

include 'header.php';
?>

<?php if (isset($cancel_success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($cancel_success) ?></div>
<?php endif; ?>
<?php if (isset($cancel_error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($cancel_error) ?></div>
<?php endif; ?>

<div class="row">

    <!-- Planning global -->
    <div class="col-md-8 mx-auto mb-4">
        <div class="card shadow-sm p-4">
            <h2 class="text-center mb-4">📅 Planning des Occupations</h2>

            <?php if (empty($occupations)): ?>
                <div class="alert alert-info text-center">Aucune réservation à venir. L'appartement est libre !</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Membre</th>
                                <th>Arrivée</th>
                                <th>Départ</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($occupations as $occ): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($occ['username']) ?></strong></td>
                                <td><?= date('d/m/Y', strtotime($occ['start_date'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($occ['end_date'])) ?></td>
                                <td>
                                    <?php if ($occ['status'] == 'approuvée'): ?>
                                        <span class="badge bg-success">Confirmé</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">En attente</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="reserver.php" class="btn btn-success">Faire une demande de séjour</a>
            </div>
        </div>
    </div>

    <!-- Mes réservations -->
    <?php if (!empty($mes_reservations)): ?>
    <div class="col-md-8 mx-auto mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white fw-bold">Mes séjours</div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Arrivée</th>
                            <th>Départ</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mes_reservations as $r): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($r['start_date'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($r['end_date'])) ?></td>
                            <td>
                                <?php
                                $badge = ($r['status'] == 'approuvée') ? 'success' : (($r['status'] == 'en attente') ? 'warning text-dark' : 'danger');
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= ucfirst($r['status']) ?></span>
                            </td>
                            <td>
                                <a href="reserver.php?edit=<?= $r['id'] ?>"
                                   class="btn btn-sm btn-outline-primary me-1">✏️ Modifier</a>
                                <a href="?cancel=<?= $r['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Annuler ce séjour du <?= date('d/m/Y', strtotime($r['start_date'])) ?> au <?= date('d/m/Y', strtotime($r['end_date'])) ?> ?')">
                                   🗑 Annuler
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php include 'footer.php'; ?>
