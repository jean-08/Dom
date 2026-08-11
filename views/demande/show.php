<?php
$pageTitle  = 'Demande #' . (int)$demande['id_demande'];
$active     = 'demandes';
$role       = $_SESSION['user']['role'] ?? '';
$idUserSess = (int)($_SESSION['user']['id_user'] ?? 0);

$estProprietaire = (int)$demande['id_user'] === $idUserSess;
$estPrestataireRetenu = $mesProfilPrestataire
    && !empty($demande['id_profile_retenu'])
    && (int)$demande['id_profile_retenu'] === (int)$mesProfilPrestataire['id_prestataire'];

// Statuts V3
$statutBadge = function (string $statut): string {
    return match ($statut) {
        'ouverte'                  => 'badge-soft-secondary',
        'en_discussion'            => 'badge-soft-info',
        'prestataire_choisi'       => 'badge-soft-info',
        'engagee'                  => 'badge-soft-primary',
        'diagnostic_propose'       => 'badge-soft-info',
        'solution_proposee'        => 'badge-soft-info',
        'intervention_planifiee'   => 'badge-soft-primary',
        'intervention_en_cours'    => 'badge-soft-warning',
        'terminee'                 => 'badge-soft-success',
        'cloturee'                 => 'badge-soft-success',
        'annulee_par_client',
        'annulee_par_prestataire'  => 'badge-soft-danger',
        'expiree'                  => 'badge-soft-danger',
        'suspendue_moderation'     => 'badge-soft-warning',
        default                    => 'badge-soft-secondary',
    };
};

$statutLabel = function (string $statut): string {
    return match ($statut) {
        'ouverte'                 => 'Ouverte',
        'en_discussion'           => 'En discussion',
        'prestataire_choisi'      => 'Prestataire choisi',
        'engagee'                 => 'Engagée',
        'diagnostic_propose'      => 'Diagnostic proposé',
        'solution_proposee'       => 'Solution proposée',
        'intervention_planifiee'  => 'Intervention planifiée',
        'intervention_en_cours'   => 'Intervention en cours',
        'terminee'                => 'Terminée',
        'cloturee'                => 'Clôturée',
        'annulee_par_client'      => 'Annulée (client)',
        'annulee_par_prestataire' => 'Annulée (prestataire)',
        'expiree'                 => 'Expirée',
        'suspendue_moderation'    => 'Suspendue',
        default                   => ucfirst(str_replace('_', ' ', $statut)),
    };
};

// Statuts V3 pour le select admin
$statutsAdmin = [
    'ouverte','en_discussion','prestataire_choisi','engagee',
    'diagnostic_propose','solution_proposee','intervention_planifiee',
    'intervention_en_cours','terminee','cloturee',
    'annulee_par_client','annulee_par_prestataire','expiree','suspendue_moderation',
];

// Ma proposition (si je suis prestataire)
$maProposition = null;
if ($mesProfilPrestataire && !empty($propositions)) {
    foreach ($propositions as $prop) {
        if ((int)$prop['id_profile'] === (int)$mesProfilPrestataire['id_prestataire']) {
            $maProposition = $prop;
            break;
        }
    }
}

$breadcrumb = [
    ['label' => 'Dashboard', 'action' => 'dashboard'],
    ['label' => 'Mes demandes', 'action' => 'demandes'],
    ['label' => '#' . (int)$demande['id_demande']],
];
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../components/breadcrumb.php';
?>

