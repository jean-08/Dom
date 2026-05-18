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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $d = [
                'description'   => trim($_POST['description'] ?? ''),
                'resultat'      => trim($_POST['resultat'] ?? ''),
                'id_demande'    => (int)$_POST['id_demande'],
                'id_prestataire'=> (int)$_POST['id_prestataire']
            ];
            $this->diag->create($d);
            $_SESSION['success'] = 'Diagnostic enregistré.';
            header('Location: index.php?action=demandes'); exit;
        }
        require __DIR__ . '/../views/diagnostic/create.php';
    }

    public function show(): void {
        $id_demande = (int)($_GET['id_demande'] ?? 0);
        $diagnostic = $this->diag->byDemande($id_demande);
        require __DIR__ . '/../views/diagnostic/show.php';
    }
}