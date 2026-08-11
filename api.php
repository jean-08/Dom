<?php
// Point d'entrée de l'API JSON, séparé du routeur HTML (index.php).
// Utilisation : POST /api.php?action=login  (body JSON)

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers_api/ApiResponse.php';
require_once __DIR__ . '/controllers_api/ApiRequest.php';
require_once __DIR__ . '/controllers_api/ApiAuth.php';

// CORS : autoriser uniquement les origines connues pour être compatible avec
// les requêtes authentifiées (Access-Control-Allow-Credentials: true).
// Configurer la variable d'environnement CORS_ALLOWED_ORIGINS avec les origines
// autorisées séparées par des virgules (ex: https://app.domassist.fr,http://localhost:3000).
$_corsAllowedOrigins = array_filter(array_map('trim', explode(',',
    getenv('CORS_ALLOWED_ORIGINS') ?: 'http://localhost,http://localhost:3000,http://localhost:8080,http://127.0.0.1'
)));
$_corsRequestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($_corsRequestOrigin !== '' && in_array($_corsRequestOrigin, $_corsAllowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $_corsRequestOrigin);
    header('Vary: Origin');
} else {
    // Fallback pour les outils de dev sans origine (ex: curl, Postman)
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Le navigateur envoie une requête OPTIONS avant chaque appel cross-origin : on répond vide.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}


$action = $_GET['action'] ?? '';

$routes = [
    'login'    => ['AuthApiController', 'login'],
    'register' => ['AuthApiController', 'register'],
    'logout'   => ['AuthApiController', 'logout'],
    'me'       => ['AuthApiController', 'me'],  

    'prestataire_candidater' => ['PrestataireApiController', 'candidater'],
    'prestataire_mon_statut' => ['PrestataireApiController', 'monStatut'],
    'prestataire_en_attente' => ['PrestataireApiController', 'enAttente'],
    'prestataire_valider'    => ['PrestataireApiController', 'valider'],
    'prestataire_rejeter'    => ['PrestataireApiController', 'rejeter'],
    'prestataire_suspendre'  => ['PrestataireApiController', 'suspendre'],

    // Compétences (matching demandes_disponibles)
    'prestataire_competences_mes'     => ['PrestataireApiController', 'competencesMes'],
    'prestataire_competences'         => ['PrestataireApiController', 'competencesShow'],
    'prestataire_competence_ajouter'  => ['PrestataireApiController', 'competenceAjouter'],
    'prestataire_competence_retirer'  => ['PrestataireApiController', 'competenceRetirer'],

    // Catalogue services
    'service_list'   => ['ServiceApiController', 'list'],
    'service_show'   => ['ServiceApiController', 'show'],
    'service_create' => ['ServiceApiController', 'create'],
    'service_update' => ['ServiceApiController', 'update'],
    'service_delete' => ['ServiceApiController', 'delete'],

    'demande_disponibles'          => ['DemandeApiController', 'disponibles'],
    'demande_create'               => ['DemandeApiController', 'create'],
    'demande_proposer'             => ['DemandeApiController', 'proposer'],
    'demande_selectionner'         => ['DemandeApiController', 'selectionner'],
    'demande_confirmer_engagement' => ['DemandeApiController', 'confirmerEngagement'],
    'demande_desister'             => ['DemandeApiController', 'desister'],
    'demande_annuler'              => ['DemandeApiController', 'annuler'],
    'demande_show'                 => ['DemandeApiController', 'show'],
    'demande_mes'                  => ['DemandeApiController', 'mes'],

    // Messagerie privée API
    'messages_thread' => ['MessageApiController', 'getThread'],
    'message_send'    => ['MessageApiController', 'send'],

    // Notifications API
    'notifications_list'     => ['NotificationApiController', 'list'],
    'notification_mark_read' => ['NotificationApiController', 'markRead'],

    'diagnostic_proposer' => ['DiagnosticApiController', 'proposer'],
    'diagnostic_show'     => ['DiagnosticApiController', 'show'],

    'solution_proposer' => ['SolutionApiController', 'proposer'],
    'solution_show'     => ['SolutionApiController', 'show'],
    'solution_valider'  => ['SolutionApiController', 'valider'],

    'intervention_demarrer' => ['InterventionApiController', 'demarrer'],
    'intervention_terminer' => ['InterventionApiController', 'terminer'],
    'intervention_show'     => ['InterventionApiController', 'show'],
    'intervention_mes'      => ['InterventionApiController', 'mes'],


    'avis_create'          => ['AvisApiController', 'create'],
    'avis_repondre'        => ['AvisApiController', 'repondre'],
    'avis_prestataire'     => ['AvisApiController', 'byPrestataire'],
    'avis_ma_reputation'   => ['AvisApiController', 'maReputation'],

    'user_list'           => ['UserApiController', 'list'],
    'user_suspendre'      => ['UserApiController', 'suspendre'],
    'user_reactiver'      => ['UserApiController', 'reactiver'],
    'user_profile_update' => ['UserApiController', 'updateProfile'],
    'user_change_password'=> ['UserApiController', 'changePassword'],
];

if (!isset($routes[$action])) {
    ApiResponse::error('Route inconnue.', 404);
}

[$class, $method] = $routes[$action];
require_once __DIR__ . "/controllers_api/{$class}.php";

try {
    (new $class())->$method();
} catch (Throwable $e) {
    ApiResponse::error('Erreur serveur : ' . $e->getMessage(), 500);
}