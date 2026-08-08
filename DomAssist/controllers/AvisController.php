<?php
require_once __DIR__ . '/../models/Avis.php';
require_once __DIR__ . '/../models/Intervention.php';
require_once __DIR__ . '/../models/Prestataire.php';

class AvisController {
    private Avis $avis;
    private Intervention $interv;
    private Prestataire $prest;

    public function __construct() {
        $this->avis   = new Avis();
        $this->interv = new Intervention();
        $this->prest  = new Prestataire();
    }

    /** Réputation (note moyenne + avis reçus) du prestataire connecté. */
    public function maReputation(): void {
        $profil = $this->prest->findByUser((int) ($_SESSION['user']['id_user'] ?? 0));
        if (!$profil || ($profil['statut_validation'] ?? '') !== 'validee') {
            $_SESSION['error'] = 'Vous devez être un prestataire validé pour consulter votre réputation.';
            header('Location: index.php?action=dashboard'); exit;
        }
        $reputation = $this->avis->reputation((int) $profil['id_prestataire']);
        $avis = $this->avis->byPrestataire((int) $profil['id_prestataire']);
        require __DIR__ . '/../views/avis/reputation.php';
    }

    public function create(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_intervention = (int) ($_POST['id_intervention'] ?? 0);
            $note            = (int) ($_POST['note'] ?? 0);
            $comment         = trim($_POST['comment'] ?? '');
            $id_user         = (int) $_SESSION['user']['id_user'];

            if ($note < 1 || $note > 5) {
                $_SESSION['error'] = 'Note invalide (1-5).';
                header('Location: index.php?action=prestataires'); exit;
            }
            if (!$id_intervention) {
                $_SESSION['error'] = 'Intervention introuvable.';
                header('Location: index.php?action=prestataires'); exit;
            }

            $resultat = $this->avis->creerPourIntervention($id_intervention, $id_user, $note, $comment);
            $_SESSION['error'] = match ($resultat) {
                'ok'           => null,
                'introuvable'  => 'Intervention introuvable.',
                'refuse'       => 'Cette intervention ne concerne pas votre demande.',
                'non_terminee' => 'Cette intervention n\'est pas encore terminée.',
                'deja_avise'   => 'Un avis existe déjà pour cette intervention.',
                default        => 'Erreur inattendue.',
            };
            $_SESSION['success'] = $resultat === 'ok' ? 'Avis publié.' : null;

            header('Location: index.php?action=prestataires'); exit;
        }

        // Formulaire : ne proposer que les interventions terminées du client, sans avis existant.
        $id_user = (int) ($_SESSION['user']['id_user'] ?? 0);
        $interventionsEligibles = $this->interv->termineesSansAvisPourClient($id_user);
        require __DIR__ . '/../views/avis/create.php';
    }

    public function delete(): void {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: index.php?action=dashboard'); exit;
        }
        $id = (int)($_GET['id'] ?? 0);
        $this->avis->delete($id);
        header('Location: index.php?action=prestataires'); exit;
    }

    /** Prestataire : droit de réponse à un avis reçu. */
    public function repondre(): void {
        $profil = $this->prest->findByUser((int) ($_SESSION['user']['id_user'] ?? 0));
        if (!$profil || ($profil['statut_validation'] ?? '') !== 'validee') {
            $_SESSION['error'] = 'Seul le prestataire concerné peut répondre à un avis.';
            header('Location: index.php?action=dashboard'); exit;
        }

        $id_avis = (int)($_POST['id_avis'] ?? 0);
        $reponse = trim($_POST['reponse_prestataire'] ?? '');

        if (!$id_avis || empty($reponse)) {
            $_SESSION['error'] = 'Le texte de réponse ne peut pas être vide.';
            header('Location: index.php?action=prestataire_show&id=' . (int)$profil['id_prestataire']); exit;
        }

        $resultat = $this->avis->repondre($id_avis, (int)$profil['id_prestataire'], $reponse);
        match ($resultat) {
            'ok'            => $_SESSION['success'] = 'Votre réponse a été publiée.',
            'introuvable'   => $_SESSION['error']   = 'Avis introuvable.',
            'non_autorise'  => $_SESSION['error']   = 'Vous n\'êtes pas le destinataire de cet avis.',
            'deja_repondu'  => $_SESSION['error']   = 'Vous avez déjà répondu à cet avis.',
            default         => $_SESSION['error']   = 'Erreur lors de l\'enregistrement de la réponse.',
        };

        header('Location: index.php?action=prestataire_show&id=' . (int)$profil['id_prestataire']); exit;
    }
}