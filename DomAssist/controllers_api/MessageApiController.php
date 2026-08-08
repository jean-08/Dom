<?php
require_once __DIR__ . '/../models/MessageThread.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/Demande.php';
require_once __DIR__ . '/../models/Prestataire.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/ApiAuth.php';
require_once __DIR__ . '/ApiRequest.php';

class MessageApiController
{
    private MessageThread $threadModel;
    private Message $messageModel;
    private Demande $demandeModel;
    private Prestataire $prestModel;

    public function __construct()
    {
        $this->threadModel  = new MessageThread();
        $this->messageModel = new Message();
        $this->demandeModel = new Demande();
        $this->prestModel   = new Prestataire();
    }

    /**
     * GET ?action=messages_thread&id_demande=N&id_profile_prestataire=P
     * Récupère les messages d'un fil de discussion sur une demande.
     */
    public function getThread(): void
    {
        $user = ApiAuth::requireAuth();

        $id_demande = (int) ($_GET['id_demande'] ?? 0);
        $id_profile = (int) ($_GET['id_profile_prestataire'] ?? 0);

        if ($id_demande <= 0) {
            ApiResponse::error('id_demande est requis.', 422);
        }

        $demande = $this->demandeModel->find($id_demande);
        if (!$demande) {
            ApiResponse::error('Demande introuvable.', 404);
        }

        $idUserSess = (int) $user['id_user'];
        if (!$id_profile) {
            $profil = $this->prestModel->findByUser($idUserSess);
            if ($profil) {
                $id_profile = (int) $profil['id_prestataire'];
            }
        }

        if (!$id_profile) {
            ApiResponse::error('id_profile_prestataire est requis.', 422);
        }

        // Vérification des accès IDOR
        $estClient = (int) $demande['id_user'] === $idUserSess;
        $profilUser = $this->prestModel->findByUser($idUserSess);
        $estPrest = $profilUser && (int) $profilUser['id_prestataire'] === $id_profile;
        $estAdmin = ($user['role'] ?? '') === 'admin';

        if (!$estClient && !$estPrest && !$estAdmin) {
            ApiResponse::error('Accès refusé à ce fil de discussion.', 403);
        }

        $thread = $this->threadModel->findOrCreate($id_demande, $id_profile);
        if (!$thread) {
            ApiResponse::error('Impossible de charger le fil.', 500);
        }

        $messages = $this->messageModel->byThread((int) $thread['id_thread']);
        $this->messageModel->markAsRead((int) $thread['id_thread'], $idUserSess);

        ApiResponse::success([
            'thread'   => $thread,
            'messages' => $messages,
        ]);
    }

    /**
     * POST ?action=message_send
     * Body : { id_demande, id_profile_prestataire?, contenu }
     */
    public function send(): void
    {
        $user = ApiAuth::requireAuth();
        $body = ApiRequest::body();

        $id_demande = (int) ($body['id_demande'] ?? 0);
        $id_profile = (int) ($body['id_profile_prestataire'] ?? 0);
        $contenu    = trim($body['contenu'] ?? '');

        if (!$id_demande || $contenu === '') {
            ApiResponse::error('id_demande et contenu sont requis.', 422);
        }

        $demande = $this->demandeModel->find($id_demande);
        if (!$demande) {
            ApiResponse::error('Demande introuvable.', 404);
        }

        $idUserSess = (int) $user['id_user'];
        if (!$id_profile) {
            $profil = $this->prestModel->findByUser($idUserSess);
            if ($profil) {
                $id_profile = (int) $profil['id_prestataire'];
            }
        }

        if (!$id_profile) {
            ApiResponse::error('id_profile_prestataire est requis.', 422);
        }

        $estClient = (int) $demande['id_user'] === $idUserSess;
        $profilUser = $this->prestModel->findByUser($idUserSess);
        $estPrest = $profilUser && (int) $profilUser['id_prestataire'] === $id_profile;
        $estAdmin = ($user['role'] ?? '') === 'admin';

        if (!$estClient && !$estPrest && !$estAdmin) {
            ApiResponse::error('Accès refusé à ce fil de discussion.', 403);
        }

        $thread = $this->threadModel->findOrCreate($id_demande, $id_profile);
        if (!$thread) {
            ApiResponse::error('Erreur de fil.', 500);
        }

        $id_msg = $this->messageModel->send((int) $thread['id_thread'], $idUserSess, $contenu);
        if ($id_msg) {
            ApiResponse::success(['message' => 'Message envoyé.', 'id_message' => $id_msg], 201);
        } else {
            ApiResponse::error('Échec de l\'envoi du message.', 500);
        }
    }
}
