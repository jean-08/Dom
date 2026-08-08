<?php
$pageTitle    = 'Accueil — Plateforme de services à domicile';
$pageSubtitle = 'Confiance, transparence et qualité pour tous vos travaux et dépannages.';
$active       = 'home';

if (session_status() === PHP_SESSION_NONE) session_start();
$userSess = $_SESSION['user'] ?? null;

function getCategoryIconClass($libelle) {
    $lib = mb_strtolower($libelle ?? '');
    if (str_contains($lib, 'plomb')) return 'bi-droplet-fill text-info';
    if (str_contains($lib, 'électr') || str_contains($lib, 'electr')) return 'bi-lightning-charge-fill text-warning';
    if (str_contains($lib, 'serrur')) return 'bi-key-fill text-danger';
    if (str_contains($lib, 'peint')) return 'bi-paint-bucket text-primary';
    if (str_contains($lib, 'jardin') || str_contains($lib, 'espace')) return 'bi-tree-fill text-success';
    if (str_contains($lib, 'ménag') || str_contains($lib, 'menag') || str_contains($lib, 'nettoy')) return 'bi-stars text-info';
    if (str_contains($lib, 'chauff')) return 'bi-fire text-danger';
    if (str_contains($lib, 'clim')) return 'bi-snow text-primary';
    if (str_contains($lib, 'menuis') || str_contains($lib, 'bricol')) return 'bi-hammer text-secondary';
    if (str_contains($lib, 'inform') || str_contains($lib, 'tech')) return 'bi-laptop text-primary';
    return 'bi-wrench-adjustable text-primary';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="DomAssist - Publiez vos besoins en dépannage et travaux à domicile, comparez les propositions de prestataires qualifiés et échangez en direct.">
  <title>DomAssist — Services et dépannage à domicile de confiance</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="public/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar Publique -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3 sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold fs-4 d-flex align-items-center gap-2" href="index.php?action=home">
      <i class="bi bi-house-gear-fill text-warning"></i>
      <span class="da-brand-font">DomAssist</span>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navPublic">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navPublic">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center gap-3">
        <li class="nav-item"><a class="nav-link text-white-50" href="#garanties">Garanties</a></li>
        <li class="nav-item"><a class="nav-link text-white-50" href="#categories">Services</a></li>
        <li class="nav-item"><a class="nav-link text-white-50" href="#comment-ca-marche">Comment ça marche</a></li>
        <?php if ($userSess): ?>
          <li class="nav-item">
            <a class="btn btn-brand btn-sm px-3 shadow-sm" href="index.php?action=dashboard">
              <i class="bi bi-speedometer2 me-1"></i>Mon Dashboard
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link text-white" href="index.php?action=login">Connexion</a></li>
          <li class="nav-item">
            <a class="btn btn-brand btn-sm px-3 shadow-sm" href="index.php?action=register">
              <i class="bi bi-person-plus me-1"></i>S'inscrire
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">

  <!-- HERO SECTION -->
  <div class="hero-section text-center text-lg-start mb-5 animate-fade-in">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <span class="badge badge-soft-info mb-3 px-3 py-2 fs-6">
          <i class="bi bi-shield-check me-1"></i> Plateforme V3 de mise en relation de confiance
        </span>
        <h1 class="display-4 fw-bold mb-3">Vos travaux & dépannages en toute sérénité</h1>
        <p class="lead mb-4 text-slate-300">
          Publiez gratuitement votre besoin en quelques clics, recevez des offres personnalisées de prestataires qualifiés dans votre zone, et échangez en direct en toute transparence.
        </p>
        <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
          <?php if ($userSess): ?>
            <a href="index.php?action=demande_create" class="btn btn-brand btn-lg px-4 shadow">
              <i class="bi bi-plus-circle me-2"></i>Publier une demande
            </a>
          <?php else: ?>
            <a href="index.php?action=register" class="btn btn-brand btn-lg px-4 shadow">
              <i class="bi bi-arrow-right-circle me-2"></i>Commencer gratuitement
            </a>
            <a href="index.php?action=login" class="btn btn-outline-light btn-lg px-4">
              Se connecter
            </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-lg-5 text-center">
        <div class="glass-card p-4 text-white shadow-lg">
          <i class="bi bi-tools fs-1 text-warning mb-3 d-block"></i>
          <h5 class="fw-bold">Vous êtes un professionnel du bâtiment ?</h5>
          <p class="small text-white-50 mb-3">Développez votre clientèle locale, recevez des opportunités ciblées selon vos compétences et gérez vos interventions facilement.</p>
          <a href="index.php?action=prestataire_candidater" class="btn btn-warning btn-sm text-dark fw-bold px-3 shadow-sm">
            <i class="bi bi-briefcase me-1"></i>Devenir Prestataire
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- STATISTIQUES EN CHIFFRES -->
  <div class="row g-3 mb-5">
    <div class="col-md-4">
      <div class="da-stat-card">
        <div class="icon-box bg-brand-blue"><i class="bi bi-check2-square"></i></div>
        <div>
          <div class="value">100%</div>
          <div class="label">Propositions transparentes & comparables</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="da-stat-card">
        <div class="icon-box bg-brand-teal"><i class="bi bi-shield-lock"></i></div>
        <div>
          <div class="value">Profils vérifiés</div>
          <div class="label">Contrôle admin & dossiers validés</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="da-stat-card">
        <div class="icon-box bg-brand-amber"><i class="bi bi-chat-heart"></i></div>
        <div>
          <div class="value">Direct & Privé</div>
          <div class="label">Messagerie contextuelle sur chaque demande</div>
        </div>
      </div>
    </div>
  </div>

  <!-- GARANTIES DE CONFIANCE -->
  <section id="garanties" class="mb-5">
    <div class="text-center mb-4">
      <h2 class="fw-bold">Pourquoi choisir DomAssist ?</h2>
      <p class="text-muted">Une expérience repensée pour garantir la satisfaction du client et l'excellence des intervenants.</p>
    </div>

    <div class="row g-4">
      <div class="col-md-6 col-lg-3">
        <div class="da-card da-card-interactive p-4 h-100 text-center">
          <div class="avatar-circle mx-auto mb-3 bg-brand-blue" style="width: 54px; height: 54px; font-size: 1.5rem;">
            <i class="bi bi-geo-alt-fill"></i>
          </div>
          <h6 class="fw-bold mb-2">Matching Géolocalisé</h6>
          <p class="small text-muted mb-0">Vos demandes sont transmises aux prestataires qualifiés intervenant précisément dans votre ville ou rayon d'action.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="da-card da-card-interactive p-4 h-100 text-center">
          <div class="avatar-circle mx-auto mb-3 bg-brand-teal" style="width: 54px; height: 54px; font-size: 1.5rem;">
            <i class="bi bi-diagram-3-fill"></i>
          </div>
          <h6 class="fw-bold mb-2">Propositions en Concurrence</h6>
          <p class="small text-muted mb-0">Pas d'attribution automatique. Vous recevez plusieurs offres (prix, délais, message) et choisissez librement votre prestataire.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="da-card da-card-interactive p-4 h-100 text-center">
          <div class="avatar-circle mx-auto mb-3 bg-brand-purple" style="width: 54px; height: 54px; font-size: 1.5rem;">
            <i class="bi bi-chat-left-dots-fill"></i>
          </div>
          <h6 class="fw-bold mb-2">Messagerie Intégrée</h6>
          <p class="small text-muted mb-0">Posez des questions, précisez vos créneaux et échangez des photos en toute confidentialité sur le fil de discussion de la demande.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="da-card da-card-interactive p-4 h-100 text-center">
          <div class="avatar-circle mx-auto mb-3 bg-brand-amber" style="width: 54px; height: 54px; font-size: 1.5rem;">
            <i class="bi bi-star-fill"></i>
          </div>
          <h6 class="fw-bold mb-2">Réputation & Avis Réels</h6>
          <p class="small text-muted mb-0">Les avis sont uniquement déposés suite à une intervention clôturée pour une totale authenticité des notes.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- SECTEURS & CATÉGORIES -->
  <section id="categories" class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <div>
        <h2 class="fw-bold mb-1">Domaines d'intervention</h2>
        <p class="text-muted mb-0">Découvrez l'ensemble de nos catégories de services spécialisés.</p>
      </div>
    </div>

    <div class="row g-3">
      <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $cat): ?>
          <?php $lib = $cat['libelle'] ?? $cat['nom'] ?? 'Service'; ?>
          <div class="col-6 col-md-4 col-lg-3">
            <div class="da-card da-card-interactive p-3 d-flex align-items-center gap-3">
              <div class="icon-box rounded-3 p-2 bg-light border fs-4">
                <i class="bi <?= getCategoryIconClass($lib) ?>"></i>
              </div>
              <div>
                <div class="fw-semibold small text-dark"><?= htmlspecialchars($lib) ?></div>
                <div class="small text-muted" style="font-size: 0.75rem;">Service vérifié</div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center text-muted py-3">
          <i class="bi bi-tools me-1"></i> Retrouvez toutes nos catégories lors de la création d'une demande.
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- COMMENT ÇA MARCHE -->
  <section id="comment-ca-marche" class="mb-5">
    <div class="da-card p-5 bg-white">
      <div class="text-center mb-5">
        <h2 class="fw-bold">Comment ça marche ?</h2>
        <p class="text-muted">3 étapes simples pour réussir tous vos travaux d'aménagement et de dépannage.</p>
      </div>

      <div class="row g-4">
        <div class="col-md-4 text-center">
          <div class="badge rounded-circle bg-brand-blue fs-4 mb-3 d-inline-flex align-items-center justify-content-center shadow" style="width: 54px; height: 54px;">1</div>
          <h5 class="fw-bold">1. Décrivez votre besoin</h5>
          <p class="text-muted small">Publiez votre demande avec titre, adresse, niveau d'urgence, créneaux préférés et photos d'illustration du problème.</p>
        </div>
        <div class="col-md-4 text-center">
          <div class="badge rounded-circle bg-brand-teal fs-4 mb-3 d-inline-flex align-items-center justify-content-center shadow" style="width: 54px; height: 54px;">2</div>
          <h5 class="fw-bold">2. Comparez & Choisissez</h5>
          <p class="text-muted small">Recevez les propositions des prestataires qualifiés. Échangez par messagerie privée et retenez le prestataire idéal.</p>
        </div>
        <div class="col-md-4 text-center">
          <div class="badge rounded-circle bg-brand-purple fs-4 mb-3 d-inline-flex align-items-center justify-content-center shadow" style="width: 54px; height: 54px;">3</div>
          <h5 class="fw-bold">3. Intervention & Clôture</h5>
          <p class="text-muted small">Le prestataire établit le diagnostic, exécute la prestation et vous validez la fin de l'intervention avant de déposer votre avis.</p>
        </div>
      </div>
    </div>
  </section>

</div>

<footer class="bg-dark text-white-50 py-4 mt-5 border-top border-secondary">
  <div class="container text-center">
    <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
      <i class="bi bi-house-gear-fill text-warning fs-5"></i>
      <span class="fw-bold text-white fs-5 da-brand-font">DomAssist</span>
    </div>
    <p class="small mb-0">© <?= date('Y') ?> DomAssist — Plateforme de mise en relation pour services à domicile. Tous droits réservés.</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

