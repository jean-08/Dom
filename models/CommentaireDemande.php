<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Commentaires publics laissés sur une demande.
 * Visibles par tous les utilisateurs connectés (client, prestataires, admin).
 */
class CommentaireDemande {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    /** Tous les commentaires d'une demande, du plus ancien au plus récent. */
    public function byDemande(int $id_demande): array {
        $s = $this->db->prepare("
            SELECT c.*,
                   u.nom, u.prenom, u.photo_url, u.role,
                   pp.id_profile AS id_prestataire
            FROM commentaire_demande c
            JOIN \"user\" u ON u.id_user = c.id_user
            LEFT JOIN prestataire_profile pp ON pp.id_user = u.id_user
            WHERE c.id_demande = ?
            ORDER BY c.created_at ASC
        ");
        $s->execute([$id_demande]);
        return $s->fetchAll();
    }

    /** Ajouter un commentaire. */
    public function create(int $id_demande, int $id_user, string $contenu): bool {
        $s = $this->db->prepare("
            INSERT INTO commentaire_demande (id_demande, id_user, contenu)
            VALUES (?, ?, ?)
        ");
        return $s->execute([$id_demande, $id_user, $contenu]);
    }

    /** Supprimer un commentaire (propriétaire ou admin). */
    public function delete(int $id_commentaire, int $id_user, string $role): bool {
        if ($role === 'admin') {
            $s = $this->db->prepare("DELETE FROM commentaire_demande WHERE id_commentaire = ?");
            return $s->execute([$id_commentaire]);
        }
        $s = $this->db->prepare("DELETE FROM commentaire_demande WHERE id_commentaire = ? AND id_user = ?");
        return $s->execute([$id_commentaire, $id_user]);
    }
}
