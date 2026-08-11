<?php
require_once __DIR__ . '/../config/database.php';

class Message {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    /**
     * Envoie un message texte dans un fil de discussion.
     */
    public function send(int $id_thread, int $id_sender, string $contenu): int|false {
        $s = $this->db->prepare("
            INSERT INTO message (id_thread, id_sender, contenu, created_at)
            VALUES (?, ?, ?, NOW())
            RETURNING id_message
        ");
        if ($s->execute([$id_thread, $id_sender, $contenu])) {
            return (int)$s->fetchColumn();
        }
        return false;
    }

    /**
     * Récupère tous les messages d'un fil de discussion avec infos expéditeur.
     */
    public function byThread(int $id_thread): array {
        $s = $this->db->prepare("
            SELECT m.*, u.prenom AS sender_prenom, u.nom AS sender_nom, u.photo_url AS sender_photo_url
            FROM message m
            JOIN \"user\" u ON u.id_user = m.id_sender
            WHERE m.id_thread = ?
            ORDER BY m.created_at ASC
        ");
        $s->execute([$id_thread]);
        return $s->fetchAll();
    }

    /**
     * Marquer comme lus tous les messages d'un fil non envoyés par l'utilisateur courant.
     */
    public function markAsRead(int $id_thread, int $id_user_current): bool {
        $s = $this->db->prepare("
            UPDATE message
            SET read_at = NOW()
            WHERE id_thread = ? AND id_sender != ? AND read_at IS NULL
        ");
        return $s->execute([$id_thread, $id_user_current]);
    }
}
