<?php
$pageTitle = 'Démarrer une intervention';
$active = 'demandes';
require __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="da-card p-4">
      <form method="POST" action="index.php?action=intervention_create&id_demande=<?= (int) $id_demande ?>">
        <input type="hidden" name="id_demande" value="<?= (int) $id_demande ?>">

        <?php if (empty($dispos)): ?>
          <div class="alert alert-warning">
            Vous n'avez aucun créneau libre. Vous pouvez démarrer sans créneau associé,
            ou <a href="index.php?action=disponibilites">ajouter une disponibilité</a> d'abord.
          </div>
        <?php else: ?>
          <div class="mb-3">
            <label class="form-label">Créneau (optionnel)</label>
            <select name="id_dispo" class="form-select">
              <option value="">— Aucun créneau associé —</option>
              <?php foreach ($dispos as $d): ?>
                <option value="<?= (int) $d['id_dispo'] ?>">
                  <?= htmlspecialchars($d['date']) ?> — <?= htmlspecialchars(substr($d['heure_debut'], 0, 5)) ?> à <?= htmlspecialchars(substr($d['heure_fin'], 0, 5)) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-brand"><i class="bi bi-play-circle me-1"></i>Démarrer l'intervention</button>
          <a href="index.php?action=demande_show&id=<?= (int) $id_demande ?>" class="btn btn-outline-secondary">Annuler</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
