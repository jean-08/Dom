<?php
require_once __DIR__ . '/../models/Demande.php';
require_once __DIR__ . '/../models/Prestataire.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Proposition.php';
require_once __DIR__ . '/../models/CommentaireDemande.php';
require_once __DIR__ . '/../utils/authorization.php';

/**
 * Contrôleur HTML des demandes — Workflow V3.
 * Publication → Propositions → Choix client → Confirmation prestataire
 * → Diagnostic → Solution → Intervention → Avis
 */
class DemandeController {
    private Demande $demande;
    private Prestataire $prest;
    private Service $service;
    private Proposition $proposition;

    public function __construct() {
        $this->demande    = new Demande();
        $this->prest      = new Prestataire();
        $this->service    = new Service();
        $this->proposition = new Proposition();
    }

    // -----------------------------------------------------------------------
    // Liste des demandes
    // -----------------------------------------------------------------------

    public function index(): void {
        $role = $_SESSION['user']['role'] ?? '';
        $id_user = (int)($_SESSION['user']['id_user'] ?? 0);
        $demandes = [];
        $demandesPostulees = [];
        $isPrestataire = false;

        if ($role === 'admin') {
            $demandes = $this->demande->all();
        } else {
            $demandes = $this->demande->byUser($id_user);
            $profil = $this->prest->findByUser($id_user);
            if ($profil && ($profil['statut_validation'] ?? '') === 'validee') {
                $isPrestataire = true;
                $demandesPostulees = $this->demande->byProfile((int)$profil['id_prestataire']);
            }
        }
        require __DIR__ . '/../views/demande/index.php';
    }

    // -----------------------------------------------------------------------
    // Création d'une demande (client)
    // -----------------------------------------------------------------------

