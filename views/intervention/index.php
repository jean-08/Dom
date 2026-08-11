<?php
$pageTitle = 'Mes interventions';
$active = 'interventions';
$role = $_SESSION['user']['role'] ?? '';

$statutBadge = fn(string $s) => match ($s) {
    'terminee'  => 'badge-soft-success',
    'en_cours'  => 'badge-soft-info',
    'planifiee' => 'badge-soft-warning',
    default     => 'badge-soft-secondary',
};

require __DIR__ . '/../layouts/header.php';
?>

<div class="da-card p-3 p-md-4">
  <?php if (empty($interventions)): ?>
    <div class="empty-state">
      <i class="bi bi-tools"></i>
      Aucune intervention pour le moment.
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-da align-middle mb-0">
        <thead>
          <tr>
            <th>#</th>
            <?php if ($role === 'admin'): ?><th>Prestataire</th><?php endif; ?>
            <th>Demande</th>
            <th>Date</th>
            <th>Statut</th>
            <th>Résultat</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($interventions as $i): ?>
          <tr>
            <td>#<?= (int) $i['id_intervention'] ?></td>
            <?php if ($role === 'admin'): ?><td><?= htmlspecialchars(($i['prenom'] ?? '') . ' ' . ($i['nom'] ?? '')) ?></td><?php endif; ?>
            <td><a href="index.php?action=demande_show&id=<?= (int) $i['id_demande'] ?>">#<?= (int) $i['id_demande'] ?></a></td>
            <td><?= htmlspecialchars($i['date']) ?></td>
            <td><span class="badge <?= $statutBadge($i['statut'] ?? '') ?>"><?= htmlspecialchars($i['statut'] ?? '—') ?></span></td>
            <td><?= htmlspecialchars($i['resultat'] ?? '—') ?></td>
            <td class="text-end">
              <?php if ($role !== 'admin' && ($i['statut'] ?? '') !== 'terminee'): ?>
                <button class="btn btn-sm btn-brand" data-bs-toggle="modal" data-bs-target="#terminerModal<?= (int) $i['id_intervention'] ?>">
                  <i class="bi bi-check2-circle me-1"></i>Clôturer
                </button>
                <div class="modal fade" id="terminerModal<?= (int) $i['id_intervention'] ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="POST" action="index.php?action=intervention_terminer">
                        <div class="modal-header">
                          <h5 class="modal-title">Clôturer l'intervention #<?= (int) $i['id_intervention'] ?></h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="id_intervention" value="<?= (int) $i['id_intervention'] ?>">
                          <label class="form-label">Résultat de l'intervention</label>
                          <textarea name="resultat" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                          <button type="submit" class="btn btn-brand">Valider la clôture</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
