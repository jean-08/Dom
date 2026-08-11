<?php
require_once __DIR__ . '/../models/Prestataire.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Avis.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/upload.php';
class PrestataireController {
    private Prestataire $prest;
    private Service $service;
    private Avis $avis;
    private User $user;

    public function __construct() {
        $this->prest   = new Prestataire();
        $this->service = new Service();
        $this->avis    = new Avis();
        $this->user    = new User();
    }

    public function index(): void {
        $prestataires = $this->prest->allValides();
        require __DIR__ . '/../views/prestataire/index.php';
    }

    /** US-1/US-2 (CDC v2) : un user connecté candidate pour devenir prestataire. */
    public function candidater(): void {
        $id_user = (int)($_SESSION['user']['id_user'] ?? 0);
        $profilExistant = $this->prest->findByUser($id_user);

        if ($profilExistant && in_array($profilExistant['statut_validation'] ?? '', ['soumise', 'en_revue', 'validee', 'suspendue'])) {
            $_SESSION['error'] = 'Vous avez déjà un profil prestataire ou une candidature en cours d\'examen.';
            header('Location: index.php?action=dashboard'); exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $bio                = trim($_POST['bio'] ?? $_POST['specialite'] ?? '');
            $lettre_motivation  = trim($_POST['lettre_motivation'] ?? '');
            $experience_annees  = !empty($_POST['experience_annees']) ? (int)$_POST['experience_annees'] : null;
            $zone_intervention  = trim($_POST['zone_intervention'] ?? '') ?: null;
            $disponibilites_type = trim($_POST['disponibilites_type'] ?? 'Semaine et Week-end');
            $accepte_urgences   = !empty($_POST['accepte_urgences']);
            $moyen_deplacement  = trim($_POST['moyen_deplacement'] ?? 'Vehicule personnel');
            $siret              = trim($_POST['siret'] ?? '') ?: null;
            $assurances_pro     = trim($_POST['assurances_pro'] ?? '') ?: null;

            if ($bio === '') {
                $_SESSION['error'] = 'Veuillez indiquer votre présentation / spécialité.';
                header('Location: index.php?action=prestataire_candidater'); exit;
            }

            // Gestion de l'upload du CV (PDF)
            $document_cv_url = $profilExistant['document_cv_url'] ?? null;
            if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmp   = $_FILES['cv_file']['tmp_name'];
                $fileName  = $_FILES['cv_file']['name'];
                $fileExt   = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if ($fileExt !== 'pdf') {
                    $_SESSION['error'] = 'Le CV doit être un fichier au format PDF.';
                    header('Location: index.php?action=prestataire_candidater'); exit;
                }
                $document_cv_url = UploadHelper::uploadFile($_FILES['cv_file'], 'cvs', 'cv_user_' . $id_user, ['pdf']);
                if ($document_cv_url === false) {
                    $_SESSION['error'] = 'Erreur lors du téléchargement du CV.';
                    header('Location: index.php?action=prestataire_candidater'); exit;
                }
            }

            $this->prest->candidater([
                'id_user'             => $id_user,
                'bio'                 => $bio,
                'lettre_motivation'   => $lettre_motivation,
                'experience_annees'   => $experience_annees,
                'zone_intervention'   => $zone_intervention,
                'disponibilites_type' => $disponibilites_type,
                'accepte_urgences'    => $accepte_urgences,
                'moyen_deplacement'   => $moyen_deplacement,
                'siret'               => $siret,
                'assurances_pro'      => $assurances_pro,
                'document_cv_url'     => $document_cv_url,
            ]);

            // Enregistrement des compétences sélectionnées (si transmises)
            if (!empty($_POST['categories']) && is_array($_POST['categories'])) {
                $profilRecup = $this->prest->findByUser($id_user);
                if ($profilRecup) {
                    $id_profile = (int)$profilRecup['id_prestataire'];
                    foreach ($_POST['categories'] as $id_cat) {
                        $this->service->addCompetence($id_profile, (int)$id_cat, null);
                    }
                }
            }

            $_SESSION['success'] = 'Votre candidature a bien été transmise et sera examinée par l\'administrateur.';

            try {
                require_once __DIR__ . '/../models/Notification.php';
                require_once __DIR__ . '/../models/User.php';
                $userObj = (new User())->find($id_user);
                if ($userObj) {
                    $admins = array_filter((new User())->all(), fn($u) => $u['role'] === 'admin');
                    foreach ($admins as $adm) {
                        (new Notification())->nouvelleCandidatureAdmin((int)$adm['id_user'], $userObj['prenom'] ?? '', $userObj['nom'] ?? '');
                    }
                }
            } catch (Throwable $e) { /* Notification silencieuse */ }

            header('Location: index.php?action=dashboard'); exit;
        }

