<?php
require_once __DIR__ . '/../../utils/upload.php';
$da_user = $_SESSION['user'] ?? ['nom' => '', 'prenom' => '', 'role' => ''];
$da_initials = strtoupper(mb_substr($da_user['prenom'] ?? '', 0, 1) . mb_substr($da_user['nom'] ?? '', 0, 1));
$da_avatarUrl = UploadHelper::getAvatarUrl($da_user['photo_url'] ?? null, $da_user['prenom'] ?? 'User', $da_user['nom'] ?? '');

$da_nbNotifs = 0;
$da_notifs   = [];
if (!empty($da_user['id_user'])) {
    require_once __DIR__ . '/../../models/Notification.php';
    $notifModel  = new Notification();
    $da_nbNotifs = $notifModel->countUnread((int)$da_user['id_user']);
    $da_notifs   = $notifModel->byUser((int)$da_user['id_user'], 5);
}
?>
<div class="da-topbar">
  <div class="d-flex align-items-center gap-3">
    <button class="btn btn-outline-secondary toggle-btn" data-toggle-sidebar type="button">
      <i class="bi bi-list"></i>
    </button>
    <div>
      <div class="da-page-title h5 mb-0"><?= htmlspecialchars($pageTitle ?? 'DomAssist') ?></div>
      <?php if (!empty($pageSubtitle)): ?>
        <div class="da-page-subtitle"><?= htmlspecialchars($pageSubtitle) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="d-flex align-items-center gap-2">

    <!-- Centre de notifications in-app -->
    <?php if (!empty($da_user['id_user'])): ?>
      <?php if (!empty($da_propositions_count) && !empty($da_propositions_url)): ?>
        <a href="<?= htmlspecialchars($da_propositions_url) ?>" class="btn btn-light border position-relative p-2 rounded-circle d-flex align-items-center justify-content-center"
           style="width: 40px; height: 40px; margin-right: 0.5rem;"
           title="Mes propositions">
          <i class="bi bi-send-fill text-secondary fs-5"></i>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success" style="font-size: 0.65rem;">
            <?= $da_propositions_count > 99 ? '99+' : $da_propositions_count ?>
          </span>
        </a>
      <?php endif; ?>
      <div class="dropdown">
        <button class="btn btn-light border position-relative p-2 rounded-circle d-flex align-items-center justify-content-center"
                style="width: 40px; height: 40px;"
                type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
          <i class="bi bi-bell text-secondary fs-5"></i>
          <?php if ($da_nbNotifs > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
              <?= $da_nbNotifs > 99 ? '99+' : $da_nbNotifs ?>
            </span>
          <?php endif; ?>
        </button>
        <div class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="width: 320px; max-width: 90vw;">
          <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
            <span class="fw-semibold small"><i class="bi bi-bell me-1"></i>Notifications</span>
            <?php if ($da_nbNotifs > 0): ?>
              <form method="POST" action="index.php?action=notification_mark_all_read" class="d-inline">
                <button class="btn btn-link btn-sm p-0 text-decoration-none small text-muted">Tout marquer comme lu</button>
              </form>
            <?php endif; ?>
          </div>

          <div style="max-height: 300px; overflow-y: auto;">
            <?php if (empty($da_notifs)): ?>
              <div class="p-3 text-center text-muted small"><i class="bi bi-bell-slash d-block fs-5 mb-1 text-muted"></i>Aucune notification</div>
            <?php else: ?>
              <?php foreach ($da_notifs as $n): ?>
                <a href="<?= htmlspecialchars($n['lien_ressource'] ?? 'index.php?action=notifications') ?>"
                   class="dropdown-item p-2 px-3 border-bottom <?= empty($n['lu']) ? 'bg-light font-weight-bold' : '' ?>" style="white-space: normal;">
                  <div class="d-flex justify-content-between align-items-start">
                    <span class="fw-semibold small text-primary mb-1"><?= htmlspecialchars($n['titre']) ?></span>
                    <?php if (empty($n['lu'])): ?>
                      <span class="badge bg-primary rounded-circle p-1 ms-1" style="width: 6px; height: 6px;"></span>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($n['corps'])): ?>
                    <div class="small text-muted" style="font-size: 0.8rem; line-height: 1.25;"><?= htmlspecialchars($n['corps']) ?></div>
                  <?php endif; ?>
                  <div class="text-end small text-muted mt-1" style="font-size: 0.68rem;"><?= date('d/m à H:i', strtotime($n['created_at'])) ?></div>
                </a>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <div class="p-2 text-center border-top bg-light">
            <a href="index.php?action=notifications" class="small text-primary text-decoration-none fw-semibold">Voir toutes les notifications</a>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Menu Profil Utilisateur -->
    <div class="dropdown">
      <button class="btn btn-light d-flex align-items-center gap-2 border" type="button" data-bs-toggle="dropdown">
        <?php if (!empty($da_user['photo_url'])): ?>
          <img src="<?= htmlspecialchars($da_avatarUrl) ?>" alt="Avatar" class="avatar-circle" style="width: 42px; height: 42px; object-fit: cover;">
        <?php else: ?>
          <span class="avatar-circle"><?= htmlspecialchars($da_initials ?: '?') ?></span>
        <?php endif; ?>
        <span class="d-none d-sm-inline"><?= htmlspecialchars(($da_user['prenom'] ?? '') . ' ' . ($da_user['nom'] ?? '')) ?></span>
        <i class="bi bi-chevron-down small"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <li><span class="dropdown-item-text small text-muted text-capitalize"><?= htmlspecialchars($da_user['role'] ?? '') ?></span></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="index.php?action=notifications"><i class="bi bi-bell me-2"></i>Notifications</a></li>
        <li><a class="dropdown-item" href="index.php?action=<?= ($da_user['role'] ?? '') === 'admin' ? 'admin_profile' : 'profile' ?>"><i class="bi bi-person-fill-gear me-2"></i>Mon profil</a></li>
        <li><a class="dropdown-item text-danger" href="index.php?action=logout" data-confirm="Se déconnecter ?"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
      </ul>
    </div>

  </div>
</div>
