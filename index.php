<?php
/**
 * Point d'entrée HTML de l'application (routeur), séparé de l'API JSON (api.php).
 * Utilisation : php -S localhost:8000
 */
session_start();

require_once __DIR__ . '/config/database.php';

// Routes accessibles sans être connecté.
$publicActions = ['login', 'register', 'home'];

$action = $_GET['action'] ?? ($_SESSION['user'] ?? null
    ? (($_SESSION['user']['role'] ?? '') === 'admin' ? 'admin_dashboard' : 'dashboard')
    : 'home');

// Redirection forcée si l'utilisateur est connecté et suspendu
if (!empty($_SESSION['user']) && !empty($_SESSION['user']['suspendu'])) {
    if (!in_array($action, ['compte_suspendu', 'logout'], true)) {
        header('Location: index.php?action=compte_suspendu');
        exit;
    }
}

// Garde d'authentification globale.
if (!in_array($action, $publicActions, true) && empty($_SESSION['user'])) {
    header('Location: index.php?action=login');
    exit;
}
// Un utilisateur non suspendu déjà connecté n'a rien à faire sur login/register.
if (in_array($action, ['login', 'register'], true) && !empty($_SESSION['user']) && empty($_SESSION['user']['suspendu'])) {
    header('Location: index.php?action=' . (($_SESSION['user']['role'] ?? '') === 'admin' ? 'admin_dashboard' : 'dashboard'));
    exit;
}

$routes = [
    // Authentification
    'home'            => ['HomeController', 'index'],
    'login'           => ['AuthController', 'login'],
    'register'        => ['AuthController', 'register'],
    'logout'          => ['AuthController', 'logout'],
    'compte_suspendu' => ['AuthController', 'suspended'],

    // Dashboards
    'dashboard'       => ['DashboardController', 'index'],
    'admin_dashboard' => ['AdminController', 'dashboard'],

    // Profil / utilisateurs
    'profile'          => ['UserController', 'profile'],
    'user_show_public' => ['UserController', 'showPublic'],
    'users'            => ['UserController', 'index'],
    'user_edit'        => ['UserController', 'edit'],
    'user_delete'      => ['UserController', 'delete'],

    // Prestataires
    'prestataires'               => ['PrestataireController', 'index'],
    'prestataire_candidater'     => ['PrestataireController', 'candidater'],
    'prestataire_show'           => ['PrestataireController', 'show'],
    // 'prestataire_create'         => ['PrestataireController', 'create'],
    'prestataire_edit'           => ['PrestataireController', 'edit'],
    'prestataire_delete'         => ['PrestataireController', 'delete'],
    'prestataire_add_competence' => ['PrestataireController', 'addCompetence'],

    // Demandes
    'demandes'                  => ['DemandeController', 'index'],
    'demande_create'            => ['DemandeController', 'create'],
    'demande_show'              => ['DemandeController', 'show'],
    'demande_delete'            => ['DemandeController', 'delete'],
    'demande_annuler'           => ['DemandeController', 'annuler'],
    // Workflow V3 : demandes éligibles + propositions
    'demandes_disponibles'      => ['DemandeController', 'disponibles'],
    'demande_proposer'          => ['DemandeController', 'proposer'],
    'demande_selectionner'      => ['DemandeController', 'selectionner'],
    'demande_confirmer_engagement' => ['DemandeController', 'confirmerEngagement'],
    'demande_desister'          => ['DemandeController', 'desister'],
    'demande_update_statut'     => ['DemandeController', 'updateStatut'],
    'demande_commenter'         => ['DemandeController', 'commenter'],


    // Messagerie privée
    'message_send'              => ['MessageController', 'send'],

    // Centre de notifications
    'notifications'                 => ['NotificationController', 'index'],
    'notification_mark_read'        => ['NotificationController', 'markRead'],
    'notification_mark_all_read'    => ['NotificationController', 'markAllRead'],

    // Diagnostic
    'diagnostic_create' => ['DiagnosticController', 'create'],
    'diagnostic_show'   => ['DiagnosticController', 'show'],

    // Solutions
    'solution_create'  => ['SolutionController', 'create'],
    'solution_valider' => ['SolutionController', 'valider'],
    'solution_refuser' => ['SolutionController', 'refuser'],

    // Interventions
    'interventions'         => ['InterventionController', 'index'],
    'intervention_create'   => ['InterventionController', 'create'],
    'intervention_terminer' => ['InterventionController', 'terminer'],

    // Disponibilités
    'disponibilites'       => ['DisponibiliteController', 'index'],
    'disponibilite_create' => ['DisponibiliteController', 'create'],
    'disponibilite_delete' => ['DisponibiliteController', 'delete'],

    // Services (catégories)
    'services'       => ['ServiceController', 'index'],
    'service_create' => ['ServiceController', 'create'],
    'service_delete' => ['ServiceController', 'delete'],

    // Produits (Désactivés)
    // 'produits'       => ['ProduitController', 'index'],
    // 'produit_create' => ['ProduitController', 'create'],
    // 'produit_delete' => ['ProduitController', 'delete'],

    // Avis / réputation
    'avis_create'   => ['AvisController', 'create'],
    'avis_repondre' => ['AvisController', 'repondre'],
    'avis_delete'   => ['AvisController', 'delete'],
    'reputation'    => ['AvisController', 'maReputation'],

    // Administration
    'admin_users'                   => ['AdminController', 'users'],
    'admin_delete_user'             => ['AdminController', 'deleteUser'],
    'admin_profile'                 => ['AdminController', 'profile'],
    'admin_suivi_demandes'          => ['AdminController', 'suiviDemandes'],
    'admin_suivi_services'          => ['AdminController', 'suiviServices'],
    'admin_suivi_interventions'     => ['AdminController', 'suiviInterventions'],
    'admin_prestataires_en_attente' => ['AdminController', 'prestatairesEnAttente'],
    'admin_valider_prestataire'     => ['AdminController', 'validerPrestataire'],
    'admin_rejeter_prestataire'     => ['AdminController', 'rejeterPrestataire'],
    'admin_suspend_user'            => ['AdminController', 'suspendUser'],
    'admin_reactivate_user'         => ['AdminController', 'reactivateUser'],
];

if (!isset($routes[$action])) {
    http_response_code(404);
    $pageTitle = 'Page introuvable';
    require __DIR__ . '/views/errors/404.php';
    exit;
}

[$class, $method] = $routes[$action];
require_once __DIR__ . "/controllers/{$class}.php";

try {
    (new $class())->$method();
} catch (Throwable $e) {
    http_response_code(500);
    $pageTitle = 'Erreur';
    $errorMessage = $e->getMessage();
    require __DIR__ . '/views/errors/500.php';
}
