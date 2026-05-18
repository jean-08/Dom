<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Nouvelle demande</h2>
<form method="POST" action="index.php?action=demande_create">
  <label>Description</label>
  <textarea name="description" required rows="4"></textarea>
  <label>Adresse</label>
  <input type="text" name="adresse" required>
  <button type="submit">Envoyer</button>
</form>
<?php require __DIR__ . '/../layout/footer.php'; ?>