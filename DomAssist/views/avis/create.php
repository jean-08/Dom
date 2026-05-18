<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Laisser un avis</h2>
<form method="POST" action="index.php?action=avis_create">
  <input type="hidden" name="id_prestataire" value="<?= (int)($_GET['id_prestataire'] ?? 0) ?>">
  <label>Note (1-5)</label>
  <input type="number" name="note" min="1" max="5" required>
  <label>Commentaire</label>
  <textarea name="comment" rows="3"></textarea>
  <button type="submit">Publier</button>
</form>
<?php require __DIR__ . '/../layout/footer.php'; ?>