<?php
$pageTitle = 'Diagnostic';
$active = 'demandes';
require __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <?php if (!$diagnostic): ?>
      <div class="empty-state">
        <i class="bi bi-clipboard2-x"></i>
        Aucun diagnostic n'a encore été enregistré pour cette demande.
      </div>
    <?php else: ?>
      <div class="da-card p-4">
        <h6 class="mb-3"><i class="bi bi-clipboard2-pulse me-2"></i>Diagnostic</h6>
        <p><?= nl2br(htmlspecialchars($diagnostic['description'])) ?></p>
        <?php if (!empty($diagnostic['resultat'])): ?>
          <p class="text-muted mb-0">Résultat : <?= nl2br(htmlspecialchars($diagnostic['resultat'])) ?></p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    <div class="mt-3">
      <a href="index.php?action=demande_show&id=<?= (int) $id_demande ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Retour à la demande
      </a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
