<?php
$pageTitle = 'Candidatures prestataires';
$pageSubtitle = 'Examinez et validez les dossiers de candidature en attente';
$active = 'admin_prestataires';
require __DIR__ . '/../layouts/header.php';
?>

<div class="da-card p-3 p-md-4">
  <?php if (empty($candidatures)): ?>
    <div class="empty-state py-5 text-center text-muted">
      <i class="bi bi-person-check fs-1 d-block mb-3"></i>
      Aucune candidature en attente d'examen.
    </div>
  <?php else: ?>
    <div class="accordion" id="candidaturesAccordion">
      <?php foreach ($candidatures as $index => $c): ?>
        <div class="accordion-item mb-3 border rounded shadow-sm overflow-hidden">
          <h2 class="accordion-header" id="heading<?= (int)$c['id_prestataire'] ?>">
            <button class="accordion-button <?= $index !== 0 ? 'collapsed' : '' ?> bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= (int)$c['id_prestataire'] ?>">
              <div class="d-flex align-items-center justify-content-between w-100 me-3">
                <div class="d-flex align-items-center gap-3">
                  <span class="avatar-circle bg-primary text-white font-weight-bold" style="width:40px; height:40px; display:inline-flex; align-items:center; justify-content:center; border-radius:50%;">
                    <?= htmlspecialchars(strtoupper(mb_substr($c['prenom'], 0, 1) . mb_substr($c['nom'], 0, 1))) ?>
                  </span>
                  <div>
                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></h6>
                    <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($c['ville'] ?? 'Ville non précisée') ?> • <?= htmlspecialchars($c['email']) ?></small>
                  </div>
                </div>
                <div class="d-none d-md-flex align-items-center gap-2">
                  <?php if (!empty($c['document_cv_url'])): ?>
                    <span class="badge bg-success"><i class="bi bi-file-pdf me-1"></i>CV PDF joint</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Sans CV PDF</span>
                  <?php endif; ?>
                  <?php if (!empty($c['accepte_urgences'])): ?>
                    <span class="badge bg-danger"><i class="bi bi-lightning-fill me-1"></i>Urgences</span>
                  <?php endif; ?>
                  <span class="badge bg-info text-dark"><?= (int)($c['experience_annees'] ?? 0) ?> ans exp.</span>
                </div>
              </div>
            </button>
          </h2>

          <div id="collapse<?= (int)$c['id_prestataire'] ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#candidaturesAccordion">
            <div class="accordion-body bg-white p-4">
              <div class="row g-4">
                <!-- Colonne de gauche : Dossier & CV -->
                <div class="col-md-7 border-end-md">
                  <h6 class="text-primary fw-bold mb-2"><i class="bi bi-person-lines-fill me-2"></i>Présentation & Spécialité</h6>
                  <p class="bg-light p-3 rounded border text-dark mb-3"><?= nl2br(htmlspecialchars($c['specialite'] ?? $c['bio'] ?? '')) ?></p>

                  <?php if (!empty($c['lettre_motivation'])): ?>
                    <h6 class="text-primary fw-bold mb-2"><i class="bi bi-file-earmark-text me-2"></i>Lettre de motivation / Parcours</h6>
                    <p class="bg-light p-3 rounded border text-dark mb-3"><?= nl2br(htmlspecialchars($c['lettre_motivation'])) ?></p>
                  <?php endif; ?>

                  <div class="d-flex flex-wrap gap-2 my-3">
                    <?php if (!empty($c['document_cv_url'])): ?>
                      <a href="<?= htmlspecialchars($c['document_cv_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i>Télécharger le CV PDF
                      </a>
                    <?php endif; ?>
                    <?php if (!empty($c['telephone'])): ?>
                      <a href="tel:<?= htmlspecialchars($c['telephone']) ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($c['telephone']) ?>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Colonne de droite : Modalités pro & Actions -->
                <div class="col-md-5">
                  <h6 class="text-primary fw-bold mb-3"><i class="bi bi-card-checklist me-2"></i>Informations Logistiques & Légales</h6>

                  <ul class="list-group list-group-flush mb-4 small">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <span class="text-muted">Expérience :</span>
                      <span class="fw-semibold"><?= (int)($c['experience_annees'] ?? 0) ?> ans</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <span class="text-muted">Zone d'intervention :</span>
                      <span class="fw-semibold"><?= htmlspecialchars($c['zone_intervention'] ?? 'Non renseignée') ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <span class="text-muted">Disponibilités :</span>
                      <span class="fw-semibold"><?= htmlspecialchars($c['disponibilites_type'] ?? 'Semaine et Week-end') ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <span class="text-muted">Moyen de déplacement :</span>
                      <span class="fw-semibold"><?= htmlspecialchars($c['moyen_deplacement'] ?? 'Véhicule personnel') ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                      <span class="text-muted">Accepte les urgences :</span>
                      <span class="fw-semibold <?= !empty($c['accepte_urgences']) ? 'text-success' : 'text-secondary' ?>">
                        <?= !empty($c['accepte_urgences']) ? 'Oui (Sous 48h)' : 'Non' ?>
                      </span>
                    </li>
                    <?php if (!empty($c['siret'])): ?>
                      <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">N° SIRET :</span>
                        <span class="fw-semibold"><?= htmlspecialchars($c['siret']) ?></span>
                      </li>
                    <?php endif; ?>
                    <?php if (!empty($c['assurances_pro'])): ?>
                      <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Assurance pro :</span>
                        <span class="fw-semibold"><?= htmlspecialchars($c['assurances_pro']) ?></span>
                      </li>
                    <?php endif; ?>
                  </ul>

                  <!-- Actions de Validation / Rejet -->
                  <div class="d-grid gap-2">
                    <form method="POST" action="index.php?action=admin_valider_prestataire">
                      <input type="hidden" name="id_prestataire" value="<?= (int)$c['id_prestataire'] ?>">
                      <button type="submit" class="btn btn-brand w-100 py-2">
                        <i class="bi bi-check-circle-fill me-2"></i>Valider cette candidature
                      </button>
                    </form>

                    <button class="btn btn-outline-danger w-100 py-2" data-bs-toggle="modal" data-bs-target="#rejetModal<?= (int)$c['id_prestataire'] ?>">
                      <i class="bi bi-x-circle me-2"></i>Rejeter la candidature
                    </button>
                  </div>

                  <!-- Modal Rejet -->
                  <div class="modal fade" id="rejetModal<?= (int)$c['id_prestataire'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <form method="POST" action="index.php?action=admin_rejeter_prestataire">
                          <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title"><i class="bi bi-exclamation-octagon me-2"></i>Rejeter la candidature</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <input type="hidden" name="id_prestataire" value="<?= (int)$c['id_prestataire'] ?>">
                            <div class="mb-3">
                              <label class="form-label fw-semibold">Motif explicatif du rejet <span class="text-danger">*</span></label>
                              <textarea name="motif" class="form-control" rows="3" required placeholder="Indiquez au candidat les éléments manquants ou la raison du refus..."></textarea>
                              <div class="form-text">Ce motif sera transmis au candidat pour qu'il puisse corriger et resoumettre son dossier.</div>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-danger">Confirmer le rejet</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
