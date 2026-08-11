<?php
$pageTitle = 'Suivi des interventions';
$pageSubtitle = 'Vue globale de toutes les interventions';
$active = 'admin_interventions';

$statutBadge = fn(string $s) => match ($s) {
    'terminee' => 'badge-soft-success',
    'en_cours' => 'badge-soft-info',
    default    => 'badge-soft-secondary',
};

require __DIR__ . '/../layouts/header.php';
?>

<div class="da-card p-3 p-md-4">
  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
    <div class="input-group" style="max-width: 320px;">
      <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
      <input type="text" class="form-control" placeholder="Rechercher..." data-table-search="suiviInterventionsTable">
    </div>
  </div>

  <?php if (empty($interventions)): ?>
    <div class="empty-state">
      <i class="bi bi-tools"></i>
      Aucune intervention enregistrée.
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-da align-middle mb-0" id="suiviInterventionsTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Prestataire</th>
            <th>Demande</th>
            <th>Date</th>
            <th>Statut</th>
            <th>Résultat</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($interventions as $i): ?>
          <tr>
            <td>#<?= (int) $i['id_intervention'] ?></td>
            <td><?= htmlspecialchars(($i['prenom'] ?? '') . ' ' . ($i['nom'] ?? '')) ?></td>
            <td><a href="index.php?action=demande_show&id=<?= (int) $i['id_demande'] ?>">#<?= (int) $i['id_demande'] ?></a></td>
            <td><?= htmlspecialchars($i['date']) ?></td>
            <td><span class="badge <?= $statutBadge($i['statut'] ?? '') ?>"><?= htmlspecialchars($i['statut'] ?? '—') ?></span></td>
            <td><?= htmlspecialchars($i['resultat'] ?? '—') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>