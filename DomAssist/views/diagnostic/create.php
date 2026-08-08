<?php
$pageTitle = 'Proposer un diagnostic';
$active = 'demandes';
$breadcrumb = [['label' => 'Dashboard', 'action' => 'dashboard'], ['label' => 'Demande #' . $id_demande, 'action' => 'demande_show&id=' . $id_demande], ['label' => 'Diagnostic']];
require __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="da-card p-4">
      <form method="POST" action="index.php?action=diagnostic_create&id_demande=<?= (int) $id_demande ?>">
        <input type="hidden" name="id_demande" value="<?= (int) $id_demande ?>">
        <div class="mb-3">
          <label class="form-label">Description du diagnostic</label>
          <textarea name="description" class="form-control" rows="4" placeholder="Ce que vous constatez sur place..." required></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Résultat / conclusion (optionnel)</label>
          <textarea name="resultat" class="form-control" rows="2"></textarea>
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-brand"><i class="bi bi-check2 me-1"></i>Enregistrer le diagnostic</button>
          <a href="index.php?action=demande_show&id=<?= (int) $id_demande ?>" class="btn btn-outline-secondary">Annuler</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
