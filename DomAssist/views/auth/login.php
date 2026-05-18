<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Connexion</h2>
<form method="POST" action="index.php?action=login">
  <label>Email</label>
  <input type="email" name="email" required>
  <label>Mot de passe</label>
  <input type="password" name="mot_de_passe" required>
  <button type="submit">Se connecter</button>
</form>
<p><a href="index.php?action=register">Créer un compte</a></p>
<?php require __DIR__ . '/../layout/footer.php'; ?>