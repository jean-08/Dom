<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Prestataire.php';

class AuthController {
    private User $user;
    public function __construct() { $this->user = new User(); }

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $mdp   = $_POST['mot_de_passe'] ?? '';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($mdp)) {
                $_SESSION['error'] = 'Champs invalides.'; header('Location: index.php?action=login'); exit;
            }
            $u = $this->user->findByEmail($email);
            if ($u && password_verify($mdp, $u['mot_de_passe'])) {
                $_SESSION['user'] = $u;
                $redirect = $u['role'] === 'admin' ? 'admin_dashboard' : 'dashboard';
                header('Location: index.php?action=' . $redirect); exit;            }
            $_SESSION['error'] = 'Identifiants incorrects.';
            header('Location: index.php?action=login'); exit;
        }
        require __DIR__ . '/../views/auth/login.php';
    }

    public function register(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $d = [
                'nom'         => trim($_POST['nom'] ?? ''),
                'prenom'      => trim($_POST['prenom'] ?? ''),
                'email'       => trim($_POST['email'] ?? ''),
                'role'        => $_POST['role'] ?? 'client',
                'mot_de_passe'=> $_POST['mot_de_passe'] ?? ''
            ];
            if (in_array('', $d, true)) {
                $_SESSION['error'] = 'Tous les champs sont requis.';
                header('Location: index.php?action=register'); exit;
            }
            if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Email invalide.';
                header('Location: index.php?action=register'); exit;
            }
            if ($this->user->findByEmail($d['email'])) {
                $_SESSION['error'] = 'Email déjà utilisé.';
                header('Location: index.php?action=register'); exit;
            }
            $this->user->create($d);
            $_SESSION['success'] = 'Compte créé. Connectez-vous.';
            header('Location: index.php?action=login'); exit;
        }
        require __DIR__ . '/../views/auth/register.php';
    }

    public function logout(): void {
        session_destroy();
        header('Location: index.php?action=login'); exit;
    }
}