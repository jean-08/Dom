<?php
$pageTitle = 'Dashboard';
$pageSubtitle = 'Bienvenue, ' . ($_SESSION['user']['prenom'] ?? '');
$active = 'dashboard';

$statutBadge = function (string $statut): string {
    return match ($statut) {
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
};

require __DIR__ . '/layouts/header.php';
?>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="da-stat-card">
      <div class="icon-box bg-brand-blue"><i class="bi bi-clipboard2-check"></i></div>
      <div>
        <div class="value"><?= count($mesDemandes) ?></div>
        <div class="label">Mes demandes</div>
      </div>
    </div>
  </div>
  <?php if ($profilPrestataire): ?>
    <div class="col-sm-6 col-lg-3">
      <div class="da-stat-card">
        <div class="icon-box bg-brand-amber"><i class="bi bi-inboxes"></i></div>
        <div>
          <div class="value"><?= count($demandesDisponibles) ?></div>
          <div class="label">Demandes disponibles</div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="da-stat-card">
        <div class="icon-box bg-brand-green"><i class="bi bi-tools"></i></div>
        <div>
          <div class="value"><?= count($mesInterventions) ?></div>
          <div class="label">Interventions</div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <a href="index.php?action=prestataire_show&id=<?= (int)$profilPrestataire['id_prestataire'] ?>" class="text-decoration-none">
        <div class="da-stat-card">
          <div class="icon-box bg-brand-purple"><i class="bi bi-star-fill"></i></div>
          <div>
            <div class="value"><?= number_format((float) $reputation['note_moyenne'], 1) ?> / 5</div>
            <div class="label"><?= (int) $reputation['nombre_avis'] ?> avis reçus <i class="bi bi-arrow-right small"></i></div>
          </div>
        </div>
      </a>
    </div>
  <?php else: ?>
    <div class="col-sm-6 col-lg-3">
      <a href="index.php?action=demande_create" class="text-decoration-none">
        <div class="da-stat-card">
          <div class="icon-box bg-brand-amber"><i class="bi bi-plus-circle"></i></div>
          <div>
            <div class="value">Nouvelle</div>
            <div class="label">Créer une demande</div>
          </div>
        </div>
      </a>
    </div>
    <div class="col-sm-6 col-lg-6">
      <a href="index.php?action=prestataire_candidater" class="text-decoration-none">
        <div class="da-stat-card">
          <div class="icon-box bg-brand-green"><i class="bi bi-briefcase-fill"></i></div>
          <div>
            <div class="value">Devenir prestataire</div>
            <div class="label">Proposez vos services sur DomAssist</div>
          </div>
        </div>
      </a>
    </div>
  <?php endif; ?>
</div>

<?php if ($profilPrestataire && in_array(($profilPrestataire['statut_validation'] ?? ''), ['soumise', 'en_attente'])): ?>
  <div class="alert alert-warning d-flex align-items-center gap-2">
    <i class="bi bi-hourglass-split"></i>
    <div>Votre candidature prestataire (spécialité : <strong><?= htmlspecialchars($profilPrestataire['specialite']) ?></strong>) est en attente de validation par un administrateur.</div>
  </div>
<?php elseif ($profilPrestataire && in_array(($profilPrestataire['statut_validation'] ?? ''), ['rejetee', 'rejete'])): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2">
    <i class="bi bi-x-circle"></i>
    <div>Votre candidature prestataire a été rejetée<?= !empty($profilPrestataire['motif_rejet']) ? ' : ' . htmlspecialchars($profilPrestataire['motif_rejet']) : '.' ?></div>
  </div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-<?= $profilPrestataire ? '6' : '12' ?>">
    <div class="da-card p-3 p-md-4">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0">Mes dernières demandes</h5>
        <a href="index.php?action=demandes" class="small text-decoration-none">Voir tout <i class="bi bi-arrow-right"></i></a>
      </div>
      <?php if (empty($mesDemandes)): ?>
        <div class="empty-state">
          <i class="bi bi-inbox"></i>
          Vous n'avez pas encore fait de demande.
          <div class="mt-3"><a href="index.php?action=demande_create" class="btn btn-brand btn-sm">Créer une demande</a></div>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-da align-middle mb-0">
            <thead><tr><th>Titre</th><th>Statut</th><th></th></tr></thead>
            <tbody>
            <?php foreach (array_slice($mesDemandes, 0, 5) as $d): ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($d['titre'] ?? mb_strimwidth($d['description'], 0, 30, '…')) ?></strong>
                  <div class="small text-muted"><?= htmlspecialchars($d['category_libelle'] ?? '') ?></div>
                </td>
                <td><span class="badge <?= $statutBadge($d['statut']) ?>"><?= htmlspecialchars($d['statut']) ?></span></td>
                <td class="text-end"><a href="index.php?action=demande_show&id=<?= (int) $d['id_demande'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($profilPrestataire && in_array(($profilPrestataire['statut_validation'] ?? ''), ['validee', 'valide'])): ?>
  <div class="col-lg-6">
    <div class="da-card p-3 p-md-4">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0">Demandes disponibles pour vous</h5>
        <a href="index.php?action=demandes_disponibles" class="small text-decoration-none">Voir tout <i class="bi bi-arrow-right"></i></a>
      </div>
      <?php if (empty($demandesDisponibles)): ?>
        <div class="empty-state">
          <i class="bi bi-check2-circle"></i>
          Aucune nouvelle demande correspondant à vos compétences pour le moment.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-da align-middle mb-0">
            <thead><tr><th>Titre / Ville</th><th>Urgence</th><th></th></tr></thead>
            <tbody>
            <?php foreach (array_slice($demandesDisponibles, 0, 5) as $d): ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($d['titre'] ?? mb_strimwidth($d['description'], 0, 30, '…')) ?></strong>
                  <div class="small text-muted"><?= htmlspecialchars($d['ville'] ?? $d['adresse'] ?? '') ?></div>
                </td>
                <td>
                  <?php if (($d['urgence'] ?? '') === 'urgent'): ?>
                    <span class="badge bg-danger">Urgent</span>
                  <?php elseif (($d['urgence'] ?? '') === 'sous_48h'): ?>
                    <span class="badge bg-warning text-dark">Sous 48h</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Normal</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <a href="index.php?action=demande_show&id=<?= (int) $d['id_demande'] ?>" class="btn btn-sm btn-brand">
                    <i class="bi bi-send me-1"></i>Proposer
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/layouts/footer.php'; ?>
