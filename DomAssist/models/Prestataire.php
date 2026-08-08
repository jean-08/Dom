<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Façade de compatibilité V3 — pointe vers prestataire_profile.
 *
 * Tous les controllers appellent new Prestataire() et s'attendent à des
 * colonnes : id_prestataire, statut_validation, specialite, id_user, …
 * Ce modèle traduit ces appels vers la structure V3 sans toucher aux controllers.
 *
 * Correspondances de colonnes :
 *   id_prestataire   → id_profile
 *   statut_validation → statut  (valeurs mappées : en_attente→soumise, valide→validee, etc.)
 *   specialite       → bio (texte libre de présentation)
 */
class Prestataire
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // -----------------------------------------------------------------------
    // Lecture
    // -----------------------------------------------------------------------

    /** Tous les profils (admin). */
    public function all(): array
    {
        return $this->db->query("
            SELECT pp.id_profile AS id_prestataire, pp.id_user, pp.bio AS specialite, pp.bio,
                   pp.statut AS statut_validation, pp.motif_rejet,
                   pp.date_soumission AS date_demande, pp.date_validation,
                   pp.experience_annees, pp.zone_intervention, pp.lettre_motivation,
                   pp.disponibilites_type, pp.accepte_urgences, pp.moyen_deplacement,
                   pp.siret, pp.assurances_pro, pp.document_cv_url,
                   u.nom, u.prenom, u.email, u.telephone, u.photo_url, u.ville
            FROM prestataire_profile pp
            JOIN \"user\" u ON u.id_user = pp.id_user
            ORDER BY pp.date_soumission DESC
        ")->fetchAll();
    }

    /** Liste publique : uniquement les profils validés. */
    public function allValides(): array
    {
        return $this->db->query("
            SELECT pp.id_profile AS id_prestataire, pp.id_user, pp.bio AS specialite, pp.bio,
                   pp.statut AS statut_validation,
                   pp.date_validation, pp.experience_annees, pp.zone_intervention,
                   pp.lettre_motivation, pp.disponibilites_type, pp.accepte_urgences,
                   pp.moyen_deplacement, pp.siret, pp.assurances_pro, pp.document_cv_url,
                   u.nom, u.prenom, u.email, u.telephone, u.photo_url, u.ville
            FROM prestataire_profile pp
            JOIN \"user\" u ON u.id_user = pp.id_user
            WHERE pp.statut = 'validee'
            ORDER BY pp.date_validation DESC
        ")->fetchAll();
    }

    /** Trouver par id_profile (alias id_prestataire). */
    public function find(int $id): array|false
    {
        $s = $this->db->prepare("
            SELECT pp.id_profile AS id_prestataire, pp.id_user, pp.bio AS specialite, pp.bio,
                   pp.statut AS statut_validation, pp.motif_rejet,
                   pp.date_soumission AS date_demande, pp.date_validation,
                   pp.experience_annees, pp.zone_intervention, pp.lettre_motivation,
                   pp.disponibilites_type, pp.accepte_urgences, pp.moyen_deplacement,
                   pp.siret, pp.assurances_pro, pp.document_cv_url,
                   u.nom, u.prenom, u.email, u.telephone, u.photo_url, u.ville
            FROM prestataire_profile pp
            JOIN \"user\" u ON u.id_user = pp.id_user
            WHERE pp.id_profile = ?
        ");
        $s->execute([$id]);
        return $s->fetch();
    }

    /** Trouver par id_user (un user ne peut avoir qu'un profil). */
    public function findByUser(int $id_user): array|false
    {
        $s = $this->db->prepare("
            SELECT pp.id_profile AS id_prestataire, pp.id_user, pp.bio AS specialite, pp.bio,
                   pp.statut AS statut_validation, pp.motif_rejet,
                   pp.date_soumission AS date_demande, pp.date_validation,
                   pp.experience_annees, pp.zone_intervention, pp.lettre_motivation,
                   pp.disponibilites_type, pp.accepte_urgences, pp.moyen_deplacement,
                   pp.siret, pp.assurances_pro, pp.document_cv_url,
                   u.nom, u.prenom, u.email, u.telephone, u.photo_url, u.ville
            FROM prestataire_profile pp
            JOIN \"user\" u ON u.id_user = pp.id_user
            WHERE pp.id_user = ?
        ");
        $s->execute([$id_user]);
        return $s->fetch();
    }

    /** File d'attente admin : candidatures soumises ou en revue. */
    public function findEnAttente(): array
    {
        return $this->db->query("
            SELECT pp.id_profile AS id_prestataire, pp.id_user, pp.bio AS specialite, pp.bio,
                   pp.statut AS statut_validation, pp.motif_rejet,
                   pp.date_soumission AS date_demande,
                   pp.experience_annees, pp.zone_intervention, pp.lettre_motivation,
                   pp.disponibilites_type, pp.accepte_urgences, pp.moyen_deplacement,
                   pp.siret, pp.assurances_pro, pp.document_cv_url,
                   u.nom, u.prenom, u.email, u.photo_url, u.telephone, u.ville
            FROM prestataire_profile pp
            JOIN \"user\" u ON u.id_user = pp.id_user
            WHERE pp.statut IN ('soumise', 'en_revue')
            ORDER BY pp.date_soumission ASC
        ")->fetchAll();
    }

    // -----------------------------------------------------------------------
    // Candidature
    // -----------------------------------------------------------------------

    /**
     * Un utilisateur candidate pour devenir prestataire (ou resoumet une candidature rejetée).
     */
    public function candidater(array $d): bool
    {
        $s = $this->db->prepare("
            INSERT INTO prestataire_profile
                (id_user, bio, lettre_motivation, experience_annees, zone_intervention,
                 disponibilites_type, accepte_urgences, moyen_deplacement, siret, assurances_pro,
                 document_cv_url, statut, date_soumission, motif_rejet)
            VALUES (?, ?, ?, ?, ?::jsonb, ?, ?::boolean, ?, ?, ?, ?, 'soumise', now(), NULL)
            ON CONFLICT (id_user) DO UPDATE SET
                bio                 = EXCLUDED.bio,
                lettre_motivation   = EXCLUDED.lettre_motivation,
                experience_annees   = EXCLUDED.experience_annees,
                zone_intervention   = EXCLUDED.zone_intervention,
                disponibilites_type = EXCLUDED.disponibilites_type,
                accepte_urgences    = EXCLUDED.accepte_urgences,
                moyen_deplacement   = EXCLUDED.moyen_deplacement,
                siret               = EXCLUDED.siret,
                assurances_pro      = EXCLUDED.assurances_pro,
                document_cv_url     = COALESCE(EXCLUDED.document_cv_url, prestataire_profile.document_cv_url),
                statut              = 'soumise',
                date_soumission     = now(),
                motif_rejet         = NULL
        ");
        $accepteUrgences = !empty($d['accepte_urgences']) && ($d['accepte_urgences'] === true || $d['accepte_urgences'] === 'true' || $d['accepte_urgences'] === 1 || $d['accepte_urgences'] === '1');
        return $s->execute([
            $d['id_user'],
            $d['bio'] ?? $d['specialite'] ?? null,
            $d['lettre_motivation'] ?? null,
            !empty($d['experience_annees']) ? (int)$d['experience_annees'] : null,
            $d['zone_intervention'] ?? null,
            $d['disponibilites_type'] ?? 'Semaine et Week-end',
            $accepteUrgences ? 'true' : 'false',
            $d['moyen_deplacement'] ?? 'Vehicule personnel',
            $d['siret'] ?? null,
            $d['assurances_pro'] ?? null,
            $d['document_cv_url'] ?? null,
        ]);
    }

    // -----------------------------------------------------------------------
    // Actions admin
    // -----------------------------------------------------------------------

    public function valider(int $id): bool
    {
        $s = $this->db->prepare("
            UPDATE prestataire_profile
            SET statut = 'validee', date_validation = now(), motif_rejet = null
            WHERE id_profile = ?
        ");
        return $s->execute([$id]);
    }

    public function rejeter(int $id, string $motif): bool
    {
        $s = $this->db->prepare("
            UPDATE prestataire_profile
            SET statut = 'rejetee', motif_rejet = ?
            WHERE id_profile = ?
        ");
        return $s->execute([$motif, $id]);
    }

    public function suspendre(int $id): bool
    {
        $s = $this->db->prepare("
            UPDATE prestataire_profile SET statut = 'suspendue' WHERE id_profile = ?
        ");
        return $s->execute([$id]);
    }

    // -----------------------------------------------------------------------
    // Mise à jour (spécialité → bio)
    // -----------------------------------------------------------------------

    public function update(int $id, array $d): bool
    {
        $s = $this->db->prepare("
            UPDATE prestataire_profile
            SET bio                 = COALESCE(?, bio),
                lettre_motivation   = COALESCE(?, lettre_motivation),
                experience_annees   = COALESCE(?, experience_annees),
                zone_intervention   = COALESCE(?, zone_intervention),
                disponibilites_type = COALESCE(?, disponibilites_type),
                accepte_urgences    = COALESCE(?, accepte_urgences),
                moyen_deplacement   = COALESCE(?, moyen_deplacement),
                siret               = COALESCE(?, siret),
                assurances_pro      = COALESCE(?, assurances_pro),
                document_cv_url     = COALESCE(?, document_cv_url)
            WHERE id_profile = ?
        ");
        return $s->execute([
            $d['bio'] ?? $d['specialite'] ?? null,
            $d['lettre_motivation'] ?? null,
            !empty($d['experience_annees']) ? (int)$d['experience_annees'] : null,
            $d['zone_intervention'] ?? null,
            $d['disponibilites_type'] ?? null,
            isset($d['accepte_urgences']) ? (!empty($d['accepte_urgences']) && ($d['accepte_urgences'] === true || $d['accepte_urgences'] === 'true' || $d['accepte_urgences'] === 1 || $d['accepte_urgences'] === '1')) : null,
            $d['moyen_deplacement'] ?? null,
            $d['siret'] ?? null,
            $d['assurances_pro'] ?? null,
            $d['document_cv_url'] ?? null,
            $id,
        ]);
    }

    // -----------------------------------------------------------------------
    // Suppression
    // -----------------------------------------------------------------------

    public function delete(int $id): bool
    {
        $s = $this->db->prepare("DELETE FROM prestataire_profile WHERE id_profile = ?");
        return $s->execute([$id]);
    }
}