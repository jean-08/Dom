<?php
$pageTitle = 'Mon profil';
$pageSubtitle = 'Compte administrateur';
$active = 'admin_profile';
require __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="da-card p-4 mb-3">
      <h6 class="mb-3">Informations</h6>
      <p class="mb-1"><strong>Nom :</strong> <?= htmlspecialchars($admin['prenom'] . ' ' . $admin['nom']) ?></p>
      <p class="mb-0"><strong>Email :</strong> <?= htmlspecialchars($admin['email']) ?></p>
    </div>

    <div class="da-card p-4">
      <h6 class="mb-3">Changer le mot de passe</h6>
      <form method="POST" action="index.php?action=admin_profile">
        <div class="mb-3">
          <label class="form-label">Ancien mot de passe</label>
          <input type="password" name="ancien_mdp" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Nouveau mot de passe</label>
          <input type="password" name="nouveau_mdp" class="form-control" minlength="6" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirmer le nouveau mot de passe</label>
          <input type="password" name="confirmer_mdp" class="form-control" minlength="6" required>
        </div>
        <button type="submit" class="btn btn-brand"><i class="bi bi-shield-check me-1"></i>Mettre à jour</button>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
