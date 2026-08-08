<?php
$pageTitle = 'Suivi des demandes';
$pageSubtitle = 'Vue globale de toutes les demandes clients';
$active = 'admin_demandes';

$statutBadge = fn(string $s) => match ($s) {
    'en_attente'          => 'badge-soft-warning',
    'acceptée'            => 'badge-soft-info',
    'diagnostic_propose'  => 'badge-soft-info',
    'solution_validee'    => 'badge-soft-info',
    'en_cours'            => 'badge-soft-info',
    'terminée'            => 'badge-soft-success',
    'refusée'             => 'badge-soft-danger',
    default               => 'badge-soft-secondary',
};

require __DIR__ . '/../layouts/header.php';
?>

<div class="da-card p-3 p-md-4">
  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
    <div class="input-group" style="max-width: 320px;">
      <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
      <input type="text" class="form-control" placeholder="Rechercher..." data-table-search="suiviDemandesTable">
    </div>
  </div>

  <?php if (empty($demandes)): ?>
    <div class="empty-state">
      <i class="bi bi-inbox"></i>
      Aucune demande enregistrée.
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-da align-middle mb-0" id="suiviDemandesTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Client</th>
            <th>Description</th>
            <th>Adresse</th>
            <th>Date</th>
            <th>Statut</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($demandes as $d): ?>
          <tr>
            <td>#<?= (int) $d['id_demande'] ?></td>
            <td><?= htmlspecialchars(($d['prenom'] ?? '') . ' ' . ($d['nom'] ?? '')) ?></td>
            <td><?= htmlspecialchars(mb_strimwidth($d['description'], 0, 40, '…')) ?></td>
            <td><?= htmlspecialchars($d['adresse']) ?></td>
            <td><?= htmlspecialchars($d['date']) ?></td>
            <td><span class="badge <?= $statutBadge($d['statut']) ?>"><?= htmlspecialchars($d['statut']) ?></span></td>
            <td class="text-end">
              <a href="index.php?action=demande_show&id=<?= (int) $d['id_demande'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
              <a href="index.php?action=demande_delete&id=<?= (int) $d['id_demande'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Supprimer cette demande ?"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>