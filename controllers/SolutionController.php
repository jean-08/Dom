<?php
require_once __DIR__ . '/../models/Solution.php';
require_once __DIR__ . '/../models/Produit.php';
require_once __DIR__ . '/../models/Diagnostic.php';
require_once __DIR__ . '/../models/Prestataire.php';

class SolutionController {
    private Solution $solution;
    private Produit  $produit;
    private Diagnostic $diag;
    private Prestataire $prest;

    public function __construct() {
        $this->solution = new Solution();
        $this->produit  = new Produit();
        $this->diag     = new Diagnostic();
        $this->prest    = new Prestataire();
    }

    public function create(): void {
        $profil = $this->prest->findByUser((int)($_SESSION['user']['id_user'] ?? 0));
        if (!$profil || ($profil['statut_validation'] ?? '') !== 'validee') {
            $_SESSION['error'] = 'Vous devez être un prestataire validé pour proposer une solution.';
            header('Location: index.php?action=dashboard'); exit;
        }

        $id_diagnostic = (int)($_GET['id_diagnostic'] ?? 0);
        $diagnostic    = $this->diag->find($id_diagnostic);
        if (!$diagnostic) { header('Location: index.php?action=demandes'); exit; }

        require_once __DIR__ . '/../models/Demande.php';
        $demandeInfo = (new Demande())->find((int)$diagnostic['id_demande']);
        if (!$demandeInfo || $demandeInfo['statut'] !== 'diagnostic_propose') {
            $_SESSION['error'] = 'Cette demande n\'est pas au statut "diagnostic proposé".';
            header('Location: index.php?action=dashboard'); exit;
        }

        // Sécurité : seul le prestataire auteur du diagnostic peut y rattacher
        // une solution — sinon n'importe quel prestataire pourrait proposer
        // une solution sur un diagnostic qui ne lui appartient pas.
        if ((int)$diagnostic['id_prestataire'] !== (int)$profil['id_prestataire']) {
            $_SESSION['error'] = 'Seul le prestataire auteur du diagnostic peut y proposer une solution.';
            header('Location: index.php?action=demandes'); exit;
        }

        $produits = $this->produit->all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $desc = trim($_POST['description'] ?? '');
            if (empty($desc)) {
                $_SESSION['error'] = 'Description requise.';
                header('Location: index.php?action=solution_create&id_diagnostic=' . $id_diagnostic); exit;
            }

            // Vérification du stock désactivée temporairement
            $ids_produits = [];
            $quantites    = [];
            /*
            $ids_produits = $_POST['id_produit'] ?? [];
            $quantites    = $_POST['quantite']   ?? [];
            foreach ($ids_produits as $k => $id_prod) {
                $id_prod = (int)$id_prod;
                $qte     = max(1, (int)($quantites[$k] ?? 1));
                if ($id_prod <= 0) continue;
                $p = $this->produit->find($id_prod);
                if ($p && (int)$p['stock'] < $qte) {
                    $_SESSION['error'] = "Stock insuffisant pour le produit « {$p['nom']} » (disponible : {$p['stock']}).";
                    header('Location: index.php?action=solution_create&id_diagnostic=' . $id_diagnostic); exit;
                }
            }
            */

            $this->solution->create([
                'description'   => $desc,
                'id_diagnostic' => $id_diagnostic
            ]);
            $id_sol = $this->solution->lastId();

            /*
            foreach ($ids_produits as $k => $id_prod) {
                $id_prod = (int)$id_prod;
                $qte     = max(1, (int)($quantites[$k] ?? 1));
                if ($id_prod > 0) {
                    $this->solution->addProduit($id_sol, $id_prod, $qte);
                    $p = $this->produit->find($id_prod);
                    if ($p) {
                        $this->produit->updateStock($id_prod, max(0, $p['stock'] - $qte));
                    }
                }
            }
            */

            $_SESSION['success'] = 'Solution enregistrée.';
            try {
                require_once __DIR__ . '/../models/Notification.php';
                require_once __DIR__ . '/../models/Demande.php';
                $demandeInfo = (new Demande())->find((int)$diagnostic['id_demande']);
                if ($demandeInfo && !empty($demandeInfo['id_user'])) {
                    (new Notification())->solutionProposee((int)$demandeInfo['id_user'], (int)$diagnostic['id_demande'], $demandeInfo['titre'] ?? '');
                }
            } catch (Throwable $e) { /* Notification silencieuse */ }
            header('Location: index.php?action=demande_show&id=' . $diagnostic['id_demande']); exit;
        }

        require __DIR__ . '/../views/solution/create.php';
    }

    /** Le client valide une solution proposée sur sa demande. */
    public function valider(): void {
        $id_solution = (int) ($_POST['id_solution'] ?? 0);
        $id_demande  = (int) ($_POST['id_demande'] ?? 0);
        $id_user     = (int) ($_SESSION['user']['id_user'] ?? 0);

        $resultat = $this->solution->valider($id_solution, $id_user);
        if ($resultat === 'ok') {
            $_SESSION['success'] = 'Solution validée. Le prestataire peut démarrer l\'intervention.';
            try {
                require_once __DIR__ . '/../models/Notification.php';
                require_once __DIR__ . '/../models/Demande.php';
                require_once __DIR__ . '/../models/PrestataireProfile.php';
                $demandeInfo = (new Demande())->find($id_demande);
                if ($demandeInfo && !empty($demandeInfo['id_profile_retenu'])) {
                    $pp = (new PrestataireProfile())->find((int)$demandeInfo['id_profile_retenu']);
                    if ($pp && !empty($pp['id_user'])) {
                        (new Notification())->solutionValidee((int)$pp['id_user'], $id_demande, $demandeInfo['titre'] ?? '');
                    }
                }
            } catch (Throwable $e) { /* Notification silencieuse */ }
        } else {
            $_SESSION['error'] = match ($resultat) {
                'introuvable'  => 'Solution introuvable.',
                'refuse'       => 'Cette solution ne concerne pas votre demande.',
                'deja_validee' => 'Cette solution est déjà validée.',
                default        => 'Erreur inattendue.',
            };
        }

        header('Location: index.php?action=demande_show&id=' . $id_demande); exit;
    }

    /** Le client me refuse une solution proposée sur sa demande. */
    public function refuser(): void {
        $id_solution = (int) ($_POST['id_solution'] ?? 0);
        $id_demande  = (int) ($_POST['id_demande'] ?? 0);
        $id_user     = (int) ($_SESSION['user']['id_user'] ?? 0);

        $resultat = $this->solution->refuser($id_solution, $id_user);
        if ($resultat === 'ok') {
            $_SESSION['success'] = 'Solution refusée.';
            try {
                require_once __DIR__ . '/../models/Notification.php';
                require_once __DIR__ . '/../models/Demande.php';
                require_once __DIR__ . '/../models/PrestataireProfile.php';
                $demandeInfo = (new Demande())->find($id_demande);
                if ($demandeInfo && !empty($demandeInfo['id_profile_retenu'])) {
                    $pp = (new PrestataireProfile())->find((int)$demandeInfo['id_profile_retenu']);
                    if ($pp && !empty($pp['id_user'])) {
                        (new Notification())->solutionRefusee((int)$pp['id_user'], $id_demande, $demandeInfo['titre'] ?? '');
                    }
                }
            } catch (Throwable $e) { /* Notification silencieuse */ }
        } else {
            $_SESSION['error'] = 'Erreur lors du refus de la solution.';
        }

        header('Location: index.php?action=demande_show&id=' . $id_demande); exit;
    }
}