<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Demande.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Intervention.php';
require_once __DIR__ . '/../models/Prestataire.php';

class AdminController {
    private User $user;
    private Demande $demande;
    private Service $service;
    private Intervention $intervention;
    private Prestataire $prest;

    public function __construct() {
        $this->user = new User();
        $this->demande = new Demande();
        $this->service = new Service();
        $this->intervention = new Intervention();
        $this->prest = new Prestataire();
    }

    private function requireAdmin(): void {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: index.php?action=dashboard');
            exit;
        }
    }

    public function dashboard(): void {
        $this->requireAdmin();
        $users = $this->user->all();
        $demandes = $this->demande->all();
        $interventions = $this->intervention->all();
        $prestataires = $this->prest->allValides();

        $totalClients = count(array_filter($users, fn($u) => $u['role'] === 'client'));
        $totalDemandes = count($demandes);
        $totalPrestataires = count($prestataires);
        $totalInterventions = count($interventions);
        
        require __DIR__ . '/../views/admin/dashboard.php';
    }

    public function users(): void {
        $this->requireAdmin();
        $allUsers   = $this->user->all();
        $perPage    = max(1, (int)($_GET['per_page'] ?? 20));
        $totalUsers = count($allUsers);
        $totalPages = (int)ceil($totalUsers / $perPage);
        $currentPage = max(1, min((int)($_GET['page'] ?? 1), $totalPages ?: 1));
        $users = array_slice($allUsers, ($currentPage - 1) * $perPage, $perPage);
        require __DIR__ . '/../views/admin/users.php';
    }

    public function deleteUser(): void {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id === (int)($_SESSION['user']['id_user'] ?? 0)) {
            $_SESSION['error'] = 'Vous ne pouvez pas supprimer votre propre compte.';
            header('Location: index.php?action=admin_users');
            exit;
        }

        $this->user->delete($id);
        $_SESSION['success'] = 'Utilisateur supprimé avec succès.';
        header('Location: index.php?action=admin_users');
        exit;
    }

    public function suspendUser(): void {
        $this->requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $motif = trim($_POST['motif'] ?? '');

        if ($id === (int)($_SESSION['user']['id_user'] ?? 0)) {
            $_SESSION['error'] = 'Vous ne pouvez pas suspendre votre propre compte.';
            header('Location: index.php?action=admin_users');
            exit;
        }

        $this->user->suspendre($id, $motif);
        try {
            require_once __DIR__ . '/../models/Notification.php';
            (new Notification())->compteSuspendu($id, $motif);
        } catch (Throwable $e) { /* Notification silencieuse */ }

        $_SESSION['success'] = 'Utilisateur suspendu.';
        header('Location: index.php?action=admin_users');
        exit;
    }

    public function reactivateUser(): void {
        $this->requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $this->user->reactiver($id);
        try {
            require_once __DIR__ . '/../models/Notification.php';
            (new Notification())->compteReactive($id);
        } catch (Throwable $e) { /* Notification silencieuse */ }

        $_SESSION['success'] = 'Utilisateur réactivé.';
        header('Location: index.php?action=admin_users');
        exit;
    }

    public function profile(): void {
        $this->requireAdmin();
        $id = (int)($_SESSION['user']['id_user'] ?? 0);
        $admin = $this->user->findWithPassword($id);

        if (!$admin) {
            header('Location: index.php?action=dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ancien_mdp = $_POST['ancien_mdp'] ?? '';
            $nouveau_mdp = $_POST['nouveau_mdp'] ?? '';
            $confirmer_mdp = $_POST['confirmer_mdp'] ?? '';

            if (empty($ancien_mdp) || empty($nouveau_mdp) || empty($confirmer_mdp)) {
                $_SESSION['error'] = 'Tous les champs sont requis.';
                header('Location: index.php?action=admin_profile');
                exit;
            }

            if (!password_verify($ancien_mdp, $admin['mot_de_passe'])) {
                $_SESSION['error'] = 'Ancien mot de passe incorrect.';
                header('Location: index.php?action=admin_profile');
                exit;
            }

            if ($nouveau_mdp !== $confirmer_mdp) {
                $_SESSION['error'] = 'Les nouveaux mots de passe ne correspondent pas.';
                header('Location: index.php?action=admin_profile');
                exit;
            }

            if (strlen($nouveau_mdp) < 6) {
                $_SESSION['error'] = 'Le mot de passe doit contenir au moins 6 caractères.';
                header('Location: index.php?action=admin_profile');
                exit;
            }

            // Mettre à jour le mot de passe
            $db = Database::getInstance();
            $s = $db->prepare("UPDATE \"user\" SET mot_de_passe=? WHERE id_user=?");
            $s->execute([password_hash($nouveau_mdp, PASSWORD_BCRYPT), $id]);

            $_SESSION['success'] = 'Mot de passe modifié avec succès.';
            header('Location: index.php?action=admin_profile');
            exit;
        }

        require __DIR__ . '/../views/admin/profile.php';
    }

    public function suiviDemandes(): void {
        $this->requireAdmin();
        $demandes = $this->demande->all();
        require __DIR__ . '/../views/admin/suivi-demandes.php';
    }

    public function suiviServices(): void {
        $this->requireAdmin();
        $services = $this->service->all();
        require __DIR__ . '/../views/admin/suivi-services.php';
    }

    public function suiviInterventions(): void {
        $this->requireAdmin();
        $interventions = $this->intervention->all();
        require __DIR__ . '/../views/admin/suivi-interventions.php';
    }

    /** US-4 (CDC v2) : liste des candidatures prestataire en attente. */
    public function prestatairesEnAttente(): void {
        $this->requireAdmin();
        $candidatures = $this->prest->findEnAttente();
        require __DIR__ . '/../views/admin/prestataires_en_attente.php';
    }

    /** US-5 (CDC v2) : validation d'une candidature. */
    public function validerPrestataire(): void {
        $this->requireAdmin();
        $id = (int)($_POST['id_prestataire'] ?? 0);
        $this->prest->valider($id);
        try {
            require_once __DIR__ . '/../models/Notification.php';
            $p = $this->prest->find($id);
            if ($p && !empty($p['id_user'])) {
                (new Notification())->candidatureValidee((int)$p['id_user']);
            }
        } catch (Throwable $e) { /* Notification silencieuse */ }
        $_SESSION['success'] = 'Prestataire validé.';
        header('Location: index.php?action=admin_prestataires_en_attente'); exit;
    }

    /** US-5 (CDC v2) : rejet d'une candidature, avec motif. */
    public function rejeterPrestataire(): void {
        $this->requireAdmin();
        $id    = (int)($_POST['id_prestataire'] ?? 0);
        $motif = trim($_POST['motif'] ?? '');
        $this->prest->rejeter($id, $motif);
        try {
            require_once __DIR__ . '/../models/Notification.php';
            $p = $this->prest->find($id);
            if ($p && !empty($p['id_user'])) {
                (new Notification())->candidatureRejetee((int)$p['id_user'], $motif);
            }
        } catch (Throwable $e) { /* Notification silencieuse */ }
        $_SESSION['success'] = 'Candidature rejetée.';
        header('Location: index.php?action=admin_prestataires_en_attente'); exit;
    }
}