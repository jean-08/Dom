<?php
require_once __DIR__ . '/../models/Notification.php';

class NotificationController {
    private Notification $notifModel;

    public function __construct() {
        $this->notifModel = new Notification();
    }

    /**
     * GET action=notifications
     * Liste complète des notifications de l'utilisateur.
     */
    public function index(): void {
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            header('Location: index.php?action=login'); exit;
        }

        $id_user       = (int)$user['id_user'];
        $notifications = $this->notifModel->byUser($id_user, 100);
        $unreadCount   = $this->notifModel->countUnread($id_user);

        require __DIR__ . '/../views/notification/index.php';
    }

    /**
     * POST action=notification_mark_read
     */
    public function markRead(): void {
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            header('Location: index.php?action=login'); exit;
        }

        $id_notif = (int)($_POST['id_notification'] ?? 0);
        if ($id_notif) {
            $this->notifModel->marquerLue($id_notif, (int)$user['id_user']);
        }

        $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=notifications';
        header('Location: ' . $redirect); exit;
    }

    /**
     * POST action=notification_mark_all_read
     */
    public function markAllRead(): void {
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            header('Location: index.php?action=login'); exit;
        }

        $this->notifModel->marquerToutesLues((int)$user['id_user']);
        $_SESSION['success'] = 'Toutes vos notifications ont été marquées comme lues.';

        $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php?action=notifications';
        header('Location: ' . $redirect); exit;
    }
}
