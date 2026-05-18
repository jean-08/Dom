<?php
require_once __DIR__ . '/../models/Prestataire.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Avis.php';

class PrestataireController {
    private Prestataire $prest;
    private Service $service;
    private Avis $avis;

    public function __construct() {
        $this->prest   = new Prestataire();
        $this->service = new Service();
        $this->avis    = new Avis();
    }

    public function index(): void {
        $prestataires = $this->prest->all();
        require __DIR__ . '/../views/prestataire/index.php';
    }

    public function show(): void {
        $id = (int)($_GET['id'] ?? 0);
        $prestataire = $this->prest->find($id);
        if (!$prestataire) { header('Location: index.php?action=prestataires'); exit; }
        $services = $this->service->byPrestataire($id);
        $avis     = $this->avis->byPrestataire($id);
        require __DIR__ . '/../views/prestataire/edit.php';
    }

    public function create(): void {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: index.php?action=dashboard'); exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $d = [
                'specialite' => trim($_POST['specialite'] ?? ''),
                'id_user'    => (int)($_POST['id_user'] ?? 0)
            ];
            $this->prest->create($d);
            $_SESSION['success'] = 'Prestataire créé.';
            header('Location: index.php?action=prestataires'); exit;
        }
        require __DIR__ . '/../views/prestataire/edit.php';
    }

    public function edit(): void {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: index.php?action=dashboard'); exit;
        }
        $id = (int)($_GET['id'] ?? 0);
        $prestataire = $this->prest->find($id);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->prest->update($id, ['specialite' => trim($_POST['specialite'] ?? '')]);
            $_SESSION['success'] = 'Prestataire mis à jour.';
            header('Location: index.php?action=prestataires'); exit;
        }
        require __DIR__ . '/../views/prestataire/edit.php';
    }

    public function delete(): void {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: index.php?action=dashboard'); exit;
        }
        $id = (int)($_GET['id'] ?? 0);
        $this->prest->delete($id);
        header('Location: index.php?action=prestataires'); exit;
    }

    public function addCompetence(): void {
        $id_prest = (int)($_POST['id_prestataire'] ?? 0);
        $id_serv  = (int)($_POST['id_service'] ?? 0);
        $niveau   = trim($_POST['niveau'] ?? '');
        $this->service->addCompetence($id_prest, $id_serv, $niveau);
        header('Location: index.php?action=prestataire_show&id=' . $id_prest); exit;
    }
}