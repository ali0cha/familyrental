<?php
// ─────────────────────────────────────────────────────────────────────────────
// config.example.php — Copiez ce fichier en config.php et remplissez les valeurs
// NE COMMITEZ JAMAIS config.php dans votre dépôt Git (voir .gitignore)
// ─────────────────────────────────────────────────────────────────────────────

// ─── Base de données ──────────────────────────────────────────────────────────
define('DB_HOST',     'localhost');
define('DB_NAME',     'your_database_name');    // ← Nom de votre base de données
define('DB_USER',     'your_database_user');    // ← Utilisateur MySQL
define('DB_PASSWORD', 'your_database_password'); // ← Mot de passe MySQL

// ─── Application ──────────────────────────────────────────────────────────────
define('APP_NAME',    'Family Villa');           // ← Nom affiché dans l'interface et les emails
define('APP_URL',     'https://example.com');    // ← URL publique de votre site (sans slash final)
define('APP_OWNER',   'Your Name');              // ← Votre prénom / nom affiché dans le footer

// ─── Connexion PDO ────────────────────────────────────────────────────────────
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASSWORD
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
