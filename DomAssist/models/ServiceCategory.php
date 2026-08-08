<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Catalogue de services maîtrisé par le produit (seedé).
 * L'admin peut uniquement activer/désactiver et réordonner.
 */
class ServiceCategory {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    /** Toutes les catégories actives, triées par ordre. */
    public function allActives(): array {
        return $this->db->query("
            SELECT * FROM service_category WHERE actif = true ORDER BY ordre ASC
        ")->fetchAll();
    }

    /** Toutes les catégories (admin). */
    public function all(): array {
        return $this->db->query("SELECT * FROM service_category ORDER BY ordre ASC")->fetchAll();
    }

    public function find(int $id): array|false {
        $s = $this->db->prepare("SELECT * FROM service_category WHERE id_category = ?");
        $s->execute([$id]);
        return $s->fetch();
    }

    /** Admin : activer/désactiver une catégorie. */
    public function setActif(int $id, bool $actif): bool {
        $s = $this->db->prepare("UPDATE service_category SET actif = ? WHERE id_category = ?");
        return $s->execute([$actif, $id]);
    }

    /** Admin : renommer le libellé d'une catégorie. */
    public function updateLibelle(int $id, string $libelle, ?string $description = null): bool {
        $s = $this->db->prepare("UPDATE service_category SET libelle = ?, description = ? WHERE id_category = ?");
        return $s->execute([$libelle, $description, $id]);
    }

    /** Admin : modifier l'ordre d'affichage. */
    public function updateOrdre(int $id, int $ordre): bool {
        $s = $this->db->prepare("UPDATE service_category SET ordre = ? WHERE id_category = ?");
        return $s->execute([$ordre, $id]);
    }

    /** Catégories auxquelles un prestataire est compétent. */
    public function byProfile(int $id_profile): array {
        $s = $this->db->prepare("
            SELECT sc.*, c.niveau
            FROM service_category sc
            JOIN competence c ON sc.id_category = c.id_category
            WHERE c.id_profile = ?
            ORDER BY sc.ordre ASC
        ");
        $s->execute([$id_profile]);
        return $s->fetchAll();
    }

    /** Ajouter ou mettre à jour une compétence (upsert). */
    public function addCompetence(int $id_profile, int $id_category, ?string $niveau = null): bool {
        $s = $this->db->prepare("
            INSERT INTO competence (id_profile, id_category, niveau)
            VALUES (?, ?, ?)
            ON CONFLICT (id_profile, id_category) DO UPDATE SET niveau = EXCLUDED.niveau
        ");
        return $s->execute([$id_profile, $id_category, $niveau]);
    }

    public function removeCompetence(int $id_profile, int $id_category): bool {
        $s = $this->db->prepare("DELETE FROM competence WHERE id_profile = ? AND id_category = ?");
        $s->execute([$id_profile, $id_category]);
        return $s->rowCount() > 0;
    }

    public function hasCompetence(int $id_profile, int $id_category): bool {
        $s = $this->db->prepare("SELECT 1 FROM competence WHERE id_profile = ? AND id_category = ?");
        $s->execute([$id_profile, $id_category]);
        return (bool)$s->fetch();
    }
}
