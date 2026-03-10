<?php
session_start();
require 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: planning.php");
    exit();
}

$token = trim($_GET['token'] ?? '');
$user  = null;
$error = null;

// Vérifier que le token existe et n'est pas expiré
if ($token) {
    $stmt = $pdo->prepare("
        SELECT * FROM users
        WHERE reset_token = ?
          AND reset_expires > NOW()
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
}

if (!$user) {
    $error = "Ce lien est invalide ou a expiré. Veuillez faire une nouvelle demande.";
}

// Traitement du nouveau mot de passe
if ($_SERVER["REQUEST_METHOD"] === "POST" && $user) {
    $password        = $_POST['password'];
    $passwordConfirm = $_POST['password_confirm'];

    if (strlen($password) < 8) {
        $formError = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($password !== $passwordConfirm) {
        $formError = "Les deux mots de passe ne correspondent pas.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Mettre à jour le mot de passe et invalider le token
        $pdo->prepare("
            UPDATE users
            SET password = ?, reset_token = NULL, reset_expires = NULL
            WHERE id = ?
        ")->execute([$hash, $user['id']]);

        $success = true;
    }
}

include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">Nouveau mot de passe</h3>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <div class="text-center">
                        <a href="forgot_password.php" class="btn btn-outline-primary">Faire une nouvelle demande</a>
                    </div>

                <?php elseif (isset($success)): ?>
                    <div class="alert alert-success">
                        Votre mot de passe a été réinitialisé avec succès !
                    </div>
                    <div class="text-center">
                        <a href="login.php" class="btn btn-primary">Se connecter</a>
                    </div>

                <?php else: ?>
                    <?php if (isset($formError)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($formError) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        <div class="mb-3">
                            <label class="form-label">Nouveau mot de passe</label>
                            <input type="password" name="password" class="form-control"
                                   minlength="8" required autofocus
                                   placeholder="8 caractères minimum">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmer le mot de passe</label>
                            <input type="password" name="password_confirm" class="form-control"
                                   minlength="8" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Valider le nouveau mot de passe</button>
                    </form>
                <?php endif; ?>

                <div class="text-center mt-3">
                    <a href="login.php">← Retour à la connexion</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
