<?php
$pageTitle = 'Centre de notifications';
$active    = 'notifications';

$typeIcon = function (string $type): string {
    return match ($type) {
        'NOUVELLE_PROPOSITION' => 'bi-envelope-paper text-primary',
        'PROPOSITION_RETENUE'  => 'bi-trophy text-warning',
        'PROPOSITION_NON_RETENUE' => 'bi-x-circle text-muted',
        'ENGAGEMENT_CONFIRME'  => 'bi-hand-thumbs-up text-success',
        'DIAGNOSTIC_PUBLIE'    => 'bi-stethoscope text-info',
        'SOLUTION_PROPOSEE'    => 'bi-lightbulb text-warning',
        'SOLUTION_VALIDEE'     => 'bi-check-circle text-success',
        'SOLUTION_REFUSEE'     => 'bi-dash-circle text-danger',
        'INTERVENTION_TERMINEE'=> 'bi-check2-all text-success',
        'CANDIDATURE_VALIDEE'  => 'bi-patch-check text-success',
        'CANDIDATURE_REJETEE'  => 'bi-exclamation-triangle text-danger',
        'COMPTE_SUSPENDU'      => 'bi-slash-circle text-danger',
        'COMPTE_REACTIVE'      => 'bi-unlock text-success',
        'NOUVELLE_CANDIDATURE' => 'bi-person-plus text-primary',
        'NOUVEAU_SIGNALEMENT'  => 'bi-flag text-danger',
        default                => 'bi-bell text-secondary',
    };
};

$breadcrumb = [
    ['label' => 'Dashboard', 'action' => 'dashboard'],
    ['label' => 'Notifications'],
];
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../components/breadcrumb.php';
?>

<div class="row g-3 justify-content-center">
  <div class="col-lg-10">

    <div class="da-card p-4 mb-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
          <h5 class="mb-1"><i class="bi bi-bell me-2"></i>Vos notifications</h5>
          <p class="text-muted small mb-0">Restez informé des propositions, messages et étapes clés de vos demandes.</p>
        </div>
        <?php if (!empty($unreadCount) && $unreadCount > 0): ?>
          <form method="POST" action="index.php?action=notification_mark_all_read">
            <button type="submit" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-check2-all me-1"></i>Tout marquer comme lu
            </button>
          </form>
        <?php endif; ?>
      </div>

      <?php if (empty($notifications)): ?>
        <div class="empty-state">
          <i class="bi bi-bell-slash"></i>
          <h6>Aucune notification pour l'instant</h6>
          <p class="text-muted small">Vous recevrez des alertes ici lors des événements majeurs de vos projets.</p>
        </div>
      <?php else: ?>
        <div class="list-group list-group-flush border rounded">
          <?php foreach ($notifications as $n): ?>
            <?php $isUnread = empty($n['lu']); ?>
            <div class="list-group-item p-3 d-flex align-items-start gap-3 <?= $isUnread ? 'bg-light' : '' ?>">
              <div class="icon-box rounded p-2 bg-white border shadow-sm fs-5">
                <i class="bi <?= $typeIcon($n['type']) ?>"></i>
              </div>

              <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="fw-semibold <?= $isUnread ? 'text-dark' : 'text-secondary' ?>">
                    <?= htmlspecialchars($n['titre']) ?>
                  </span>
                  <span class="small text-muted" style="font-size: 0.78rem;">
                    <?= date('d/m/Y à H:i', strtotime($n['created_at'])) ?>
                  </span>
                </div>

                <?php if (!empty($n['corps'])): ?>
                  <p class="mb-2 small text-muted"><?= htmlspecialchars($n['corps']) ?></p>
                <?php endif; ?>

                <div class="d-flex gap-2 align-items-center mt-2">
                  <?php if (!empty($n['lien_ressource'])): ?>
                    <a href="<?= htmlspecialchars($n['lien_ressource']) ?>" class="btn btn-xs btn-brand btn-sm">
                      Voir la ressource <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                  <?php endif; ?>

                  <?php if ($isUnread): ?>
                    <form method="POST" action="index.php?action=notification_mark_read" class="d-inline">
                      <input type="hidden" name="id_notification" value="<?= (int)$n['id_notification'] ?>">
                      <button type="submit" class="btn btn-sm btn-link text-decoration-none text-muted small p-0 ms-2">
                        <i class="bi bi-check me-1"></i>Marquer comme lue
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
