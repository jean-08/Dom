<?php
require_once __DIR__ . '/../../utils/upload.php';
$pageTitle    = 'Mon Profil';
$pageSubtitle = 'Gérez vos informations personnelles et les paramètres de sécurité de votre compte';
$active       = 'profile';
require __DIR__ . '/../layouts/header.php';

$avatarUrl = UploadHelper::getAvatarUrl($u['photo_url'] ?? null, $u['prenom'] ?? 'User', $u['nom'] ?? '');
?>

<div class="row justify-content-center">
  <div class="col-lg-10">
    <div class="da-card p-4">

      <!-- Header Profil avec Avatar & Statut -->
      <div class="d-flex align-items-center gap-4 pb-4 mb-4 border-bottom flex-wrap">
        <div class="position-relative">
          <img id="profileAvatarPreview" src="<?= htmlspecialchars($avatarUrl) ?>" data-fallback-url="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="rounded-circle shadow-sm border border-3 border-white" style="width: 100px; height: 100px; object-fit: cover;">
          <span class="position-absolute bottom-0 end-0 bg-success border border-white border-2 rounded-circle p-2" title="Compte Actif"></span>
        </div>
        <div>
          <h3 class="mb-1 fw-bold"><?= htmlspecialchars(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')) ?></h3>
          <p class="text-muted mb-2"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($u['email'] ?? '') ?> • <span class="badge bg-primary text-capitalize"><?= htmlspecialchars($u['role'] ?? 'client') ?></span></p>
          <small class="text-secondary"><i class="bi bi-calendar-event me-1"></i>Membre depuis le <?= date('d/m/Y', strtotime($u['created_at'] ?? 'now')) ?></small>
        </div>
      </div>

      <!-- Navigation par Onglets -->
      <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active fw-semibold" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-pane" type="button" role="tab">
            <i class="bi bi-person-lines-fill me-2"></i>Informations personnelles
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-semibold" id="security-tab" data-bs-toggle="tab" data-bs-target="#security-pane" type="button" role="tab">
            <i class="bi bi-shield-lock-fill me-2"></i>Sécurité & Mot de passe
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-semibold" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings-pane" type="button" role="tab">
            <i class="bi bi-gear-fill me-2"></i>Paramètres du compte
          </button>
        </li>
      </ul>

      <div class="tab-content" id="profileTabsContent">

        <!-- Onglet 1 : Informations Personnelles -->
        <div class="tab-pane fade show active" id="info-pane" role="tabpanel">
          <form method="POST" action="index.php?action=profile" enctype="multipart/form-data">
            <input type="hidden" name="subaction" value="info">

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Prénom <span class="text-danger">*</span></label>
                <input type="text" name="prenom" class="form-control" required value="<?= htmlspecialchars($u['prenom'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($u['nom'] ?? '') ?>">
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Email principal <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($u['email'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Email secondaire <span class="text-muted small">(Optionnel)</span></label>
                <input type="email" name="email_secondaire" class="form-control" placeholder="secours@gmail.com" value="<?= htmlspecialchars($u['email_secondaire'] ?? '') ?>">
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Téléphone de contact</label>
                <input type="tel" name="telephone" class="form-control" placeholder="03X XX XXX XX" value="<?= htmlspecialchars($u['telephone'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Ville </label>
                <input type="text" name="ville" class="form-control" placeholder="Tana..." value="<?= htmlspecialchars($u['ville'] ?? '') ?>">
              </div>
            </div>

            <!-- <div class="mb-3">
              <label class="form-label fw-semibold">Adresse postale / Rue</label>
              <input type="text" name="adresse_rue" class="form-control" placeholder="12 rue de la Paix" value="<?= htmlspecialchars($u['adresse_rue'] ?? '') ?>">
            </div> -->

            <div class="mb-3">
              <label class="form-label fw-semibold">Bio </label>
              <textarea name="bio" class="form-control" rows="3" "><?= htmlspecialchars($u['bio'] ?? '') ?></textarea>
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold">Photo de profil </label>
              <input type="file" name="photo_file" id="photoFileInput" class="form-control" accept="image/jpeg,image/png,image/webp">
              <div class="mt-3 d-flex align-items-center gap-3">
                <img id="photoPreview" src="<?= htmlspecialchars($avatarUrl) ?>" data-fallback-url="<?= htmlspecialchars($avatarUrl) ?>" alt="Aperçu de la photo de profil" class="rounded-circle border border-2 border-white shadow-sm" style="width: 84px; height: 84px; object-fit: cover;">
                <div class="text-muted small">L’aperçu se met à jour immédiatement après votre sélection.</div>
              </div>
            </div>

            <button type="submit" class="btn btn-brand">
              <i class="bi bi-save me-1"></i>Enregistrer les modifications
            </button>
          </form>
        </div>

        <!-- Onglet 2 : Sécurité -->
        <div class="tab-pane fade" id="security-pane" role="tabpanel">
          <form method="POST" action="index.php?action=profile" class="col-md-8">
            <input type="hidden" name="subaction" value="password">

            <div class="mb-3">
              <label class="form-label fw-semibold">Mot de passe actuel <span class="text-danger">*</span></label>
              <input type="password" name="ancien_mot_de_passe" class="form-control" required placeholder="••••••••">
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Nouveau mot de passe <span class="text-danger">*</span></label>
              <input type="password" name="nouveau_mot_de_passe" class="form-control" required minlength="8" placeholder="Au moins 8 caractères">
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold">Confirmer le nouveau mot de passe <span class="text-danger">*</span></label>
              <input type="password" name="confirm_mot_de_passe" class="form-control" required minlength="8" placeholder="Au moins 8 caractères">
            </div>

            <button type="submit" class="btn btn-warning">
              <i class="bi bi-key-fill me-1"></i>Modifier mon mot de passe
            </button>
          </form>
        </div>

        <!-- Onglet 3 : Paramètres -->
        <div class="tab-pane fade" id="settings-pane" role="tabpanel">
          <div class="alert alert-info">
            <h6 class="alert-heading fw-bold mb-1"><i class="bi bi-info-circle-fill me-2"></i>Informations de compte</h6>
            <p class="mb-0 small">Votre compte est actuellement <strong>Actif</strong>. Votre rôle principal est <strong><?= htmlspecialchars(strtoupper($u['role'] ?? 'client')) ?></strong>.</p>
          </div>

          <div class="card p-3 border-danger border-opacity-25 bg-danger bg-opacity-10 mt-4">
            <h6 class="text-danger fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Zone sensible — Confidentialité & RGPD</h6>
            <p class="small text-muted mb-3">Conformément au RGPD, vous disposez d'un droit d'accès et de rectification de vos données. Pour demander la suppression définitive de votre compte ou un export complet, contactez le support administrateur.</p>
            <div>
              <a href="mailto:support@domassist.local" class="btn btn-sm btn-outline-danger"><i class="bi bi-envelope-exclamation me-1"></i>Contacter le DPO / Support</a>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('photoFileInput');
    const preview = document.getElementById('photoPreview');
    const headerPreview = document.getElementById('profileAvatarPreview');

    if (!input || (!preview && !headerPreview)) {
      return;
    }

    const applyPreview = (sourceUrl) => {
      if (preview) preview.src = sourceUrl;
      if (headerPreview) headerPreview.src = sourceUrl;
    };

    const fallbackUrl = (preview?.dataset.fallbackUrl || headerPreview?.dataset.fallbackUrl || '');

    input.addEventListener('change', function () {
      const file = this.files && this.files[0];
      if (!file) {
        if (fallbackUrl) applyPreview(fallbackUrl);
        return;
      }

      const reader = new FileReader();
      reader.onload = function (event) {
        if (event.target?.result) {
          applyPreview(event.target.result);
        }
      };
      reader.readAsDataURL(file);
    });
  });
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
