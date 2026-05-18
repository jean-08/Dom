<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Créer un diagnostic</h2>
<form method="POST" action="index.php?action=diagnostic_create">
  <input type="hidden" name="id_demande" value="<?= (int)$id_demande ?>">
  <input type="hidden" name="id_prestataire" value="<?= (int)($prest['id_prestataire'] ?? 0) ?>">
  <label>Description</label>
  <textarea name="description" required rows="3"></textarea>
  <label>Résultat</label>
  <textarea name="resultat" rows="3"></textarea>
  <button type="submit">Enregistrer</button>
</form>
<?php require __DIR__ . '/../layout/footer.php'; ?>