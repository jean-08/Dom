<?php
require_once __DIR__ . '/../utils/logger.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Modèle Demande V3 — Marketplace de mise en relation.
 * Workflow : OUVERTE → EN_DISCUSSION → PRESTATAIRE_CHOISI → ENGAGEE → ... → CLOTUREE
 */
class Demande {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    // -----------------------------------------------------------------------
    // Lecture
    // -----------------------------------------------------------------------

    public function find(int $id): array|false {
        $s = $this->db->prepare("
            SELECT d.*,
                   u.nom AS client_nom, u.prenom AS client_prenom, u.email AS client_email,
                   u.telephone AS client_telephone, u.photo_url AS client_photo,
                   sc.libelle AS category_libelle, sc.code AS category_code,
                   pp.id_profile AS profile_retenu_id,
                   ur.nom AS prestataire_nom, ur.prenom AS prestataire_prenom
            FROM demande d
            JOIN \"user\" u ON u.id_user = d.id_user
            JOIN service_category sc ON sc.id_category = d.id_category
            LEFT JOIN prestataire_profile pp ON pp.id_profile = d.id_profile_retenu
            LEFT JOIN \"user\" ur ON ur.id_user = pp.id_user
            WHERE d.id_demande = ?
        ");
        $s->execute([$id]);
        return $s->fetch();
    }

    /** Toutes les demandes (admin). */
    public function all(): array {
        return $this->db->query("
            SELECT d.*, u.nom AS client_nom, u.prenom AS client_prenom,
                   sc.libelle AS category_libelle
            FROM demande d
            JOIN \"user\" u ON u.id_user = d.id_user
            JOIN service_category sc ON sc.id_category = d.id_category
            ORDER BY d.created_at DESC
        ")->fetchAll();
    }

    /** Demandes d'un client (son historique). */
    public function byUser(int $id_user): array {
        $s = $this->db->prepare("
            SELECT d.*, sc.libelle AS category_libelle,
                   (SELECT COUNT(*) FROM proposition p WHERE p.id_demande = d.id_demande AND p.statut = 'envoyee') AS nb_propositions_actives,
                   (SELECT COUNT(*) FROM proposition p WHERE p.id_demande = d.id_demande) AS nb_propositions_total
            FROM demande d
            JOIN service_category sc ON sc.id_category = d.id_category
            WHERE d.id_user = ?
            ORDER BY d.created_at DESC
        ");
        $s->execute([$id_user]);
        return $s->fetchAll();
    }

    /** Demandes éligibles pour un prestataire (via fonction SQL). */
    public function eligibles(int $id_profile): array {
        $s = $this->db->prepare("SELECT * FROM demandes_eligibles(?)");
        $s->execute([$id_profile]);
        return $s->fetchAll();
    }

    /** Demandes sur lesquelles un prestataire a une proposition. */
    public function byProfile(int $id_profile): array {
        $s = $this->db->prepare("
            SELECT d.*, sc.libelle AS category_libelle,
                   prop.statut AS statut_proposition, prop.prix_indicatif,
                   u.nom AS client_nom, u.prenom AS client_prenom
            FROM demande d
            JOIN proposition prop ON prop.id_demande = d.id_demande
            JOIN service_category sc ON sc.id_category = d.id_category
            JOIN \"user\" u ON u.id_user = d.id_user
            WHERE prop.id_profile = ?
            ORDER BY d.created_at DESC
        ");
        $s->execute([$id_profile]);
        return $s->fetchAll();
    }

    /** Demandes engagées avec ce prestataire (retenu + engagé). */
    public function missionsActives(int $id_profile): array {
        $s = $this->db->prepare("
            SELECT d.*, sc.libelle AS category_libelle,
                   u.nom AS client_nom, u.prenom AS client_prenom, u.telephone AS client_telephone
            FROM demande d
            JOIN service_category sc ON sc.id_category = d.id_category
            JOIN \"user\" u ON u.id_user = d.id_user
            WHERE d.id_profile_retenu = ?
              AND d.statut IN ('engagee','diagnostic_propose','solution_proposee',
                               'intervention_planifiee','intervention_en_cours')
            ORDER BY d.created_at ASC
        ");
        $s->execute([$id_profile]);
        return $s->fetchAll();
    }

    // -----------------------------------------------------------------------
    // Création et publication
    // -----------------------------------------------------------------------

    /**
     * Créer une demande (état initial : 'ouverte', published_at = now()).
     * Les photos sont gérées séparément via DemandeMedia.
     */
    public function create(array $d): int|false {
        $s = $this->db->prepare("
            INSERT INTO demande
                (id_user, titre, description, id_category, urgence,
                 budget_min, budget_max, disponibilites_client,
                 adresse, ville, code_postal, telephone_contact,
                 statut, published_at, expires_at)
            VALUES
                (?, ?, ?, ?, ?,
                 ?, ?, ?,
                 ?, ?, ?, ?,
                 'ouverte', now(),
                 compute_demande_expiration(?, now()))
            RETURNING id_demande
        ");
        $ok = $s->execute([
            $d['id_user'],
            $d['titre'],
            $d['description'],
            $d['id_category'],
            $d['urgence'] ?? 'normal',
            !empty($d['budget_min']) ? (float)$d['budget_min'] : null,
            !empty($d['budget_max']) ? (float)$d['budget_max'] : null,
            !empty($d['disponibilites_client']) ? $d['disponibilites_client'] : null,
            !empty($d['adresse']) ? $d['adresse'] : null,
            !empty($d['ville']) ? $d['ville'] : 'Non précisée',
            !empty($d['code_postal']) ? $d['code_postal'] : '00000',
            !empty($d['telephone_contact']) ? $d['telephone_contact'] : null,
            $d['id_category'],   // pour compute_demande_expiration
        ]);
        if ($ok) {
            $row = $s->fetch();
            return (int)($row['id_demande'] ?? $this->db->lastInsertId());
        }
        return false;
    }

    public function lastId(): int { return (int)$this->db->lastInsertId(); }

    // -----------------------------------------------------------------------
    // Transitions de statut (simples, sans logique métier complexe)
    // -----------------------------------------------------------------------

    /** Client annule sa propre demande (avant intervention). */
    public function annulerParClient(int $id_demande, int $id_user): string {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare("SELECT statut, id_user FROM demande WHERE id_demande = ? FOR UPDATE");
            $s->execute([$id_demande]);
            $d = $s->fetch();
            if (!$d) { $this->db->rollBack(); return 'introuvable'; }
            if ((int)$d['id_user'] !== $id_user) { $this->db->rollBack(); return 'non_autorise'; }
            if (in_array($d['statut'], ['intervention_en_cours','terminee','cloturee','annulee_par_client'])) {
                $this->db->rollBack(); return 'mauvais_statut';
            }
            $this->db->prepare("
                UPDATE demande SET statut = 'annulee_par_client', closed_at = now() WHERE id_demande = ?
            ")->execute([$id_demande]);
            // Expirer les propositions encore actives
            $this->db->prepare("
                UPDATE proposition SET statut = 'expiree' WHERE id_demande = ? AND statut = 'envoyee'
            ")->execute([$id_demande]);
            $this->db->prepare("
                INSERT INTO demande_event (id_demande, id_actor, type) VALUES (?, ?, 'ANNULEE')
            ")->execute([$id_demande, $id_user]);
            $this->db->commit();
            return 'ok';
        } catch (Throwable $e) { $this->db->rollBack(); logError($e); throw $e; }
    }

    /** Admin : supprimer une demande. */
    public function delete(int $id): bool {
        $s = $this->db->prepare("DELETE FROM demande WHERE id_demande = ?");
        return $s->execute([$id]);
    }

    // -----------------------------------------------------------------------
    // Propositions (délégation aux fonctions SQL)
    // -----------------------------------------------------------------------

    /** Envoyer une proposition (appel fonction SQL atomique). */
    public function envoyerProposition(int $id_demande, int $id_profile, string $message, ?float $prix, ?string $delai): string {
        $s = $this->db->prepare("SELECT envoyer_proposition(?, ?, ?, ?, ?) AS resultat");
        $s->execute([$id_demande, $id_profile, $message, $prix, $delai]);
        return $s->fetch()['resultat'];
    }

    /** Sélectionner un prestataire (client). */
    public function selectionnerPrestataire(int $id_demande, int $id_user, int $id_proposition): string {
        $s = $this->db->prepare("SELECT selectionner_prestataire(?, ?, ?) AS resultat");
        $s->execute([$id_demande, $id_user, $id_proposition]);
        return $s->fetch()['resultat'];
    }

    /** Confirmer l'engagement (prestataire). */
    public function confirmerEngagement(int $id_demande, int $id_profile): string {
        $s = $this->db->prepare("SELECT confirmer_engagement(?, ?) AS resultat");
        $s->execute([$id_demande, $id_profile]);
        return $s->fetch()['resultat'];
    }

    /** Désistement (prestataire après sélection). */
    public function desister(int $id_demande, int $id_profile): string {
        $s = $this->db->prepare("SELECT desister_prestataire(?, ?) AS resultat");
        $s->execute([$id_demande, $id_profile]);
        return $s->fetch()['resultat'];
    }

    // -----------------------------------------------------------------------
    // Dashboard : files d'action métier
    // -----------------------------------------------------------------------

    /** Demandes du client nécessitant une action (nouvelles propositions, solution à valider, avis à déposer). */
    public function actionsClient(int $id_user): array {
        $s = $this->db->prepare("
            SELECT d.id_demande, d.titre, d.statut, sc.libelle AS category_libelle,
                   (SELECT COUNT(*) FROM proposition p WHERE p.id_demande = d.id_demande AND p.statut = 'envoyee') AS nb_nouvelles_propositions,
                   CASE WHEN d.statut = 'terminee' THEN
                       NOT EXISTS (SELECT 1 FROM intervention i JOIN avis a ON a.id_intervention = i.id_intervention
                                   WHERE i.id_demande = d.id_demande)
                   ELSE false END AS avis_a_deposer
            FROM demande d
            JOIN service_category sc ON sc.id_category = d.id_category
            WHERE d.id_user = ?
              AND d.statut NOT IN ('cloturee','annulee_par_client','annulee_par_prestataire','expiree')
            ORDER BY d.created_at DESC
        ");
        $s->execute([$id_user]);
        return $s->fetchAll();
    }
}