    public function create(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_user = (int)$_SESSION['user']['id_user'];

            // Champs V3 — avec fallback sur les anciens noms de champ
            $titre       = trim($_POST['titre'] ?? $_POST['description'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $id_category = !empty($_POST['id_category']) ? (int)$_POST['id_category']
                         : (!empty($_POST['id_service']) ? (int)$_POST['id_service'] : 0);
            $ville       = trim($_POST['ville'] ?? '');
            if (empty($ville)) { $ville = 'Non précisée'; }
            $code_postal = trim($_POST['code_postal'] ?? '');
            if (empty($code_postal)) { $code_postal = '00000'; }
            $adresse     = trim($_POST['adresse'] ?? '');
            $urgence     = in_array($_POST['urgence'] ?? '', ['normal','sous_48h','urgent'])
                         ? $_POST['urgence'] : 'normal';

            if (empty($titre) || !$id_category) {
                $_SESSION['error'] = 'Titre et catégorie de service sont obligatoires.';
                header('Location: index.php?action=demande_create'); exit;
            }
            if (empty($description)) {
                $description = $titre; // fallback minimal
            }

            $id_demande = $this->demande->create([
                'id_user'     => $id_user,
                'titre'       => $titre,
                'description' => $description,
                'id_category' => $id_category,
                'urgence'     => $urgence,
                'adresse'     => $adresse,
                'ville'       => $ville,
                'code_postal' => $code_postal,
                'telephone_contact' => trim($_POST['telephone_contact'] ?? null) ?: null,
                'budget_min'  => !empty($_POST['budget_min']) ? (float)$_POST['budget_min'] : null,
                'budget_max'  => !empty($_POST['budget_max']) ? (float)$_POST['budget_max'] : null,
                'disponibilites_client' => trim($_POST['disponibilites_client'] ?? null) ?: null,
            ]);

            if ($id_demande) {
                // Traitement des photos d'illustration du problème
                if (!empty($_FILES['photos'])) {
                    require_once __DIR__ . '/../utils/upload.php';
                    require_once __DIR__ . '/../models/DemandeMedia.php';
                    $photos = UploadHelper::uploadMultipleImages($_FILES['photos'], 'demandes', 'demande_' . $id_demande, 5);
                    if (!empty($photos)) {
                        (new DemandeMedia())->addBatch($id_demande, $photos);
                    }
                }
                $_SESSION['success'] = 'Demande publiée. Les prestataires peuvent maintenant vous envoyer des propositions.';
            } else {
                $_SESSION['error'] = 'Erreur lors de la création de la demande.';
            }

            header('Location: index.php?action=demandes'); exit;
        }
        $services = $this->service->all();
        require __DIR__ . '/../views/demande/create.php';
    }

    // -----------------------------------------------------------------------
    // Consultation d'une demande
    // -----------------------------------------------------------------------

    public function show(): void {
        $id = (int)($_GET['id'] ?? 0);
        $demande = $this->demande->find($id);
        if (!$demande) {
            header('Location: index.php?action=demandes');
            exit;
        }

        $user = $_SESSION['user'] ?? [];
        $role = $user['role'] ?? '';
        $ok   = false;

        if ($role === 'admin') {
            $ok = true;
        } elseif ((int)$demande['id_user'] === (int)($user['id_user'] ?? 0)) {
            $ok = true;
        } else {
            // Prestataire : accès autorisé si le prestataire est validé et la demande est ouverte/en discussion (pour lui permettre de postuler)
            // OU s'il est le prestataire retenu, OU s'il a déjà fait une proposition.
            $profil = $this->prest->findByUser((int)($user['id_user'] ?? 0));
            if ($profil && ($profil['statut_validation'] ?? '') === 'validee') {
                $id_profile = (int)$profil['id_prestataire'];

                // Tout prestataire validé peut voir les demandes ouvertes ou en discussion
                if (in_array($demande['statut'], ['ouverte', 'en_discussion'])) {
                    $ok = true;
                }
                // Accès si prestataire retenu
                if (!$ok && !empty($demande['id_profile_retenu']) && (int)$demande['id_profile_retenu'] === $id_profile) {
                    $ok = true;
                }
                // Accès si proposition envoyée (pour les statuts avancés)
                if (!$ok && $this->proposition->findByDemandeAndProfile($id, $id_profile)) {
                    $ok = true;
                }
            }
        }

        if (!$ok) {
            $_SESSION['error'] = 'Accès refusé à cette demande.';
            header('Location: index.php?action=demandes');
            exit;
        }

        require_once __DIR__ . '/../models/Diagnostic.php';
        require_once __DIR__ . '/../models/Solution.php';
        require_once __DIR__ . '/../models/Intervention.php';
        require_once __DIR__ . '/../models/Avis.php';
        require_once __DIR__ . '/../models/DemandeMedia.php';
        require_once __DIR__ . '/../models/MessageThread.php';
        require_once __DIR__ . '/../models/Message.php';

        $propositionsRaw      = $this->proposition->byDemande($id);
        $medias               = (new DemandeMedia())->byDemande($id);
        $diagnostic           = (new Diagnostic())->byDemande($id) ?: null;
        $solutions            = $diagnostic ? (new Solution())->byDiagnostic((int)$diagnostic['id_diagnostic']) : [];
        $mesProfilPrestataire = $this->prest->findByUser((int)($user['id_user'] ?? 0)) ?: null;
        // Commentaires publics visibles par tous
        $commentaires         = (new CommentaireDemande())->byDemande($id);
        // Intervention et avis liés à cette demande
        $interventionModel    = new Intervention();
        $interventionsDemande = $interventionModel->byProfile($mesProfilPrestataire ? (int)$mesProfilPrestataire['id_prestataire'] : 0);
        // Chercher l'intervention spécifique de cette demande
        $interventionDemande  = null;
        foreach ($interventionsDemande as $iv) {
            if ((int)$iv['id_demande'] === $id) { $interventionDemande = $iv; break; }
        }
        // Si client, chercher l'intervention de sa demande via les interventions du prestataire retenu
        if (!$interventionDemande && !empty($demande['id_profile_retenu'])) {
            $ivsRetenu = $interventionModel->byProfile((int)$demande['id_profile_retenu']);
            foreach ($ivsRetenu as $iv) {
                if ((int)$iv['id_demande'] === $id) { $interventionDemande = $iv; break; }
            }
        }
        $avisIntervention = ($interventionDemande && !empty($interventionDemande['id_intervention']))
            ? (new Avis())->byIntervention((int)$interventionDemande['id_intervention'])
            : null;

        $estClient = (int)$demande['id_user'] === (int)($user['id_user'] ?? 0);

        // Filtrage des propositions selon le rôle
        $propositions = [];
        foreach ($propositionsRaw as $prop) {
            $idPrestataireProp = (int)$prop['id_profile'];
            $idPrestataireSess = $mesProfilPrestataire ? (int)$mesProfilPrestataire['id_prestataire'] : 0;
            if ($estClient || $role === 'admin' || $idPrestataireProp === $idPrestataireSess) {
                $propositions[] = $prop;
            }
        }

        // Charger la messagerie privée rattachée à la demande
        $threadModel      = new MessageThread();
        $messageModel     = new Message();
        $threads          = [];
        $messagesByThread = [];

        if ($estClient || $role === 'admin') {
            $threads = $threadModel->byDemande($id);
            foreach ($threads as $t) {
                $idThread = (int)$t['id_thread'];
                $messagesByThread[$idThread] = $messageModel->byThread($idThread);
                if (!empty($user['id_user'])) {
                    $messageModel->markAsRead($idThread, (int)$user['id_user']);
                }
            }
        } elseif ($mesProfilPrestataire) {
            $t = $threadModel->findOrCreate($id, (int)$mesProfilPrestataire['id_prestataire']);
            if ($t) {
                $threads[] = $t;
                $idThread = (int)$t['id_thread'];
                $messagesByThread[$idThread] = $messageModel->byThread($idThread);
                if (!empty($user['id_user'])) {
                    $messageModel->markAsRead($idThread, (int)$user['id_user']);
                }
            }
        }

        require __DIR__ . '/../views/demande/show.php';
    }

    // -----------------------------------------------------------------------
    // Suppression (admin)
    // -----------------------------------------------------------------------

    public function delete(): void {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: index.php?action=dashboard'); exit;
        }
        $id = (int)($_GET['id'] ?? 0);
        $this->demande->delete($id);
        header('Location: index.php?action=demandes'); exit;
    }

