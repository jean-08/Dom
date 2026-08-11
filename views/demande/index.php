<?php
$pageTitle = 'Mes demandes';
$pageSubtitle = 'Historique et suivi de vos demandes';
$active = 'demandes';
$role = $_SESSION['user']['role'] ?? '';
$activeTab = (!empty($_GET['tab']) && $_GET['tab'] === 'presta' && !empty($isPrestataire)) ? 'presta' : 'client';

$statutBadge = function (string $statut): string {
    return match ($statut) {
        'ouverte'                  => 'badge-soft-secondary',
        'en_discussion'            => 'badge-soft-info',
        'prestataire_choisi'       => 'badge-soft-info',
        'engagee'                  => 'badge-soft-primary',
        'diagnostic_propose'       => 'badge-soft-info',
        'solution_proposee'        => 'badge-soft-info',
        'intervention_planifiee'   => 'badge-soft-primary',
        'intervention_en_cours'    => 'badge-soft-warning',
        'terminee'                 => 'badge-soft-success',
        'cloturee'                 => 'badge-soft-success',
        'annulee_par_client',
        'annulee_par_prestataire'  => 'badge-soft-danger',
        'expiree'                  => 'badge-soft-danger',
        'suspendue_moderation'     => 'badge-soft-warning',
        default                    => 'badge-soft-secondary',
    };
};

$statutLabel = function (string $statut): string {
    return match ($statut) {
        'ouverte'                 => 'Ouverte',
        'en_discussion'           => 'En discussion',
        'prestataire_choisi'      => 'Prestataire choisi',
        'engagee'                 => 'Engagée',
        'diagnostic_propose'      => 'Diagnostic proposé',
        'solution_proposee'       => 'Solution proposée',
        'intervention_planifiee'  => 'Intervention planifiée',
        'intervention_en_cours'   => 'Intervention en cours',
        'terminee'                => 'Terminée',
        'cloturee'                => 'Clôturée',
        'annulee_par_client'      => 'Annulée (client)',
        'annulee_par_prestataire' => 'Annulée (prestataire)',
        'expiree'                 => 'Expirée',
        'suspendue_moderation'    => 'Suspendue',
        default                   => ucfirst(str_replace('_', ' ', $statut)),
    };
};

require __DIR__ . '/../layouts/header.php';
?>

