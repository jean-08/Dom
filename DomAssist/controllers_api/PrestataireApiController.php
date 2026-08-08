<?php
require_once __DIR__ . '/../models/Prestataire.php';
require_once __DIR__ . '/../models/SessionToken.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/ApiAuth.php';
require_once __DIR__ . '/ApiRequest.php';

class PrestataireApiController
{
    private Prestataire $prest;
    private Service $service;

    public function __construct()
    {
        $this->prest   = new Prestataire();
        $this->service = new Service();
    }

    /**
     * Profil prestataire du user connecté, ou erreur 403 si absent.
     * N'exige pas le statut "validee" : un candidat en attente peut déjà
     * déclarer ses compétences pour que l'admin les voie à la validation.
     * Statut V3 : 'suspendue' (et non 'suspendu').
     */
    private function monProfilOuErreur(array $user): array
    {
        $p = $this->prest->findByUser((int) $user['id_user']);
        if (!$p) {
            ApiResponse::error('Aucun profil prestataire. Candidatez d\'abord.', 403);
        }
        // V3 : le statut de suspension est 'suspendue' (féminin, aligné sur statut_profile)
        if (($p['statut_validation'] ?? '') === 'suspendue') {
            ApiResponse::error('Profil prestataire suspendu.', 403);
        }
        return $p;
    }

    /**
     * POST ?action=prestataire_candidater
     * Body : { bio, lettre_motivation?, experience_annees?, zone_intervention?, disponibilites_type?, accepte_urgences?, moyen_deplacement?, siret?, assurances_pro?, document_cv_url? }
     * Un user connecté candidate pour devenir prestataire (V3).
     */
    public function candidater(): void
    {
        $user = ApiAuth::requireAuth();

        $existant = $this->prest->findByUser((int) $user['id_user']);
        if ($existant && in_array($existant['statut_validation'] ?? '', ['soumise', 'en_revue', 'validee', 'suspendue'])) {
            ApiResponse::error('Vous avez déjà un profil prestataire (ou une candidature en cours).', 409);
        }

        $d = ApiRequest::body();
        $bio = trim($d['bio'] ?? $d['specialite'] ?? '');
        if ($bio === '') {
            ApiResponse::error('Veuillez renseigner votre présentation (bio).', 422);
        }

        $this->prest->candidater([
            'id_user'             => (int) $user['id_user'],
            'bio'                 => $bio,
            'lettre_motivation'   => trim($d['lettre_motivation'] ?? '') ?: null,
            'experience_annees'   => !empty($d['experience_annees']) ? (int) $d['experience_annees'] : null,
            'zone_intervention'   => trim($d['zone_intervention'] ?? '') ?: null,
            'disponibilites_type' => trim($d['disponibilites_type'] ?? 'Semaine et Week-end'),
            'accepte_urgences'    => !empty($d['accepte_urgences']),
            'moyen_deplacement'   => trim($d['moyen_deplacement'] ?? 'Vehicule personnel'),
            'siret'               => trim($d['siret'] ?? '') ?: null,
            'assurances_pro'      => trim($d['assurances_pro'] ?? '') ?: null,
            'document_cv_url'     => trim($d['document_cv_url'] ?? '') ?: null,
        ]);

        ApiResponse::success(['message' => 'Candidature envoyée, en attente de validation.'], 201);
    }

