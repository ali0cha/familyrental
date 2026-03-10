<?php
session_start();
require 'config.php';
require 'mailer.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id    = $_SESSION['user_id'];
$edit_id    = isset($_GET['edit']) ? (int) $_GET['edit'] : null;
$edit_mode  = false;
$booking    = null;

// ── Mode édition : charger la réservation existante ───────────────────────────
if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$edit_id, $user_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        header("Location: planning.php");
        exit();
    }
    $edit_mode = true;
}

// ── Traitement du formulaire ──────────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];
    $post_edit  = isset($_POST['edit_id']) ? (int) $_POST['edit_id'] : null;

    if ($start_date >= $end_date) {
        $error = "La date de départ doit être ultérieure à la date d'arrivée.";
    } else {
        // Vérifier les chevauchements (en excluant la réservation en cours si édition)
        $exclude_id = $post_edit ?? 0;
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM bookings
            WHERE status != 'refusée'
              AND id != :exclude_id
              AND start_date < :end_date
              AND end_date   > :start_date
        ");
        $stmt->execute([
            'exclude_id' => $exclude_id,
            'end_date'   => $end_date,
            'start_date' => $start_date,
        ]);

        if ($stmt->fetchColumn() > 0) {
            $error = "Désolé, ces dates chevauchent une réservation déjà approuvée ou en attente.";
        } elseif ($post_edit) {
            // ── Modification ──
            $verify = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
            $verify->execute([$post_edit, $user_id]);
            $existing = $verify->fetch();

            if (!$existing) {
                $error = "Réservation introuvable.";
            } else {
                $pdo->prepare("
                    UPDATE bookings
                    SET start_date = ?, end_date = ?, status = 'en attente'
                    WHERE id = ? AND user_id = ?
                ")->execute([$start_date, $end_date, $post_edit, $user_id]);

                mailReservationModifiee($_SESSION['username'], $start_date, $end_date);

                $success = "Votre demande de modification a bien été envoyée. Elle doit être reconfirmée par l'administrateur.";
                $edit_mode = false;
                $booking   = null;
            }
        } else {
            // ── Nouvelle réservation ──
            $insert = $pdo->prepare("INSERT INTO bookings (user_id, start_date, end_date) VALUES (?, ?, ?)");
            if ($insert->execute([$user_id, $start_date, $end_date])) {
                mailNouvelleReservation($_SESSION['username'], $start_date, $end_date);
                $success = "Demande envoyée ! Elle est en attente de l'approbation du propriétaire.";
            } else {
                $error = "Erreur lors de l'enregistrement.";
            }
        }
    }
}

include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm p-4">

            <h3 class="text-center mb-4">
                <?= $edit_mode ? '✏️ Modifier ma réservation' : 'Nouvelle Réservation' ?>
            </h3>

            <?php if (isset($error)):   echo "<div class='alert alert-danger'>"  . htmlspecialchars($error)   . "</div>"; endif; ?>
            <?php if (isset($success)): echo "<div class='alert alert-success'>" . htmlspecialchars($success) . "</div>"; endif; ?>

            <?php if (!isset($success)): ?>
            <form method="POST">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="edit_id" value="<?= $booking['id'] ?>">
                    <div class="alert alert-info small mb-3">
                        ℹ️ Modifier les dates remettra votre réservation <strong>en attente de validation</strong> par l'administrateur.
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Date d'arrivée</label>
                    <input type="date" name="start_date" class="form-control" required
                           min="<?= date('Y-m-d') ?>"
                           value="<?= $edit_mode ? htmlspecialchars($booking['start_date']) : '' ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Date de départ</label>
                    <input type="date" name="end_date" class="form-control" required
                           min="<?= date('Y-m-d') ?>"
                           value="<?= $edit_mode ? htmlspecialchars($booking['end_date']) : '' ?>">
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?= $edit_mode ? 'Enregistrer les modifications' : 'Soumettre la demande' ?>
                    </button>
                    <a href="planning.php" class="btn btn-outline-secondary">
                        <?= $edit_mode ? 'Annuler la modification' : 'Consulter le planning' ?>
                    </a>
                </div>
            </form>
            <?php else: ?>
                <div class="text-center mt-2">
                    <a href="planning.php" class="btn btn-outline-secondary">← Retour au planning</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
