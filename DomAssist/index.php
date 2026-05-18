<?php
session_start();
require_once __DIR__ . '/config/database.php';

$action = $_GET['action'] ?? 'dashboard';

$public = ['login', 'register'];
if (!isset($_SESSION['user']) && !in_array($action, $public)) {
    header('Location: index.php?action=login'); exit;
}

$routes = [
    'login'                  => ['AuthController',         'login'],
    'register'               => ['AuthController',         'register'],
    'logout'                 => ['AuthController',         'logout'],
    'users'                  => ['UserController',         'index'],
    'user_edit'              => ['UserController',         'edit'],
    'user_delete'            => ['UserController',         'delete'],
    'profile'                => ['UserController',         'profile'],
    'prestataires'           => ['PrestataireController',  'index'],
    'prestataire_show'       => ['PrestataireController',  'show'],
    'prestataire_create'     => ['PrestataireController',  'create'],
    'prestataire_edit'       => ['PrestataireController',  'edit'],
    'prestataire_delete'     => ['PrestataireController',  'delete'],
    'prestataire_competence' => ['PrestataireController',  'addCompetence'],
    'demandes'               => ['DemandeController',      'index'],
    'demande_create'         => ['DemandeController',      'create'],
    'demande_show'           => ['DemandeController',      'show'],
    'demande_statut'         => ['DemandeController',      'updateStatut'],
    'demande_delete'         => ['DemandeController',      'delete'],
    'diagnostic_create'      => ['DiagnosticController',   'create'],
    'diagnostic_show'        => ['DiagnosticController',   'show'],
    'interventions'          => ['InterventionController', 'index'],
    'intervention_create'    => ['InterventionController', 'create'],
    'disponibilites'         => ['DisponibiliteController','index'],
    'disponibilite_create'   => ['DisponibiliteController','create'],
    'disponibilite_delete'   => ['DisponibiliteController','delete'],
    'services'               => ['ServiceController',      'index'],
    'service_create'         => ['ServiceController',      'create'],
    'service_delete'         => ['ServiceController',      'delete'],
    'solution_create'        => ['SolutionController',     'create'],
    'produits'               => ['ProduitController',      'index'],
    'produit_create'         => ['ProduitController',      'create'],
    'produit_delete'         => ['ProduitController',      'delete'],
    'avis_create'            => ['AvisController',         'create'],
    'avis_delete'            => ['AvisController',         'delete'],
    'admin_dashboard'        => ['AdminController',        'dashboard'],
    'admin_users'            => ['AdminController',        'users'],
    'admin_delete_user'      => ['AdminController',        'deleteUser'],
    'admin_profile'          => ['AdminController',        'profile'],
    'admin_suivi_demandes'   => ['AdminController',        'suiviDemandes'],
    'admin_suivi_services'   => ['AdminController',        'suiviServices'],
    'admin_suivi_interventions' => ['AdminController',     'suiviInterventions'],
];

if ($action === 'dashboard') {
require __DIR__ . '/views/layout/header.php';
    $role = $_SESSION['user']['role'] ?? '';
    $prenom = htmlspecialchars($_SESSION['user']['prenom'] ?? '');
    $nom    = htmlspecialchars($_SESSION['user']['nom'] ?? '');
    ?>
    <p class="welcome-msg">Connecté en tant que <strong><?= $prenom . ' ' . $nom ?></strong></p>

    <div class="hero-banner">
      <div class="hero-text">
        <h1>Besoin d'un service à domicile ?</h1>
        <p>Décrivez votre problème, un prestataire qualifié vous répond rapidement.</p>
        <a href="index.php?action=demande_create" class="btn">+ Faire une demande</a>
      </div>
      <span class="hero-icon">🔧</span>
    </div>

    <div class="dash-cards">
      <a href="index.php?action=demandes" class="dash-card">
        <span class="dash-card-icon">📋</span>
        <span class="dash-card-title">Mes demandes</span>
        <span class="dash-card-desc">Suivez l'état de vos demandes</span>
      </a>
      <a href="index.php?action=prestataires" class="dash-card">
        <span class="dash-card-icon">👷</span>
        <span class="dash-card-title">Prestataires</span>
        <span class="dash-card-desc">Consultez les prestataires disponibles</span>
      </a>
      <a href="index.php?action=services" class="dash-card">
        <span class="dash-card-icon">🛠</span>
        <span class="dash-card-title">Services</span>
        <span class="dash-card-desc">Découvrez les services proposés</span>
      </a>
      <a href="index.php?action=interventions" class="dash-card">
        <span class="dash-card-icon">📅</span>
        <span class="dash-card-title">Interventions</span>
        <span class="dash-card-desc">Historique de vos interventions</span>
      </a>
      <?php if ($role === 'prestataire'): ?>
      <a href="index.php?action=disponibilites" class="dash-card">
        <span class="dash-card-icon">🗓</span>
        <span class="dash-card-title">Disponibilités</span>
        <span class="dash-card-desc">Gérez vos créneaux</span>
      </a>
      <?php endif; ?>
    </div>
    <?php
    require __DIR__ . '/views/layout/footer.php';
    exit;
}

if (!isset($routes[$action])) {
    http_response_code(404);
    require __DIR__ . '/views/layout/header.php';
    echo '<h2>Page introuvable</h2><a href="index.php?action=dashboard">← Retour</a>';
    require __DIR__ . '/views/layout/footer.php';
    exit;
}

[$class, $method] = $routes[$action];
require_once __DIR__ . '/controllers/' . $class . '.php';
$controller = new $class();
$controller->$method();
