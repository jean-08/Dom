<?php
/**
 * Vue d’erreur générique (500 Internal Server Error).
 * Affiche un message utilisateur convivial et un identifiant de suivi.
 */

// Si une ID d’erreur a été fournie via la session ou le GET, on l’affiche sinon on en crée une.
$errorId = $_GET['error_id'] ?? ($_SESSION['error_id'] ?? null);
if (!$errorId) {
    $errorId = bin2hex(random_bytes(8));
    $_SESSION['error_id'] = $errorId;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Erreur serveur – DomAssist</title>
    <style>
        body {font-family: Arial, sans-serif; background:#f8f9fa; color:#212529; margin:0; display:flex; align-items:center; justify-content:center; height:100vh;}
        .container {max-width:600px; text-align:center; padding:2rem; background:#fff; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1);}
        h1 {color:#dc3545; margin-bottom:1rem;}
        p {margin-bottom:1rem;}
        .code {font-family:monospace; background:#e9ecef; padding:0.5rem 1rem; border-radius:4px; display:inline-block;}
    </style>
</head>
<body>
<div class="container">
    <h1>Oops ! Une erreur interne s’est produite.</h1>
    <p>Nous avons été informés du problème. Veuillez réessayer plus tard.</p>
    <p>Identifiant de suivi : <span class="code"><?= htmlspecialchars($errorId) ?></span></p>
    <p>Vous pouvez contacter le support en partageant cet identifiant.</p>
    <a href="index.php">Retour à l’accueil</a>
</div>
</body>
</html>
