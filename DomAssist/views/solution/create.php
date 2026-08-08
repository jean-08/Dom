<?php
$pageTitle = 'Proposer une solution';
$active = 'demandes';
require __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="da-card p-4">
      <form method="POST" action="index.php?action=solution_create&id_diagnostic=<?= (int) $id_diagnostic ?>">
        <div class="mb-3">
          <label class="form-label">Description de la solution</label>
          <textarea name="description" class="form-control" rows="4" placeholder="Détaillez l'intervention proposée..." required></textarea>
        </div>

        <label class="form-label">Produits utilisés (optionnel)</label>
        <div id="produitsRows">
          <div class="row g-2 mb-2 produit-row">
            <div class="col-7">
              <select name="id_produit[]" class="form-select form-select-sm">
                <option value="">— Aucun —</option>
                <?php foreach (($produits ?? []) as $p): ?>
                  <option value="<?= (int) $p['id_produit'] ?>">
                    <?= htmlspecialchars($p['nom']) ?> (stock : <?= (int) $p['stock'] ?>, <?= number_format((float) $p['prix'], 2) ?> €)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-3">
              <input type="number" name="quantite[]" class="form-control form-control-sm" min="1" value="1" placeholder="Quantité">
            </div>
          </div>
        </div>
        <button type="button" id="addProduitRow" class="btn btn-sm btn-outline-secondary mb-3">
          <i class="bi bi-plus-lg me-1"></i>Ajouter un produit
        </button>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-brand"><i class="bi bi-check2 me-1"></i>Enregistrer la solution</button>
          <a href="index.php?action=diagnostic_show&id_demande=<?= (int) ($diagnostic['id_demande'] ?? 0) ?>" class="btn btn-outline-secondary">Annuler</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('addProduitRow').addEventListener('click', function () {
  var rows = document.getElementById('produitsRows');
  var clone = rows.querySelector('.produit-row').cloneNode(true);
  clone.querySelectorAll('select, input').forEach(function (el) {
    if (el.tagName === 'SELECT') el.selectedIndex = 0; else el.value = 1;
  });
  rows.appendChild(clone);
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
