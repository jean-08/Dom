<?php
require_once __DIR__ . '/../../utils/upload.php';
$note = 0; $nb = count($avis ?? []);
foreach (($avis ?? []) as $a) { $note += (int)$a['note']; }
$moyenne = $nb > 0 ? round($note / $nb, 1) : 0;
$pageTitle = htmlspecialchars(($prestataire['prenom'] ?? '') . ' ' . ($prestataire['nom'] ?? ''));
$active = 'prestataires';
require __DIR__ . '/../layouts/header.php';
?>

<div class="row g-3">
  <!-- Colonne gauche : carte profil -->
  <div class="col-lg-4">
    <div class="da-card p-4 text-center shadow-sm rounded-3">
      <?php if (!empty($prestataire['photo_url'])): ?>
        <img src="<?= htmlspecialchars(UploadHelper::getAvatarUrl($prestataire['photo_url'] ?? null, $prestataire['prenom'] ?? 'User', $prestataire['nom'] ?? '')) ?>" alt="Avatar" class="avatar-circle mx-auto mb-3" style="width:72px;height:72px;font-size:1.6rem;object-fit:cover;">
      <?php else: ?>
        <span class="avatar-circle mx-auto mb-3" style="width:72px;height:72px;font-size:1.6rem;">
          <?= htmlspecialchars(strtoupper(mb_substr($prestataire['prenom'] ?? '', 0, 1) . mb_substr($prestataire['nom'] ?? '', 0, 1))) ?>
        </span>
      <?php endif; ?>
      <h5 class="mb-1 fw-bold"><?= htmlspecialchars(($prestataire['prenom'] ?? '') . ' ' . ($prestataire['nom'] ?? '')) ?></h5>

      <div class="mb-2">
        <span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>Profil vérifié</span>
      </div>

      <?php if (!empty($prestataire['specialite'])): ?>
        <p class="text-muted small mb-2"><?= htmlspecialchars($prestataire['specialite']) ?></p>
      <?php endif; ?>

      <div class="mb-3">
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <i class="bi <?= $i <= round($moyenne) ? 'bi-star-fill text-warning' : 'bi-star text-secondary' ?>"></i>
        <?php endfor; ?>
        <span class="small text-muted ms-1 fw-bold"><?= number_format($moyenne, 1) ?>/5</span>
        <div class="text-muted small">(<?= $nb ?> avis client<?= $nb > 1 ? 's' : '' ?>)</div>
      </div>

      <div class="border-top pt-3 text-start small">
        <?php if (!empty($prestataire['experience_annees'])): ?>
          <div class="mb-2"><i class="bi bi-briefcase text-primary me-2"></i><strong>Expérience :</strong> <?= (int)$prestataire['experience_annees'] ?> ans</div>
        <?php endif; ?>
        <?php if (!empty($prestataire['zone_intervention'])): ?>
          <div class="mb-2"><i class="bi bi-geo-alt text-primary me-2"></i><strong>Zone :</strong> <?= htmlspecialchars($prestataire['zone_intervention']) ?></div>
        <?php endif; ?>
        <?php if (!empty($prestataire['disponibilites_type'])): ?>
          <div class="mb-2"><i class="bi bi-clock text-primary me-2"></i><strong>Horaires :</strong> <?= htmlspecialchars($prestataire['disponibilites_type']) ?></div>
        <?php endif; ?>
        <?php if (!empty($prestataire['accepte_urgences'])): ?>
          <div class="mb-2"><span class="badge bg-warning text-dark"><i class="bi bi-lightning-charge me-1"></i>Accepte les urgences</span></div>
        <?php endif; ?>
        <?php if (!empty($prestataire['moyen_deplacement'])): ?>
          <div class="mb-2"><i class="bi bi-truck text-primary me-2"></i><strong>Véhicule :</strong> <?= htmlspecialchars($prestataire['moyen_deplacement']) ?></div>
        <?php endif; ?>
        <?php if (!empty($prestataire['document_cv_url'])): ?>
          <div class="mb-2">
            <a href="<?= htmlspecialchars($prestataire['document_cv_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100">
              <i class="bi bi-file-earmark-pdf me-1"></i>Télécharger le CV
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Colonne droite : compétences + avis -->
  <div class="col-lg-8">
    <div class="da-card p-4 mb-3 shadow-sm rounded-3">
      <h6 class="fw-bold text-primary mb-3"><i class="bi bi-award me-2"></i>Compétences validées</h6>
      <?php if (empty($services)): ?>
        <p class="text-muted mb-0">Aucune compétence renseignée.</p>
      <?php else: ?>
        <div class="d-flex flex-wrap gap-2">
          <?php foreach ($services as $s): ?>
            <span class="badge badge-soft-info p-2 fs-6">
              <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($s['nom'] ?? $s['libelle'] ?? '') ?><?= !empty($s['niveau']) ? ' · ' . htmlspecialchars($s['niveau']) : '' ?>
            </span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="da-card p-4 shadow-sm rounded-3">
      <h6 class="fw-bold text-primary mb-3"><i class="bi bi-star me-2"></i>Avis clients &amp; Réputation (<?= $nb ?>)</h6>
      <?php if (empty($avis)): ?>
        <p class="text-muted mb-0">Aucun avis pour le moment.</p>
      <?php else: ?>
        <?php foreach ($avis as $a): ?>
          <div class="border rounded p-3 mb-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="fw-bold"><i class="bi bi-person-circle me-1 text-secondary"></i><?= htmlspecialchars(($a['prenom'] ?? 'Client') . ' ' . mb_substr($a['nom'] ?? '', 0, 1) . '.') ?></div>
              <span class="text-warning fw-bold"><?= str_repeat('★', (int)$a['note']) . str_repeat('☆', 5 - (int)$a['note']) ?> (<?= (int)$a['note'] ?>/5)</span>
            </div>
            <?php if (!empty($a['comment'])): ?>
              <p class="mb-2 text-dark small"><?= nl2br(htmlspecialchars($a['comment'])) ?></p>
            <?php endif; ?>
            <?php if (!empty($a['reponse_prestataire'])): ?>
              <div class="mt-2 p-3 bg-light border-start border-3 border-primary rounded small">
                <div class="fw-bold text-primary mb-1"><i class="bi bi-reply-fill me-1"></i>Réponse du prestataire :</div>
                <div class="text-secondary"><?= nl2br(htmlspecialchars($a['reponse_prestataire'])) ?></div>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
