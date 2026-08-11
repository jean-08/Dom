<?php
$pageTitle = 'Mes disponibilités';
$active = 'disponibilites';
$dispos = $dispos ?? [];

$statutBadge = fn(string $s) => $s === 'libre' ? 'badge-soft-success' : 'badge-soft-secondary';

require __DIR__ . '/../layouts/header.php';
?>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="da-card p-4">
      <h6 class="mb-3">Ajouter un créneau</h6>
      <form method="POST" action="index.php?action=disponibilite_create">
        <div class="mb-3">
          <label class="form-label">Date</label>
          <input type="date" name="date" class="form-control" required>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label">Début</label>
            <input type="time" name="heure_debut" class="form-control" required>
          </div>
          <div class="col-6">
            <label class="form-label">Fin</label>
            <input type="time" name="heure_fin" class="form-control" required>
          </div>
        </div>
        <button type="submit" class="btn btn-brand w-100"><i class="bi bi-plus-circle me-1"></i>Ajouter</button>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="da-card p-3 p-md-4">
      <?php if (empty($dispos)): ?>
        <div class="empty-state">
          <i class="bi bi-calendar3"></i>
          Aucune disponibilité enregistrée.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-da align-middle mb-0">
            <thead><tr><th>Date</th><th>Début</th><th>Fin</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($dispos as $d): ?>
              <tr>
                <td><?= htmlspecialchars($d['date']) ?></td>
                <td><?= htmlspecialchars(substr($d['heure_debut'], 0, 5)) ?></td>
                <td><?= htmlspecialchars(substr($d['heure_fin'], 0, 5)) ?></td>
                <td><span class="badge <?= $statutBadge($d['statut']) ?>"><?= htmlspecialchars($d['statut']) ?></span></td>
                <td class="text-end">
                  <a href="index.php?action=disponibilite_delete&id=<?= (int) $d['id_dispo'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Supprimer ce créneau ?"><i class="bi bi-trash"></i></a>
                </td>
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
