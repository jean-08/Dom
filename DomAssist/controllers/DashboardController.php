<?php
require_once __DIR__ . '/../models/Demande.php';
require_once __DIR__ . '/../models/Prestataire.php';
require_once __DIR__ . '/../models/Intervention.php';
require_once __DIR__ . '/../models/Avis.php';

/**
 * Dashboard unifié : un utilisateur est toujours "client" et peut,
 * en plus, avoir un profil prestataire (en attente, validé, rejeté...).
 * Cette vue agrège les deux sans dupliquer la logique métier des modèles.
 */
class DashboardController {
    private Demande $demande;
    private Prestataire $prest;
    private Intervention $interv;
    private Avis $avis;

    public function __construct() {
        $this->demande = new Demande();
        $this->prest   = new Prestataire();
        $this->interv  = new Intervention();
        $this->avis    = new Avis();
    }

    public function index(): void {
        $id_user = (int) ($_SESSION['user']['id_user'] ?? 0);

        $mesDemandes = $this->demande->byUser($id_user);

        $profilPrestataire = $this->prest->findByUser($id_user);
        $demandesDisponibles = [];
        $mesInterventions = [];
        $reputation = ['note_moyenne' => 0, 'nombre_avis' => 0];

        if ($profilPrestataire && ($profilPrestataire['statut_validation'] ?? '') === 'validee') {
            $demandesDisponibles = $this->demande->eligibles((int) $profilPrestataire['id_prestataire']);
            $mesInterventions    = $this->interv->byPrestataire((int) $profilPrestataire['id_prestataire']);
            $reputation          = $this->avis->reputation((int) $profilPrestataire['id_prestataire']);
        }

        require __DIR__ . '/../views/dashboard.php';
    }
}
