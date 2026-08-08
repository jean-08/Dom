<?php
$pageTitle = 'Prestataires';
$pageSubtitle = 'Professionnels validés par DomAssist';
$active = 'prestataires';
$role = $_SESSION['user']['role'] ?? '';
require __DIR__ . '/../layouts/header.php';
?>

<?php if ($role === 'admin'): ?>
  <div class="mb-3 text-end">
    <!-- <a href="index.php?action=prestataire_create" class="btn btn-brand btn-sm"><i class="bi bi-plus-circle me-1"></i>Ajouter un prestataire</a> -->
  </div>
<?php endif; ?>

<?php if (empty($prestataires)): ?>
  <div class="da-card p-4">
    <div class="empty-state">
      <i class="bi bi-person-lines-fill"></i>
      Aucun prestataire validé pour le moment.
    </div>
  </div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($prestataires as $p): ?>
      <div class="col-md-6 col-lg-4">
        <div class="da-card p-3 h-100 d-flex flex-column">
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="avatar-circle"><?= htmlspecialchars(strtoupper(mb_substr($p['prenom'], 0, 1) . mb_substr($p['nom'], 0, 1))) ?></span>
            <div>
              <div class="fw-semibold"><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></div>
              <div class="text-muted small"><?= htmlspecialchars($p['specialite']) ?></div>
            </div>
          </div>
          <a href="index.php?action=prestataire_show&id=<?= (int) $p['id_prestataire'] ?>" class="btn btn-outline-secondary btn-sm mt-auto">
            <i class="bi bi-eye me-1"></i>Voir le profil
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
