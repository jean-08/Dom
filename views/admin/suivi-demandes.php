<?php
$pageTitle = 'Suivi des demandes';
$pageSubtitle = 'Vue globale de toutes les demandes clients';
$active = 'admin_demandes';

$statutBadge = fn(string $s) => match ($s) {
    'ouverte'                => 'badge-soft-success',
    'en_discussion'          => 'badge-soft-info',
    'prestataire_choisi'     => 'badge-soft-primary',
    'engagee'                => 'badge-soft-primary',
    'diagnostic_propose'     => 'badge-soft-info',
    'solution_proposee'      => 'badge-soft-info',
    'intervention_planifiee' => 'badge-soft-warning',
    'intervention_en_cours'  => 'badge-soft-warning',
    'terminee'               => 'badge-soft-success',
    'cloturee'               => 'badge-soft-secondary',
    'annulee_par_client', 'annulee_par_prestataire', 'expiree', 'suspendue_moderation' => 'badge-soft-danger',
    default                  => 'badge-soft-secondary',
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
            <th>Titre / Catégorie</th>
            <th>Ville</th>
            <th>Date</th>
            <th>Statut</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($demandes as $d): ?>
          <tr>
            <td>#<?= (int) $d['id_demande'] ?></td>
            <td><?= htmlspecialchars(($d['client_prenom'] ?? $d['prenom'] ?? '') . ' ' . ($d['client_nom'] ?? $d['nom'] ?? '')) ?></td>
            <td>
              <strong><?= htmlspecialchars($d['titre'] ?? mb_strimwidth($d['description'], 0, 30, '…')) ?></strong>
              <div class="small text-muted"><?= htmlspecialchars($d['category_libelle'] ?? '') ?></div>
            </td>
            <td><?= htmlspecialchars($d['ville'] ?? $d['adresse'] ?? '—') ?></td>
            <td><?= htmlspecialchars($d['published_at'] ?? $d['date'] ?? '—') ?></td>
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
