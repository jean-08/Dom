<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Créer une intervention</h2>
<form method="POST" action="index.php?action=intervention_create">
  <input type="hidden" name="id_demande" value="<?= (int)$id_demande ?>">
  <input type="hidden" name="id_prestataire" value="<?= (int)($prest['id_prestataire'] ?? 0) ?>">
  <label>Résultat</label>
  <textarea name="resultat" rows="3"></textarea>
  <label>Disponibilité</label>
  <select name="id_dispo">
    <option value="">-- Aucune --</option>
    <?php foreach ($dispos as $d): ?>
    <option value="<?= $d['id_dispo'] ?>"><?= $d['date'] ?> <?= $d['heure_debut'] ?>-<?= $d['heure_fin'] ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit">Enregistrer</button>
</form>
<?php require __DIR__ . '/../layout/footer.php'; ?>