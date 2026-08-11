<?php
require_once __DIR__ . '/../models/Diagnostic.php';
require_once __DIR__ . '/../models/Prestataire.php';

class DiagnosticController {
    private Diagnostic $diag;
    private Prestataire $prest;

    public function __construct() {
        $this->diag  = new Diagnostic();
        $this->prest = new Prestataire();
    }

    public function create(): void {
        $id_demande = (int)($_GET['id_demande'] ?? 0);
        $prest = $this->prest->findByUser((int)$_SESSION['user']['id_user']);

        // Sécurité : id_prestataire ne doit jamais venir du formulaire (IDOR),
        // il est toujours dérivé de la session de l'utilisateur connecté.
        if (!$prest || ($prest['statut_validation'] ?? '') !== 'validee') {
            $_SESSION['error'] = 'Vous devez être un prestataire validé pour proposer un diagnostic.';
            header('Location: index.php?action=demandes'); exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $description = trim($_POST['description'] ?? '');
            $resultat    = trim($_POST['resultat'] ?? '');
            $id_demande_post = (int)($_POST['id_demande'] ?? $id_demande);

            if ($description === '') {
                $_SESSION['error'] = 'Description requise.';
                header('Location: index.php?action=diagnostic_create&id_demande=' . $id_demande_post); exit;
            }

            $resultatOp = $this->diag->proposer(
                $id_demande_post,
                (int)$prest['id_prestataire'],
                $description,
                $resultat
            );

            if ($resultatOp === 'ok') {
                $_SESSION['success'] = 'Diagnostic enregistré.';
                try {
                    require_once __DIR__ . '/../models/Notification.php';
                    require_once __DIR__ . '/../models/Demande.php';
                    $demandeInfo = (new Demande())->find($id_demande_post);
                    if ($demandeInfo && !empty($demandeInfo['id_user'])) {
                        (new Notification())->diagnosticPublie((int)$demandeInfo['id_user'], $id_demande_post, $demandeInfo['titre'] ?? '');
                    }
                } catch (Throwable $e) { /* Notification silencieuse */ }
            } else {
                $_SESSION['error'] = match ($resultatOp) {
                    'introuvable'       => 'Demande introuvable.',
                    'non_assigne'       => 'Cette demande ne vous est pas assignée, ou n\'est pas au bon statut.',
                    'deja_diagnostique' => 'Un diagnostic existe déjà pour cette demande.',
                    default             => 'Erreur inattendue.',
                };
            }

            header('Location: index.php?action=demande_show&id=' . $id_demande_post); exit;
        }
        require __DIR__ . '/../views/diagnostic/create.php';
    }

    public function show(): void {
        $id_demande = (int)($_GET['id_demande'] ?? 0);
        $diagnostic = $this->diag->byDemande($id_demande);
        require __DIR__ . '/../views/diagnostic/show.php';
    }
}