<?php
require_once __DIR__ . '/../models/Intervention.php';
require_once __DIR__ . '/../models/Disponibilite.php';
require_once __DIR__ . '/../models/Prestataire.php';

class InterventionController {
    private Intervention $interv;
    private Disponibilite $dispo;
    private Prestataire $prest;

    public function __construct() {
        $this->interv = new Intervention();
        $this->dispo  = new Disponibilite();
        $this->prest  = new Prestataire();
    }

    public function index(): void {
        $role = $_SESSION['user']['role'] ?? '';
        if ($role === 'admin') {
            $interventions = $this->interv->all();
        } else {
            $prest = $this->prest->findByUser((int)$_SESSION['user']['id_user']);
            $interventions = $prest ? $this->interv->byPrestataire($prest['id_prestataire']) : [];
        }
        require __DIR__ . '/../views/intervention/index.php';
    }

    public function create(): void {
        $id_demande = (int)($_GET['id_demande'] ?? 0);
        $prest = $this->prest->findByUser((int)$_SESSION['user']['id_user']);

        // Sécurité : id_prestataire ne doit jamais venir du formulaire (IDOR),
        // il est toujours dérivé de la session de l'utilisateur connecté.
        if (!$prest || ($prest['statut_validation'] ?? '') !== 'validee') {
            $_SESSION['error'] = 'Vous devez être un prestataire validé pour démarrer une intervention.';
            header('Location: index.php?action=demandes'); exit;
        }

        $dispos = $this->dispo->libres($prest['id_prestataire']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_demande_post = (int)($_POST['id_demande'] ?? $id_demande);
            $id_dispo = !empty($_POST['id_dispo']) ? (int)$_POST['id_dispo'] : null;

            $resultatOp = $this->interv->demarrer($id_demande_post, (int)$prest['id_prestataire'], $id_dispo);

            if (ctype_digit((string)$resultatOp)) {
                if ($id_dispo) {
                    $this->dispo->setOccupe($id_dispo);
                }
                $_SESSION['success'] = 'Intervention démarrée.';
                header('Location: index.php?action=interventions'); exit;
            }

            $_SESSION['error'] = match ($resultatOp) {
                'introuvable'   => 'Demande introuvable.',
                'non_assigne'   => 'Cette demande ne vous est pas assignée.',
                'deja_demarree' => 'La solution n\'est pas validée, ou l\'intervention est déjà en cours.',
                default         => 'Erreur inattendue.',
            };
            header('Location: index.php?action=interventions'); exit;
        }
        require __DIR__ . '/../views/intervention/create.php';
    }

    /** Le prestataire assigné clôture son intervention et enregistre le résultat. */
    public function terminer(): void {
        $prest = $this->prest->findByUser((int) ($_SESSION['user']['id_user'] ?? 0));
        if (!$prest) { header('Location: index.php?action=dashboard'); exit; }

        $id_intervention = (int) ($_POST['id_intervention'] ?? 0);
        $resultat_texte   = trim($_POST['resultat'] ?? '');

        $resultat = $this->interv->terminer($id_intervention, (int) $prest['id_prestataire'], $resultat_texte);
        if ($resultat === 'ok') {
            $_SESSION['success'] = 'Intervention clôturée.';
            try {
                require_once __DIR__ . '/../models/Notification.php';
                require_once __DIR__ . '/../models/Demande.php';
                $intervObj = $this->interv->find($id_intervention);
                if ($intervObj && !empty($intervObj['id_demande'])) {
                    $demandeInfo = (new Demande())->find((int)$intervObj['id_demande']);
                    if ($demandeInfo && !empty($demandeInfo['id_user'])) {
                        (new Notification())->interventionTerminee((int)$demandeInfo['id_user'], (int)$intervObj['id_demande'], $demandeInfo['titre'] ?? '');
                    }
                }
            } catch (Throwable $e) { /* Notification silencieuse */ }
        } else {
            $_SESSION['error'] = match ($resultat) {
                'introuvable'    => 'Intervention introuvable.',
                'non_assigne'    => 'Cette intervention ne vous est pas assignée.',
                'deja_terminee'  => 'Cette intervention est déjà terminée.',
                default          => 'Erreur inattendue.',
            };
        }

        header('Location: index.php?action=interventions'); exit;
    }
}