    // -----------------------------------------------------------------------
    // Vue prestataire : demandes éligibles (V3 — remplace "disponibles")
    // -----------------------------------------------------------------------

    public function disponibles(): void {
        $prest = $this->prest->findByUser((int)($_SESSION['user']['id_user'] ?? 0));
        if (!$prest || ($prest['statut_validation'] ?? '') !== 'validee') {
            $_SESSION['error'] = 'Vous devez être un prestataire validé pour accéder à cette page.';
            header('Location: index.php?action=dashboard'); exit;
        }
        // Afficher TOUTES les demandes ouvertes ou en discussion, sans filtre compétences
        $demandes = $this->demande->allOuvertes();
        
        $postuleesIds = [];
        $demandesPostulees = $this->demande->byProfile((int)$prest['id_prestataire']);
        foreach ($demandesPostulees as $dp) {
            $postuleesIds[] = (int)$dp['id_demande'];
        }
        
        require __DIR__ . '/../views/demande/disponibles.php';
    }

    // -----------------------------------------------------------------------
    // NOUVELLE ACTION : prestataire envoie une proposition (V3)
    // -----------------------------------------------------------------------

    public function proposer(): void {
        $prest = $this->prest->findByUser((int)($_SESSION['user']['id_user'] ?? 0));
        if (!$prest || ($prest['statut_validation'] ?? '') !== 'validee') {
            $_SESSION['error'] = 'Vous devez être un prestataire validé pour envoyer une proposition.';
            header('Location: index.php?action=dashboard'); exit;
        }

        $id_demande = (int)($_POST['id_demande'] ?? 0);
        $message    = trim($_POST['message'] ?? '');
        $prix       = !empty($_POST['prix_indicatif']) ? (float)$_POST['prix_indicatif'] : null;
        $delai      = trim($_POST['delai_texte'] ?? '') ?: null;

        if (!$id_demande || $message === '') {
            $_SESSION['error'] = 'Le message de proposition est obligatoire.';
            header('Location: index.php?action=demande_show&id=' . $id_demande); exit;
        }

        $resultat = $this->demande->envoyerProposition(
            $id_demande, (int)$prest['id_prestataire'], $message, $prix, $delai
        );

        if ($resultat === 'ok') {
            $_SESSION['success'] = 'Votre proposition a été envoyée.';
            require_once __DIR__ . '/../services/NotificationService.php';
            $notificationService = new NotificationService();
            $demandeInfo = $this->demande->find($id_demande);
            if ($demandeInfo && !empty($demandeInfo['id_user'])) {
                $nomPrest = trim(($prest['prenom'] ?? '') . ' ' . ($prest['nom'] ?? ''));
                $notificationService->nouvelleProposition((int)$demandeInfo['id_user'], $id_demande, $demandeInfo['titre'] ?? 'demande', $nomPrest);
            }
        } else {
            match ($resultat) {
                'deja_proposee'          => $_SESSION['error']   = 'Vous avez déjà une proposition active sur cette demande.',
                'mauvais_statut'         => $_SESSION['error']   = 'Cette demande n\'accepte plus de nouvelles propositions.',
                'introuvable'            => $_SESSION['error']   = 'Demande introuvable.',
                'prestataire_non_valide' => $_SESSION['error']   = 'Votre profil prestataire n\'est pas validé.',
                default                  => $_SESSION['error']   = 'Erreur inattendue.',
            };
        }
        header('Location: index.php?action=demande_show&id=' . $id_demande); exit;
    }

