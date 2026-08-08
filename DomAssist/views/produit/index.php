<?php
$pageTitle = 'Produits';
$active = 'admin_produits';
$role = $_SESSION['user']['role'] ?? '';
$produits = $produits ?? [];
require __DIR__ . '/../layouts/header.php';
?>

<div class="row g-3">
  <?php if ($role === 'admin'): ?>
    <div class="col-lg-4">
      <div class="da-card p-4">
        <h6 class="mb-3">Ajouter un produit</h6>
        <form method="POST" action="index.php?action=produit_create">
          <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" class="form-control" required>
          </div>
          <div class="row g-2">
            <div class="col-6 mb-3">
              <label class="form-label">Prix (€)</label>
              <input type="number" step="0.01" min="0" name="prix" class="form-control" required>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label">Stock</label>
              <input type="number" min="0" name="stock" class="form-control" required>
            </div>
          </div>
          <button type="submit" class="btn btn-brand w-100"><i class="bi bi-plus-circle me-1"></i>Ajouter</button>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <div class="col-lg-<?= $role === 'admin' ? '8' : '12' ?>">
    <div class="da-card p-3 p-md-4">
      <?php if (empty($produits)): ?>
        <div class="empty-state">
          <i class="bi bi-box-seam"></i>
          Aucun produit au catalogue.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-da align-middle mb-0">
            <thead><tr><th>Nom</th><th>Prix</th><th>Stock</th><th>Statut</th><?php if ($role === 'admin'): ?><th class="text-end">Actions</th><?php endif; ?></tr></thead>
            <tbody>
            <?php foreach ($produits as $p): ?>
              <tr>
                <td class="fw-semibold"><?= htmlspecialchars($p['nom']) ?></td>
                <td><?= number_format((float) $p['prix'], 2) ?> €</td>
                <td><?= (int) $p['stock'] ?></td>
                <td><span class="badge <?= $p['statut'] === 'disponible' ? 'badge-soft-success' : 'badge-soft-danger' ?>"><?= htmlspecialchars($p['statut']) ?></span></td>
                <?php if ($role === 'admin'): ?>
                  <td class="text-end">
                    <a href="index.php?action=produit_delete&id=<?= (int) $p['id_produit'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Supprimer ce produit ?"><i class="bi bi-trash"></i></a>
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
