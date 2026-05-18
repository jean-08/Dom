<?php
require_once __DIR__ . '/../models/User.php';

class UserController {
    private User $user;
    public function __construct() { $this->user = new User(); }

    private function requireAdmin(): void {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: index.php?action=dashboard'); exit;
        }
    }

    public function index(): void {
        $this->requireAdmin();
        $users = $this->user->all();
        require __DIR__ . '/../views/user/index.php';
    }

    public function edit(): void {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $u  = $this->user->find($id);
        if (!$u) { header('Location: index.php?action=users'); exit; }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $d = [
                'nom'    => trim($_POST['nom'] ?? ''),
                'prenom' => trim($_POST['prenom'] ?? ''),
                'email'  => trim($_POST['email'] ?? ''),
                'role'   => $_POST['role'] ?? 'client'
            ];
            $this->user->update($id, $d);
            $_SESSION['success'] = 'Utilisateur mis à jour.';
            header('Location: index.php?action=users'); exit;
        }
        require __DIR__ . '/../views/user/edit.php';
    }

    public function delete(): void {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $this->user->delete($id);
        header('Location: index.php?action=users'); exit;
    }

    public function profile(): void {
        $id = (int)($_SESSION['user']['id_user'] ?? 0);
        $u  = $this->user->find($id);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $d = [
                'nom'    => trim($_POST['nom'] ?? ''),
                'prenom' => trim($_POST['prenom'] ?? ''),
                'email'  => trim($_POST['email'] ?? ''),
                'role'   => $_SESSION['user']['role']
            ];
            $this->user->update($id, $d);
            $_SESSION['user'] = array_merge($_SESSION['user'], $d);
            $_SESSION['success'] = 'Profil mis à jour.';
            header('Location: index.php?action=profile'); exit;
        }
        require __DIR__ . '/../views/user/edit.php';
    }
}