    /**
     * GET ?action=prestataire_mon_statut
     * Consulter le statut de sa propre candidature/profil prestataire (V3).
     */
    public function monStatut(): void
    {
        $user = ApiAuth::requireAuth();
        $mine = $this->prest->findByUser((int) $user['id_user']);

        if (!$mine) {
            ApiResponse::success(['statut' => null]);
        }

        ApiResponse::success([
            'statut'              => $mine['statut_validation'],
            'bio'                 => $mine['specialite'],       // alias façade V3
            'lettre_motivation'   => $mine['lettre_motivation'] ?? null,
            'experience_annees'   => isset($mine['experience_annees']) ? (int) $mine['experience_annees'] : null,
            'zone_intervention'   => $mine['zone_intervention'] ?? null,
            'disponibilites_type' => $mine['disponibilites_type'] ?? null,
            'accepte_urgences'    => !empty($mine['accepte_urgences']),
            'moyen_deplacement'   => $mine['moyen_deplacement'] ?? null,
            'siret'               => $mine['siret'] ?? null,
            'assurances_pro'      => $mine['assurances_pro'] ?? null,
            'document_cv_url'     => $mine['document_cv_url'] ?? null,
            'motif_rejet'         => $mine['motif_rejet'] ?? null,
            'date_demande'        => $mine['date_demande'] ?? null,
            'date_validation'     => $mine['date_validation'] ?? null,
        ]);
    }

    /** Admin : consulte la file d'attente des candidatures (statuts soumise / en_revue). */
    public function enAttente(): void
    {
        ApiAuth::requireAdmin();
        ApiResponse::success(['candidatures' => $this->prest->findEnAttente()]);
    }

    /**
     * POST ?action=prestataire_valider
     * Body : { id_prestataire }
     * Admin valide une candidature.
     */
    public function valider(): void
    {
        ApiAuth::requireAdmin();
        $id = (int) (ApiRequest::body()['id_prestataire'] ?? 0);
        if (!$id) {
            ApiResponse::error('id_prestataire requis.', 422);
        }
        $this->prest->valider($id);
        ApiResponse::success(['message' => 'Prestataire validé.']);
    }

    /**
     * POST ?action=prestataire_rejeter
     * Body : { id_prestataire, motif? }
     * Admin rejette une candidature.
     */
    public function rejeter(): void
    {
        ApiAuth::requireAdmin();
        $d     = ApiRequest::body();
        $id    = (int) ($d['id_prestataire'] ?? 0);
        $motif = trim($d['motif'] ?? '');
        if (!$id) {
            ApiResponse::error('id_prestataire requis.', 422);
        }
        $this->prest->rejeter($id, $motif);
        ApiResponse::success(['message' => 'Candidature rejetée.']);
    }

    /**
     * POST ?action=prestataire_suspendre
     * Body : { id_prestataire }
     * Admin suspend un prestataire déjà validé (abus/signalement).
     */
    public function suspendre(): void
    {
        ApiAuth::requireAdmin();
        $id = (int) (ApiRequest::body()['id_prestataire'] ?? 0);
        if (!$id) {
            ApiResponse::error('id_prestataire requis.', 422);
        }
        $this->prest->suspendre($id);
        ApiResponse::success(['message' => 'Prestataire suspendu.']);
    }

    // -------------------------------------------------------------------------
    // Compétences — V3 : table `competence` (id_category, id_profile, niveau)
    // Les routes acceptent id_category (V3) avec fallback id_service (V1 rétrocompat.)
    // -------------------------------------------------------------------------

    /**
     * GET ?action=prestataire_competences_mes
     * Liste les catégories/compétences du prestataire connecté.
     */
    public function competencesMes(): void
    {
        $user = ApiAuth::requireAuth();
        $p    = $this->monProfilOuErreur($user);

        $list = $this->service->byPrestataire((int) $p['id_prestataire']);
        ApiResponse::success([
            'competences' => array_map([$this, 'formatCompetence'], $list),
        ]);
    }

