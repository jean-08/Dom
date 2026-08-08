<?php
/**
 * Affiche les messages flash stockés en session ($_SESSION['success'] / $_SESSION['error'])
 * sous forme de toasts Bootstrap. Consomme (unset) les messages après affichage.
 */
$da_success = $_SESSION['success'] ?? null;
$da_error   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<?php if ($da_success || $da_error): ?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
  <?php if ($da_success): ?>
  <div class="toast align-items-center text-white bg-success border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body">
        <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($da_success) ?>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <?php endif; ?>
  <?php if ($da_error): ?>
  <div class="toast align-items-center text-white bg-danger border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body">
        <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($da_error) ?>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>