<div class="da-card p-3 p-md-4">
  <?php if (!empty($isPrestataire)): ?>
    <ul class="nav nav-tabs mb-3" id="demandeTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'client' ? 'active' : '' ?>" id="client-tab" data-bs-toggle="tab" data-bs-target="#client-pane" type="button" role="tab" aria-controls="client-pane" aria-selected="<?= $activeTab === 'client' ? 'true' : 'false' ?>">
          <i class="bi bi-person me-1"></i>Mes demandes (Client)
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'presta' ? 'active' : '' ?>" id="presta-tab" data-bs-toggle="tab" data-bs-target="#presta-pane" type="button" role="tab" aria-controls="presta-pane" aria-selected="<?= $activeTab === 'presta' ? 'true' : 'false' ?>">
          <i class="bi bi-briefcase me-1"></i>Mes propositions (Prestataire)
        </button>
      </li>
    </ul>
  <?php endif; ?>

  <div class="tab-content" id="demandeTabsContent">
    <!-- ONGLE T : CLIENT -->
    <div class="tab-pane fade <?= $activeTab === 'client' ? 'show active' : '' ?>" id="client-pane" role="tabpanel" aria-labelledby="client-tab" tabindex="0">
      <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <div class="input-group" style="max-width: 320px;">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input type="text" class="form-control" placeholder="Rechercher..." data-table-search="demandesTable">
        </div>
        <?php if ($role !== 'admin'): ?>
          <a href="index.php?action=demande_create" class="btn btn-brand btn-sm"><i class="bi bi-plus-circle me-1"></i>Nouvelle demande</a>
        <?php endif; ?>
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
                <th>Titre / Description</th>
                <th>Ville</th>
                <th>Catégorie</th>
                <th>Publié le</th>
                <th>Statut</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($demandes as $d): ?>
              <tr>
                <td>#<?= (int) $d['id_demande'] ?></td>
                <?php if ($role === 'admin'): ?>
                  <td><?= htmlspecialchars(($d['client_prenom'] ?? $d['prenom'] ?? '') . ' ' . ($d['client_nom'] ?? $d['nom'] ?? '')) ?></td>
                <?php endif; ?>
                <td>
                  <strong><?= htmlspecialchars(mb_strimwidth($d['titre'] ?? $d['description'] ?? '', 0, 45, '…')) ?></strong>
                  <?php if (!empty($d['category_libelle'])): ?>
                    <div class="small text-muted"><?= htmlspecialchars($d['category_libelle']) ?></div>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($d['ville'] ?? '—') ?></td>
                <td>
                  <?php if (!empty($d['category_libelle'])): ?>
                    <span class="badge badge-soft-info"><?= htmlspecialchars($d['category_libelle']) ?></span>
                  <?php endif; ?>
                </td>
                <td class="text-nowrap small text-muted">
                  <?= !empty($d['published_at']) ? date('d/m/Y', strtotime($d['published_at'])) : (isset($d['created_at']) ? date('d/m/Y', strtotime($d['created_at'])) : '—') ?>
                </td>
                <td>
                  <span class="badge <?= $statutBadge($d['statut']) ?>">
                    <?= $statutLabel($d['statut']) ?>
                  </span>
                </td>
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

    <!-- ONGLE T : PRESTATAIRE (Mes propositions) -->
    <?php if (!empty($isPrestataire)): ?>
      <div class="tab-pane fade <?= $activeTab === 'presta' ? 'show active' : '' ?>" id="presta-pane" role="tabpanel" aria-labelledby="presta-tab" tabindex="0">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
          <div class="input-group" style="max-width: 320px;">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" placeholder="Rechercher..." data-table-search="postulesTable">
          </div>
        </div>

        <?php if (empty($demandesPostulees)): ?>
          <div class="empty-state">
            <i class="bi bi-send-exclamation"></i>
            Vous n'avez pas encore postulé sur des demandes.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-da align-middle mb-0" id="postulesTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Client</th>
                  <th>Titre / Description</th>
                  <th>Ville</th>
                  <th>Ma Proposition</th>
                  <th>Statut Demande</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($demandesPostulees as $dp): ?>
                <?php
                  $propBadge = match ($dp['statut_proposition']) {
                      'retenue'     => 'badge-soft-success',
                      'non_retenue' => 'badge-soft-danger',
                      'retiree'     => 'badge-soft-secondary',
                      'expiree'     => 'badge-soft-danger',
                      default       => 'badge-soft-info',
                  };
                  $propLabel = match ($dp['statut_proposition']) {
                      'retenue'     => 'Retenue',
                      'non_retenue' => 'Non retenue',
                      'retiree'     => 'Retirée',
                      'expiree'     => 'Expirée',
                      default       => 'Envoyée',
                  };
                ?>
                <tr>
                  <td>#<?= (int)$dp['id_demande'] ?></td>
                  <td><?= htmlspecialchars(($dp['client_prenom'] ?? '') . ' ' . ($dp['client_nom'] ?? '')) ?></td>
                  <td>
                    <strong><?= htmlspecialchars(mb_strimwidth($dp['titre'] ?? $dp['description'] ?? '', 0, 45, '…')) ?></strong>
                    <?php if (!empty($dp['category_libelle'])): ?>
                      <div class="small text-muted"><?= htmlspecialchars($dp['category_libelle']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($dp['ville'] ?? '—') ?></td>
                  <td>
                    <span class="badge <?= $propBadge ?>"><?= $propLabel ?></span>
                    <?php if (!empty($dp['prix_indicatif'])): ?>
                      <div class="small text-muted mt-1"><?= number_format((float)$dp['prix_indicatif'], 2, ',', ' ') ?> €</div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge <?= $statutBadge($dp['statut']) ?>">
                      <?= $statutLabel($dp['statut']) ?>
                    </span>
                  </td>
                  <td class="text-end">
                    <a href="index.php?action=demande_show&id=<?= (int)$dp['id_demande'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
