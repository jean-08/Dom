<?php
require_once __DIR__ . '/../../utils/upload.php';
$pageTitle = 'Utilisateurs';
$pageSubtitle = 'Gestion des comptes de la plateforme';
$active = 'admin_users';
require __DIR__ . '/../layouts/header.php';
?>

<div class="da-card p-3 p-md-4">
  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
    <div class="input-group" style="max-width: 320px;">
      <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
      <input type="text" class="form-control" placeholder="Rechercher..." data-table-search="adminUsersTable">
    </div>
  </div>

  <?php if (empty($users)): ?>
    <div class="empty-state">
      <i class="bi bi-people"></i>
      Aucun utilisateur pour le moment.
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-da align-middle mb-0" id="adminUsersTable">
        <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td class="d-flex align-items-center gap-2">
              <?php if (!empty($u['photo_url'])): ?>
                <img src="<?= htmlspecialchars(UploadHelper::getAvatarUrl($u['photo_url'] ?? null, $u['prenom'] ?? 'User', $u['nom'] ?? '')) ?>" alt="Avatar" class="avatar-circle" style="width: 42px; height: 42px; object-fit: cover;">
              <?php else: ?>
                <span class="avatar-circle"><?= htmlspecialchars(strtoupper(mb_substr($u['prenom'], 0, 1) . mb_substr($u['nom'], 0, 1))) ?></span>
              <?php endif; ?>
              <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?>
            </td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="badge <?= $u['role'] === 'admin' ? 'badge-soft-info' : 'badge-soft-secondary' ?> text-capitalize"><?= htmlspecialchars($u['role']) ?></span></td>
            <td class="text-end">
              <a href="index.php?action=user_edit&id=<?= (int) $u['id_user'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
              <?php if ((int) $u['id_user'] !== (int) ($_SESSION['user']['id_user'] ?? 0)): ?>
                <!-- <a href="index.php?action=admin_delete_user&id=<?= (int) $u['id_user'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Supprimer cet utilisateur ?"><i class="bi bi-trash"></i></a> -->
                <?php if (!empty($u['suspendu'])): ?>
                  <form method="POST" action="index.php?action=admin_reactivate_user" class="d-inline">
                    <input type="hidden" name="id" value="<?= (int) $u['id_user'] ?>">
                    <button class="btn btn-sm btn-outline-success" title="Réactiver"><i class="bi bi-unlock"></i></button>
                  </form>
                <?php else: ?>
                  <form method="POST" action="index.php?action=admin_suspend_user" class="d-inline" onsubmit="return confirm('Suspendre cet utilisateur ?');">
                    <input type="hidden" name="id" value="<?= (int) $u['id_user'] ?>">
                    <button class="btn btn-sm btn-outline-warning" title="Suspendre"><i class="bi bi-lock"></i></button>
                  </form>
                <?php endif; ?>
                <a href="index.php?action=admin_delete_user&id=<?= (int) $u['id_user'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Supprimer cet utilisateur ?"><i class="bi bi-trash"></i></a>              
                <?php else: ?>
                <span class="badge badge-soft-secondary">vous</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <?php
  // Pagination (BUG-010 fix)
  $paginationCurrent    = $currentPage ?? 1;
  $paginationTotalPages = $totalPages  ?? 1;
  $paginationAction     = 'admin_users';
  require __DIR__ . '/../components/pagination.php';
  ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
