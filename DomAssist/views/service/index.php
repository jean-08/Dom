<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Services</h2>
<?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
<form method="POST" action="index.php?action=service_create">
  <input type="text" name="nom" placeholder="Nom du service" required>
  <textarea name="description" placeholder="Description"></textarea>
  <button type="submit">Ajouter</button>
</form>
<?php endif; ?>
<table>
  <tr><th>Nom</th><th>Description</th><th>Action</th></tr>
  <?php foreach ($services as $s): ?>
  <tr>
    <td><?= htmlspecialchars($s['nom']) ?></td>
    <td><?= htmlspecialchars($s['description'] ?? '') ?></td>
    <td>
      <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
      <a href="index.php?action=service_delete&id=<?= $s['id_service'] ?>"
         onclick="return confirm('Supprimer ?')">Supprimer</a>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php require __DIR__ . '/../layout/footer.php'; ?>