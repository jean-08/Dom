<?php
$pageTitle = 'Suivi des services';
$pageSubtitle = 'Vue globale des catégories de services';
$active = 'admin_services';
require __DIR__ . '/../layouts/header.php';
?>

<div class="da-card p-3 p-md-4">
  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
    <div class="input-group" style="max-width: 320px;">
      <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
      <input type="text" class="form-control" placeholder="Rechercher..." data-table-search="suiviServicesTable">
    </div>
    <a href="index.php?action=services" class="btn btn-brand btn-sm"><i class="bi bi-gear me-1"></i>Gérer les catégories</a>
  </div>

  <?php if (empty($services)): ?>
    <div class="empty-state">
      <i class="bi bi-tags"></i>
      Aucune catégorie de service enregistrée.
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-da align-middle mb-0" id="suiviServicesTable">
        <thead><tr><th>#</th><th>Nom</th><th>Description</th></tr></thead>
        <tbody>
        <?php foreach ($services as $s): ?>
          <tr>
            <td>#<?= (int) $s['id_service'] ?></td>
            <td class="fw-semibold"><?= htmlspecialchars($s['nom']) ?></td>
            <td class="text-muted small"><?= htmlspecialchars($s['description'] ?? '—') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
