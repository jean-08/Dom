<?php
require_once __DIR__ . '/../models/Avis.php';
require_once __DIR__ . '/../models/Intervention.php';
require_once __DIR__ . '/../models/Prestataire.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/ApiAuth.php';
require_once __DIR__ . '/ApiRequest.php';

class AvisApiController
{
    private Avis $avis;
    private Intervention $interv;
    private Prestataire $prest;

    public function __construct()
    {
        $this->avis   = new Avis();
        $this->interv = new Intervention();
        $this->prest  = new Prestataire();
    }

    /** POST ?action=avis_create  { id_intervention, note, comment? } */
    public function create(): void
    {
        $user = ApiAuth::requireAuth();

        $d = ApiRequest::body();
        $id_intervention = (int) ($d['id_intervention'] ?? 0);
        $note    = (int) ($d['note'] ?? 0);
        $comment = trim($d['comment'] ?? '');

        if (!$id_intervention || $note < 1 || $note > 5) {
            ApiResponse::error('id_intervention requis, note entre 1 et 5.', 422);
        }

        $resultat = $this->avis->creerPourIntervention($id_intervention, (int) $user['id_user'], $note, $comment);
        match ($resultat) {
            'ok'            => ApiResponse::success(['message' => 'Avis publié.', 'id_avis' => $this->avis->lastId()], 201),
            'introuvable'   => ApiResponse::error('Intervention introuvable.', 404),
            'refuse'        => ApiResponse::error('Cette intervention ne concerne pas votre demande.', 403),
            'non_terminee'  => ApiResponse::error('Cette intervention n\'est pas encore terminée.', 409),
            'deja_avise'    => ApiResponse::error('Un avis existe déjà pour cette intervention.', 409),
            default         => ApiResponse::error('Erreur inattendue.', 500),
        };
    }

    /** GET ?action=avis_prestataire&id_prestataire=N — annuaire public, avis + note moyenne. */
    public function byPrestataire(): void
    {
        $id = (int) ($_GET['id_prestataire'] ?? 0);
        if ($id <= 0) {
            ApiResponse::error('Paramètre id_prestataire requis.', 422);
        }
        if (!$this->prest->find($id)) {
            ApiResponse::error('Prestataire introuvable.', 404);
        }

        $rep = $this->avis->reputation($id);
        $list = $this->avis->byPrestataire($id);

        ApiResponse::success([
            'id_prestataire' => $id,
            'note_moyenne'   => round((float) $rep['note_moyenne'], 2),
            'nombre_avis'    => (int) $rep['nombre_avis'],
            'avis'           => array_map([$this, 'format'], $list),
        ]);
    }

    /** GET ?action=avis_ma_reputation — vue prestataire connecté (US "Consulter sa réputation"). */
    public function maReputation(): void
    {
        $user = ApiAuth::requireAuth();
        $p = $this->prest->findByUser((int) $user['id_user']);
        if (!$p) {
            ApiResponse::error('Aucun profil prestataire.', 403);
        }

        $rep = $this->avis->reputation((int) $p['id_prestataire']);
        ApiResponse::success([
            'note_moyenne' => round((float) $rep['note_moyenne'], 2),
            'nombre_avis'  => (int) $rep['nombre_avis'],
            'avis'         => array_map([$this, 'format'], $this->avis->byPrestataire((int) $p['id_prestataire'])),
        ]);
    }

    /** POST ?action=avis_repondre  { id_avis, reponse_prestataire } */
    public function repondre(): void
    {
        $user = ApiAuth::requireAuth();
        $p = $this->prest->findByUser((int) $user['id_user']);
        if (!$p || ($p['statut_validation'] ?? '') !== 'validee') {
            ApiResponse::error('Vous devez être un prestataire validé pour répondre à un avis.', 403);
        }

        $d = ApiRequest::body();
        $id_avis = (int) ($d['id_avis'] ?? 0);
        $reponse = trim($d['reponse'] ?? $d['reponse_prestataire'] ?? '');

        if (!$id_avis || $reponse === '') {
            ApiResponse::error('id_avis et reponse sont requis.', 422);
        }

        $res = $this->avis->repondre($id_avis, (int)$p['id_prestataire'], $reponse);
        match ($res) {
            'ok'           => ApiResponse::success(['message' => 'Réponse enregistrée.']),
            'introuvable'  => ApiResponse::error('Avis introuvable.', 404),
            'non_autorise' => ApiResponse::error('Vous n\'êtes pas le destinataire de cet avis.', 403),
            'deja_repondu' => ApiResponse::error('Vous avez déjà répondu à cet avis.', 409),
            default        => ApiResponse::error('Erreur inattendue.', 500),
        };
    }

    private function format(array $a): array
    {
        return [
            'id_avis'             => (int) ($a['id_avis'] ?? 0),
            'note'                => (int) ($a['note'] ?? 0),
            'comment'             => $a['comment'] ?? null,
            'id_intervention'     => isset($a['id_intervention']) ? (int) $a['id_intervention'] : null,
            'reponse_prestataire' => $a['reponse_prestataire'] ?? null,
            'reponse_created_at'  => $a['reponse_created_at'] ?? null,
            'nom'                 => $a['nom'] ?? null,
            'prenom'              => $a['prenom'] ?? null,
        ];
    }
}