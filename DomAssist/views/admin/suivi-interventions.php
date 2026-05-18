<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Suivi des Interventions</h2>
<p style="color: #666; margin-bottom: 20px;">Consultation uniquement - vous ne pouvez pas ajouter d'interventions.</p>
<table class="admin-table">
  <tr>
    <th>Résultat</th>
    <th>Date</th>
    <th>Prestataire</th>
  </tr>
  <?php if (empty($interventions)): ?>
    <tr><td colspan="3" style="text-align:center; color:#999; padding:20px;">Aucune intervention enregistrée.</td></tr>
  <?php else: ?>
    <?php foreach ($interventions as $i): ?>
      <tr>
        <td><?= htmlspecialchars(substr($i['resultat'] ?? '', 0, 50)) ?></td>
        <td><?= htmlspecialchars($i['date']) ?></td>
        <td><?= htmlspecialchars(($i['nom'] ?? '') . ' ' . ($i['prenom'] ?? '')) ?></td>
      </tr>
    <?php endforeach; ?>
  <?php endif; ?>
</table>
<a href="index.php?action=admin_dashboard" style="margin-top: 20px; display: inline-block;">← Retour</a>
<?php require __DIR__ . '/../layout/footer.php'; ?>
