<?php
require_once __DIR__ . '/../models/Solution.php';
require_once __DIR__ . '/../models/Produit.php';
require_once __DIR__ . '/../models/Diagnostic.php';

class SolutionController {
    private Solution $solution;
    private Produit  $produit;
    private Diagnostic $diag;

    public function __construct() {
        $this->solution = new Solution();
        $this->produit  = new Produit();
        $this->diag     = new Diagnostic();
    }

    public function create(): void {
        $role = $_SESSION['user']['role'] ?? '';
        if (!in_array($role, ['prestataire', 'admin'])) {
            header('Location: index.php?action=dashboard'); exit;
        }

        $id_diagnostic = (int)($_GET['id_diagnostic'] ?? 0);
        $diagnostic    = $this->diag->find($id_diagnostic);
        if (!$diagnostic) { header('Location: index.php?action=demandes'); exit; }

        $produits = $this->produit->all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $desc = trim($_POST['description'] ?? '');
            if (empty($desc)) {
                $_SESSION['error'] = 'Description requise.';
                header('Location: index.php?action=solution_create&id_diagnostic=' . $id_diagnostic); exit;
            }
            $this->solution->create([
                'description'   => $desc,
                'id_diagnostic' => $id_diagnostic
            ]);
            $id_sol = $this->solution->lastId();

            // Produits utilisés
            $ids_produits = $_POST['id_produit'] ?? [];
            $quantites    = $_POST['quantite']   ?? [];
            foreach ($ids_produits as $k => $id_prod) {
                $id_prod = (int)$id_prod;
                $qte     = max(1, (int)($quantites[$k] ?? 1));
                if ($id_prod > 0) {
                    $this->solution->addProduit($id_sol, $id_prod, $qte);
                    // Mise à jour stock
                    $p = $this->produit->find($id_prod);
                    if ($p) {
                        $this->produit->updateStock($id_prod, max(0, $p['stock'] - $qte));
                    }
                }
            }

            $_SESSION['success'] = 'Solution enregistrée.';
            header('Location: index.php?action=diagnostic_show&id_demande=' . $diagnostic['id_demande']); exit;
        }

        require __DIR__ . '/../views/solution/create.php';
    }
}
