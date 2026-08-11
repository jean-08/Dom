<?php
$isSelf = (int) $u['id_user'] === (int) ($_SESSION['user']['id_user'] ?? 0);
$isAdminManaging = ($_SESSION['user']['role'] ?? '') === 'admin' && !$isSelf;
$pageTitle = $isAdminManaging ? 'Modifier l\'utilisateur' : 'Mon profil';
$active = $isAdminManaging ? 'admin_users' : 'profile';
$action = $isAdminManaging ? 'user_edit&id=' . (int) $u['id_user'] : 'profile';

require __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="da-card p-4">
      <form method="POST" action="index.php?action=<?= $action ?>">
        <div class="row g-2">
          <div class="col-6 mb-3">
            <label class="form-label">Prénom</label>
            <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($u['prenom']) ?>" required>
          </div>
          <div class="col-6 mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($u['nom']) ?>" required>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($u['email']) ?>" required>
        </div>
        <?php if ($isAdminManaging): ?>
          <div class="mb-3">
            <label class="form-label">Rôle</label>
            <select name="role" class="form-select">
              <option value="client" <?= $u['role'] === 'client' ? 'selected' : '' ?>>Client</option>
              <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
          </div>
        <?php endif; ?>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-brand"><i class="bi bi-check2 me-1"></i>Enregistrer</button>
          <a href="index.php?action=<?= $isAdminManaging ? 'admin_users' : 'dashboard' ?>" class="btn btn-outline-secondary">Annuler</a>
        </div>
      </form>
    </div>

    <!-- <?php if (!$isAdminManaging): ?>
      <div class="da-card p-4 mt-3">
        <h6 class="mb-3">Sécurité</h6>
        <p class="text-muted small mb-0">Pour changer votre mot de passe, contactez un administrateur.</p>
      </div>
    <?php endif; ?> -->
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
