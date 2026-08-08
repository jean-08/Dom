<?php
$pageTitle = 'Compte Suspendu';
if (session_status() === PHP_SESSION_NONE) session_start();
$userSess = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Compte Suspendu — DomAssist</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="public/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 py-5">

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">

      <div class="da-card p-4 p-md-5 border-0 shadow-lg text-center animate-fade-in">
        <div class="avatar-circle mx-auto mb-4 bg-brand-red" style="width: 72px; height: 72px; font-size: 2.25rem;">
          <i class="bi bi-shield-x"></i>
        </div>

        <h2 class="fw-bold text-danger mb-2">Votre compte a été suspendu</h2>
        <p class="text-muted mb-4">L'accès à votre compte DomAssist est temporairement ou définitivement restreint suite à une décision de modération.</p>

        <div class="card bg-slate-50 border-1 border-slate-200 text-start p-3 mb-4 rounded-3">
          <div class="d-flex align-items-center gap-2 text-danger fw-bold mb-2">
            <i class="bi bi-exclamation-triangle-fill"></i> Motif de la suspension :
          </div>
          <p class="mb-3 text-dark fs-6 bg-white p-2.5 rounded border border-slate-200">
            <?= htmlspecialchars($userSess['motif_suspension'] ?? 'Non spécifié par l\'administrateur.') ?>
          </p>

          <div class="row g-2 text-muted small">
            <div class="col-6">
              <span class="d-block fw-semibold text-secondary">Date de suspension :</span>
              <?= htmlspecialchars(!empty($userSess['date_suspension']) ? date('d/m/Y H:i', strtotime($userSess['date_suspension'])) : 'N/A') ?>
            </div>
            <div class="col-6">
              <span class="d-block fw-semibold text-secondary">Fin de suspension :</span>
              <?= htmlspecialchars(!empty($userSess['date_fin_suspension']) ? date('d/m/Y H:i', strtotime($userSess['date_fin_suspension'])) : 'Indéfinie / Permanent') ?>
            </div>
          </div>
        </div>

        <div class="alert alert-info d-flex align-items-start gap-2 text-start small mb-4">
          <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 text-info"></i>
          <div>
            Si vous pensez qu'il s'agit d'une erreur ou souhaitez contester cette mesure, vous pouvez contacter notre équipe de modération en indiquant votre adresse email (<strong><?= htmlspecialchars($userSess['email'] ?? '') ?></strong>).
          </div>
        </div>

        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
          <a href="mailto:support@domassist.fr?subject=Contestation%20de%20suspension%20Compte%20<?= urlencode($userSess['email'] ?? '') ?>" class="btn btn-brand px-4 shadow-sm">
            <i class="bi bi-envelope me-1.5"></i> Contacter le support
          </a>
          <a href="index.php?action=logout" class="btn btn-outline-secondary px-4">
            <i class="bi bi-box-arrow-right me-1.5"></i> Se déconnecter
          </a>
        </div>
      </div>

      <div class="text-center mt-4 text-muted small">
        <i class="bi bi-house-gear-fill text-warning me-1"></i> DomAssist — Plateforme de confiance
      </div>

    </div>
  </div>
</div>

</body>
</html>
