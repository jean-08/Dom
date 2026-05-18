<?php require __DIR__ . '/../layout/header.php'; ?>
<h2><?= isset($prestataire['id_prestataire']) ? 'Détail prestataire' : 'Nouveau prestataire' ?></h2>

<?php if (isset($prestataire)): ?>
  <p><strong>Nom :</strong> <?= htmlspecialchars($prestataire['nom'].' '.$prestataire['prenom']) ?></p>
  <p><strong>Spécialité :</strong> <?= htmlspecialchars($prestataire['specialite']) ?></p>

  <?php if (!empty($services)): ?>
  <h3>Compétences</h3>
  <ul><?php foreach ($services as $s): ?>
    <li><?= htmlspecialchars($s['nom']) ?> — Niveau : <?= htmlspecialchars($s['niveau'] ?? '-') ?></li>
  <?php endforeach; ?></ul>
  <?php endif; ?>

  <?php if (!empty($avis)): ?>
  <h3>Avis</h3>
  <?php foreach ($avis as $a): ?>
    <div class="avis">
      <strong><?= htmlspecialchars($a['nom'].' '.$a['prenom']) ?></strong>
      — Note : <?= (int)$a['note'] ?>/5
      <p><?= htmlspecialchars($a['comment']) ?></p>
      <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
      <a href="index.php?action=avis_delete&id=<?= $a['id_avis'] ?>">Supprimer</a>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <?php endif; ?>

  <?php if (($_SESSION['user']['role'] ?? '') !== 'admin'): ?>
  <h3>Laisser un avis</h3>
  <form method="POST" action="index.php?action=avis_create">
    <input type="hidden" name="id_prestataire" value="<?= $prestataire['id_prestataire'] ?>">
    <label>Note (1-5)</label>
    <input type="number" name="note" min="1" max="5" required>
    <label>Commentaire</label>
    <textarea name="comment"></textarea>
    <button type="submit">Publier</button>
  </form>
  <?php endif; ?>

<?php else: ?>
<form method="POST">
  <label>Spécialité</label>
  <input type="text" name="specialite" required>
  <label>ID Utilisateur</label>
  <input type="number" name="id_user" required>
  <button type="submit">Enregistrer</button>
</form>
<?php endif; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>