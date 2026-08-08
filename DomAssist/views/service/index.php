<?php
$pageTitle = 'Catégories de services';
$active = 'admin_services';
$role = $_SESSION['user']['role'] ?? '';
$services = $services ?? [];
require __DIR__ . '/../layouts/header.php';
?>

<div class="row g-3">
  <?php if ($role === 'admin'): ?>
    <div class="col-lg-4">
      <div class="da-card p-4">
        <h6 class="mb-3">Ajouter une catégorie</h6>
        <form method="POST" action="index.php?action=service_create">
          <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
          </div>
          <button type="submit" class="btn btn-brand w-100"><i class="bi bi-plus-circle me-1"></i>Ajouter</button>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <div class="col-lg-<?= $role === 'admin' ? '8' : '12' ?>">
    <div class="da-card p-3 p-md-4">
      <?php if (empty($services)): ?>
        <div class="empty-state">
          <i class="bi bi-tags"></i>
          Aucune catégorie de service pour le moment.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-da align-middle mb-0">
            <thead><tr><th>Nom</th><th>Description</th><?php if ($role === 'admin'): ?><th class="text-end">Actions</th><?php endif; ?></tr></thead>
            <tbody>
            <?php foreach ($services as $s): ?>
              <tr>
                <td class="fw-semibold"><?= htmlspecialchars($s['nom']) ?></td>
                <td class="text-muted small"><?= htmlspecialchars($s['description'] ?? '—') ?></td>
                <?php if ($role === 'admin'): ?>
                  <td class="text-end">
                    <a href="index.php?action=service_delete&id=<?= (int) $s['id_service'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Supprimer cette catégorie ?"><i class="bi bi-trash"></i></a>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
