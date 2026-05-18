<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Interventions</h2>
<table>
  <tr><th>Date</th><th>Résultat</th><th>Prestataire</th></tr>
  <?php foreach ($interventions as $i): ?>
  <tr>
    <td><?= htmlspecialchars($i['date']) ?></td>
    <td><?= htmlspecialchars(substr($i['resultat'] ?? '-', 0, 60)) ?></td>
    <td><?= htmlspecialchars(($i['nom'] ?? '').' '.($i['prenom'] ?? '')) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php require __DIR__ . '/../layout/footer.php'; ?>