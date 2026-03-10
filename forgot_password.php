<?php
session_start();
require 'config.php';
require 'mailer.php';

if (isset($_SESSION['user_id'])) {
    header("Location: planning.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    // On cherche l'utilisateur mais on affiche toujours le même message
    // pour ne pas révéler si un email existe ou non
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Générer un token sécurisé valable 1 heure
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?")
            ->execute([$token, $expires, $user['id']]);

        mailReinitialisationMotDePasse($user['username'], $email, $token);
    }

    // Message générique dans tous les cas
    $success = "Si cette adresse est associée à un compte, un email de réinitialisation vient d'être envoyé.";
}

include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="text-center mb-2">Mot de passe oublié</h3>
                <p class="text-muted text-center small mb-4">
                    Entrez votre adresse email pour recevoir un lien de réinitialisation.
                </p>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php else: ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Adresse email</label>
                            <input type="email" name="email" class="form-control" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Envoyer le lien</button>
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