<div class="row g-3">

  <!-- ===== Colonne principale ===== -->
  <div class="col-lg-8">

    <!-- Détails de la demande -->
    <div class="da-card p-4 mb-3">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <h5 class="mb-0"><?= htmlspecialchars($demande['titre'] ?? 'Demande #' . $demande['id_demande']) ?></h5>
        <span class="badge <?= $statutBadge($demande['statut']) ?> fs-6"><?= $statutLabel($demande['statut']) ?></span>
      </div>

      <?php if (!empty($demande['description'])): ?>
        <p class="mb-2"><strong>Description :</strong><br><?= nl2br(htmlspecialchars($demande['description'])) ?></p>
      <?php endif; ?>

      <div class="row small text-muted g-2 mt-2">
        <?php if (!empty($demande['category_libelle'])): ?>
          <div class="col-auto"><i class="bi bi-tag me-1"></i><?= htmlspecialchars($demande['category_libelle']) ?></div>
        <?php endif; ?>
        <?php if (!empty($demande['urgence'])): ?>
          <?php $urg = $demande['urgence'] === 'urgent' ? 'badge-soft-danger' : ($demande['urgence'] === 'sous_48h' ? 'badge-soft-warning' : 'badge-soft-secondary'); ?>
          <div class="col-auto">
            <span class="badge <?= $urg ?>">
              <?= $demande['urgence'] === 'urgent' ? 'Urgent' : ($demande['urgence'] === 'sous_48h' ? 'Sous 48h' : 'Normal') ?>
            </span>
          </div>
        <?php endif; ?>
        <?php if (!empty($demande['ville'])): ?>
          <div class="col-auto"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($demande['ville']) ?><?= !empty($demande['code_postal']) ? ' (' . htmlspecialchars($demande['code_postal']) . ')' : '' ?></div>
        <?php endif; ?>
        <?php if (!empty($demande['adresse'])): ?>
          <div class="col-auto">
            <i class="bi bi-house me-1"></i>
            <?php if ($estProprietaire || $role === 'admin' || $estPrestataireRetenu): ?>
              <?= htmlspecialchars($demande['adresse']) ?>
            <?php else: ?>
              <span class="text-muted fst-italic">Adresse masquée (visible après sélection)</span>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($demande['budget_min']) || !empty($demande['budget_max'])): ?>
          <div class="col-auto">
            <i class="bi bi-wallet2 me-1"></i>
            <?php
              if (!empty($demande['budget_min']) && !empty($demande['budget_max'])) {
                  echo number_format((float)$demande['budget_min'], 0, ',', ' ') . ' – ' . number_format((float)$demande['budget_max'], 0, ',', ' ') . ' €';
              } elseif (!empty($demande['budget_max'])) {
                  echo '≤ ' . number_format((float)$demande['budget_max'], 0, ',', ' ') . ' €';
              } else {
                  echo '≥ ' . number_format((float)$demande['budget_min'], 0, ',', ' ') . ' €';
              }
            ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($demande['disponibilites_client'])): ?>
          <div class="col-auto"><i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($demande['disponibilites_client']) ?></div>
        <?php endif; ?>
        <?php if (!empty($demande['published_at'])): ?>
          <div class="col-auto"><i class="bi bi-clock me-1"></i>Publiée le <?= date('d/m/Y à H:i', strtotime($demande['published_at'])) ?></div>
        <?php endif; ?>
      </div>

      <!-- Galerie de photos d'illustration du problème -->
      <?php if (!empty($medias)): ?>
        <div class="mt-4 pt-3 border-top">
          <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-images me-2"></i>Photos d'illustration du problème (<?= count($medias) ?>)</h6>
          <div class="row g-2">
            <?php foreach ($medias as $media): ?>
              <div class="col-6 col-sm-4 col-md-3">
                <a href="<?= htmlspecialchars($media['url']) ?>" target="_blank" class="d-block text-decoration-none">
                  <img src="<?= htmlspecialchars($media['url']) ?>" alt="Photo du problème" class="img-fluid rounded border shadow-sm" style="height: 120px; width: 100%; object-fit: cover;">
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Actions client : annuler -->
      <?php if ($estProprietaire && !in_array($demande['statut'], ['cloturee','annulee_par_client','annulee_par_prestataire','expiree','terminee'])): ?>
        <div class="mt-3">
          <form method="POST" action="index.php?action=demande_annuler" class="d-inline"
                onsubmit="return confirm('Annuler cette demande ?');">
            <input type="hidden" name="id_demande" value="<?= (int)$demande['id_demande'] ?>">
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i>Annuler la demande</button>
          </form>
        </div>
      <?php endif; ?>
    </div>

    <!-- ===== SECTION PROPOSITIONS ===== -->
    <?php if (!empty($propositions)): ?>
      <div class="da-card p-4 mb-3">
        <h6 class="mb-3"><i class="bi bi-people me-2"></i>Propositions (<?= count($propositions) ?>)</h6>

        <?php foreach ($propositions as $prop): ?>
          <?php
            $propBadge = match ($prop['statut']) {
                'retenue'     => 'badge-soft-success',
                'non_retenue' => 'badge-soft-danger',
                'retiree'     => 'badge-soft-secondary',
                'expiree'     => 'badge-soft-danger',
                default       => 'badge-soft-info',   // envoyee
            };
            $propLabel = match ($prop['statut']) {
                'retenue'     => 'Retenue',
                'non_retenue' => 'Non retenue',
                'retiree'     => 'Retirée',
                'expiree'     => 'Expirée',
                default       => 'En attente',
            };
          ?>
          <div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div class="d-flex align-items-center gap-2">
                <span class="avatar-circle">
                  <?= htmlspecialchars(strtoupper(
                      mb_substr($prop['prestataire_prenom'] ?? '', 0, 1) .
                      mb_substr($prop['prestataire_nom'] ?? '', 0, 1)
                  )) ?>
                </span>
                <div>
                  <div class="fw-semibold"><?= htmlspecialchars(($prop['prestataire_prenom'] ?? '') . ' ' . ($prop['prestataire_nom'] ?? '')) ?></div>
                  <?php if (!empty($prop['prestataire_ville'])): ?>
                    <div class="small text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($prop['prestataire_ville']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="text-end">
                <span class="badge <?= $propBadge ?>"><?= $propLabel ?></span>
                <?php if ((float)($prop['note_moyenne'] ?? 0) > 0): ?>
                  <div class="small text-muted mt-1">
                    <i class="bi bi-star-fill text-warning"></i>
                    <?= number_format((float)$prop['note_moyenne'], 1) ?>
                    (<?= (int)$prop['nombre_avis'] ?> avis)
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <p class="mb-2 small"><?= nl2br(htmlspecialchars($prop['message_prestataire'] ?? '')) ?></p>

            <div class="d-flex flex-wrap gap-3 small text-muted">
              <?php if (!empty($prop['prix_indicatif'])): ?>
                <span><i class="bi bi-currency-euro me-1"></i><?= number_format((float)$prop['prix_indicatif'], 2, ',', ' ') ?> €</span>
              <?php endif; ?>
              <?php if (!empty($prop['delai_texte'])): ?>
                <span><i class="bi bi-clock me-1"></i><?= htmlspecialchars($prop['delai_texte']) ?></span>
              <?php endif; ?>
              <?php if (!empty($prop['experience_annees'])): ?>
                <span><i class="bi bi-briefcase me-1"></i><?= (int)$prop['experience_annees'] ?> ans d'exp.</span>
              <?php endif; ?>
            </div>

            <!-- Client : sélectionner une proposition -->
            <?php if ($estProprietaire && $prop['statut'] === 'envoyee'
                && in_array($demande['statut'], ['ouverte', 'en_discussion'])): ?>
              <form method="POST" action="index.php?action=demande_selectionner" class="mt-2">
                <input type="hidden" name="id_demande"    value="<?= (int)$demande['id_demande'] ?>">
                <input type="hidden" name="id_proposition" value="<?= (int)$prop['id_proposition'] ?>">
                <button class="btn btn-sm btn-brand">
                  <i class="bi bi-check2-circle me-1"></i>Choisir ce prestataire
                </button>
              </form>
            <?php endif; ?>

            <!-- Prestataire retenu : confirmer engagement -->
            <?php if ($mesProfilPrestataire
                && (int)$prop['id_profile'] === (int)$mesProfilPrestataire['id_prestataire']
                && $prop['statut'] === 'retenue'
                && $demande['statut'] === 'prestataire_choisi'): ?>
              <form method="POST" action="index.php?action=demande_confirmer_engagement" class="mt-2 d-inline">
                <input type="hidden" name="id_demande" value="<?= (int)$demande['id_demande'] ?>">
                <button class="btn btn-sm btn-brand">
                  <i class="bi bi-lightning-charge me-1"></i>Confirmer mon engagement
                </button>
              </form>
              <form method="POST" action="index.php?action=demande_desister" class="mt-2 d-inline"
                    onsubmit="return confirm('Se désister de cette demande ?');">
                <input type="hidden" name="id_demande" value="<?= (int)$demande['id_demande'] ?>">
                <button class="btn btn-sm btn-outline-danger">
                  <i class="bi bi-box-arrow-left me-1"></i>Se désister
                </button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php elseif ($estProprietaire && in_array($demande['statut'], ['ouverte'])): ?>
      <div class="da-card p-4 mb-3 text-center text-muted">
        <i class="bi bi-hourglass-split fs-4 d-block mb-2"></i>
        Aucune proposition reçue pour l'instant.
      </div>
    <?php endif; ?>

    <?php /* Discussion privée (messagerie thread) — désactivée pour l'instant, sera implémentée séparément */ ?>



    <!-- ===== DIAGNOSTIC ===== -->
    <?php if ($diagnostic): ?>
      <div class="da-card p-4 mb-3">
        <h6 class="mb-3"><i class="bi bi-clipboard2-pulse me-2"></i>Diagnostic</h6>
        <p class="mb-2"><?= nl2br(htmlspecialchars($diagnostic['description'])) ?></p>
        <?php if (!empty($diagnostic['resultat'])): ?>
          <p class="text-muted small mb-0">Résultat : <?= htmlspecialchars($diagnostic['resultat']) ?></p>
        <?php endif; ?>
      </div>

      <?php if (!empty($solutions)): ?>
        <div class="da-card p-4 mb-3">
          <h6 class="mb-3"><i class="bi bi-lightbulb me-2"></i>Solution(s) proposée(s)</h6>
          <?php foreach ($solutions as $sol): ?>
            <div class="border rounded p-3 mb-2">
              <p class="mb-2"><?= nl2br(htmlspecialchars($sol['description'])) ?></p>
              <?php if (!empty($sol['validee_par_client'])): ?>
                <span class="badge badge-soft-success">Validée par le client</span>
              <?php elseif (!empty($sol['refusee_par_client'])): ?>
                <span class="badge badge-soft-danger">Refusée par le client</span>
              <?php elseif ($estProprietaire && $demande['statut'] === 'solution_proposee'): ?>
                <div class="d-flex gap-2">
                  <form method="POST" action="index.php?action=solution_valider">
                    <input type="hidden" name="id_solution" value="<?= (int)$sol['id_solution'] ?>">
                    <input type="hidden" name="id_demande"  value="<?= (int)$demande['id_demande'] ?>">
                    <button class="btn btn-sm btn-brand"><i class="bi bi-check2-circle me-1"></i>Valider cette solution</button>
                  </form>
                  <form method="POST" action="index.php?action=solution_refuser">
                    <input type="hidden" name="id_solution" value="<?= (int)$sol['id_solution'] ?>">
                    <input type="hidden" name="id_demande"  value="<?= (int)$demande['id_demande'] ?>">
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i>Refuser</button>
                  </form>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
          <?php if ($estPrestataireRetenu && $demande['statut'] === 'diagnostic_propose'): ?>
            <div class="text-center mt-3 pt-3 border-top">
              <a href="index.php?action=solution_create&id_diagnostic=<?= (int)$diagnostic['id_diagnostic'] ?>"
                 class="btn btn-brand btn-sm">Proposer une nouvelle solution</a>
            </div>
          <?php endif; ?>
        </div>
      <?php elseif ($estPrestataireRetenu && $demande['statut'] === 'diagnostic_propose'): ?>
        <div class="da-card p-4 mb-3 text-center">
          <a href="index.php?action=solution_create&id_diagnostic=<?= (int)$diagnostic['id_diagnostic'] ?>"
             class="btn btn-brand btn-sm">Proposer une solution</a>
        </div>
      <?php elseif ($estProprietaire && $demande['statut'] === 'diagnostic_propose'): ?>
        <div class="da-card p-4 mb-3 text-center text-muted">
          <i class="bi bi-hourglass-split fs-4 d-block mb-2"></i>
          Diagnostic posé. En attente de la proposition de solution par le prestataire.
        </div>
      <?php endif; ?>

    <?php elseif ($estPrestataireRetenu && $demande['statut'] === 'engagee'): ?>
      <div class="da-card p-4 mb-3 text-center">
        <p class="text-muted mb-2">Aucun diagnostic n'a encore été proposé.</p>
        <a href="index.php?action=diagnostic_create&id_demande=<?= (int)$demande['id_demande'] ?>"
           class="btn btn-brand btn-sm">Proposer un diagnostic</a>
      </div>
    <?php elseif ($estProprietaire && $demande['statut'] === 'engagee'): ?>
      <div class="da-card p-4 mb-3 text-center text-muted">
        <i class="bi bi-hourglass-split fs-4 d-block mb-2"></i>
        Le prestataire a été retenu. En attente du dépôt de son diagnostic.
      </div>
    <?php endif; ?>

    <?php if ($estPrestataireRetenu && in_array($demande['statut'], ['intervention_planifiee'])): ?>
      <div class="da-card p-4 mb-3">
        <div class="d-flex align-items-center gap-3">
          <i class="bi bi-play-circle-fill text-success fs-3"></i>
          <div>
            <div class="fw-semibold">La solution a été validée par le client</div>
            <div class="small text-muted">Vous pouvez maintenant démarrer l'intervention sur place ou à distance.</div>
          </div>
          <a href="index.php?action=intervention_create&id_demande=<?= (int)$demande['id_demande'] ?>"
             class="btn btn-brand btn-sm ms-auto">
            <i class="bi bi-play-circle me-1"></i>Démarrer l'intervention
          </a>
        </div>
      </div>
    <?php elseif ($estProprietaire && $demande['statut'] === 'intervention_planifiee'): ?>
      <div class="da-card p-4 mb-3 text-center text-muted">
        <i class="bi bi-clock-history fs-4 d-block mb-2 text-success"></i>
        La solution a été validée. En attente du démarrage de l'intervention par le prestataire.
      </div>
    <?php endif; ?>

    <!-- ===== INTERVENTION EN COURS / TERMINEE ===== -->
    <?php if ($interventionDemande): ?>
      <div class="da-card p-4 mb-3">
        <h6 class="mb-3"><i class="bi bi-tools me-2"></i>Intervention</h6>
        <?php
          $statutInterv = $interventionDemande['statut'] ?? '';
          $badgeInterv  = $statutInterv === 'terminee' ? 'badge-soft-success' : 'badge-soft-warning';
          $labelInterv  = $statutInterv === 'terminee' ? 'Terminée' : 'En cours';
        ?>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="small text-muted">
            <i class="bi bi-calendar me-1"></i>
            Démarrée le <?= date('d/m/Y', strtotime($interventionDemande['date'] ?? 'now')) ?>
          </span>
          <span class="badge <?= $badgeInterv ?>"><?= $labelInterv ?></span>
        </div>
        <?php if (!empty($interventionDemande['resultat'])): ?>
          <div class="alert alert-light border small mb-2">
            <strong>Compte rendu :</strong><br>
            <?= nl2br(htmlspecialchars($interventionDemande['resultat'])) ?>
          </div>
        <?php endif; ?>

        <?php if ($statutInterv === 'terminee' && $estProprietaire): ?>
          <?php if ($avisIntervention): ?>
            <!-- Avis déjà déposé -->
            <div class="alert alert-success d-flex align-items-center gap-2 mb-0">
              <i class="bi bi-star-fill text-warning fs-5"></i>
              <div>
                <strong>Votre avis a été publié</strong>
                <span class="ms-2">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="bi bi-star<?= $i <= (int)$avisIntervention['note'] ? '-fill text-warning' : ' text-muted' ?>"></i>
                  <?php endfor; ?>
                </span>
                <?php if (!empty($avisIntervention['comment'])): ?>
                  <div class="text-muted small mt-1"><?= htmlspecialchars($avisIntervention['comment']) ?></div>
                <?php endif; ?>
              </div>
            </div>
          <?php else: ?>
            <!-- Inviter le client à laisser un avis -->
            <div class="alert alert-warning d-flex align-items-center gap-3 mb-0">
              <i class="bi bi-star fs-4"></i>
              <div class="flex-grow-1">
                <strong>L'intervention est terminée !</strong>
                <div class="small text-muted">Partagez votre expérience pour aider la communauté.</div>
              </div>
              <button class="btn btn-sm btn-warning" type="button"
                      data-bs-toggle="collapse" data-bs-target="#collapseAvis">
                Laisser un avis
              </button>
            </div>
            <div class="collapse mt-3" id="collapseAvis">
              <form method="POST" action="index.php?action=avis_create">
                <input type="hidden" name="id_intervention" value="<?= (int)$interventionDemande['id_intervention'] ?>">
                <div class="mb-3">
                  <label class="form-label fw-semibold">Note <span class="text-danger">*</span></label>
                  <div class="d-flex gap-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="note" id="note<?= $i ?>" value="<?= $i ?>" required>
                        <label class="form-check-label" for="note<?= $i ?>">
                          <i class="bi bi-star-fill text-warning"></i> <?= $i ?>
                        </label>
                      </div>
                    <?php endfor; ?>
                  </div>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Commentaire <span class="text-muted small">(optionnel)</span></label>
                  <textarea name="comment" class="form-control" rows="3" placeholder="Décrivez votre expérience..."></textarea>
                </div>
                <button type="submit" class="btn btn-brand btn-sm">
                  <i class="bi bi-star me-1"></i>Publier mon avis
                </button>
              </form>
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <?php if ($estPrestataireRetenu && $statutInterv === 'en_cours'): ?>
          <!-- Prestataire : terminer l'intervention -->
          <form method="POST" action="index.php?action=intervention_terminer" class="mt-2">
            <input type="hidden" name="id_intervention" value="<?= (int)$interventionDemande['id_intervention'] ?>">
            <div class="mb-2">
              <label class="form-label fw-semibold small">Compte rendu de l'intervention</label>
              <textarea name="resultat" class="form-control form-control-sm" rows="3"
                        placeholder="Décrivez ce qui a été fait..." required></textarea>
            </div>
            <button type="submit" class="btn btn-sm btn-brand"
                    onclick="return confirm('Clôturer cette intervention ?')">
              <i class="bi bi-check2-circle me-1"></i>Clôturer l'intervention
            </button>
          </form>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- ===== COMMENTAIRES PUBLICS ===== -->
    <div class="da-card p-4 mb-3" id="commentaires">
      <h6 class="mb-3"><i class="bi bi-chat-square-text me-2"></i>Commentaires publics
        <?php if (!empty($commentaires)): ?>
          <span class="badge badge-soft-secondary ms-1"><?= count($commentaires) ?></span>
        <?php endif; ?>
      </h6>

      <?php if (empty($commentaires)): ?>
        <p class="text-muted small fst-italic mb-3">Aucun commentaire pour l'instant. Soyez le premier à réagir.</p>
      <?php else: ?>
        <div class="d-flex flex-column gap-3 mb-4">
          <?php foreach ($commentaires as $com): ?>
            <div class="d-flex gap-3">
              <span class="avatar-circle flex-shrink-0" style="width:36px;height:36px;font-size:.85rem;">
                <?= htmlspecialchars(strtoupper(
                    mb_substr($com['prenom'] ?? '', 0, 1) .
                    mb_substr($com['nom'] ?? '', 0, 1)
                )) ?>
              </span>
              <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <span class="fw-semibold small"><?= htmlspecialchars(($com['prenom'] ?? '') . ' ' . ($com['nom'] ?? '')) ?></span>
                  <?php if (($com['role'] ?? '') === 'admin'): ?>
                    <span class="badge badge-soft-danger" style="font-size:.65rem;">Admin</span>
                  <?php elseif (!empty($com['id_prestataire'])): ?>
                    <span class="badge badge-soft-info" style="font-size:.65rem;">Prestataire</span>
                  <?php else: ?>
                    <span class="badge badge-soft-secondary" style="font-size:.65rem;">Client</span>
                  <?php endif; ?>
                  <span class="text-muted" style="font-size:.75rem;"><?= date('d/m/Y à H:i', strtotime($com['created_at'])) ?></span>
                </div>
                <p class="mb-0 small" style="white-space:pre-wrap;"><?= nl2br(htmlspecialchars($com['contenu'])) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (!in_array($demande['statut'], ['cloturee','annulee_par_client','annulee_par_prestataire','expiree'])): ?>
        <form method="POST" action="index.php?action=demande_commenter" class="mt-2">
          <input type="hidden" name="id_demande" value="<?= (int)$demande['id_demande'] ?>">
          <div class="d-flex gap-2 align-items-start">
            <span class="avatar-circle flex-shrink-0" style="width:36px;height:36px;font-size:.85rem;">
              <?= htmlspecialchars(strtoupper(
                  mb_substr($_SESSION['user']['prenom'] ?? '', 0, 1) .
                  mb_substr($_SESSION['user']['nom'] ?? '', 0, 1)
              )) ?>
            </span>
            <div class="flex-grow-1">
              <textarea name="contenu" class="form-control form-control-sm" rows="2"
                        placeholder="Laisser un commentaire public sur cette demande..." required></textarea>
              <div class="mt-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-brand">
                  <i class="bi bi-send me-1"></i>Commenter
                </button>
                <?php if ($mesProfilPrestataire && !$estProprietaire
                    && ($mesProfilPrestataire['statut_validation'] ?? '') === 'validee'
                    && in_array($demande['statut'], ['ouverte','en_discussion'])
                    && !$maProposition): ?>
                  <button type="button" class="btn btn-sm btn-outline-brand"
                          data-bs-toggle="modal" data-bs-target="#modalPostuler">
                    <i class="bi bi-send-check me-1"></i>Postuler (privé)
                  </button>
                <?php elseif ($maProposition): ?>
                  <span class="badge badge-soft-success align-self-center">
                    <i class="bi bi-check-circle me-1"></i>Candidature envoyée
                  </span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </form>
      <?php endif; ?>
    </div>

  </div><!-- /col-lg-8 -->


  <!-- ===== Colonne droite ===== -->
  <div class="col-lg-4">

    <!-- Infos client -->
    <div class="da-card p-4 mb-3">
      <h6 class="mb-3">Client</h6>
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="avatar-circle">
          <?= htmlspecialchars(strtoupper(
              mb_substr($demande['client_prenom'] ?? $demande['prenom'] ?? '', 0, 1) .
              mb_substr($demande['client_nom']    ?? $demande['nom']    ?? '', 0, 1)
          )) ?>
        </span>
        <div>
          <div class="fw-semibold">
            <?= htmlspecialchars(
                ($demande['client_prenom'] ?? $demande['prenom'] ?? '') . ' ' .
                ($demande['client_nom']    ?? $demande['nom']    ?? '')
            ) ?>
          </div>
          <?php if (!empty($demande['client_email'])): ?>
            <div class="small text-muted">
              <?php if ($estProprietaire || $role === 'admin' || $estPrestataireRetenu): ?>
                <?= htmlspecialchars($demande['client_email']) ?>
              <?php else: ?>
                <span class="fst-italic text-muted">Email masqué</span>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <?php if (!empty($demande['telephone_contact'])): ?>
        <div class="small text-muted">
          <i class="bi bi-telephone me-1"></i>
          <?php if ($estProprietaire || $role === 'admin' || $estPrestataireRetenu): ?>
            <?= htmlspecialchars($demande['telephone_contact']) ?>
          <?php else: ?>
            <span class="fst-italic text-muted">Téléphone masqué</span>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Admin : changer le statut -->
    <?php if ($role === 'admin'): ?>
      <div class="da-card p-4 mb-3">
        <h6 class="mb-2">Changer le statut (admin)</h6>
        <form method="POST" action="index.php?action=demande_update_statut" class="d-flex gap-2">
          <input type="hidden" name="id_demande" value="<?= (int)$demande['id_demande'] ?>">
          <select name="statut" class="form-select form-select-sm">
            <?php foreach ($statutsAdmin as $s): ?>
              <option value="<?= $s ?>" <?= $demande['statut'] === $s ? 'selected' : '' ?>>
                <?= $statutLabel($s) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-sm btn-brand">OK</button>
        </form>
      </div>
    <?php endif; ?>

    <!-- Prestataire retenu (info) -->
    <?php if (!empty($demande['prestataire_nom']) && !in_array($demande['statut'], ['ouverte','en_discussion'])): ?>
      <div class="da-card p-4">
        <h6 class="mb-3">Prestataire retenu</h6>
        <div class="d-flex align-items-center gap-2">
          <span class="avatar-circle">
            <?= htmlspecialchars(strtoupper(
                mb_substr($demande['prestataire_prenom'] ?? '', 0, 1) .
                mb_substr($demande['prestataire_nom']    ?? '', 0, 1)
            )) ?>
          </span>
          <div class="fw-semibold">
            <?= htmlspecialchars(($demande['prestataire_prenom'] ?? '') . ' ' . ($demande['prestataire_nom'] ?? '')) ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

  </div><!-- /col-lg-4 -->
