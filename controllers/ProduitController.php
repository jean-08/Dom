<?php
require_once __DIR__ . '/../models/Produit.php';

class ProduitController {
    private Produit $produit;
    public function __construct() { $this->produit = new Produit(); }

    private function requireAdmin(): void {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: index.php?action=dashboard'); exit;
        }
    }

    public function index(): void {
        $produits = $this->produit->all();
        require __DIR__ . '/../views/produit/index.php';
    }

    public function create(): void {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $d = [
                'nom'   => trim($_POST['nom'] ?? ''),
                'prix'  => (float)($_POST['prix'] ?? 0),
                'stock' => (int)($_POST['stock'] ?? 0)
            ];
            $this->produit->create($d);
            $_SESSION['success'] = 'Produit ajouté.';
            header('Location: index.php?action=produits'); exit;
        }
        require __DIR__ . '/../views/produit/index.php';
    }

    public function delete(): void {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $this->produit->delete($id);
        header('Location: index.php?action=produits'); exit;
    }
}