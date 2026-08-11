<?php
/**
 * Modal générique. Attend :
 * $modalId (string), $modalTitle (string), $modalBody (HTML string, déjà échappé si besoin),
 * $modalFooter (HTML string, optionnel — sinon un bouton Fermer par défaut).
 */
$modalId     = $modalId ?? 'daModal';
$modalTitle  = $modalTitle ?? 'Fenêtre';
$modalBody   = $modalBody ?? '';
$modalFooter = $modalFooter ?? '<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>';
?>
<div class="modal fade" id="<?= htmlspecialchars($modalId) ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= htmlspecialchars($modalTitle) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body"><?= $modalBody ?></div>
      <div class="modal-footer"><?= $modalFooter ?></div>
    </div>
  </div>
</div>
