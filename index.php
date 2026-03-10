<?php
session_start();

// 1. Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Aucune session active -> Direction la page de connexion
    header("Location: login.php");
    exit();
}

// 2. Si l'utilisateur est connecté, vérifier son rôle
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    // C'est l'administrateur -> Direction le tableau de bord admin
    header("Location: admin.php");
    exit();
} else {
    // C'est un membre de la famille -> Direction le planning
    header("Location: planning.php");
    exit();
}
?>
