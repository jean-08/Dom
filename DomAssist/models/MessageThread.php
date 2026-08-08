<?php
require_once __DIR__ . '/../config/database.php';

class MessageThread {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    /**
     * Obtenir ou créer un fil de discussion unique entre une demande et un profil prestataire.
     */
    public function findOrCreate(int $id_demande, int $id_profile_prestataire): array|false {
        $s = $this->db->prepare("
            SELECT t.*, d.id_user AS id_client, d.titre AS demande_titre
            FROM message_thread t
            JOIN demande d ON d.id_demande = t.id_demande
            WHERE t.id_demande = ? AND t.id_profile_prestataire = ?
        ");
        $s->execute([$id_demande, $id_profile_prestataire]);
        $row = $s->fetch();
        if ($row) {
            return $row;
        }

        $ins = $this->db->prepare("
            INSERT INTO message_thread (id_demande, id_profile_prestataire)
            VALUES (?, ?)
            RETURNING id_thread
        ");
        if ($ins->execute([$id_demande, $id_profile_prestataire])) {
            $newId = $ins->fetchColumn();
            return $this->find((int)$newId);
        }
        return false;
    }

    public function find(int $id_thread): array|false {
        $s = $this->db->prepare("
            SELECT t.*, d.id_user AS id_client, d.titre AS demande_titre, d.statut AS demande_statut,
                   u_client.prenom AS client_prenom, u_client.nom AS client_nom,
                   u_prest.prenom AS prest_prenom, u_prest.nom AS prest_nom, pp.id_user AS id_user_prestataire
            FROM message_thread t
            JOIN demande d ON d.id_demande = t.id_demande
            JOIN \"user\" u_client ON u_client.id_user = d.id_user
            JOIN prestataire_profile pp ON pp.id_profile = t.id_profile_prestataire
            JOIN \"user\" u_prest ON u_prest.id_user = pp.id_user
            WHERE t.id_thread = ?
        ");
        $s->execute([$id_thread]);
        return $s->fetch();
    }

    /**
     * Tous les fils de discussion d'une demande.
     */
    public function byDemande(int $id_demande): array {
        $s = $this->db->prepare("
            SELECT t.*,
                   u_prest.prenom AS prest_prenom, u_prest.nom AS prest_nom, pp.id_user AS id_user_prestataire,
                   (SELECT COUNT(*) FROM message m WHERE m.id_thread = t.id_thread) AS nb_messages,
                   (SELECT MAX(m.created_at) FROM message m WHERE m.id_thread = t.id_thread) AS dernier_message_at
            FROM message_thread t
            JOIN prestataire_profile pp ON pp.id_profile = t.id_profile_prestataire
            JOIN \"user\" u_prest ON u_prest.id_user = pp.id_user
            WHERE t.id_demande = ?
            ORDER BY t.created_at DESC
        ");
        $s->execute([$id_demande]);
        return $s->fetchAll();
    }
}