    /**
     * GET ?action=prestataire_competences&id_prestataire=N
     * Consultation publique des compétences d'un prestataire validé (annuaire).
     * Admin / propriétaire : accès quel que soit le statut.
     * V3 : vérification statut 'validee' (et non 'valide').
     */
    public function competencesShow(): void
    {
        $id = (int) ($_GET['id_prestataire'] ?? 0);
        if ($id <= 0) {
            ApiResponse::error('Paramètre id_prestataire requis.', 422);
        }

        $profil = $this->prest->find($id);
        if (!$profil) {
            ApiResponse::error('Prestataire introuvable.', 404);
        }

        // Public : uniquement les profils validés. Sinon propriétaire ou admin.
        $user = null;
        $token = ApiAuth::extraireToken();
        if ($token) {
            $user = (new SessionToken())->verifier($token);
        }

        $estProprietaire = $user && (int) ($user['id_user'] ?? 0) === (int) $profil['id_user'];
        $estAdmin        = $user && ($user['role'] ?? '') === 'admin';
        // V3 : statut validé = 'validee' (et non 'valide')
        $estValide       = ($profil['statut_validation'] ?? '') === 'validee';

        if (!$estValide && !$estProprietaire && !$estAdmin) {
            ApiResponse::error('Profil prestataire non visible.', 403);
        }

        $list = $this->service->byPrestataire($id);
        ApiResponse::success([
            'id_prestataire' => $id,
            'competences'    => array_map([$this, 'formatCompetence'], $list),
        ]);
    }

    /**
     * POST ?action=prestataire_competence_ajouter
     * Body : { id_category (V3) ou id_service (V1 rétrocompat.), niveau? }
     * Upsert : si la compétence existe déjà, le niveau est mis à jour.
     */
    public function competenceAjouter(): void
    {
        $user = ApiAuth::requireAuth();
        $p    = $this->monProfilOuErreur($user);

        $d = ApiRequest::body();
        // V3 : id_category ; fallback id_service pour rétrocompatibilité
        $id_category = (int) ($d['id_category'] ?? $d['id_service'] ?? 0);
        $niveau      = trim($d['niveau'] ?? 'intermediaire');

        if ($id_category <= 0) {
            ApiResponse::error('id_category requis.', 422);
        }

        if (!$this->service->find($id_category)) {
            ApiResponse::error('Catégorie de service introuvable.', 404);
        }

        if ($niveau === '') {
            $niveau = 'intermediaire';
        }
        // Normalisation : valeurs admises debutant|intermediaire|avance|expert
        $niveau = strtolower($niveau);

        $this->service->addCompetence((int) $p['id_prestataire'], $id_category, $niveau);

        ApiResponse::success([
            'message'     => 'Compétence enregistrée.',
            'id_category' => $id_category,
            'niveau'      => $niveau,
        ], 201);
    }

    /**
     * POST ?action=prestataire_competence_retirer
     * Body : { id_category (V3) ou id_service (V1 rétrocompat.) }
     */
    public function competenceRetirer(): void
    {
        $user = ApiAuth::requireAuth();
        $p    = $this->monProfilOuErreur($user);

        $d = ApiRequest::body();
        // V3 : id_category ; fallback id_service pour rétrocompatibilité
        $id_category = (int) ($d['id_category'] ?? $d['id_service'] ?? 0);
        if ($id_category <= 0) {
            ApiResponse::error('id_category requis.', 422);
        }

        $ok = $this->service->removeCompetence((int) $p['id_prestataire'], $id_category);
        if (!$ok) {
            ApiResponse::error('Cette compétence n\'était pas enregistrée.', 404);
        }

        ApiResponse::success(['message' => 'Compétence retirée.']);
    }

    /**
     * Formater une compétence pour la réponse JSON (V3).
     * La façade Service.php retourne les colonnes sous les alias V1 (id_service, nom)
     * pour rétrocompatibilité — on expose les deux noms.
     */
    private function formatCompetence(array $row): array
    {
        return [
            'id_category' => (int) ($row['id_service'] ?? 0),   // V3 (id_service = alias de id_category)
            'id_service'  => (int) ($row['id_service'] ?? 0),   // rétrocompatibilité V1
            'nom'         => $row['nom'] ?? null,
            'description' => $row['description'] ?? null,
            'niveau'      => $row['niveau'] ?? null,
        ];
    }
}