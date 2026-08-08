<?php
$pageTitle = 'Mes demandes';
$pageSubtitle = 'Historique et suivi de vos demandes';
$active = 'demandes';
$role = $_SESSION['user']['role'] ?? '';

$statutBadge = function (string $statut): string {
    return match ($statut) {
        'en_attente'          => 'badge-soft-warning',
        'acceptée'            => 'badge-soft-info',
        'diagnostic_propose'  => 'badge-soft-info',
        'solution_validee'    => 'badge-soft-info',
        'en_cours'            => 'badge-soft-info',
        'terminée'            => 'badge-soft-success',
        'refusée'             => 'badge-soft-danger',
        default               => 'badge-soft-secondary',
    };
};

require __DIR__ . '/../layouts/header.php';
?>

<div class="da-card p-3 p-md-4">
  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
    <div class="input-group" style="max-width: 320px;">
      <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
      <input type="text" class="form-control" placeholder="Rechercher..." data-table-search="demandesTable">
    </div>
    <a href="index.php?action=demande_create" class="btn btn-brand btn-sm"><i class="bi bi-plus-circle me-1"></i>Nouvelle demande</a>
  </div>

  <?php if (empty($demandes)): ?>
    <div class="empty-state">
      <i class="bi bi-inbox"></i>
      Aucune demande pour le moment.
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-da align-middle mb-0" id="demandesTable">
        <thead>
          <tr>
            <th>#</th>
            <?php if ($role === 'admin'): ?><th>Client</th><?php endif; ?>
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
            <?php if ($role === 'admin'): ?><td><?= htmlspecialchars(($d['prenom'] ?? '') . ' ' . ($d['nom'] ?? '')) ?></td><?php endif; ?>
            <td><?= htmlspecialchars(mb_strimwidth($d['description'], 0, 45, '…')) ?></td>
            <td><?= htmlspecialchars($d['adresse']) ?></td>
            <td><?= htmlspecialchars($d['date']) ?></td>
            <td><span class="badge <?= $statutBadge($d['statut']) ?>"><?= htmlspecialchars($d['statut']) ?></span></td>
            <td class="text-end">
              <a href="index.php?action=demande_show&id=<?= (int) $d['id_demande'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
              <?php if ($role === 'admin'): ?>
                <a href="index.php?action=demande_delete&id=<?= (int) $d['id_demande'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Supprimer cette demande ?"><i class="bi bi-trash"></i></a>
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
