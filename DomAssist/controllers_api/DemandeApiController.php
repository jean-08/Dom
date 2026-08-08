<?php
require_once __DIR__ . '/../models/Demande.php';
require_once __DIR__ . '/../models/Prestataire.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/ApiAuth.php';
require_once __DIR__ . '/ApiRequest.php';

class DemandeApiController
{
    private Demande $demande;
    private Prestataire $prest;

    public function __construct()
    {
        $this->demande = new Demande();
        $this->prest   = new Prestataire();
    }

    /**
     * Vérifie que l'utilisateur est un prestataire avec le statut 'validee' (V3).
     * Retourne le profil prestataire si valide, sinon renvoie une erreur 403.
     */
    private function prestataireValideOuErreur(array $user): array
    {
        $p = $this->prest->findByUser((int) $user['id_user']);
        if (!$p || ($p['statut_validation'] ?? '') !== 'validee') {
            ApiResponse::error('Vous devez être un prestataire validé pour accéder à cette action.', 403);
        }
        return $p;
    }

    /**
     * POST ?action=demande_create
     * Body : { titre, description, id_category, urgence, adresse, ville,
     *          code_postal, telephone_contact, budget_min, budget_max, disponibilites_client }
     */
    public function create(): void
    {
        $user = ApiAuth::requireAuth();

        $d = ApiRequest::body();
        $titre       = trim($d['titre'] ?? '');
        $description = trim($d['description'] ?? '');
        $id_category = (int) ($d['id_category'] ?? 0);
        $ville       = trim($d['ville'] ?? '');
        if ($ville === '') { $ville = 'Non précisée'; }
        $code_postal = trim($d['code_postal'] ?? '');
        if ($code_postal === '') { $code_postal = '00000'; }

        if ($titre === '' || !$id_category) {
            ApiResponse::error('titre et id_category sont requis.', 422);
        }

        if ($description === '') {
            $description = $titre;
        }

        $id_created = $this->demande->create([
            'id_user'               => (int) $user['id_user'],
            'titre'                 => $titre,
            'description'           => $description,
            'id_category'           => $id_category,
            'urgence'               => $d['urgence'] ?? 'normal',
            'adresse'               => $d['adresse'] ?? null,
            'ville'                 => $ville,
            'code_postal'           => $code_postal,
            'telephone_contact'     => $d['telephone_contact'] ?? null,
            'budget_min'            => !empty($d['budget_min']) ? (float) $d['budget_min'] : null,
            'budget_max'            => !empty($d['budget_max']) ? (float) $d['budget_max'] : null,
            'disponibilites_client' => $d['disponibilites_client'] ?? null,
        ]);

        if ($id_created) {
            if (!empty($d['photos']) && is_array($d['photos'])) {
                require_once __DIR__ . '/../models/DemandeMedia.php';
                (new DemandeMedia())->addBatch($id_created, $d['photos']);
            }
            ApiResponse::success(['message' => 'Demande créée.', 'id_demande' => $id_created], 201);
        } else {
            ApiResponse::error('Erreur lors de la création de la demande.', 500);
        }
    }

    /**
     * Protection IDOR V3 :
     * propriétaire, prestataire retenu (id_profile_retenu), ou admin uniquement.
     */
    private function requireAccesDemande(array $user, int $id_demande): array
    {
        $demande = $this->demande->find($id_demande);
        if (!$demande) {
            ApiResponse::error('Demande introuvable.', 404);
        }

        if (($user['role'] ?? '') === 'admin') {
            return $demande;
        }

        if ((int) $demande['id_user'] === (int) $user['id_user']) {
            return $demande;
        }

        // Vérifier si l'utilisateur est le prestataire retenu (V3 : id_profile_retenu)
        $profil = $this->prest->findByUser((int) $user['id_user']);
        if ($profil
            && !empty($demande['id_profile_retenu'])
            && (int) $demande['id_profile_retenu'] === (int) $profil['id_prestataire']
        ) {
            return $demande;
        }

        ApiResponse::error('Accès refusé à cette demande.', 403);
    }

