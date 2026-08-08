<?php
$pageTitle = 'Dashboard admin';
$pageSubtitle = 'Vue d\'ensemble de la plateforme';
$active = 'admin_dashboard';

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

<div class="row g-3 mb-4">
<div class="col-sm-6 col-lg-3">
    <div class="da-stat-card">
      <div class="icon-box bg-brand-blue"><i class="bi bi-people-fill"></i></div>
      <div><div class="value"><?= (int) $totalClients ?></div><div class="label">Clients</div></div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="da-stat-card">
      <div class="icon-box bg-brand-amber"><i class="bi bi-clipboard2-check"></i></div>
      <div><div class="value"><?= (int) $totalDemandes ?></div><div class="label">Demandes</div></div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="da-stat-card">
      <div class="icon-box bg-brand-purple"><i class="bi bi-tools"></i></div>
      <div><div class="value"><?= (int) $totalPrestataires ?></div><div class="label">Prestataires</div></div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="da-stat-card">
      <div class="icon-box bg-brand-green"><i class="bi bi-tools"></i></div>
      <div><div class="value"><?= (int) $totalInterventions ?></div><div class="label">Interventions</div></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="da-card p-3 p-md-4">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0">Dernières demandes</h5>
        <a href="index.php?action=admin_suivi_demandes" class="small text-decoration-none">Voir tout <i class="bi bi-arrow-right"></i></a>
      </div>
      <?php if (empty($demandes)): ?>
        <div class="empty-state"><i class="bi bi-inbox"></i>Aucune demande.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-da align-middle mb-0">
            <thead><tr><th>#</th><th>Description</th><th>Statut</th></tr></thead>
            <tbody>
            <?php foreach (array_slice($demandes, 0, 6) as $d): ?>
              <tr>
                <td><a href="index.php?action=demande_show&id=<?= (int) $d['id_demande'] ?>">#<?= (int) $d['id_demande'] ?></a></td>
                <td><?= htmlspecialchars(mb_strimwidth($d['description'], 0, 40, '…')) ?></td>
                <td><span class="badge <?= $statutBadge($d['statut']) ?>"><?= htmlspecialchars($d['statut']) ?></span></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="da-card p-3 p-md-4">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0">Candidatures en attente</h5>
        <a href="index.php?action=admin_prestataires_en_attente" class="small text-decoration-none">Voir tout <i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="d-grid gap-2">
        <a href="index.php?action=admin_prestataires_en_attente" class="btn btn-outline-secondary btn-sm text-start">
          <i class="bi bi-person-badge me-2"></i>Gérer les candidatures prestataire
        </a>
        <a href="index.php?action=admin_users" class="btn btn-outline-secondary btn-sm text-start">
          <i class="bi bi-people me-2"></i>Gérer les utilisateurs
        </a>
        <a href="index.php?action=services" class="btn btn-outline-secondary btn-sm text-start">
          <i class="bi bi-tags me-2"></i>Gérer les catégories de services
        </a>
        <a href="index.php?action=produits" class="btn btn-outline-secondary btn-sm text-start">
          <i class="bi bi-box-seam me-2"></i>Gérer le catalogue produits
        </a>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
