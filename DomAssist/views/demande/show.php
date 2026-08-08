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
          <div class="col-auto"><i class="bi bi-house me-1"></i><?= htmlspecialchars($demande['adresse']) ?></div>
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
        Aucune proposition reçue pour l'instant. Les prestataires de votre zone seront notifiés.
      </div>
    <?php endif; ?>

    <!-- ===== MESSAGERIE PRIVÉE RATTACHÉE À LA DEMANDE ===== -->
    <?php if (!empty($threads) || $mesProfilPrestataire): ?>
      <div class="da-card p-4 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Discussion privée</h6>
          <span class="badge badge-soft-info"><i class="bi bi-shield-lock me-1"></i>Messagerie sécurisée</span>
        </div>

        <?php if (empty($threads) && $mesProfilPrestataire): ?>
          <!-- Premier message du prestataire -->
          <div class="p-3 bg-light rounded mb-3 text-center text-muted small">
            Envoyez un message direct au client pour poser une question ou clarifier la demande.
          </div>
          <form method="POST" action="index.php?action=message_send">
            <input type="hidden" name="id_demande" value="<?= (int)$demande['id_demande'] ?>">
            <input type="hidden" name="id_profile_prestataire" value="<?= (int)$mesProfilPrestataire['id_prestataire'] ?>">
            <div class="input-group">
              <textarea name="contenu" class="form-control" rows="2" placeholder="Écrire votre message au client..." required></textarea>
              <button class="btn btn-brand" type="submit"><i class="bi bi-send me-1"></i>Envoyer</button>
            </div>
          </form>

        <?php elseif (count($threads) === 1): ?>
          <?php
            $t = $threads[0];
            $idThread = (int)$t['id_thread'];
            $msgs = $messagesByThread[$idThread] ?? [];
            $nomInterlocuteur = $estClient || $role === 'admin'
                ? trim(($t['prest_prenom'] ?? '') . ' ' . ($t['prest_nom'] ?? ''))
                : trim(($t['client_prenom'] ?? '') . ' ' . ($t['client_nom'] ?? ''));
          ?>
          <div class="small text-muted mb-2">
            Conversation avec <strong><?= htmlspecialchars($nomInterlocuteur ?: 'Utilisateur') ?></strong>
          </div>

          <div class="border rounded p-3 mb-3 bg-light" style="max-height: 350px; overflow-y: auto;">
            <?php if (empty($msgs)): ?>
              <p class="text-muted small text-center my-3"><i class="bi bi-chat-dots me-1"></i>Aucun message échangé pour l'instant.</p>
            <?php else: ?>
              <?php foreach ($msgs as $msg): ?>
                <?php $isMe = (int)$msg['id_sender'] === $idUserSess; ?>
                <div class="d-flex mb-2 <?= $isMe ? 'justify-content-end' : 'justify-content-start' ?>">
                  <div class="p-2 px-3 rounded shadow-sm <?= $isMe ? 'bg-brand-blue text-white' : 'bg-white text-dark border' ?>" style="max-width: 80%;">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                      <span class="fw-semibold small <?= $isMe ? 'text-white-50' : 'text-primary' ?>" style="font-size: 0.75rem;">
                        <?= htmlspecialchars(($msg['sender_prenom'] ?? '') . ' ' . ($msg['sender_nom'] ?? '')) ?>
                      </span>
                      <span class="small <?= $isMe ? 'text-white-50' : 'text-muted' ?>" style="font-size: 0.7rem;">
                        <?= date('d/m H:i', strtotime($msg['created_at'])) ?>
                      </span>
                    </div>
                    <div class="small" style="white-space: pre-wrap; word-break: break-word;"><?= htmlspecialchars($msg['contenu']) ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <?php if (!in_array($demande['statut'], ['cloturee','annulee_par_client','annulee_par_prestataire','expiree'])): ?>
            <form method="POST" action="index.php?action=message_send">
              <input type="hidden" name="id_demande" value="<?= (int)$demande['id_demande'] ?>">
              <input type="hidden" name="id_profile_prestataire" value="<?= (int)$t['id_profile_prestataire'] ?>">
              <div class="input-group">
                <textarea name="contenu" class="form-control form-control-sm" rows="2" placeholder="Écrire un message..." required></textarea>
                <button class="btn btn-brand btn-sm" type="submit"><i class="bi bi-send me-1"></i>Envoyer</button>
              </div>
            </form>
          <?php endif; ?>

        <?php else: ?>
          <!-- Plusieurs fils (Client avec plusieurs prestataires) -->
          <ul class="nav nav-pills mb-3 gap-1" id="chatTabs" role="tablist">
            <?php foreach ($threads as $idx => $t): ?>
              <?php
                $idT = (int)$t['id_thread'];
                $nomP = trim(($t['prest_prenom'] ?? '') . ' ' . ($t['prest_nom'] ?? ''));
                $nbM = count($messagesByThread[$idT] ?? []);
              ?>
              <li class="nav-item" role="presentation">
                <button class="nav-link btn-sm <?= $idx === 0 ? 'active' : '' ?>"
                        id="tab-thread-<?= $idT ?>"
                        data-bs-toggle="pill"
                        data-bs-target="#content-thread-<?= $idT ?>"
                        type="button" role="tab">
                  <i class="bi bi-person me-1"></i><?= htmlspecialchars($nomP) ?>
                  <?php if ($nbM > 0): ?>
                    <span class="badge bg-secondary ms-1"><?= $nbM ?></span>
                  <?php endif; ?>
                </button>
              </li>
            <?php endforeach; ?>
          </ul>

          <div class="tab-content" id="chatTabsContent">
            <?php foreach ($threads as $idx => $t): ?>
              <?php
                $idT = (int)$t['id_thread'];
                $msgs = $messagesByThread[$idT] ?? [];
                $nomP = trim(($t['prest_prenom'] ?? '') . ' ' . ($t['prest_nom'] ?? ''));
              ?>
              <div class="tab-pane fade <?= $idx === 0 ? 'show active' : '' ?>" id="content-thread-<?= $idT ?>" role="tabpanel">
                <div class="border rounded p-3 mb-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                  <?php if (empty($msgs)): ?>
                    <p class="text-muted small text-center my-3"><i class="bi bi-chat-dots me-1"></i>Aucun message dans cette discussion.</p>
                  <?php else: ?>
                    <?php foreach ($msgs as $msg): ?>
                      <?php $isMe = (int)$msg['id_sender'] === $idUserSess; ?>
                      <div class="d-flex mb-2 <?= $isMe ? 'justify-content-end' : 'justify-content-start' ?>">
                        <div class="p-2 px-3 rounded shadow-sm <?= $isMe ? 'bg-brand-blue text-white' : 'bg-white text-dark border' ?>" style="max-width: 80%;">
                          <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                            <span class="fw-semibold small <?= $isMe ? 'text-white-50' : 'text-primary' ?>" style="font-size: 0.75rem;">
                              <?= htmlspecialchars(($msg['sender_prenom'] ?? '') . ' ' . ($msg['sender_nom'] ?? '')) ?>
                            </span>
                            <span class="small <?= $isMe ? 'text-white-50' : 'text-muted' ?>" style="font-size: 0.7rem;">
                              <?= date('d/m H:i', strtotime($msg['created_at'])) ?>
                            </span>
                          </div>
                          <div class="small" style="white-space: pre-wrap; word-break: break-word;"><?= htmlspecialchars($msg['contenu']) ?></div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>

                <?php if (!in_array($demande['statut'], ['cloturee','annulee_par_client','annulee_par_prestataire','expiree'])): ?>
                  <form method="POST" action="index.php?action=message_send">
                    <input type="hidden" name="id_demande" value="<?= (int)$demande['id_demande'] ?>">
                    <input type="hidden" name="id_profile_prestataire" value="<?= (int)$t['id_profile_prestataire'] ?>">
                    <div class="input-group">
                      <textarea name="contenu" class="form-control form-control-sm" rows="2" placeholder="Répondre à <?= htmlspecialchars($nomP) ?>..." required></textarea>
                      <button class="btn btn-brand btn-sm" type="submit"><i class="bi bi-send me-1"></i>Envoyer</button>
                    </div>
                  </form>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

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
              <?php elseif ($estProprietaire): ?>
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
        </div>
      <?php elseif ($estPrestataireRetenu): ?>
        <div class="da-card p-4 mb-3 text-center">
          <a href="index.php?action=solution_create&id_diagnostic=<?= (int)$diagnostic['id_diagnostic'] ?>"
             class="btn btn-brand btn-sm">Proposer une solution</a>
        </div>
      <?php endif; ?>

    <?php elseif ($estPrestataireRetenu && $demande['statut'] === 'engagee'): ?>
      <div class="da-card p-4 mb-3 text-center">
        <p class="text-muted mb-2">Aucun diagnostic n'a encore été proposé.</p>
        <a href="index.php?action=diagnostic_create&id_demande=<?= (int)$demande['id_demande'] ?>"
           class="btn btn-brand btn-sm">Proposer un diagnostic</a>
      </div>
    <?php endif; ?>

    <!-- Démarrer l'intervention -->
    <?php if ($estPrestataireRetenu && $demande['statut'] === 'solution_proposee'): ?>
      <div class="da-card p-4 mb-3 text-center">
        <p class="text-muted mb-2">La solution est validée : vous pouvez démarrer l'intervention.</p>
        <a href="index.php?action=intervention_create&id_demande=<?= (int)$demande['id_demande'] ?>"
           class="btn btn-brand btn-sm">Démarrer l'intervention</a>
      </div>
    <?php endif; ?>

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
            <div class="small text-muted"><?= htmlspecialchars($demande['client_email']) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <?php if (!empty($demande['telephone_contact'])): ?>
        <div class="small text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($demande['telephone_contact']) ?></div>
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

<?php require __DIR__ . '/../layouts/footer.php'; ?>
