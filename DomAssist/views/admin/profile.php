<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Mon Profil Admin</h2>
<p style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
  ℹ️ Vous ne pouvez modifier que votre mot de passe.
</p>

<form method="POST" style="max-width: 400px;">
  <h3>Informations personnelles</h3>
  <p><strong>Nom :</strong> <?= htmlspecialchars($admin['nom']) ?></p>
  <p><strong>Prénom :</strong> <?= htmlspecialchars($admin['prenom']) ?></p>
  <p><strong>Email :</strong> <?= htmlspecialchars($admin['email']) ?></p>
  <p><strong>Rôle :</strong> <?= htmlspecialchars($admin['role']) ?></p>

  <hr>

  <h3>Changer le mot de passe</h3>
  
  <label>Ancien mot de passe</label>
  <input type="password" name="ancien_mdp" required style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;">

  <label>Nouveau mot de passe</label>
  <input type="password" name="nouveau_mdp" required style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;">

  <label>Confirmer le mot de passe</label>
  <input type="password" name="confirmer_mdp" required style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;">

  <button type="submit" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer;">Modifier le mot de passe</button>
</form>

<a href="index.php?action=admin_dashboard" style="margin-top: 20px; display: inline-block;">← Retour</a>
<?php require __DIR__ . '/../layout/footer.php'; ?>
