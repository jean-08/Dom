<?php
$pageTitle = 'Demandes disponibles';
$pageSubtitle = 'Demandes correspondant à vos compétences';
$active = 'prestataire_demandes';
require __DIR__ . '/../layouts/header.php';
?>

<div class="da-card p-3 p-md-4">
  <?php if (empty($demandes)): ?>
    <div class="empty-state">
      <i class="bi bi-inboxes"></i>
      Aucune demande disponible pour vos compétences actuellement.
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-da align-middle mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Catégorie</th>
            <th>Titre</th>
            <th>Urgence</th>
            <th>Ville</th>
            <th>Budget</th>
            <th>Publiée le</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($demandes as $d): ?>
          <?php
            $urgenceBadge = match ($d['urgence'] ?? 'normal') {
                'urgent'   => 'badge-soft-danger',
                'sous_48h' => 'badge-soft-warning',
                default    => 'badge-soft-secondary',
            };
            $urgenceLabel = match ($d['urgence'] ?? 'normal') {
                'urgent'   => 'Urgent',
                'sous_48h' => 'Sous 48h',
                default    => 'Normal',
            };
            $budget = '';
            if (!empty($d['budget_min']) && !empty($d['budget_max'])) {
                $budget = number_format((float)$d['budget_min'], 0, ',', ' ') . '–' . number_format((float)$d['budget_max'], 0, ',', ' ') . ' €';
            } elseif (!empty($d['budget_max'])) {
                $budget = '≤ ' . number_format((float)$d['budget_max'], 0, ',', ' ') . ' €';
            } elseif (!empty($d['budget_min'])) {
                $budget = '≥ ' . number_format((float)$d['budget_min'], 0, ',', ' ') . ' €';
            }
          ?>
          <tr>
            <td>#<?= (int)$d['id_demande'] ?></td>
            <td><span class="badge badge-soft-info"><?= htmlspecialchars($d['category_libelle'] ?? $d['libelle'] ?? '—') ?></span></td>
            <td><?= htmlspecialchars(mb_strimwidth($d['titre'] ?? $d['description'] ?? '', 0, 55, '…')) ?></td>
            <td><span class="badge <?= $urgenceBadge ?>"><?= $urgenceLabel ?></span></td>
            <td><?= htmlspecialchars($d['ville'] ?? '—') ?></td>
            <td class="text-nowrap"><?= $budget ?: '<span class="text-muted small">NC</span>' ?></td>
            <td class="text-nowrap small text-muted"><?= isset($d['published_at']) ? date('d/m/Y', strtotime($d['published_at'])) : '—' ?></td>
            <td class="text-end">
              <a href="index.php?action=demande_show&id=<?= (int)$d['id_demande'] ?>"
                 class="btn btn-sm btn-outline-secondary me-1" title="Voir le détail">
                <i class="bi bi-eye"></i>
              </a>
              <!-- Formulaire de proposition directe (raccourci) -->
              <button type="button" class="btn btn-sm btn-brand"
                      data-bs-toggle="modal"
                      data-bs-target="#modalProposer"
                      data-id-demande="<?= (int)$d['id_demande'] ?>"
                      data-titre="<?= htmlspecialchars($d['titre'] ?? '#' . $d['id_demande']) ?>">
                <i class="bi bi-send me-1"></i>Proposer
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Modal : envoyer une proposition -->
<div class="modal fade" id="modalProposer" tabindex="-1" aria-labelledby="modalProposerLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="index.php?action=demande_proposer">
        <div class="modal-header">
          <h5 class="modal-title" id="modalProposerLabel">Envoyer une proposition</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_demande" id="modalIdDemande" value="">

          <p class="text-muted mb-3">Demande : <strong id="modalTitreDemande"></strong></p>

          <div class="mb-3">
            <label class="form-label fw-semibold">Votre message / présentation <span class="text-danger">*</span></label>
            <textarea name="message" class="form-control" rows="4" required
                      placeholder="Présentez votre approche, votre expérience sur ce type de problème..."></textarea>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Prix indicatif (€) <span class="text-muted small">(optionnel)</span></label>
              <input type="number" name="prix_indicatif" class="form-control" min="0" step="0.01"
                     placeholder="Ex : 120">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Délai indicatif <span class="text-muted small">(optionnel)</span></label>
              <input type="text" name="delai_texte" class="form-control"
                     placeholder="Ex : 2 à 3 jours ouvrés">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-brand"><i class="bi bi-send me-1"></i>Envoyer la proposition</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var modalEl = document.getElementById('modalProposer');
  if (!modalEl) return;
  modalEl.addEventListener('show.bs.modal', function (event) {
    var btn = event.relatedTarget;
    document.getElementById('modalIdDemande').value    = btn.dataset.idDemande;
    document.getElementById('modalTitreDemande').textContent = btn.dataset.titre;
  });
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
