<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Suivi des Services</h2>
<p style="color: #666; margin-bottom: 20px;">Consultation uniquement - vous ne pouvez pas ajouter de services.</p>
<table class="admin-table">
  <tr>
    <th>Nom</th>
    <th>Description</th>
  </tr>
  <?php if (empty($services)): ?>
    <tr><td colspan="2" style="text-align:center; color:#999; padding:20px;">Aucun service enregistré.</td></tr>
  <?php else: ?>
    <?php foreach ($services as $s): ?>
      <tr>
        <td><?= htmlspecialchars($s['nom']) ?></td>
        <td><?= htmlspecialchars($s['description'] ?? 'N/A') ?></td>
      </tr>
    <?php endforeach; ?>
  <?php endif; ?>
</table>
<a href="index.php?action=admin_dashboard" style="margin-top: 20px; display: inline-block;">← Retour</a>
<?php require __DIR__ . '/../layout/footer.php'; ?>
