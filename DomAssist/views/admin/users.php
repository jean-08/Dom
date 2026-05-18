<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Gestion des Utilisateurs</h2>
<table>
  <tr>
    <th>Nom</th>
    <th>Prénom</th>
    <th>Email</th>
    <th>Rôle</th>
    <th>Actions</th>
  </tr>
  <?php foreach ($users as $u): ?>
    <tr>
      <td><?= htmlspecialchars($u['nom']) ?></td>
      <td><?= htmlspecialchars($u['prenom']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><?= htmlspecialchars($u['role']) ?></td>
      <td>
        <?php if ((int)$u['id_user'] !== (int)($_SESSION['user']['id_user'] ?? 0)): ?>
          <a href="index.php?action=admin_delete_user&id=<?= (int)$u['id_user'] ?>" onclick="return confirm('Supprimer cet utilisateur ?')">🗑️ Supprimer</a>
        <?php else: ?>
          <span style="color: #999;">Vous</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
<a href="index.php?action=admin_dashboard" style="margin-top: 20px; display: inline-block;">← Retour</a>
<?php require __DIR__ . '/../layout/footer.php'; ?>
