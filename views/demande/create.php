<?php
$pageTitle = 'Nouvelle demande';
$pageSubtitle = 'Décrivez votre besoin en détail pour recevoir les propositions des prestataires';
$active = 'demande_create';
$breadcrumb = [['label' => 'Dashboard', 'action' => 'dashboard'], ['label' => 'Nouvelle demande']];
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../components/breadcrumb.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-9">
    <div class="da-card p-4 shadow-sm rounded-3">
      <form method="POST" action="index.php?action=demande_create" enctype="multipart/form-data">

        <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-card-checklist me-2"></i>Informations générales</h5>

        <!-- Titre -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Titre de la demande <span class="text-danger">*</span></label>
          <input type="text" name="titre" class="form-control"
                 placeholder="Ex : Fuite d'eau sous l'évier de la cuisine" required
                 value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>">
        </div>

        <!-- Catégorie -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Catégorie de service <span class="text-danger">*</span></label>
          <select name="id_category" class="form-select" required>
            <option value="">— Choisir une catégorie —</option>
            <?php foreach (($services ?? []) as $s): ?>
              <option value="<?= (int)($s['id_category'] ?? $s['id_service'] ?? 0) ?>"
                <?= (int)($_POST['id_category'] ?? 0) === (int)($s['id_category'] ?? $s['id_service'] ?? 0) ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['libelle'] ?? $s['nom'] ?? '') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Description -->
        <div class="mb-3">
          <label class="form-label fw-semibold">Description détaillée du besoin</label>
          <textarea name="description" class="form-control" rows="4"
                    placeholder="Décrivez votre problème ou projet en détail pour aider les prestataires à estimer leur intervention..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <!-- Urgence -->
        <div class="mb-4">
          <label class="form-label fw-semibold">Niveau d'urgence</label>
          <div class="row g-2">
            <div class="col-md-4">
              <div class="form-check custom-option p-3 border rounded-3 text-center">
                <input class="form-check-input float-none mb-1" type="radio" name="urgence" id="urg_normal" value="normal"
                       <?= ($_POST['urgence'] ?? 'normal') === 'normal' ? 'checked' : '' ?>>
                <label class="form-check-label d-block fw-semibold" for="urg_normal">
                  <span class="badge bg-secondary mb-1">Normal</span>
                  <small class="d-block text-muted">Intervention sous 7 jours</small>
                </label>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-check custom-option p-3 border rounded-3 text-center">
                <input class="form-check-input float-none mb-1" type="radio" name="urgence" id="urg_48h" value="sous_48h"
                       <?= ($_POST['urgence'] ?? '') === 'sous_48h' ? 'checked' : '' ?>>
                <label class="form-check-label d-block fw-semibold" for="urg_48h">
                  <span class="badge bg-warning text-dark mb-1">Sous 48h</span>
                  <small class="d-block text-muted">Intervention rapide</small>
                </label>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-check custom-option p-3 border rounded-3 text-center">
                <input class="form-check-input float-none mb-1" type="radio" name="urgence" id="urg_urgent" value="urgent"
                       <?= ($_POST['urgence'] ?? '') === 'urgent' ? 'checked' : '' ?>>
                <label class="form-check-label d-block fw-semibold" for="urg_urgent">
                  <span class="badge bg-danger mb-1">Urgent</span>
                  <small class="d-block text-muted">Dépannage immédiat</small>
                </label>
              </div>
            </div>
          </div>
        </div>

        <hr class="my-4">

        <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-camera me-2"></i>Photos d'illustration du problème</h5>
        <div class="mb-4">
          <label class="form-label fw-semibold">Ajouter des photos <span class="text-muted small">(1 à 5 photos, max 5 Mo par fichier)</span></label>
          <input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" class="form-control">
          <div class="form-text">Formats autorisés : JPG, PNG, WEBP. Des visuels précis facilitent l'établissement d'une proposition adaptée.</div>
        </div>

        <hr class="my-4">

        <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-geo-alt me-2"></i>Localisation & Accès</h5>

        <!-- Localisation -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Ville <span class="text-danger">*</span></label>
            <input type="text" name="ville" class="form-control" placeholder="Ex : Paris" required
                   value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>">
          </div>
          <!-- <div class="col-md-6">
            <label class="form-label fw-semibold">Code postal <span class="text-muted small">(optionnel)</span></label>
            <input type="text" name="code_postal" class="form-control" placeholder="Ex : 75001"
                   value="<?= htmlspecialchars($_POST['code_postal'] ?? '') ?>">
          </div> -->
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Adresse exacte <span class="text-muted small">(visible uniquement par le prestataire sélectionné)</span></label>
          <input type="text" name="adresse" class="form-control" placeholder="Numéro, bâtiment..."
                 value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>">
        </div>

        <!-- Contact -->
        <div class="mb-4">
          <label class="form-label fw-semibold">Téléphone de contact <span class="text-muted small">(optionnel)</span></label>
          <input type="tel" name="telephone_contact" class="form-control" placeholder="03X XX XXX XX"
                 value="<?= htmlspecialchars($_POST['telephone_contact'] ?? '') ?>">
        </div>

        <hr class="my-4">

        <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-piggy-bank me-2"></i>Budget & Disponibilités</h5>

        <!-- Budget -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Budget min. (Ar) <span class="text-muted small">(optionnel)</span></label>
            <input type="number" name="budget_min" class="form-control" min="0" step="5000" placeholder="Ex : 20000"
                   value="<?= htmlspecialchars($_POST['budget_min'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Budget max. (Ar) <span class="text-muted small">(optionnel)</span></label>
            <input type="number" name="budget_max" class="form-control" min="0" step="5000" placeholder="Ex : 100000"
                   value="<?= htmlspecialchars($_POST['budget_max'] ?? '') ?>">
          </div>
        </div>

        <!-- Disponibilités -->
        <div class="mb-4">
          <label class="form-label fw-semibold">Disponibilités client <span class="text-muted small">(optionnel)</span></label>
          <input type="text" name="disponibilites_client" class="form-control"
                 placeholder="Ex : Soirées en semaine à partir de 18h, ou Samedi toute la journée"
                 value="<?= htmlspecialchars($_POST['disponibilites_client'] ?? '') ?>">
        </div>

        <div class="d-flex gap-2 justify-content-end mt-4">
          <a href="index.php?action=dashboard" class="btn btn-outline-secondary">Annuler</a>
          <button type="submit" class="btn btn-brand px-4">
            <i class="bi bi-send me-1"></i>Publier la demande
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
