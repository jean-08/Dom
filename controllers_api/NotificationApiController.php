<?php
require_once __DIR__ . '/ApiAuth.php';
require_once __DIR__ . '/ApiRequest.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/../models/Notification.php';

class NotificationApiController {
    private Notification $notifModel;

    public function __construct() {
        $this->notifModel = new Notification();
    }

    /**
     * GET /api.php?action=notifications_list
     */
    public function list(): void {
        $user = ApiAuth::requireAuth();
        $id_user = (int)$user['id_user'];

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $notifications = $this->notifModel->byUser($id_user, $limit);
        $unreadCount   = $this->notifModel->countUnread($id_user);

        ApiResponse::success([
            'unread_count'  => $unreadCount,
            'notifications' => array_map(function ($n) {
                return [
                    'id_notification' => (int)$n['id_notification'],
                    'type'            => $n['type'],
                    'titre'           => $n['titre'],
                    'corps'           => $n['corps'],
                    'lien_ressource'  => $n['lien_ressource'],
                    'lu'              => (bool)$n['lu'],
                    'created_at'      => $n['created_at'],
                ];
            }, $notifications),
        ]);
    }

    /**
     * POST /api.php?action=notification_mark_read
     */
    public function markRead(): void {
        $user = ApiAuth::requireAuth();
        $data = ApiRequest::getJsonBody();
        $id_user = (int)$user['id_user'];

        $id_notification = (int)($data['id_notification'] ?? $_POST['id_notification'] ?? 0);
        $all = !empty($data['all']) || !empty($_POST['all']);

        if ($all) {
            $this->notifModel->marquerToutesLues($id_user);
            ApiResponse::success([], 'Toutes les notifications ont été marquées comme lues.');
            return;
        }

        if (!$id_notification) {
            ApiResponse::error('Identifiant de notification manquant.', 400);
            return;
        }

        $this->notifModel->marquerLue($id_notification, $id_user);
        ApiResponse::success([], 'Notification marquée comme lue.');
    }
}
