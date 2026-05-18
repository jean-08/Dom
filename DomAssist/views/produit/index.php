<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Produits</h2>
<?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
<form method="POST" action="index.php?action=produit_create">
  <input type="text" name="nom" placeholder="Nom" required>
  <input type="number" name="prix" placeholder="Prix" step="0.01" required>
  <input type="number" name="stock" placeholder="Stock" required>
  <button type="submit">Ajouter</button>
</form>
<?php endif; ?>
<table>
  <tr><th>Nom</th><th>Prix</th><th>Stock</th><th>Statut</th><th>Action</th></tr>
  <?php foreach ($produits as $p): ?>
  <tr>
    <td><?= htmlspecialchars($p['nom']) ?></td>
    <td><?= number_format($p['prix'],2) ?> €</td>
    <td><?= (int)$p['stock'] ?></td>
    <td><?= htmlspecialchars($p['statut']) ?></td>
    <td>
      <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
      <a href="index.php?action=produit_delete&id=<?= $p['id_produit'] ?>"
         onclick="return confirm('Supprimer ?')">Supprimer</a>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php require __DIR__ . '/../layout/footer.php'; ?>