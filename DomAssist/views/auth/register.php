<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Créer un compte — DomAssist</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="public/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card row g-0">
    <div class="col-md-5 auth-side d-none d-md-flex">
      <i class="bi bi-house-gear-fill fs-1 mb-3"></i>
      <h2 class="fw-bold">Rejoignez DomAssist</h2>
      <p class="opacity-75">Créez votre compte client gratuitement. Vous pourrez ensuite candidater pour devenir prestataire à tout moment.</p>
    </div>
    <div class="col-md-7">
      <div class="p-4 p-md-5">
        <h3 class="fw-bold mb-1">Créer un compte</h3>
        <p class="text-muted mb-4">Quelques informations pour démarrer</p>

        <?php if (!empty($_SESSION['error'])): ?>
          <div class="alert alert-danger py-2"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=register">
          <div class="row">
            <div class="col-sm-6 mb-3">
              <label class="form-label">Prénom</label>
              <input type="text" name="prenom" class="form-control" required>
            </div>
            <div class="col-sm-6 mb-3">
              <label class="form-label">Nom</label>
              <input type="text" name="nom" class="form-control" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Adresse email</label>
            <input type="email" name="email" class="form-control" placeholder="vous@exemple.com" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <input type="password" name="mot_de_passe" class="form-control" placeholder="8 caractères minimum" required minlength="8">
          </div>
          <button type="submit" class="btn btn-brand w-100 py-2">Créer mon compte</button>
        </form>

        <p class="text-center text-muted mt-4 mb-0 small">
          Déjà un compte ? <a href="index.php?action=login" class="text-decoration-none fw-semibold">Se connecter</a>
        </p>
      </div>
    </div>
  </div>
</div>
</body>
</html>