    // -----------------------------------------------------------------------
    // NOUVELLE ACTION : commentaire public sur une demande
    // -----------------------------------------------------------------------

    public function commenter(): void {
        $id_user    = (int)($_SESSION['user']['id_user'] ?? 0);
        $id_demande = (int)($_POST['id_demande'] ?? 0);
        $contenu    = trim($_POST['contenu'] ?? '');

        if (!$id_demande || $contenu === '') {
            $_SESSION['error'] = 'Le commentaire ne peut pas être vide.';
            header('Location: index.php?action=demande_show&id=' . $id_demande); exit;
        }

        // Vérifier que la demande existe et est accessible
        $demande = $this->demande->find($id_demande);
        if (!$demande) {
            header('Location: index.php?action=demandes'); exit;
        }

        (new CommentaireDemande())->create($id_demande, $id_user, $contenu);

        // Envoyer une notification in-app à tous les participants uniques de la demande
        require_once __DIR__ . '/../services/NotificationService.php';
        $notificationService = new NotificationService();
        $notificationService->nouveauCommentaire($id_demande, $id_user, $contenu);

        $_SESSION['success'] = 'Commentaire ajouté.';
        header('Location: index.php?action=demande_show&id=' . $id_demande . '#commentaires'); exit;
    }

    // -----------------------------------------------------------------------
    // NOUVELLE ACTION : client sélectionne une proposition (V3)
    // -----------------------------------------------------------------------

    public function selectionner(): void {
        $id_user       = (int)($_SESSION['user']['id_user'] ?? 0);
        $id_demande    = (int)($_POST['id_demande'] ?? 0);
        $id_proposition = (int)($_POST['id_proposition'] ?? 0);
        // Verify ownership or admin rights
        $demande = $this->demande->find($id_demande);
        requireOwnerOrAdmin($demande['id_user'] ?? null, 'dashboard');

        if (!$id_demande || !$id_proposition) {
            $_SESSION['error'] = 'Paramètres manquants.';
            header('Location: index.php?action=demandes'); exit;
        }

        $resultat = $this->demande->selectionnerPrestataire($id_demande, $id_user, $id_proposition);

        if ($resultat === 'ok') {
            $_SESSION['success'] = 'Prestataire sélectionné. Il doit maintenant confirmer son engagement.';
            require_once __DIR__ . '/../services/NotificationService.php';
            $notificationService = new NotificationService();
            $propInfo = $this->proposition->find($id_proposition);
            $demandeInfo = $this->demande->find($id_demande);
            if ($propInfo && $demandeInfo && !empty($propInfo['id_user'])) {
                $notificationService->propositionRetenue((int)$propInfo['id_user'], $id_demande, $demandeInfo['titre'] ?? '');
            }
        } else {
            match ($resultat) {
                'non_autorise'              => $_SESSION['error']   = 'Vous n\'êtes pas autorisé à effectuer cette action.',
                'mauvais_statut'            => $_SESSION['error']   = 'La demande n\'est pas dans un état permettant la sélection.',
                'introuvable'               => $_SESSION['error']   = 'Demande introuvable.',
                'proposition_introuvable'   => $_SESSION['error']   = 'Proposition introuvable.',
                'proposition_non_disponible'=> $_SESSION['error']   = 'Cette proposition n\'est plus disponible.',
                default                     => $_SESSION['error']   = 'Erreur inattendue.',
            };
        }
        header('Location: index.php?action=demande_show&id=' . $id_demande); exit;
    }

    // -----------------------------------------------------------------------
    // NOUVELLE ACTION : prestataire confirme son engagement (V3)
    // -----------------------------------------------------------------------

