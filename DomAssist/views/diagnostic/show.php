<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Diagnostic</h2>
<?php if ($diagnostic): ?>
  <p><strong>Description :</strong> <?= htmlspecialchars($diagnostic['description']) ?></p>
  <p><strong>Résultat :</strong> <?= htmlspecialchars($diagnostic['resultat'] ?? '-') ?></p>
  <p><strong>Date :</strong> <?= htmlspecialchars($diagnostic['date']) ?></p>
<?php else: ?>
  <p>Aucun diagnostic pour cette demande.</p>
<?php endif; ?>
<a href="index.php?action=demandes">← Retour</a>
<?php require __DIR__ . '/../layout/footer.php'; ?>