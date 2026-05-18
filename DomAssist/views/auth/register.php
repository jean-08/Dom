<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Inscription</h2>
<form method="POST" action="index.php?action=register">
  <label>Nom</label>
  <input type="text" name="nom" required>
  <label>Prénom</label>
  <input type="text" name="prenom" required>
  <label>Email</label>
  <input type="email" name="email" required>
  <label>Mot de passe</label>
  <input type="password" name="mot_de_passe" required minlength="6">
  <label>Rôle</label>
  <select name="role" required>
      <option value="client">Client</option>
      <option value="prestataire">Prestataire</option>
  </select>
  <button type="submit">S'inscrire</button>
</form>
<p><a href="index.php?action=login">Déjà un compte ?</a></p>
<?php require __DIR__ . '/../layout/footer.php'; ?>