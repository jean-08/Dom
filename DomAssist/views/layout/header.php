<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DomAssist</title>
  <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
<nav>
  <a href="index.php?action=dashboard" class="nav-brand">DomAssist</a>

  <?php if (isset($_SESSION['user'])): ?>
    <?php $role = $_SESSION['user']['role'] ?? ''; ?>

    <?php if ($role === 'client'): ?>
      <a href="index.php?action=demandes">Demandes</a>
      <a href="index.php?action=prestataires">Prestataires</a>
      <a href="index.php?action=services">Services</a>
      <a href="index.php?action=interventions">Interventions</a>
    <?php endif; ?>

    <?php if ($role === 'prestataire'): ?>
      <a href="index.php?action=demandes">Demandes</a>
      <a href="index.php?action=disponibilites">Disponibilités</a>
    <?php endif; ?>

    <?php if ($role === 'admin' && strpos($_GET['action'] ?? '', 'admin') !== false): ?>
      <span class="nav-label">Espace Admin</span>
    <?php endif; ?>

    <span class="nav-sep"></span>

    <?php if ($role === 'admin' && strpos($_GET['action'] ?? '', 'admin') === false): ?>
      <a href="index.php?action=admin_dashboard" class="nav-admin">⚙ Espace Admin</a>
    <?php endif; ?>

    <span class="nav-user">👤 <?= htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom']) ?></span>
    <a href="index.php?action=profile">Mon profil</a>
    <a href="index.php?action=logout" class="nav-logout">Déconnexion</a>

  <?php endif; ?>
</nav>
<div class="container">
  <?php if (!empty($_SESSION['error'])): ?>
    <div class="alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION['success'])): ?>
    <div class="alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>