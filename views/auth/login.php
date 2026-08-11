<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion — DomAssist</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="public/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-side">
      <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><circle cx="12" cy="15" r="2.2"/></svg>
      <h2>DomAssist</h2>
      <p>Trouvez un prestataire de confiance pour vos travaux à domicile, ou proposez vos services en tant que professionnel.</p>
      <ul>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg> Demandes suivies de bout en bout</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg> Prestataires validés par nos équipes</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg> Avis et réputation transparents</li>
      </ul>
    </div>
    <div class="auth-main">
      <h3>Connexion</h3>
      <p class="auth-subtitle">Accédez à votre espace DomAssist</p>

      <?php if (!empty($_SESSION['error'])): ?>
        <div class="auth-alert auth-alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
      <?php endif; ?>
      <?php if (!empty($_SESSION['success'])): ?>
        <div class="auth-alert auth-alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
      <?php endif; ?>

      <form method="POST" action="index.php?action=login">
        <div class="auth-field">
          <label for="email">Adresse email</label>
          <div class="auth-field-group">
            <span class="auth-field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
            <input id="email" type="email" name="email" placeholder="vous@exemple.com" required autofocus aria-label="Adresse email">
          </div>
        </div>
        <div class="auth-field">
          <label for="mot_de_passe">Mot de passe</label>
          <div class="auth-field-group">
            <span class="auth-field-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span>
            <input id="mot_de_passe" type="password" name="mot_de_passe" placeholder="••••••••" required aria-label="Mot de passe">
            <button type="button" class="auth-toggle-password" aria-label="Afficher le mot de passe">Afficher</button>
          </div>
        </div>
        <div class="auth-field auth-remember">
          <label class="auth-remember-label"><input id="remember_me" type="checkbox" name="remember"> Se souvenir de moi</label>
        </div>
        <button type="submit" class="auth-submit">Se connecter</button>
      </form>

      <p class="auth-footer-link">
        Pas encore de compte ? <a href="index.php?action=register">Créer un compte</a>
      </p>
    </div>
  </div>
</div>
    </body>
    <script>
    (function(){
      var form = document.querySelector('form');
      var storageKey = 'domassist_login';
      var email = document.getElementById('email');
      var pwd = document.getElementById('mot_de_passe');
      var remember = document.getElementById('remember_me');
      try {
        var data = JSON.parse(localStorage.getItem(storageKey) || '{}');
        if (data.email) email.value = data.email;
        if (data.password && data.remember) pwd.value = data.password;
        if (remember && typeof data.remember !== 'undefined') remember.checked = data.remember;
      } catch(e){}
      function save(){
        var d = {email: email.value, password: pwd.value, remember: remember ? remember.checked : false};
        localStorage.setItem(storageKey, JSON.stringify(d));
      }
      [email, pwd].forEach(function(el){ if(el) el.addEventListener('input', save); });
      if(remember) remember.addEventListener('change', save);
      if(form) form.addEventListener('submit', function(){ if(!(remember && remember.checked)){ localStorage.removeItem(storageKey); } });
      var toggle = document.querySelector('.auth-toggle-password');
      if(toggle){ toggle.addEventListener('click', function(){ if(pwd.type === 'password'){ pwd.type = 'text'; toggle.textContent = 'Masquer'; } else { pwd.type = 'password'; toggle.textContent = 'Afficher'; } }); }
    })();
    </script>
</html>
