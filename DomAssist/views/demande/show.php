<?php require __DIR__ . '/../layout/header.php'; ?>
<h2>Demande #<?= $demande['id_demande'] ?></h2>
<p><strong>Description :</strong> <?= htmlspecialchars($demande['description']) ?></p>
<p><strong>Adresse :</strong> <?= htmlspecialchars($demande['adresse']) ?></p>
<p><strong>Date :</strong> <?= htmlspecialchars($demande['date']) ?></p>
<p><strong>Statut :</strong> <span class="badge <?= $demande['statut'] ?>"><?= htmlspecialchars($demande['statut']) ?></span></p>
<p><strong>Client :</strong> <?= htmlspecialchars($demande['nom'].' '.$demande['prenom']) ?></p>
<a href="index.php?action=diagnostic_show&id_demande=<?= $demande['id_demande'] ?>">Voir diagnostic</a>
<?php if (($_SESSION['user']['role'] ?? '') === 'prestataire'): ?>
<a href="index.php?action=diagnostic_create&id_demande=<?= $demande['id_demande'] ?>" class="btn">Créer diagnostic</a>
<a href="index.php?action=intervention_create&id_demande=<?= $demande['id_demande'] ?>" class="btn">Créer intervention</a>
<?php endif; ?>
<br><a href="index.php?action=demandes">← Retour</a>
<?php require __DIR__ . '/../layout/footer.php'; ?>