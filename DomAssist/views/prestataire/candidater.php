<?php
$pageTitle    = 'Devenir prestataire';
$pageSubtitle = 'Constituez votre dossier professionnel pour proposer vos services sur DomAssist';
$active       = 'prestataire_candidater';
require __DIR__ . '/../layouts/header.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-9 col-xl-8">

    <?php if (!empty($profilExistant['statut_validation']) && $profilExistant['statut_validation'] === 'rejetee'): ?>
      <div class="alert alert-warning shadow-sm mb-4">
        <h5 class="alert-heading text-danger mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Candidature précédente non retenue</h5>
        <p class="mb-1">Votre précédente candidature avait été rejetée pour le motif suivant :</p>
        <blockquote class="blockquote fs-6 text-dark bg-white p-2 rounded border-start border-danger border-4 mb-2">
          <?= htmlspecialchars($profilExistant['motif_rejet'] ?? 'Dossier incomplet ou non conforme.') ?>
        </blockquote>
        <p class="mb-0 small">Vous pouvez compléter et resoumettre votre dossier ci-dessous avec des informations mises à jour.</p>
      </div>
    <?php endif; ?>

    <div class="da-card p-4">
      <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:48px; height:48px; font-size:1.5rem;">
          <i class="bi bi-briefcase-fill"></i>
        </div>
        <div>
          <h4 class="mb-0">Dossier de candidature professionnel</h4>
          <span class="text-muted small">Examen et validation sous 24h à 48h par l'équipe d'administration</span>
        </div>
      </div>

      <form method="POST" action="index.php?action=prestataire_candidater" enctype="multipart/form-data">

        <!-- 1. Présentation & CV -->
        <h5 class="text-primary mb-3"><i class="bi bi-person-vcard me-2"></i>1. Présentation & Curriculum Vitae</h5>

        <div class="mb-3">
          <label class="form-label fw-semibold">Présentation / Spécialité <span class="text-danger">*</span></label>
          <textarea name="bio" class="form-control" rows="3" required
                    placeholder="Ex : Artisans plombier avec 8 ans d'expérience, spécialisé en installation sanitaires et recherche de fuites..."><?= htmlspecialchars($_POST['bio'] ?? $profilExistant['bio'] ?? '') ?></textarea>
          <div class="form-text">Présentez brièvement vos activités phares et votre niveau d'expertise.</div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Lettre de motivation / Parcours professionnel</label>
          <textarea name="lettre_motivation" class="form-control" rows="4"
                    placeholder="Expliquez votre parcours, vos références ou pourquoi vous souhaitez rejoindre la communauté DomAssist..."><?= htmlspecialchars($_POST['lettre_motivation'] ?? $profilExistant['lettre_motivation'] ?? '') ?></textarea>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold">Curriculum Vitae (PDF) <span class="text-danger">*</span></label>
          <input type="file" name="cv_file" class="form-control" accept="application/pdf" <?= empty($profilExistant['document_cv_url']) ? 'required' : '' ?>>
          <div class="form-text">Joignez votre CV au format PDF (max 10 Mo).
            <?php if (!empty($profilExistant['document_cv_url'])): ?>
              <span class="badge bg-success ms-2"><i class="bi bi-file-earmark-check me-1"></i>CV actuellement enregistré</span>
            <?php endif; ?>
          </div>
        </div>

        <!-- 2. Compétences & Spécialités -->
        <h5 class="text-primary mb-3 border-top pt-3"><i class="bi bi-tools me-2"></i>2. Domaines de compétences</h5>
        <div class="mb-4">
          <label class="form-label fw-semibold mb-2">Sélectionnez vos catégories de services :</label>
          <div class="row g-2">
            <?php foreach (($categories ?? []) as $cat): ?>
              <div class="col-md-6">
                <div class="form-check p-2 border rounded bg-light">
                  <input class="form-check-input ms-1 me-2" type="checkbox" name="categories[]" value="<?= (int)$cat['id_category'] ?>" id="cat_<?= (int)$cat['id_category'] ?>">
                  <label class="form-check-label fw-medium" for="cat_<?= (int)$cat['id_category'] ?>">
                    <?= htmlspecialchars($cat['libelle'] ?? '') ?>
                  </label>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- 3. Expérience & Logistique -->
        <h5 class="text-primary mb-3 border-top pt-3"><i class="bi bi-geo-alt me-2"></i>3. Expérience & Zone d'intervention</h5>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Années d'expérience professionnelle</label>
            <input type="number" name="experience_annees" class="form-control" min="0" max="60"
                   placeholder="Ex : 5"
                   value="<?= htmlspecialchars($_POST['experience_annees'] ?? $profilExistant['experience_annees'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Moyen de déplacement</label>
            <select name="moyen_deplacement" class="form-select">
              <option value="Vehicule personnel" <?= ($_POST['moyen_deplacement'] ?? $profilExistant['moyen_deplacement'] ?? '') === 'Vehicule personnel' ? 'selected' : '' ?>>Véhicule léger personnel</option>
              <option value="Utilitaire pro" <?= ($_POST['moyen_deplacement'] ?? $profilExistant['moyen_deplacement'] ?? '') === 'Utilitaire pro' ? 'selected' : '' ?>>Fourgon / Utilitaire professionnel</option>
              <option value="Deux-roues / Scooter" <?= ($_POST['moyen_deplacement'] ?? $profilExistant['moyen_deplacement'] ?? '') === 'Deux-roues / Scooter' ? 'selected' : '' ?>>Deux-roues / Moto / Scooter</option>
              <option value="Transports en commun" <?= ($_POST['moyen_deplacement'] ?? $profilExistant['moyen_deplacement'] ?? '') === 'Transports en commun' ? 'selected' : '' ?>>Transports en commun</option>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Zone d'intervention (Villes / Départements / Rayon)</label>
          <input type="text" name="zone_intervention" class="form-control"
                 placeholder="Ex : Paris, Île-de-France, rayon 30km autour de Lyon"
                 value="<?= htmlspecialchars($_POST['zone_intervention'] ?? $profilExistant['zone_intervention'] ?? '') ?>">
        </div>

        <div class="row mb-4">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Plages de disponibilités récurrentes</label>
            <select name="disponibilites_type" class="form-select">
              <option value="Semaine et Week-end">Semaine et Week-end</option>
              <option value="Semaine (Lundi au Vendredi)">Semaine (Lundi au Vendredi)</option>
              <option value="Soirées et Week-ends">Soirées et Week-ends</option>
              <option value="24/7 Astreinte">Astreinte 24h/24 et 7j/7</option>
            </select>
          </div>
          <div class="col-md-6 d-flex align-items-center mt-3 mt-md-4">
            <div class="form-check form-switch fs-6">
              <input class="form-check-input me-2" type="checkbox" name="accepte_urgences" id="accepte_urgences" value="1" <?= !empty($_POST['accepte_urgences']) || !empty($profilExistant['accepte_urgences']) ? 'checked' : '' ?>>
              <label class="form-check-label fw-semibold" for="accepte_urgences">
                J'accepte les demandes d'intervention d'urgence (sous 48h)
              </label>
            </div>
          </div>
        </div>

        <!-- 4. Informations légales (optionnelles) -->
        <h5 class="text-primary mb-3 border-top pt-3"><i class="bi bi-shield-check me-2"></i>4. Informations légales (Optionnelles / Pro)</h5>
        <div class="row mb-4">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Numéro SIRET (si entreprise/micro-entreprise)</label>
            <input type="text" name="siret" class="form-control" placeholder="14 chiffres" maxlength="20"
                   value="<?= htmlspecialchars($_POST['siret'] ?? $profilExistant['siret'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Assurance Responsabilité Civile / Décennale</label>
            <input type="text" name="assurances_pro" class="form-control" placeholder="Compagnie d'assurance & N° de police"
                   value="<?= htmlspecialchars($_POST['assurances_pro'] ?? $profilExistant['assurances_pro'] ?? '') ?>">
          </div>
        </div>

        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-brand btn-lg py-2">
            <i class="bi bi-send-fill me-2"></i>Soumettre ma candidature complète
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
