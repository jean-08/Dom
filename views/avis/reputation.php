<?php
$pageTitle = 'Ma réputation';
$pageSubtitle = 'Notes et avis reçus de vos clients';
$active = 'reputation';
require __DIR__ . '/../layouts/header.php';

$moyenne = (float) ($reputation['note_moyenne'] ?? 0);
$nb      = (int) ($reputation['nombre_avis'] ?? 0);
?>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="da-card p-4 text-center">
      <div class="display-5 fw-bold"><?= number_format($moyenne, 1) ?></div>
      <div class="mb-2">
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <i class="bi <?= $i <= round($moyenne) ? 'bi-star-fill text-warning' : 'bi-star text-secondary' ?>"></i>
        <?php endfor; ?>
      </div>
      <div class="text-muted small"><?= $nb ?> avis reçu<?= $nb > 1 ? 's' : '' ?></div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="da-card p-3 p-md-4">
      <h6 class="mb-3">Détail des avis</h6>
      <?php if (empty($avis)): ?>
        <div class="empty-state">
          <i class="bi bi-star"></i>
          Vous n'avez pas encore reçu d'avis.
        </div>
      <?php else: ?>
        <?php foreach ($avis as $a): ?>
          <div class="border-bottom pb-2 mb-2">
            <div class="d-flex justify-content-between align-items-center">
              <strong><?= htmlspecialchars(($a['prenom'] ?? '') . ' ' . ($a['nom'] ?? '')) ?></strong>
              <span class="text-warning"><?= str_repeat('★', (int) $a['note']) . str_repeat('☆', 5 - (int) $a['note']) ?></span>
            </div>
            <?php if (!empty($a['comment'])): ?>
              <p class="mb-0 small text-muted"><?= htmlspecialchars($a['comment']) ?></p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