        $categories = $this->service->all();
        require __DIR__ . '/../views/prestataire/candidater.php';
    }

    public function show(): void {
        $id = (int)($_GET['id'] ?? 0);
        $prestataire = $this->prest->find($id);
        if (!$prestataire) { header('Location: index.php?action=prestataires'); exit; }
        $services = $this->service->byPrestataire($id);
        $avis     = $this->avis->byPrestataire($id);
                // Determine which view to render: edit for admin/owner, public otherwise
        $monProfilPrestataire = $this->prest->findByUser((int)($_SESSION['user']['id_user'] ?? 0)) ?: null;
        $isAdmin = (($_SESSION['user']['role'] ?? '') === 'admin');
        $isOwner = ($monProfilPrestataire && $monProfilPrestataire['id_prestataire'] == $id);
        if ($isAdmin || $isOwner) {
            // Admins or the profile owner can edit the prestataire
            require __DIR__ . '/../views/prestataire/edit.php';
        } else {
            // Public view for other users
            require __DIR__ . '/../views/prestataire/show.php';
        }

    }

    // public function create(): void {
    //     if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    //         header('Location: index.php?action=dashboard'); exit;
    //     }
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $d = [
    //             'specialite' => trim($_POST['specialite'] ?? ''),
    //             'id_user'    => (int)($_POST['id_user'] ?? 0)
    //         ];
    //         $this->prest->create($d);
    //         $_SESSION['success'] = 'Prestataire créé.';
    //         header('Location: index.php?action=prestataires'); exit;
    //     }
    //     $users = $this->user->all();
    //     require __DIR__ . '/../views/prestataire/edit.php';
    // }

    public function edit(): void {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: index.php?action=dashboard'); exit;
        }
        $id = (int)($_GET['id'] ?? 0);
        $prestataire = $this->prest->find($id);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->prest->update($id, [
                'bio'               => trim($_POST['bio'] ?? $_POST['specialite'] ?? ''),
                'experience_annees' => !empty($_POST['experience_annees']) ? (int)$_POST['experience_annees'] : null,
                'zone_intervention' => trim($_POST['zone_intervention'] ?? '') ?: null,
            ]);
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
        $role = $_SESSION['user']['role'] ?? '';

        if ($role === 'admin') {
            // L'admin peut gérer les compétences de n'importe quel prestataire.
            $id_prest = (int)($_POST['id_prestataire'] ?? 0);
        } else {
            // Sécurité : pour un prestataire, id_prestataire ne doit jamais venir
            // du formulaire (IDOR) — il est toujours dérivé de sa propre session.
            $profil = $this->prest->findByUser((int)($_SESSION['user']['id_user'] ?? 0));
            if (!$profil) {
                $_SESSION['error'] = 'Aucun profil prestataire.';
                header('Location: index.php?action=dashboard'); exit;
            }
            $id_prest = (int)$profil['id_prestataire'];
        }

        // Accepte id_category (V3) ou id_service (V1 fallback)
        $id_serv = (int)($_POST['id_category'] ?? $_POST['id_service'] ?? 0);
        $niveau  = trim($_POST['niveau'] ?? '');
        $this->service->addCompetence($id_prest, $id_serv, $niveau);
        header('Location: index.php?action=prestataire_show&id=' . $id_prest); exit;
    }
}