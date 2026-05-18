<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Prestataires</h2>
<?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
<a href="index.php?action=prestataire_create" class="btn">+ Ajouter</a>
<?php endif; ?>
<table>
  <tr><th>Nom</th><th>Spécialité</th><th>Email</th><th>Actions</th></tr>
  <?php foreach ($prestataires as $p): ?>
  <tr>
    <td><?= htmlspecialchars($p['nom'].' '.$p['prenom']) ?></td>
    <td><?= htmlspecialchars($p['specialite']) ?></td>
    <td><?= htmlspecialchars($p['email']) ?></td>
    <td>
      <a href="index.php?action=prestataire_show&id=<?= $p['id_prestataire'] ?>">Voir</a>
      <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
      <a href="index.php?action=prestataire_edit&id=<?= $p['id_prestataire'] ?>">Modifier</a>
      <a href="index.php?action=prestataire_delete&id=<?= $p['id_prestataire'] ?>"
         onclick="return confirm('Supprimer ?')">Supprimer</a>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php require __DIR__ . '/../layout/footer.php'; ?>