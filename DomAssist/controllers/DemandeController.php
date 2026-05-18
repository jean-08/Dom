<?php
require_once __DIR__ . '/../models/Demande.php';

class DemandeController {
    private Demande $demande;
    public function __construct() { $this->demande = new Demande(); }

    public function index(): void {
        $role = $_SESSION['user']['role'] ?? '';
        if ($role === 'admin') {
            $demandes = $this->demande->all();
        } else {
            $demandes = $this->demande->byUser((int)$_SESSION['user']['id_user']);
        }
        require __DIR__ . '/../views/demande/index.php';
    }

    public function create(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $d = [
                'description' => trim($_POST['description'] ?? ''),
                'adresse'     => trim($_POST['adresse'] ?? ''),
                'id_user'     => (int)$_SESSION['user']['id_user']
            ];
            if (empty($d['description']) || empty($d['adresse'])) {
                $_SESSION['error'] = 'Champs requis.';
                header('Location: index.php?action=demande_create'); exit;
            }
            $this->demande->create($d);
            $_SESSION['success'] = 'Demande envoyée.';
            header('Location: index.php?action=demandes'); exit;
        }
        require __DIR__ . '/../views/demande/create.php';
    }

    public function show(): void {
        $id = (int)($_GET['id'] ?? 0);
        $demande = $this->demande->find($id);
        if (!$demande) { header('Location: index.php?action=demandes'); exit; }
        require __DIR__ . '/../views/demande/show.php';
    }

    public function updateStatut(): void {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: index.php?action=dashboard'); exit;
        }
        $id     = (int)($_POST['id_demande'] ?? 0);
        $statut = $_POST['statut'] ?? '';
        $allowed = ['en_attente','acceptée','refusée','terminée'];
        if (in_array($statut, $allowed)) {
            $this->demande->updateStatut($id, $statut);
        }
        header('Location: index.php?action=demandes'); exit;
    }

    public function delete(): void {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: index.php?action=dashboard'); exit;
        }
        $id = (int)($_GET['id'] ?? 0);
        $this->demande->delete($id);
        header('Location: index.php?action=demandes'); exit;
    }
}