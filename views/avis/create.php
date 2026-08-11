<?php
$pageTitle = 'Laisser un avis';
$active = 'demandes';
require __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <?php if (empty($interventionsEligibles)): ?>
      <div class="da-card p-4">
        <div class="empty-state">
          <i class="bi bi-star"></i>
          Aucune intervention terminée en attente d'avis pour le moment.
        </div>
      </div>
    <?php else: ?>
      <div class="da-card p-4">
        <form method="POST" action="index.php?action=avis_create">
          <div class="mb-3">
            <label class="form-label">Intervention concernée</label>
            <select name="id_intervention" class="form-select" required>
              <?php foreach ($interventionsEligibles as $i): ?>
                <option value="<?= (int) $i['id_intervention'] ?>">
                  #<?= (int) $i['id_intervention'] ?> — <?= htmlspecialchars($i['prenom'] . ' ' . $i['nom']) ?> (<?= htmlspecialchars($i['specialite']) ?>) — <?= htmlspecialchars($i['date']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Note</label>
            <select name="note" class="form-select" required>
              <?php for ($n = 5; $n >= 1; $n--): ?>
                <option value="<?= $n ?>"><?= str_repeat('★', $n) ?> (<?= $n ?>/5)</option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Commentaire (optionnel)</label>
            <textarea name="comment" class="form-control" rows="3"></textarea>
          </div>
          <button type="submit" class="btn btn-brand"><i class="bi bi-star-fill me-1"></i>Publier l'avis</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
