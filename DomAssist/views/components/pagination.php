<?php
/**
 * Pagination simple côté client (sur tableau déjà chargé) ou côté requête.
 * Attend : $paginationCurrent (int), $paginationTotalPages (int), $paginationAction (string, ex: 'users')
 * Les paramètres GET additionnels doivent déjà être fusionnés dans $paginationExtraQuery (string, ex: '&statut=x').
 */
$paginationCurrent    = $paginationCurrent ?? 1;
$paginationTotalPages = $paginationTotalPages ?? 1;
$paginationAction     = $paginationAction ?? 'dashboard';
$paginationExtraQuery = $paginationExtraQuery ?? '';

if ($paginationTotalPages > 1):
?>
<nav aria-label="pagination" class="mt-3">
  <ul class="pagination pagination-sm justify-content-center mb-0">
    <li class="page-item <?= $paginationCurrent <= 1 ? 'disabled' : '' ?>">
      <a class="page-link" href="index.php?action=<?= htmlspecialchars($paginationAction) ?>&page=<?= max(1, $paginationCurrent - 1) ?><?= $paginationExtraQuery ?>">Précédent</a>
    </li>
    <?php for ($p = 1; $p <= $paginationTotalPages; $p++): ?>
      <li class="page-item <?= $p === $paginationCurrent ? 'active' : '' ?>">
        <a class="page-link" href="index.php?action=<?= htmlspecialchars($paginationAction) ?>&page=<?= $p ?><?= $paginationExtraQuery ?>"><?= $p ?></a>
      </li>
    <?php endfor; ?>
    <li class="page-item <?= $paginationCurrent >= $paginationTotalPages ? 'disabled' : '' ?>">
      <a class="page-link" href="index.php?action=<?= htmlspecialchars($paginationAction) ?>&page=<?= min($paginationTotalPages, $paginationCurrent + 1) ?><?= $paginationExtraQuery ?>">Suivant</a>
    </li>
  </ul>
</nav>
<?php endif; ?>
