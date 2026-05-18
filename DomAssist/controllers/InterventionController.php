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
        $dispos = $prest ? $this->dispo->libres($prest['id_prestataire']) : [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $d = [
                'resultat'       => trim($_POST['resultat'] ?? ''),
                'id_prestataire' => (int)$_POST['id_prestataire'],
                'id_demande'     => (int)$_POST['id_demande'],
                'id_dispo'       => !empty($_POST['id_dispo']) ? (int)$_POST['id_dispo'] : null
            ];
            $this->interv->create($d);
            if ($d['id_dispo']) {
                $this->dispo->setOccupe($d['id_dispo']);
            }
            $_SESSION['success'] = 'Intervention créée.';
            header('Location: index.php?action=interventions'); exit;
        }
        require __DIR__ . '/../views/intervention/create.php';
    }
}