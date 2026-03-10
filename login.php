<?php
session_start();
require 'config.php';

// Si déjà connecté, on redirige
if (isset($_SESSION['user_id'])) {
    header("Location: planning.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([trim($_POST['username'])]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        if ($user['is_active'] == 0) {
            $error = "Votre compte n'a pas encore été validé par l'administrateur.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: " . ($user['role'] == 'admin' ? "admin.php" : "planning.php"));
            exit();
        }
    } else {
        $error = "Identifiants incorrects.";
    }
}

include 'header.php';
?>
<div class="row justify-content-center">
<div class="col-md-5">
<div class="card shadow-sm">
<div class="card-body p-4">
<h3 class="text-center mb-4">Connexion</h3>
<?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

<form method="POST">
<div class="mb-3">
<label class="form-label">Pseudo</label>
<input type="text" name="username" class="form-control" required autofocus>
</div>
<div class="mb-3">
<label class="form-label">Mot de passe</label>
<input type="password" name="password" class="form-control" required>
<div class="text-end mt-1">
<a href="forgot_password.php" class="small text-muted">Mot de passe oublié ?</a>
</div>
</div>
<button type="submit" class="btn btn-primary w-100">Se connecter</button>
</form>
<div class="text-center mt-3"><a href="register.php">Pas de compte ? S'inscrire</a></div>
</div>
</div>
</div>
</div>
<?php include 'footer.php'; ?>

