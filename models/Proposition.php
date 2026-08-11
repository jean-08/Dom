<?php
require_once __DIR__ . '/../utils/logger.php';

require_once __DIR__ . '/../config/database.php';

/**
 * Entité centrale du workflow marketplace.
 * Un prestataire envoie une proposition ; le client choisit parmi N propositions.
 */
class Proposition {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function find(int $id): array|false {
        $s = $this->db->prepare("
            SELECT p.*, pp.bio, pp.experience_annees, pp.zone_intervention,
                   u.nom AS prestataire_nom, u.prenom AS prestataire_prenom,
                   u.photo_url AS prestataire_photo, u.ville AS prestataire_ville,
                   COALESCE(rep.note_moyenne, 0)::float AS note_moyenne,
                   COALESCE(rep.nombre_avis, 0) AS nombre_avis
            FROM proposition p
            JOIN prestataire_profile pp ON pp.id_profile = p.id_profile
            JOIN \"user\" u ON u.id_user = pp.id_user
            LEFT JOIN (
                SELECT id_profile, AVG(note)::float AS note_moyenne, COUNT(*) AS nombre_avis
                FROM avis GROUP BY id_profile
            ) rep ON rep.id_profile = p.id_profile
            WHERE p.id_proposition = ?
        ");
        $s->execute([$id]);
        return $s->fetch();
    }

    /** Toutes les propositions d'une demande, avec profil enrichi (pour le client). */
    public function byDemande(int $id_demande): array {
        $s = $this->db->prepare("
            SELECT p.*, pp.bio, pp.experience_annees, pp.zone_intervention,
                   u.nom AS prestataire_nom, u.prenom AS prestataire_prenom,
                   u.photo_url AS prestataire_photo, u.ville AS prestataire_ville,
                   COALESCE(rep.note_moyenne, 0)::float AS note_moyenne,
                   COALESCE(rep.nombre_avis, 0) AS nombre_avis
            FROM proposition p
            JOIN prestataire_profile pp ON pp.id_profile = p.id_profile
            JOIN \"user\" u ON u.id_user = pp.id_user
            LEFT JOIN (
                SELECT id_profile, AVG(note)::float AS note_moyenne, COUNT(*) AS nombre_avis
                FROM avis GROUP BY id_profile
            ) rep ON rep.id_profile = p.id_profile
            WHERE p.id_demande = ?
            ORDER BY
                CASE p.statut WHEN 'retenue' THEN 0 WHEN 'envoyee' THEN 1 ELSE 2 END,
                p.created_at ASC
        ");
        $s->execute([$id_demande]);
        return $s->fetchAll();
    }

    /** Propositions actives d'un prestataire (en attente de réponse client). */
    public function activesByProfile(int $id_profile): array {
        $s = $this->db->prepare("
            SELECT p.*, d.titre AS demande_titre, d.statut AS demande_statut,
                   d.urgence, d.ville, sc.libelle AS category_libelle,
                   u.nom AS client_nom, u.prenom AS client_prenom
            FROM proposition p
            JOIN demande d ON d.id_demande = p.id_demande
            JOIN service_category sc ON sc.id_category = d.id_category
            JOIN \"user\" u ON u.id_user = d.id_user
            WHERE p.id_profile = ? AND p.statut = 'envoyee'
            ORDER BY p.created_at DESC
        ");
        $s->execute([$id_profile]);
        return $s->fetchAll();
    }

    /** Proposition d'un prestataire pour une demande donnée (unicité). */
    public function findByDemandeAndProfile(int $id_demande, int $id_profile): array|false {
        $s = $this->db->prepare("SELECT * FROM proposition WHERE id_demande = ? AND id_profile = ?");
        $s->execute([$id_demande, $id_profile]);
        return $s->fetch();
    }

    /** Retirer sa propre proposition (statut → 'retiree'). */
    public function retirer(int $id_proposition, int $id_profile): string {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare("SELECT statut, id_demande FROM proposition WHERE id_proposition = ? AND id_profile = ? FOR UPDATE");
            $s->execute([$id_proposition, $id_profile]);
            $prop = $s->fetch();
            if (!$prop) { $this->db->rollBack(); return 'introuvable'; }
            if ($prop['statut'] !== 'envoyee') { $this->db->rollBack(); return 'mauvais_statut'; }

            $this->db->prepare("UPDATE proposition SET statut = 'retiree', decided_at = now() WHERE id_proposition = ?")
                     ->execute([$id_proposition]);

            // Vérifier s'il reste des propositions actives
            $nb = $this->db->prepare("SELECT COUNT(*) AS nb FROM proposition WHERE id_demande = ? AND statut = 'envoyee'");
            $nb->execute([$prop['id_demande']]);
            $restantes = (int)$nb->fetch()['nb'];

            if ($restantes === 0) {
                $this->db->prepare("UPDATE demande SET statut = 'ouverte' WHERE id_demande = ? AND statut = 'en_discussion'")
                         ->execute([$prop['id_demande']]);
            }

            $this->db->prepare("
                INSERT INTO demande_event (id_demande, id_actor, type, payload)
                SELECT ?, pp.id_user, 'PROPOSITION_RETIREE', jsonb_build_object('id_proposition', ?)
                FROM prestataire_profile pp WHERE pp.id_profile = ?
            ")->execute([$prop['id_demande'], $id_proposition, $id_profile]);

            $this->db->commit();
            return 'ok';
        } catch (Throwable $e) { $this->db->rollBack(); logError($e); throw $e; }
    }

    public function lastId(): int { return (int)$this->db->lastInsertId(); }

    /** Nombre de propositions actives sur une demande. */
    public function countActives(int $id_demande): int {
        $s = $this->db->prepare("SELECT COUNT(*) AS nb FROM proposition WHERE id_demande = ? AND statut = 'envoyee'");
        $s->execute([$id_demande]);
        return (int)$s->fetch()['nb'];
    }
}