    public function confirmerEngagement(): void {
        $prest = $this->prest->findByUser((int)($_SESSION['user']['id_user'] ?? 0));
        if (!$prest) {
            header('Location: index.php?action=dashboard'); exit;
        }

        $id_demande = (int)($_POST['id_demande'] ?? 0);
        $resultat   = $this->demande->confirmerEngagement($id_demande, (int)$prest['id_prestataire']);

        if ($resultat === 'ok') {
            $_SESSION['success'] = 'Engagement confirmé. La mission peut commencer.';
            require_once __DIR__ . '/../services/NotificationService.php';
            $notificationService = new NotificationService();
            $demandeInfo = $this->demande->find($id_demande);
            if ($demandeInfo && !empty($demandeInfo['id_user'])) {
                $nomPrest = trim(($prest['prenom'] ?? '') . ' ' . ($prest['nom'] ?? ''));
                $notificationService->engagementConfirme((int)$demandeInfo['id_user'], $id_demande, $demandeInfo['titre'] ?? '', $nomPrest);
            }
        } else {
            match ($resultat) {
                'non_autorise'   => $_SESSION['error']   = 'Vous n\'êtes pas le prestataire retenu pour cette demande.',
                'mauvais_statut' => $_SESSION['error']   = 'Cette demande n\'est pas en attente de confirmation.',
                'introuvable'    => $_SESSION['error']   = 'Demande introuvable.',
                default          => $_SESSION['error']   = 'Erreur inattendue.',
            };
        }
        header('Location: index.php?action=demande_show&id=' . $id_demande); exit;
    }

    // -----------------------------------------------------------------------
    // NOUVELLE ACTION : prestataire se désiste après sélection (V3)
    // -----------------------------------------------------------------------

    public function desister(): void {
        $prest = $this->prest->findByUser((int)($_SESSION['user']['id_user'] ?? 0));
        if (!$prest) {
            header('Location: index.php?action=dashboard'); exit;
        }

        $id_demande = (int)($_POST['id_demande'] ?? 0);
        $resultat   = $this->demande->desister($id_demande, (int)$prest['id_prestataire']);

        match ($resultat) {
            'ok'             => $_SESSION['success'] = 'Désistement enregistré.',
            'non_autorise'   => $_SESSION['error']   = 'Vous n\'êtes pas autorisé à vous désister de cette demande.',
            'mauvais_statut' => $_SESSION['error']   = 'Le désistement n\'est pas possible à ce stade.',
            'introuvable'    => $_SESSION['error']   = 'Demande introuvable.',
            default          => $_SESSION['error']   = 'Erreur inattendue.',
        };
        header('Location: index.php?action=demandes'); exit;
    }

    // -----------------------------------------------------------------------
    // NOUVELLE ACTION : client annule sa demande (V3)
    // -----------------------------------------------------------------------

    public function annuler(): void {
        $id_user    = (int)($_SESSION['user']['id_user'] ?? 0);
        $id_demande = (int)($_POST['id_demande'] ?? 0);
        // Verify ownership or admin rights
        $demande = $this->demande->find($id_demande);
        requireOwnerOrAdmin($demande['id_user'] ?? null, 'dashboard');
        if (!$id_demande) {
            header('Location: index.php?action=demandes'); exit;
        }

        $resultat = $this->demande->annulerParClient($id_demande, $id_user);

        match ($resultat) {
            'ok'             => $_SESSION['success'] = 'Demande annulée.',
            'non_autorise'   => $_SESSION['error']   = 'Vous ne pouvez pas annuler cette demande.',
            'mauvais_statut' => $_SESSION['error']   = 'L\'annulation n\'est pas possible à ce stade (intervention en cours ou demande déjà clôturée).',
            'introuvable'    => $_SESSION['error']   = 'Demande introuvable.',
            default          => $_SESSION['error']   = 'Erreur inattendue.',
        };
        header('Location: index.php?action=demandes'); exit;
    }

    // -----------------------------------------------------------------------
    // ACTION ADMIN : changement manuel du statut d'une demande (V3)
    // -----------------------------------------------------------------------

    public function updateStatut(): void {
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            header('Location: index.php?action=dashboard'); exit;
        }

        $id_demande    = (int)($_POST['id_demande'] ?? 0);
        $nouveauStatut = $_POST['statut'] ?? '';

        $statutsValides = [
            'ouverte','en_discussion','prestataire_choisi','engagee',
            'diagnostic_propose','solution_proposee','intervention_planifiee',
            'intervention_en_cours','terminee','cloturee',
            'annulee_par_client','annulee_par_prestataire','expiree','suspendue_moderation',
        ];

        if (!$id_demande || !in_array($nouveauStatut, $statutsValides, true)) {
            $_SESSION['error'] = 'Paramètres invalides.';
            header('Location: index.php?action=demande_show&id=' . $id_demande); exit;
        }

        $db = \Database::getInstance();
        $s  = $db->prepare("UPDATE demande SET statut = ? WHERE id_demande = ?");
        $s->execute([$nouveauStatut, $id_demande]);

        $_SESSION['success'] = 'Statut mis à jour.';
        header('Location: index.php?action=demande_show&id=' . $id_demande); exit;
    }
}