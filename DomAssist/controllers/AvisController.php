<?php
require_once __DIR__ . '/../models/Avis.php';

class AvisController {
    private Avis $avis;
    public function __construct() { $this->avis = new Avis(); }

    public function create(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $d = [
                'note'           => (int)($_POST['note'] ?? 0),
                'comment'        => trim($_POST['comment'] ?? ''),
                'id_user'        => (int)$_SESSION['user']['id_user'],
                'id_prestataire' => (int)($_POST['id_prestataire'] ?? 0)
            ];
            if ($d['note'] < 1 || $d['note'] > 5) {
                $_SESSION['error'] = 'Note invalide (1-5).';
                header('Location: index.php?action=prestataires'); exit;
            }
            $this->avis->create($d);
            $_SESSION['success'] = 'Avis publié.';
            header('Location: index.php?action=prestataires'); exit;
        }
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
}