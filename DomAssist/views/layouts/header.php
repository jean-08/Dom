<?php
/**
 * Layout partagé. Variables attendues dans la vue avant include :
 * $pageTitle, $pageSubtitle (optionnel), $active (clé menu actif)
 *
 * Calcule $da_isPrestataire pour que la sidebar sache si l'utilisateur
 * a (ou a demandé) un profil prestataire.
 */
if (session_status() === PHP_SESSION_NONE) session_start();

$da_isPrestataire = false;
if (!empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') !== 'admin') {
    require_once __DIR__ . '/../../models/Prestataire.php';
    $da_isPrestataire = (bool) (new Prestataire())->findByUser((int) $_SESSION['user']['id_user']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="DomAssist - Plateforme moderne de mise en relation de confiance pour vos services à domicile et dépannages.">
  <title><?= htmlspecialchars($pageTitle ?? 'DomAssist') ?> — DomAssist</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="public/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="da-shell">
  <?php require __DIR__ . '/../components/sidebar.php'; ?>

  <div class="da-main">
    <?php require __DIR__ . '/../components/navbar.php'; ?>
    <?php require __DIR__ . '/../components/alerts.php'; ?>

    <div class="da-content animate-fade-in">

