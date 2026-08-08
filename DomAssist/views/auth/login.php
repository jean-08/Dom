<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion — DomAssist</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="public/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card row g-0">
    <div class="col-md-5 auth-side d-none d-md-flex">
      <i class="bi bi-house-gear-fill fs-1 mb-3"></i>
      <h2 class="fw-bold">DomAssist</h2>
      <p class="opacity-75">Trouvez un prestataire de confiance pour vos travaux à domicile, ou proposez vos services en tant que professionnel.</p>
      <ul class="list-unstyled small opacity-75 mt-3">
        <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Demandes suivies de bout en bout</li>
        <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Prestataires validés par nos équipes</li>
        <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Avis et réputation transparents</li>
      </ul>
    </div>
    <div class="col-md-7">
      <div class="p-4 p-md-5">
        <h3 class="fw-bold mb-1">Connexion</h3>
        <p class="text-muted mb-4">Accédez à votre espace DomAssist</p>

        <?php if (!empty($_SESSION['error'])): ?>
          <div class="alert alert-danger py-2"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['success'])): ?>
          <div class="alert alert-success py-2"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=login">
          <div class="mb-3">
            <label class="form-label">Adresse email</label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
              <input type="email" name="email" class="form-control" placeholder="vous@exemple.com" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
              <input type="password" name="mot_de_passe" class="form-control" placeholder="••••••••" required>
            </div>
          </div>
          <button type="submit" class="btn btn-brand w-100 py-2">Se connecter</button>
        </form>

        <p class="text-center text-muted mt-4 mb-0 small">
          Pas encore de compte ? <a href="index.php?action=register" class="text-decoration-none fw-semibold">Créer un compte</a>
        </p>
      </div>
    </div>
  </div>
</div>
</body>
</html>
