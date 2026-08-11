<?php
require_once __DIR__ . '/../models/Diagnostic.php';
require_once __DIR__ . '/../models/Demande.php';
require_once __DIR__ . '/../models/Prestataire.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/ApiAuth.php';
require_once __DIR__ . '/ApiRequest.php';

class DiagnosticApiController
{
    private Diagnostic $diag;
    private Demande $demande;
    private Prestataire $prest;

    public function __construct()
    {
        $this->diag    = new Diagnostic();
        $this->demande = new Demande();
        $this->prest   = new Prestataire();
    }

    /** POST ?action=diagnostic_proposer  { id_demande, description, resultat } */
    public function proposer(): void
    {
        $user = ApiAuth::requireAuth();
        $p = $this->prest->findByUser((int) $user['id_user']);
        // V3 : statut validé = 'validee'
        if (!$p || ($p['statut_validation'] ?? '') !== 'validee') {
            ApiResponse::error('Vous devez être un prestataire validé pour proposer un diagnostic.', 403);
        }

        $d = ApiRequest::body();
        $id_demande  = (int) ($d['id_demande'] ?? 0);
        $description = trim($d['description'] ?? '');
        $resultat    = trim($d['resultat'] ?? '');

        if (!$id_demande || $description === '') {
            ApiResponse::error('id_demande et description sont requis.', 422);
        }

        $resultatOp = $this->diag->proposer($id_demande, (int) $p['id_prestataire'], $description, $resultat);
        match ($resultatOp) {
            'ok'                 => ApiResponse::success(['message' => 'Diagnostic enregistré.', 'id_diagnostic' => $this->diag->lastId()], 201),
            'introuvable'        => ApiResponse::error('Demande introuvable.', 404),
            'non_assigne'        => ApiResponse::error('Cette demande ne vous est pas assignée, ou n\'est pas au bon statut.', 403),
            'deja_diagnostique'  => ApiResponse::error('Un diagnostic existe déjà pour cette demande.', 409),
            default              => ApiResponse::error('Erreur inattendue.', 500),
        };
    }

    /** GET ?action=diagnostic_show&id_demande=N */
    public function show(): void
    {
        $user = ApiAuth::requireAuth();
        $id_demande = (int) ($_GET['id_demande'] ?? 0);
        if (!$id_demande) {
            ApiResponse::error('Paramètre id_demande requis.', 422);
        }

        // Réutilise le même contrôle d'accès (propriétaire / prestataire assigné / admin) que sur la demande.
        $demande = $this->demande->find($id_demande);
        if (!$demande) {
            ApiResponse::error('Demande introuvable.', 404);
        }
        $this->verifierAcces($user, $demande);

        $diagnostic = $this->diag->byDemande($id_demande);
        if (!$diagnostic) {
            ApiResponse::success(['diagnostic' => null]);
        }
        ApiResponse::success(['diagnostic' => $diagnostic]);
    }

    private function verifierAcces(array $user, array $demande): void
    {
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