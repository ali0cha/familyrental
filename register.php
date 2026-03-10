<?php
session_start();
require 'config.php';
require 'mailer.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user  = trim($_POST['username']);
    $email = trim($_POST['email']);
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_active) VALUES (?, ?, ?, 0)");
        $stmt->execute([$user, $email, $pass]);

        // Notifier les admins qu'un nouveau compte attend validation
        mailNouvelUtilisateur($user, $email);

        $success = "Inscription réussie ! Votre compte est en attente de validation par l'administrateur.";
    } catch (PDOException $e) {
        $error = "Ce pseudo est déjà utilisé. Veuillez en choisir un autre.";
    }
}

include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">Créer un compte</h3>
                <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
                <?php if(isset($error))   echo "<div class='alert alert-danger'>$error</div>"; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Pseudo</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Adresse email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">S'inscrire</button>
                </form>
                <div class="text-center mt-3"><a href="login.php">Déjà inscrit ? Connectez-vous</a></div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
