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
        require_once __DIR__ . '/../utils/upload.php';
        $id = (int)($_SESSION['user']['id_user'] ?? 0);
        $u  = $this->user->find($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subaction = $_POST['subaction'] ?? 'info';

            if ($subaction === 'password') {
                $ancien  = $_POST['ancien_mot_de_passe'] ?? '';
                $nouveau = $_POST['nouveau_mot_de_passe'] ?? '';
                $confirm = $_POST['confirm_mot_de_passe'] ?? '';

                if (empty($ancien) || empty($nouveau)) {
                    $_SESSION['error'] = 'Veuillez remplir tous les champs de mot de passe.';
                } elseif ($nouveau !== $confirm) {
                    $_SESSION['error'] = 'Le nouveau mot de passe et la confirmation ne correspondent pas.';
                } elseif (mb_strlen($nouveau) < 8) {
                    $_SESSION['error'] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
                } else {
                    $uWithPass = $this->user->findWithPassword($id);
                    if (!$uWithPass || !password_verify($ancien, $uWithPass['mot_de_passe'])) {
                        $_SESSION['error'] = 'L\'ancien mot de passe est incorrect.';
                    } else {
                        $this->user->updatePassword($id, $nouveau);
                        $_SESSION['success'] = 'Votre mot de passe a été modifié avec succès.';
                    }
                }
                header('Location: index.php?action=profile'); exit;
            }

            // Subaction == 'info' (Mise à jour des informations personnelles & avatar)
            $nom              = trim($_POST['nom'] ?? '');
            $prenom           = trim($_POST['prenom'] ?? '');
            $email            = trim($_POST['email'] ?? '');
            $email_secondaire = trim($_POST['email_secondaire'] ?? '');
            $telephone        = trim($_POST['telephone'] ?? '');
            $adresse_rue      = trim($_POST['adresse_rue'] ?? '');
            $ville            = trim($_POST['ville'] ?? '');
            $bio              = trim($_POST['bio'] ?? '');

            if (empty($nom) || empty($prenom) || empty($email)) {
                $_SESSION['error'] = 'Nom, prénom et email principal sont obligatoires.';
                header('Location: index.php?action=profile'); exit;
            }

            $photo_url = $u['photo_url'] ?? null;
            if (isset($_FILES['photo_file']) && $_FILES['photo_file']['error'] === UPLOAD_ERR_OK) {
                $uploaded = UploadHelper::uploadImage($_FILES['photo_file'], 'avatars', 'avatar_user_' . $id);
                if ($uploaded) {
                    $photo_url = $uploaded;
                } else {
                    $_SESSION['error'] = 'Format d\'image invalide ou fichier trop volumineux (max 5 Mo).';
                    header('Location: index.php?action=profile'); exit;
                }
            }

            $this->user->update($id, [
                'nom'              => $nom,
                'prenom'           => $prenom,
                'email'            => $email,
                'email_secondaire' => $email_secondaire ?: null,
                'telephone'        => $telephone ?: null,
                'adresse_rue'      => $adresse_rue ?: null,
                'ville'            => $ville ?: null,
                'bio'              => $bio ?: null,
                'photo_url'        => $photo_url,
            ]);

            // Mettre à jour la session utilisateur
            $_SESSION['user']['nom']       = $nom;
            $_SESSION['user']['prenom']    = $prenom;
            $_SESSION['user']['email']     = $email;
            $_SESSION['user']['photo_url'] = $photo_url;

            $_SESSION['success'] = 'Profil mis à jour avec succès.';
            header('Location: index.php?action=profile'); exit;
        }

        require __DIR__ . '/../views/user/profile.php';
    }

    public function showPublic(): void {
        require_once __DIR__ . '/../utils/upload.php';
        require_once __DIR__ . '/../models/Demande.php';
        $id = (int)($_GET['id'] ?? 0);
        $u  = $this->user->find($id);
        if (!$u) {
            header('Location: index.php?action=dashboard'); exit;
        }

        $demandeModel = new Demande();
        $demandesPublished = $demandeModel->byUser($id);
        $nbPublished = count($demandesPublished);
        $nbCloturees = 0;
        foreach ($demandesPublished as $d) {
            if (in_array($d['statut'] ?? '', ['terminee', 'cloturee'], true)) {
                $nbCloturees++;
            }
        }

        require __DIR__ . '/../views/user/show_public.php';
    }
}