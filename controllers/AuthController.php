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
                if (!empty($u['suspendu'])) {
                    header('Location: index.php?action=compte_suspendu');
                    exit;
                }
                $redirect = $u['role'] === 'admin' ? 'admin_dashboard' : 'dashboard';
                header('Location: index.php?action=' . $redirect); exit;
            }
            $_SESSION['error'] = 'Identifiants incorrects.';
            header('Location: index.php?action=login'); exit;
        }
        require __DIR__ . '/../views/auth/login.php';
    }

    public function suspended(): void {
        if (empty($_SESSION['user'])) {
            header('Location: index.php?action=login');
            exit;
        }
        $u = $this->user->find((int)$_SESSION['user']['id_user']);
        if ($u) {
            $_SESSION['user'] = array_merge($_SESSION['user'], $u);
        }
        if (empty($_SESSION['user']['suspendu'])) {
            header('Location: index.php?action=dashboard');
            exit;
        }
        require __DIR__ . '/../views/auth/suspended.php';
    }

    public function register(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $d = [
                'nom'         => trim($_POST['nom'] ?? ''),
                'prenom'      => trim($_POST['prenom'] ?? ''),
                'email'       => trim($_POST['email'] ?? ''),
                // Le rôle n'est jamais pris depuis le formulaire : tout compte créé
                // publiquement est "client" par défaut (cf. CDC v2). Devenir prestataire
                // passe par une candidature dédiée, soumise à validation admin.
                'role'        => 'client',
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
        header('Location: index.php?action=home'); exit;
    }
}