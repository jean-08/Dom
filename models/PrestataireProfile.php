<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Profil prestataire avec cycle de candidature enrichi.
 * Un utilisateur peut être à la fois client ET prestataire (deux rôles orthogonaux).
 */
class PrestataireProfile {
    private PDO $db;

    private static function normalizeJsonbField(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_array($value)) {
            return json_encode($value);
        }
        if (!is_string($value)) {
            return json_encode($value);
        }
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }
        json_decode($trimmed);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $trimmed;
        }
        return json_encode($trimmed);
    }

    public function __construct() { $this->db = Database::getInstance(); }

    public function find(int $id): array|false {
        $s = $this->db->prepare("
            SELECT pp.*, pp.id_profile AS id_prestataire, pp.statut AS statut_validation, pp.bio AS specialite,
                   u.nom, u.prenom, u.email, u.telephone, u.photo_url, u.ville
            FROM prestataire_profile pp
            JOIN \"user\" u ON u.id_user = pp.id_user
            WHERE pp.id_profile = ?
        ");
        $s->execute([$id]);
        return $s->fetch();
    }

    public function findByUser(int $id_user): array|false {
        $s = $this->db->prepare("
            SELECT pp.*, pp.id_profile AS id_prestataire, pp.statut AS statut_validation, pp.bio AS specialite,
                   u.nom, u.prenom, u.email, u.telephone, u.photo_url, u.ville
            FROM prestataire_profile pp
            JOIN \"user\" u ON u.id_user = pp.id_user
            WHERE pp.id_user = ?
        ");
        $s->execute([$id_user]);
        return $s->fetch();
    }

    /** Tous les profils validés (visibles dans les recherches et le matching). */
    public function allValides(): array {
        return $this->db->query("
            SELECT pp.*, pp.id_profile AS id_prestataire, pp.statut AS statut_validation, pp.bio AS specialite,
                   u.nom, u.prenom, u.email, u.photo_url, u.ville
            FROM prestataire_profile pp
            JOIN \"user\" u ON u.id_user = pp.id_user
            WHERE pp.statut = 'validee'
            ORDER BY pp.date_validation DESC
        ")->fetchAll();
    }

    /** File d'attente admin : candidatures soumises ou en revue. */
    public function findEnAttente(): array {
        return $this->db->query("
            SELECT pp.*, pp.id_profile AS id_prestataire, pp.statut AS statut_validation, pp.bio AS specialite,
                   u.nom, u.prenom, u.email, u.photo_url
            FROM prestataire_profile pp
            JOIN \"user\" u ON u.id_user = pp.id_user
            WHERE pp.statut IN ('soumise', 'en_revue')
            ORDER BY pp.date_soumission ASC
        ")->fetchAll();
    }

    /** Créer un brouillon de candidature. */
    public function creerBrouillon(int $id_user): bool {
        $s = $this->db->prepare("
            INSERT INTO prestataire_profile (id_user, statut)
            VALUES (?, 'brouillon')
            ON CONFLICT (id_user) DO NOTHING
        ");
        return $s->execute([$id_user]);
    }

    /** Mettre à jour le dossier (brouillon ou rejeté). */
    public function updateDossier(int $id_profile, array $d): bool {
        $s = $this->db->prepare("
            UPDATE prestataire_profile
            SET bio = ?,
                lettre_motivation = ?,
                experience_annees = ?,
                zone_intervention = ?::jsonb,
                disponibilites_type = ?,
                accepte_urgences = ?,
                moyen_deplacement = ?,
                siret = ?,
                assurances_pro = ?,
                document_cv_url = COALESCE(?, document_cv_url)
            WHERE id_profile = ?
        ");
        return $s->execute([
            $d['bio'] ?? null,
            $d['lettre_motivation'] ?? null,
            !empty($d['experience_annees']) ? (int)$d['experience_annees'] : null,
            self::normalizeJsonbField($d['zone_intervention'] ?? null),
            $d['disponibilites_type'] ?? 'Semaine et Week-end',
            !empty($d['accepte_urgences']) && ($d['accepte_urgences'] === true || $d['accepte_urgences'] === 'true' || $d['accepte_urgences'] === 1 || $d['accepte_urgences'] === '1'),
            $d['moyen_deplacement'] ?? 'Vehicule personnel',
            $d['siret'] ?? null,
            $d['assurances_pro'] ?? null,
            $d['document_cv_url'] ?? null,
            $id_profile,
        ]);
    }

    /**
     * Soumettre la candidature à l'admin.
     * Conditions : profil en brouillon ou rejeté + CGU acceptées.
     * Retourne : 'ok' | 'introuvable' | 'mauvais_statut' | 'dossier_incomplet'
     */
    public function soumettre(int $id_profile, int $id_user): string {
        $s = $this->db->prepare("SELECT statut, bio FROM prestataire_profile WHERE id_profile = ? AND id_user = ?");
        $s->execute([$id_profile, $id_user]);
        $profile = $s->fetch();

        if (!$profile) return 'introuvable';
        if (!in_array($profile['statut'], ['brouillon', 'rejetee'])) return 'mauvais_statut';
        if (empty($profile['bio'])) return 'dossier_incomplet';

        // Vérifier qu'il a au moins une compétence
        $sc = $this->db->prepare("SELECT COUNT(*) AS nb FROM competence WHERE id_profile = ?");
        $sc->execute([$id_profile]);
        if ((int)$sc->fetch()['nb'] === 0) return 'dossier_incomplet';

        $upd = $this->db->prepare("
            UPDATE prestataire_profile
            SET statut = 'soumise', date_soumission = now(), motif_rejet = null, cgu_acceptees_at = now()
            WHERE id_profile = ?
        ");
        $upd->execute([$id_profile]);
        return 'ok';
    }

    /** Admin : passer en revue. */
    public function mettreEnRevue(int $id): bool {
        $s = $this->db->prepare("UPDATE prestataire_profile SET statut = 'en_revue' WHERE id_profile = ? AND statut = 'soumise'");
        return $s->execute([$id]);
    }

    /** Admin : valider la candidature. */
    public function valider(int $id): bool {
        $s = $this->db->prepare("
            UPDATE prestataire_profile
            SET statut = 'validee', date_validation = now(), motif_rejet = null
            WHERE id_profile = ?
        ");
        return $s->execute([$id]);
    }

    /** Admin : rejeter avec motif obligatoire. */
    public function rejeter(int $id, string $motif): bool {
        $s = $this->db->prepare("
            UPDATE prestataire_profile
            SET statut = 'rejetee', motif_rejet = ?
            WHERE id_profile = ?
        ");
        return $s->execute([$motif, $id]);
    }

    /** Admin : suspendre un profil prestataire. */
    public function suspendre(int $id): bool {
        $s = $this->db->prepare("UPDATE prestataire_profile SET statut = 'suspendue' WHERE id_profile = ?");
        return $s->execute([$id]);
    }

    /** Admin : réactiver un profil prestataire suspendu. */
    public function reactiver(int $id): bool {
        $s = $this->db->prepare("UPDATE prestataire_profile SET statut = 'validee' WHERE id_profile = ?");
        return $s->execute([$id]);
    }

    public function lastId(): int { return (int)$this->db->lastInsertId(); }

    /** Réputation (note moyenne + nombre d'avis). */
    public function reputation(int $id_profile): array {
        $s = $this->db->prepare("
            SELECT COALESCE(AVG(note), 0)::float AS note_moyenne, COUNT(*) AS nombre_avis
            FROM avis WHERE id_profile = ?
        ");
        $s->execute([$id_profile]);
        return $s->fetch();
    }
}
