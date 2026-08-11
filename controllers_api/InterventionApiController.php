<?php
require_once __DIR__ . '/../models/Intervention.php';
require_once __DIR__ . '/../models/Demande.php';
require_once __DIR__ . '/../models/Disponibilite.php';
require_once __DIR__ . '/../models/Prestataire.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/ApiAuth.php';
require_once __DIR__ . '/ApiRequest.php';

class InterventionApiController
{
    private Intervention $interv;
    private Demande $demande;
    private Disponibilite $dispo;
    private Prestataire $prest;

    public function __construct()
    {
        $this->interv  = new Intervention();
        $this->demande = new Demande();
        $this->dispo   = new Disponibilite();
        $this->prest   = new Prestataire();
    }

    private function prestataireValideOuErreur(array $user): array
    {
        $p = $this->prest->findByUser((int) $user['id_user']);
        // V3 : statut validé = 'validee'
        if (!$p || ($p['statut_validation'] ?? '') !== 'validee') {
            ApiResponse::error('Vous devez être un prestataire validé pour accéder à cette action.', 403);
        }
        return $p;
    }

    /** POST ?action=intervention_demarrer  { id_demande, id_dispo? } */
    public function demarrer(): void
    {
        $user = ApiAuth::requireAuth();
        $p = $this->prestataireValideOuErreur($user);

        $d = ApiRequest::body();
        $id_demande = (int) ($d['id_demande'] ?? 0);
        $id_dispo   = !empty($d['id_dispo']) ? (int) $d['id_dispo'] : null;
        if (!$id_demande) {
            ApiResponse::error('id_demande requis.', 422);
        }

        $resultat = $this->interv->demarrer($id_demande, (int) $p['id_prestataire'], $id_dispo);

        // demarrer() renvoie soit l'id_intervention créé (numérique), soit un code d'erreur.
        if (ctype_digit($resultat)) {
            if ($id_dispo) {
                $this->dispo->setOccupe($id_dispo);
            }
            ApiResponse::success(['message' => 'Intervention démarrée.', 'id_intervention' => (int) $resultat], 201);
        }

        match ($resultat) {
            'introuvable'    => ApiResponse::error('Demande introuvable.', 404),
            'non_assigne'    => ApiResponse::error('Cette demande ne vous est pas assignée.', 403),
            'deja_demarree'  => ApiResponse::error('La solution n\'est pas validée, ou l\'intervention est déjà en cours.', 409),
            default          => ApiResponse::error('Erreur inattendue.', 500),
        };
    }

    /** POST ?action=intervention_terminer  { id_intervention, resultat } */
    public function terminer(): void
    {
        $user = ApiAuth::requireAuth();
        $p = $this->prestataireValideOuErreur($user);

        $d = ApiRequest::body();
        $id_intervention = (int) ($d['id_intervention'] ?? 0);
        $resultat_texte   = trim($d['resultat'] ?? '');
        if (!$id_intervention || $resultat_texte === '') {
            ApiResponse::error('id_intervention et resultat sont requis.', 422);
        }

        $resultat = $this->interv->terminer($id_intervention, (int) $p['id_prestataire'], $resultat_texte);
        match ($resultat) {
            'ok'             => ApiResponse::success(['message' => 'Intervention terminée.']),
            'introuvable'    => ApiResponse::error('Intervention introuvable.', 404),
            'non_assigne'    => ApiResponse::error('Cette intervention ne vous appartient pas.', 403),
            'deja_terminee'  => ApiResponse::error('Cette intervention est déjà terminée.', 409),
            default          => ApiResponse::error('Erreur inattendue.', 500),
        };
    }

    /** GET ?action=intervention_show&id=N */
    public function show(): void
    {
        $user = ApiAuth::requireAuth();
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) {
            ApiResponse::error('Paramètre id requis.', 422);
        }

        $intervention = $this->interv->find($id);
        if (!$intervention) {
            ApiResponse::error('Intervention introuvable.', 404);
        }
        $this->verifierAcces($user, $intervention);

        ApiResponse::success(['intervention' => $intervention]);
    }

    /** GET ?action=intervention_mes — historique du prestataire connecté, ou vue globale pour l'admin. */
    public function mes(): void
    {
        $user = ApiAuth::requireAuth();
        if (($user['role'] ?? '') === 'admin') {
            ApiResponse::success(['interventions' => $this->interv->all()]);
        }

        $p = $this->prest->findByUser((int) $user['id_user']);
        if (!$p) {
            ApiResponse::success(['interventions' => []]);
        }
        ApiResponse::success(['interventions' => $this->interv->byPrestataire((int) $p['id_prestataire'])]);
    }

    private function verifierAcces(array $user, array $intervention): void
    {
        if (($user['role'] ?? '') === 'admin') {
            return;
        }
        if ((int) $intervention['id_user_demande'] === (int) $user['id_user']) {
            return;
        }
        $profil = $this->prest->findByUser((int) $user['id_user']);
        // V3 : la colonne id_prestataire dans intervention pointe vers id_profile (façade)
        // id_prestataire est l'alias retourné par la façade Prestataire (= id_profile)
        if ($profil && (int) ($intervention['id_profile'] ?? $intervention['id_prestataire'] ?? 0) === (int) $profil['id_prestataire']) {
            return;
        }
        ApiResponse::error('Accès refusé à cette intervention.', 403);
    }
}