    /** GET ?action=demande_show&id=N */
    public function show(): void
    {
        $user = ApiAuth::requireAuth();
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            ApiResponse::error('Paramètre id requis.', 422);
        }
        require_once __DIR__ . '/../models/DemandeMedia.php';
        $demande = $this->requireAccesDemande($user, $id);
        $medias  = (new DemandeMedia())->byDemande($id);
        $formatted = $this->format($demande);
        $formatted['medias'] = $medias;
        ApiResponse::success(['demande' => $formatted]);
    }

    /**
     * GET ?action=demande_mes
     * Admin : toutes les demandes.
     * Client : ses propres demandes.
     * Prestataire validé : ses propres demandes + celles sur lesquelles il a proposé.
     */
    public function mes(): void
    {
        $user = ApiAuth::requireAuth();
        $role = $user['role'] ?? '';

        if ($role === 'admin') {
            $list = $this->demande->all();
            ApiResponse::success(['demandes' => array_map([$this, 'format'], $list)]);
        }

        $profil = $this->prest->findByUser((int) $user['id_user']);
        $own    = $this->demande->byUser((int) $user['id_user']);

        if ($profil && ($profil['statut_validation'] ?? '') === 'validee') {
            // Demandes sur lesquelles ce prestataire a une proposition (V3 : byProfile)
            $avecPropositions = $this->demande->byProfile((int) $profil['id_prestataire']);
            $byId = [];
            foreach (array_merge($own, $avecPropositions) as $dem) {
                $byId[(int) $dem['id_demande']] = $dem;
            }
            ApiResponse::success(['demandes' => array_map([$this, 'format'], array_values($byId))]);
        }

        ApiResponse::success(['demandes' => array_map([$this, 'format'], $own)]);
    }

    /**
     * GET ?action=demande_disponibles
     * Retourne les demandes éligibles pour le prestataire connecté (V3 : eligibles()).
     */
    public function disponibles(): void
    {
        $user = ApiAuth::requireAuth();
        $p    = $this->prestataireValideOuErreur($user);
        // V3 : eligibles() filtre via demandes_eligibles() (SQL) basée sur id_profile
        $list = $this->demande->eligibles((int) $p['id_prestataire']);
        ApiResponse::success(['demandes' => array_map([$this, 'format'], $list)]);
    }

    /**
     * POST ?action=demande_proposer
     * Body : { id_demande, message, prix_indicatif?, delai_estime? }
     * Prestataire validé envoie une proposition sur une demande ouverte.
     */
    public function proposer(): void
    {
        $user = ApiAuth::requireAuth();
        $p    = $this->prestataireValideOuErreur($user);

        $body       = ApiRequest::body();
        $id_demande = (int) ($body['id_demande'] ?? 0);
        $message    = trim($body['message'] ?? '');

        if (!$id_demande || $message === '') {
            ApiResponse::error('id_demande et message sont requis.', 422);
        }

        $prix  = !empty($body['prix_indicatif'])  ? (float) $body['prix_indicatif']  : null;
        $delai = !empty($body['delai_estime'])    ? trim($body['delai_estime'])       : null;

        $resultat = $this->demande->envoyerProposition(
            $id_demande,
            (int) $p['id_prestataire'],
            $message,
            $prix,
            $delai
        );

        match ($resultat) {
            'ok'                     => ApiResponse::success(['message' => 'Proposition envoyée.'], 201),
            'mauvais_statut'         => ApiResponse::error('La demande n\'est pas en état d\'accepter des propositions.', 409),
            'deja_proposee'          => ApiResponse::error('Vous avez déjà envoyé une proposition sur cette demande.', 409),
            'prestataire_non_valide' => ApiResponse::error('Profil prestataire non valide.', 403),
            'introuvable'            => ApiResponse::error('Demande introuvable.', 404),
            default                  => ApiResponse::error('Erreur inattendue : ' . $resultat, 500),
        };
    }

    /**
     * POST ?action=demande_selectionner
     * Body : { id_demande, id_proposition }
     * Client sélectionne une proposition (passe la demande en PRESTATAIRE_CHOISI).
     */
    public function selectionner(): void
    {
        $user = ApiAuth::requireAuth();

        $body          = ApiRequest::body();
        $id_demande    = (int) ($body['id_demande'] ?? 0);
        $id_proposition = (int) ($body['id_proposition'] ?? 0);

        if (!$id_demande || !$id_proposition) {
            ApiResponse::error('id_demande et id_proposition sont requis.', 422);
        }

        // Vérifier que l'utilisateur est bien le propriétaire de la demande
        $demande = $this->demande->find($id_demande);
        if (!$demande) {
            ApiResponse::error('Demande introuvable.', 404);
        }
        if ((int) $demande['id_user'] !== (int) $user['id_user'] && ($user['role'] ?? '') !== 'admin') {
            ApiResponse::error('Seul le client propriétaire peut sélectionner un prestataire.', 403);
        }

        $resultat = $this->demande->selectionnerPrestataire(
            $id_demande,
            (int) $user['id_user'],
            $id_proposition
        );

        match ($resultat) {
            'ok'                        => ApiResponse::success(['message' => 'Prestataire sélectionné.']),
            'mauvais_statut'            => ApiResponse::error('La demande n\'est pas en état de sélection.', 409),
            'non_autorise'              => ApiResponse::error('Action non autorisée.', 403),
            'proposition_introuvable', 'proposition_non_disponible' => ApiResponse::error('Proposition introuvable ou invalide.', 404),
            'introuvable'               => ApiResponse::error('Demande introuvable.', 404),
            default                     => ApiResponse::error('Erreur inattendue : ' . $resultat, 500),
        };
    }

    /**
     * POST ?action=demande_confirmer_engagement
     * Body : { id_demande }
     * Prestataire retenu confirme son engagement (passe en ENGAGEE).
     */
    public function confirmerEngagement(): void
    {
        $user = ApiAuth::requireAuth();
        $p    = $this->prestataireValideOuErreur($user);

        $id_demande = (int) (ApiRequest::body()['id_demande'] ?? 0);
        if (!$id_demande) {
            ApiResponse::error('id_demande requis.', 422);
        }

        $resultat = $this->demande->confirmerEngagement($id_demande, (int) $p['id_prestataire']);

        match ($resultat) {
            'ok'             => ApiResponse::success(['message' => 'Engagement confirmé.']),
            'mauvais_statut' => ApiResponse::error('La demande n\'est pas en attente de confirmation.', 409),
            'non_autorise'   => ApiResponse::error('Vous n\'êtes pas le prestataire retenu pour cette demande.', 403),
            'introuvable'    => ApiResponse::error('Demande introuvable.', 404),
            default          => ApiResponse::error('Erreur inattendue : ' . $resultat, 500),
        };
    }

    /**
     * POST ?action=demande_desister
     * Body : { id_demande }
     * Prestataire retenu se désiste après sélection (repasse la demande en EN_DISCUSSION).
     */
    public function desister(): void
    {
        $user = ApiAuth::requireAuth();
        $p    = $this->prestataireValideOuErreur($user);

        $id_demande = (int) (ApiRequest::body()['id_demande'] ?? 0);
        if (!$id_demande) {
            ApiResponse::error('id_demande requis.', 422);
        }

        $resultat = $this->demande->desister($id_demande, (int) $p['id_prestataire']);

        match ($resultat) {
            'ok'             => ApiResponse::success(['message' => 'Désistement enregistré.']),
            'mauvais_statut' => ApiResponse::error('Le désistement n\'est pas possible dans l\'état actuel.', 409),
            'non_autorise'   => ApiResponse::error('Vous n\'êtes pas le prestataire retenu pour cette demande.', 403),
            'introuvable'    => ApiResponse::error('Demande introuvable.', 404),
            default          => ApiResponse::error('Erreur inattendue : ' . $resultat, 500),
        };
    }

    /**
     * POST ?action=demande_annuler
     * Body : { id_demande }
     * Client annule sa propre demande.
     */
    public function annuler(): void
    {
        $user       = ApiAuth::requireAuth();
        $id_demande = (int) (ApiRequest::body()['id_demande'] ?? 0);
        if (!$id_demande) {
            ApiResponse::error('id_demande requis.', 422);
        }

        $resultat = $this->demande->annulerParClient($id_demande, (int) $user['id_user']);

        match ($resultat) {
            'ok'             => ApiResponse::success(['message' => 'Demande annulée.']),
            'mauvais_statut' => ApiResponse::error('La demande ne peut pas être annulée dans son état actuel.', 409),
            'non_autorise'   => ApiResponse::error('Vous n\'êtes pas le propriétaire de cette demande.', 403),
            'introuvable'    => ApiResponse::error('Demande introuvable.', 404),
            default          => ApiResponse::error('Erreur inattendue : ' . $resultat, 500),
        };
    }

    /**
     * Formater une demande pour la réponse JSON (V3).
     * Colonnes V3 : id_category, titre, urgence, category_libelle, id_profile_retenu.
     */
    private function format(array $d): array
    {
        return [
            'id_demande'       => (int) ($d['id_demande'] ?? 0),
            'titre'            => $d['titre'] ?? null,
            'description'      => $d['description'] ?? null,
            'statut'           => $d['statut'] ?? null,
            'urgence'          => $d['urgence'] ?? null,
            'adresse'          => $d['adresse'] ?? null,
            'ville'            => $d['ville'] ?? null,
            'code_postal'      => $d['code_postal'] ?? null,
            'budget_min'       => isset($d['budget_min'])  ? (float) $d['budget_min']  : null,
            'budget_max'       => isset($d['budget_max'])  ? (float) $d['budget_max']  : null,
            'published_at'     => $d['published_at'] ?? null,
            'expires_at'       => $d['expires_at'] ?? null,
            'id_user'          => isset($d['id_user'])     ? (int) $d['id_user']        : null,
            'id_category'      => isset($d['id_category']) ? (int) $d['id_category']    : null,
            'category_libelle' => $d['category_libelle']  ?? null,
            'id_profile_retenu'=> isset($d['id_profile_retenu']) && $d['id_profile_retenu'] !== null
                                    ? (int) $d['id_profile_retenu'] : null,
            'client_nom'       => $d['client_nom']    ?? ($d['nom']    ?? null),
            'client_prenom'    => $d['client_prenom'] ?? ($d['prenom'] ?? null),
        ];
    }
}