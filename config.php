<?php
// ─── Database ─────────────────────────────────────────────────────────────────
define('DB_HOST',     'localhost');
define('DB_NAME',     'your_database_name');   // ← À modifier
define('DB_USER',     'your_database_user');   // ← À modifier
define('DB_PASSWORD', 'your_database_password'); // ← À modifier

// ─── Application ──────────────────────────────────────────────────────────────
define('APP_NAME',    'Family Villa');          // ← Nom de votre villa / application
define('APP_URL',     'https://example.com/villa'); // ← URL publique de votre site (sans slash final)
define('APP_OWNER',   'Your Name');             // ← Votre prénom / nom affiché dans le footer

// ─── PDO connection ───────────────────────────────────────────────────────────
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
