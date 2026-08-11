<?php
require_once __DIR__ . '/../models/Disponibilite.php';
require_once __DIR__ . '/../models/Prestataire.php';

class DisponibiliteController {
    private Disponibilite $dispo;
    private Prestataire $prest;

    public function __construct() {
        $this->dispo = new Disponibilite();
        $this->prest = new Prestataire();
    }

    public function index(): void {
        $prest = $this->prest->findByUser((int)($_SESSION['user']['id_user'] ?? 0));
        if (!$prest || ($prest['statut_validation'] ?? '') !== 'validee') {
            $_SESSION['error'] = 'Vous devez être un prestataire validé pour gérer vos disponibilités.';
            header('Location: index.php?action=dashboard'); exit;
        }
        $dispos = $this->dispo->byPrestataire($prest['id_prestataire']);
        require __DIR__ . '/../views/disponibilite/index.php';
    }

    public function create(): void {
        $prest = $this->prest->findByUser((int)($_SESSION['user']['id_user'] ?? 0));
        if (!$prest || ($prest['statut_validation'] ?? '') !== 'validee') {
            $_SESSION['error'] = 'Vous devez être un prestataire validé pour ajouter une disponibilité.';
            header('Location: index.php?action=dashboard'); exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $d = [
                'date'           => $_POST['date'] ?? '',
                'heure_debut'    => $_POST['heure_debut'] ?? '',
                'heure_fin'      => $_POST['heure_fin'] ?? '',
                'id_prestataire' => $prest['id_prestataire']
            ];
            $this->dispo->create($d);
            $_SESSION['success'] = 'Disponibilité ajoutée.';
            header('Location: index.php?action=disponibilites'); exit;
        }
        require __DIR__ . '/../views/disponibilite/index.php';
    }

    public function delete(): void {
        $prest = $this->prest->findByUser((int)($_SESSION['user']['id_user'] ?? 0));
        if (!$prest || ($prest['statut_validation'] ?? '') !== 'validee') {
            header('Location: index.php?action=dashboard'); exit;
        }
        $id = (int)($_GET['id'] ?? 0);
        $this->dispo->delete($id);
        $_SESSION['success'] = 'Disponibilité supprimée.';
        header('Location: index.php?action=disponibilites'); exit;
    }
}