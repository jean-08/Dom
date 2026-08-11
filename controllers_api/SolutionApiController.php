<?php
require_once __DIR__ . '/../models/Solution.php';
require_once __DIR__ . '/../models/Diagnostic.php';
require_once __DIR__ . '/../models/Demande.php';
require_once __DIR__ . '/../models/Produit.php';
require_once __DIR__ . '/../models/Prestataire.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/ApiAuth.php';
require_once __DIR__ . '/ApiRequest.php';

class SolutionApiController
{
    private Solution $solution;
    private Diagnostic $diag;
    private Demande $demande;
    private Produit $produit;
    private Prestataire $prest;

    public function __construct()
    {
        $this->solution = new Solution();
        $this->diag     = new Diagnostic();
        $this->demande  = new Demande();
        $this->produit  = new Produit();
        $this->prest    = new Prestataire();
    }

    /**
     * POST ?action=solution_proposer
     * { id_diagnostic, description, produits: [{ id_produit, quantite }, ...] }
     * Seul le prestataire auteur du diagnostic peut y rattacher une solution.
     */
    public function proposer(): void
    {
        $user = ApiAuth::requireAuth();
        $p = $this->prest->findByUser((int) $user['id_user']);
        // V3 : statut validé = 'validee'
        if (!$p || ($p['statut_validation'] ?? '') !== 'validee') {
            ApiResponse::error('Vous devez être un prestataire validé pour proposer une solution.', 403);
        }

        $d = ApiRequest::body();
        $id_diagnostic = (int) ($d['id_diagnostic'] ?? 0);
        $description   = trim($d['description'] ?? '');
        $produits      = is_array($d['produits'] ?? null) ? $d['produits'] : [];

        if (!$id_diagnostic || $description === '') {
            ApiResponse::error('id_diagnostic et description sont requis.', 422);
        }

        $diagnostic = $this->diag->find($id_diagnostic);
        if (!$diagnostic) {
            ApiResponse::error('Diagnostic introuvable.', 404);
        }
        // V3 : la table diagnostic stocke id_profile (et non id_prestataire)
        if ((int) ($diagnostic['id_profile'] ?? $diagnostic['id_prestataire'] ?? 0) !== (int) $p['id_prestataire']) {
            ApiResponse::error('Seul le prestataire auteur du diagnostic peut y proposer une solution.', 403);
        }

        $demandeRow = $this->demande->find((int) $diagnostic['id_demande']);
        if (!$demandeRow || $demandeRow['statut'] !== 'diagnostic_propose') {
            ApiResponse::error('Cette demande n\'est plus au statut "diagnostic proposé".', 409);
        }

        $this->solution->create(['description' => $description, 'id_diagnostic' => $id_diagnostic]);
        $id_solution = $this->solution->lastId();

        // Rattachement des produits utilisés, avec décrément de stock (même logique que le contrôleur HTML).
        foreach ($produits as $item) {
            $id_produit = (int) ($item['id_produit'] ?? 0);
            $quantite   = max(1, (int) ($item['quantite'] ?? 1));
            if ($id_produit <= 0) {
                continue;
            }
            $produitRow = $this->produit->find($id_produit);
            if (!$produitRow) {
                continue;
            }
            if ((int) $produitRow['stock'] < $quantite) {
                ApiResponse::error("Stock insuffisant pour le produit « {$produitRow['nom']} » (disponible : {$produitRow['stock']}).", 409);
            }
            $this->solution->addProduit($id_solution, $id_produit, $quantite);
            $this->produit->updateStock($id_produit, (int) $produitRow['stock'] - $quantite);
        }

        ApiResponse::success(['message' => 'Solution enregistrée.', 'id_solution' => $id_solution], 201);
    }

    /** GET ?action=solution_show&id_diagnostic=N */
    public function show(): void
    {
        $user = ApiAuth::requireAuth();
        $id_diagnostic = (int) ($_GET['id_diagnostic'] ?? 0);
        if (!$id_diagnostic) {
            ApiResponse::error('Paramètre id_diagnostic requis.', 422);
        }

        $diagnostic = $this->diag->find($id_diagnostic);
        if (!$diagnostic) {
            ApiResponse::error('Diagnostic introuvable.', 404);
        }
        $demandeRow = $this->demande->find((int) $diagnostic['id_demande']);
        $this->verifierAcces($user, $demandeRow);

        ApiResponse::success(['solutions' => $this->solution->byDiagnostic($id_diagnostic)]);
    }

    /** POST ?action=solution_valider  { id_solution } — le client valide une solution proposée sur sa demande. */
    public function valider(): void
    {
        $user = ApiAuth::requireAuth();
        $id_solution = (int) (ApiRequest::body()['id_solution'] ?? 0);
        if (!$id_solution) {
            ApiResponse::error('id_solution requis.', 422);
        }

        $resultat = $this->solution->valider($id_solution, (int) $user['id_user']);
        match ($resultat) {
            'ok'           => ApiResponse::success(['message' => 'Solution validée. L\'intervention peut démarrer.']),
            'introuvable'  => ApiResponse::error('Solution introuvable.', 404),
            'refuse'       => ApiResponse::error('Vous ne pouvez pas valider cette solution.', 403),
            'deja_validee' => ApiResponse::error('Cette solution est déjà validée (ou la demande n\'est plus à ce stade).', 409),
            default        => ApiResponse::error('Erreur inattendue.', 500),
        };
    }

    private function verifierAcces(array $user, array|false $demande): void
    {
        if (!$demande) {
            ApiResponse::error('Demande introuvable.', 404);
        }
        if (($user['role'] ?? '') === 'admin') {
            return;
        }
        if ((int) $demande['id_user'] === (int) $user['id_user']) {
            return;
        }
        $profil = $this->prest->findByUser((int) $user['id_user']);
        // V3 : IDOR via id_profile_retenu (et non id_prestataire_assigne)
        if ($profil
            && !empty($demande['id_profile_retenu'])
            && (int) $demande['id_profile_retenu'] === (int) $profil['id_prestataire']
        ) {
            return;
        }
        ApiResponse::error('Accès refusé à cette demande.', 403);
    }
}