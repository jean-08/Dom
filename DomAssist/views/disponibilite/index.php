<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Mes disponibilités</h2>
<form method="POST" action="index.php?action=disponibilite_create">
  <label>Date</label><input type="date" name="date" required>
  <label>Début</label><input type="time" name="heure_debut" required>
  <label>Fin</label><input type="time" name="heure_fin" required>
  <button type="submit">Ajouter</button>
</form>
<table>
  <tr><th>Date</th><th>Début</th><th>Fin</th><th>Statut</th><th>Action</th></tr>
  <?php foreach ($dispos as $d): ?>
  <tr>
    <td><?= htmlspecialchars($d['date']) ?></td>
    <td><?= htmlspecialchars($d['heure_debut']) ?></td>
    <td><?= htmlspecialchars($d['heure_fin']) ?></td>
    <td><?= htmlspecialchars($d['statut']) ?></td>
    <td><a href="index.php?action=disponibilite_delete&id=<?= $d['id_dispo'] ?>"
           onclick="return confirm('Supprimer ?')">Supprimer</a></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php require __DIR__ . '/../layout/footer.php'; ?>