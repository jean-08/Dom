<?php
$pageTitle = 'Profil Membre — ' . htmlspecialchars(($u['prenom'] ?? 'Utilisateur') . ' ' . mb_substr($u['nom'] ?? '', 0, 1) . '.');
$pageSubtitle = 'Membre de la communauté DomAssist';
$active = 'users';
$breadcrumb = [
    ['label' => 'Dashboard', 'action' => 'dashboard'],
    ['label' => 'Profil Membre']
];
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../components/breadcrumb.php';

$avatarUrl = UploadHelper::getAvatarUrl($u['photo_url'] ?? null, $u['prenom'] ?? 'User', $u['nom'] ?? '');
$nomProtect = htmlspecialchars(($u['prenom'] ?? '') . ' ' . mb_substr($u['nom'] ?? '', 0, 1) . '.');
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="da-card p-4 shadow-sm rounded-3">
      <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="rounded-circle border" style="width: 80px; height: 80px; object-fit: cover;">
        <div>
          <h4 class="fw-bold mb-1"><?= $nomProtect ?></h4>
          <div class="d-flex flex-wrap gap-2 align-items-center text-muted small">
            <span class="badge bg-light text-dark border"><i class="bi bi-person me-1"></i>Membre client</span>
            <?php if (!empty($u['ville'])): ?>
              <span><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($u['ville']) ?></span>
            <?php endif; ?>
            <?php if (!empty($u['created_at'])): ?>
              <span><i class="bi bi-calendar-check me-1"></i>Inscrit le <?= date('d/m/Y', strtotime($u['created_at'])) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php if (!empty($u['bio'])): ?>
        <div class="mb-4">
          <h6 class="fw-bold text-primary mb-2"><i class="bi bi-quote me-1"></i>Présentation</h6>
          <p class="text-secondary"><?= nl2br(htmlspecialchars($u['bio'])) ?></p>
        </div>
      <?php endif; ?>

      <!-- Statistiques d'activité -->
      <h6 class="fw-bold text-primary mb-3"><i class="bi bi-bar-chart-line me-1"></i>Activité sur la plateforme</h6>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="p-3 border rounded-3 bg-light text-center">
            <div class="fs-3 fw-bold text-primary"><?= (int)($nbPublished ?? 0) ?></div>
            <div class="small text-muted">Demandes publiées</div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="p-3 border rounded-3 bg-light text-center">
            <div class="fs-3 fw-bold text-success"><?= (int)($nbCloturees ?? 0) ?></div>
            <div class="small text-muted">Interventions clôturées</div>
          </div>
        </div>
      </div>

      <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-shield-check fs-4 me-3 text-info"></i>
        <div class="small">
          <strong>Vie privée protégée :</strong> Les coordonnées complètes (numéro de téléphone et adresse exacte) sont masquées du profil public et transmises uniquement aux prestataires retenus lors d'un engagement.
        </div>
      </div>

      <div class="d-flex justify-content-end">
        <a href="javascript:history.back()" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Retour</a>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
