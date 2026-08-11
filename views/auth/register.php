<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Créer un compte — DomAssist</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="public/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-side">
      <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><circle cx="12" cy="15" r="2.2"/></svg>
      <h2>Rejoignez DomAssist</h2>
      <p>Créez votre compte client gratuitement. Vous pourrez ensuite candidater pour devenir prestataire à tout moment.</p>
    </div>
    <div class="auth-main">
      <h3>Créer un compte</h3>
      <p class="auth-subtitle">Quelques informations pour démarrer</p>

      <?php if (!empty($_SESSION['error'])): ?>
        <div class="auth-alert auth-alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
      <?php endif; ?>

      <form method="POST" action="index.php?action=register">
        <div class="auth-row">
          <div class="auth-field">
            <label for="prenom">Prénom</label>
            <div class="auth-field-group">
              <input id="prenom" type="text" name="prenom" required autofocus aria-label="Prénom">
            </div>
          </div>
          <div class="auth-field">
            <label for="nom">Nom</label>
            <div class="auth-field-group">
              <input id="nom" type="text" name="nom" required aria-label="Nom">
            </div>
          </div>
        </div>
        <div class="auth-field">
          <label for="email">Adresse email</label>
          <div class="auth-field-group">
            <span class="auth-field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
            <input id="email" type="email" name="email" placeholder="vous@exemple.com" required>
          </div>
        </div>
        <div class="auth-field">
          <label for="mot_de_passe">Mot de passe</label>
          <div class="auth-field-group">
            <span class="auth-field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span>
            <input id="mot_de_passe" type="password" name="mot_de_passe" placeholder="8 caractères minimum" required minlength="8" aria-label="Mot de passe">
            <button type="button" class="auth-toggle-password" aria-label="Afficher le mot de passe">Afficher</button>
          </div>
          <small class="auth-hint">Utilisez 8 caractères ou plus. Mélangez lettres et chiffres.</small>
        </div>
        <button type="submit" class="auth-submit">Créer mon compte</button>
      </form>

      <p class="auth-footer-link">
        Déjà un compte ? <a href="index.php?action=login">Se connecter</a>
      </p>
    </div>
  </div>
</div>
</body>
    </html>
</html>
