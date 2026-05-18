<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Demandes</h2>
<a href="index.php?action=demande_create" class="btn">+ Nouvelle demande</a>
<table>
  <tr><th>Description</th><th>Date</th><th>Statut</th><th>Adresse</th><th>Actions</th></tr>
  <?php foreach ($demandes as $d): ?>
  <tr>
    <td><?= htmlspecialchars(substr($d['description'],0,60)) ?>...</td>
    <td><?= htmlspecialchars($d['date']) ?></td>
    <td><span class="badge <?= $d['statut'] ?>"><?= htmlspecialchars($d['statut']) ?></span></td>
    <td><?= htmlspecialchars($d['adresse']) ?></td>
    <td>
      <a href="index.php?action=demande_show&id=<?= $d['id_demande'] ?>">Voir</a>
      <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
      <form method="POST" action="index.php?action=demande_statut" style="display:inline">
        <input type="hidden" name="id_demande" value="<?= $d['id_demande'] ?>">
        <select name="statut">
          <?php foreach (['en_attente','acceptée','refusée','terminée'] as $s): ?>
          <option value="<?= $s ?>" <?= $d['statut']===$s?'selected':'' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit">Mettre à jour</button>
      </form>
      <a href="index.php?action=demande_delete&id=<?= $d['id_demande'] ?>"
         onclick="return confirm('Supprimer ?')">Supprimer</a>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php require __DIR__ . '/../layout/footer.php'; ?>