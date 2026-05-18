<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Créer une solution</h2>
<form method="POST" action="index.php?action=solution_create&id_diagnostic=<?= (int)$id_diagnostic ?>">
  <label>Description</label>
  <textarea name="description" required rows="4"></textarea>

  <h3>Produits utilisés</h3>
  <div id="produits-list">
    <div class="produit-row">
      <select name="id_produit[]">
        <option value="">-- Aucun --</option>
        <?php foreach ($produits as $p): ?>
        <option value="<?= $p['id_produit'] ?>"><?= htmlspecialchars($p['nom']) ?> (stock: <?= $p['stock'] ?>)</option>
        <?php endforeach; ?>
      </select>
      <input type="number" name="quantite[]" min="1" value="1" placeholder="Qté">
    </div>
  </div>
  <button type="button" onclick="addProduit()">+ Ajouter produit</button>
  <br><br>
  <button type="submit">Enregistrer la solution</button>
</form>
<br><a href="index.php?action=demandes">← Retour</a>

<script>
function addProduit() {
  var clone = document.querySelector('.produit-row').cloneNode(true);
  clone.querySelector('select').value = '';
  clone.querySelector('input').value = 1;
  document.getElementById('produits-list').appendChild(clone);
}
</script>
<?php require __DIR__ . '/../layout/footer.php'; ?>
