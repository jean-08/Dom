<?php
/**
 * Attend $breadcrumb = [ ['label' => 'Accueil', 'action' => 'dashboard'], ['label' => 'Page courante'] ]
 * Le dernier élément (sans 'action') est affiché en texte actif.
 */
if (!empty($breadcrumb)):
?>
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0 small">
    <?php foreach ($breadcrumb as $i => $crumb): ?>
      <?php if (!empty($crumb['action']) && $i !== array_key_last($breadcrumb)): ?>
        <li class="breadcrumb-item"><a href="index.php?action=<?= htmlspecialchars($crumb['action']) ?>" class="text-decoration-none"><?= htmlspecialchars($crumb['label']) ?></a></li>
      <?php else: ?>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($crumb['label']) ?></li>
      <?php endif; ?>
    <?php endforeach; ?>
  </ol>
</nav>
<?php endif; ?>
