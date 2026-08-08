<?php
require_once __DIR__ . '/../models/MessageThread.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/Demande.php';
require_once __DIR__ . '/../models/Prestataire.php';

class MessageController {
    private MessageThread $threadModel;
    private Message $messageModel;
    private Demande $demandeModel;
    private Prestataire $prestModel;

    public function __construct() {
        $this->threadModel  = new MessageThread();
        $this->messageModel = new Message();
        $this->demandeModel = new Demande();
        $this->prestModel   = new Prestataire();
    }

    /**
     * POST action=message_send
     * Envoie d'un message texte dans la discussion privée rattachée à la demande.
     */
    public function send(): void {
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            header('Location: index.php?action=login'); exit;
        }

        $id_demande = (int)($_POST['id_demande'] ?? 0);
        $id_profile = (int)($_POST['id_profile_prestataire'] ?? 0);
        $contenu    = trim($_POST['contenu'] ?? '');

        if (!$id_demande || $contenu === '') {
            $_SESSION['error'] = 'Le message ne peut pas être vide.';
            header('Location: index.php?action=demande_show&id=' . $id_demande); exit;
        }

        $demande = $this->demandeModel->find($id_demande);
        if (!$demande) {
            $_SESSION['error'] = 'Demande introuvable.';
            header('Location: index.php?action=demandes'); exit;
        }

        $idUserSess = (int)$user['id_user'];
        $roleSess   = $user['role'] ?? '';

        // Si id_profile n'est pas fourni, le déduire s'il s'agit d'un prestataire connecté
        if (!$id_profile) {
            $profil = $this->prestModel->findByUser($idUserSess);
            if ($profil) {
                $id_profile = (int)$profil['id_prestataire'];
            }
        }

        if (!$id_profile) {
            $_SESSION['error'] = 'Destinataire du fil non précisé.';
            header('Location: index.php?action=demande_show&id=' . $id_demande); exit;
        }

        // Vérification des droits d'accès
        $estClient = (int)$demande['id_user'] === $idUserSess;
        $profilUser = $this->prestModel->findByUser($idUserSess);
        $estPrest = $profilUser && (int)$profilUser['id_prestataire'] === $id_profile;
        $estAdmin = $roleSess === 'admin';

        if (!$estClient && !$estPrest && !$estAdmin) {
            $_SESSION['error'] = 'Vous n\'avez pas accès à ce fil de discussion.';
            header('Location: index.php?action=demande_show&id=' . $id_demande); exit;
        }

        // Obtenir ou créer le fil de discussion
        $thread = $this->threadModel->findOrCreate($id_demande, $id_profile);
        if ($thread) {
            $this->messageModel->send((int)$thread['id_thread'], $idUserSess, $contenu);
            $_SESSION['success'] = 'Message envoyé.';

            // Notifier le destinataire du fil
            require_once __DIR__ . '/../models/Notification.php';
            $recipientUser = $estClient ? (int)$thread['id_user_prestataire'] : (int)$demande['id_user'];
            if ($recipientUser && $recipientUser !== $idUserSess) {
                $senderName = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
                (new Notification())->creer(
                    $recipientUser,
                    'MESSAGE_RECU',
                    "Nouveau message de $senderName",
                    mb_substr($contenu, 0, 100) . (mb_strlen($contenu) > 100 ? '…' : ''),
                    "index.php?action=demande_show&id=$id_demande"
                );
            }
        } else {
            $_SESSION['error'] = 'Impossible d\'ouvrir le fil de discussion.';
        }

        header('Location: index.php?action=demande_show&id=' . $id_demande); exit;
    }
}