</div><!-- /row -->

<?php if ($mesProfilPrestataire && !$estProprietaire
    && ($mesProfilPrestataire['statut_validation'] ?? '') === 'validee'
    && !$maProposition): ?>
<!-- Modal : Postuler (candidature privée — visible par le client uniquement) -->
<div class="modal fade" id="modalPostuler" tabindex="-1" aria-labelledby="modalPostulerLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="index.php?action=demande_proposer">
        <input type="hidden" name="id_demande" value="<?= (int)$demande['id_demande'] ?>">
        <div class="modal-header">
          <h5 class="modal-title" id="modalPostulerLabel">
            <i class="bi bi-send-check me-2"></i>Postuler à cette demande
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info small mb-3">
            <i class="bi bi-shield-lock me-1"></i>
            Votre candidature sera <strong>visible uniquement par le client</strong>. Ce n'est pas un commentaire public.
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Votre message / présentation <span class="text-danger">*</span></label>
            <textarea name="message" class="form-control" rows="4" required
                      placeholder="Présentez votre approche, expérience, disponibilité... Ex : Je peux venir demain matin pour un diagnostic, j'ai 8 ans d'expérience en plomberie."></textarea>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Prix indicatif (€) <span class="text-muted small">(optionnel)</span></label>
              <input type="number" name="prix_indicatif" class="form-control" min="0" step="0.01" placeholder="Ex : 80">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Délai indicatif <span class="text-muted small">(optionnel)</span></label>
              <input type="text" name="delai_texte" class="form-control" placeholder="Ex : Disponible demain matin">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-brand">
            <i class="bi bi-send-check me-1"></i>Envoyer ma candidature (privée)
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
