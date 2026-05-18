<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Suivi des Demandes</h2>
<p style="color: #666; margin-bottom: 20px;">Consultation uniquement - vous ne pouvez pas ajouter de demandes.</p>
<table>
  <tr>
    <th>ID</th>
    <th>Description</th>
    <th>Date</th>
    <th>Statut</th>
    <th>Adresse</th>
    <th>Utilisateur</th>
  </tr>
  <?php foreach ($demandes as $d): ?>
    <tr>
      <td><?= (int)$d['id_demande'] ?></td>
      <td><?= htmlspecialchars(substr($d['description'], 0, 50)) ?>...</td>
      <td><?= htmlspecialchars($d['date']) ?></td>
      <td><span style="background: #e7f5ff; padding: 5px 10px; border-radius: 3px;"><?= htmlspecialchars($d['statut']) ?></span></td>
      <td><?= htmlspecialchars($d['adresse']) ?></td>
      <td><?= htmlspecialchars($d['nom'] ?? 'N/A') ?></td>
    </tr>
  <?php endforeach; ?>
</table>
<a href="index.php?action=admin_dashboard" style="margin-top: 20px; display: inline-block;">← Retour</a>
<?php require __DIR__ . '/../layout/footer.php'; ?>
