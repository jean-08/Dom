<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Modifier utilisateur</h2>
<form method="POST" action="index.php?action=edit&id=<?= isset($_GET['id']) ? (int)$_GET['id'] : '' ?>">
  <label>Nom</label>
  <input type="text" name="nom" value="<?= htmlspecialchars($u['nom'] ?? '') ?>" required>
  <label>Prénom</label>
  <input type="text" name="prenom" value="<?= htmlspecialchars($u['prenom'] ?? '') ?>" required>
  <label>Email</label>
  <input type="email" name="email" value="<?= htmlspecialchars($u['email'] ?? '') ?>" required>

  <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
    <label>Rôle</label>
    <select name="role">
      <?php foreach (['client', 'admin', 'prestataire'] as $r): ?>
        <option value="<?= $r ?>" <?= ($u['role'] ?? '') === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option> <!-- ucfirt=> Maj first char -->
      <?php endforeach; ?>
    </select>
  <?php endif; ?>

  <button type="submit">Enregistrer</button>
</form>
<?php require __DIR__ . '/../layout/footer.php'; ?>