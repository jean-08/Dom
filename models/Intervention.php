<?php
require_once __DIR__ . '/../utils/logger.php';
require_once __DIR__ . '/../config/database.php';
class Intervention {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function all(): array {
        return $this->db->query("
            SELECT i.*, i.id_profile AS id_prestataire, u.nom, u.prenom
            FROM intervention i
            JOIN prestataire_profile pp ON i.id_profile = pp.id_profile
            JOIN \"user\" u ON pp.id_user = u.id_user
            ORDER BY i.date DESC
        ")->fetchAll();
    }
    public function byPrestataire(int $id): array {
        return $this->byProfile($id);
    }
    public function byProfile(int $id): array {
        $s = $this->db->prepare("SELECT i.*, i.id_profile AS id_prestataire FROM intervention i WHERE id_profile=? ORDER BY date DESC");
        $s->execute([$id]); return $s->fetchAll();
    }
    public function create(array $d): bool {
        $id_profile = $d['id_profile'] ?? $d['id_prestataire'] ?? null;
        $s = $this->db->prepare("INSERT INTO intervention (resultat,date,id_profile,id_demande,id_dispo) VALUES (?,?,?,?,?)");
        return $s->execute([$d['resultat'],date('Y-m-d'),$id_profile,$d['id_demande'],$d['id_dispo']??null]);
    }
    public function update(int $id, string $resultat): bool {
        $s = $this->db->prepare("UPDATE intervention SET resultat=? WHERE id_intervention=?");
        return $s->execute([$resultat,$id]);
    }

    /** API Sprint 4 / V3 — une intervention avec le contexte de sa demande, pour les contrôles d'accès. */
    public function find(int $id_intervention): array|false {
        $s = $this->db->prepare("
            SELECT interv.*, interv.id_profile AS id_prestataire, dem.id_user AS id_user_demande, dem.id_profile_retenu, dem.id_profile_retenu AS id_prestataire_assigne
            FROM intervention interv
            JOIN demande dem ON dem.id_demande = interv.id_demande
            WHERE interv.id_intervention = ?
        ");
        $s->execute([$id_intervention]);
        return $s->fetch();
    }

    /**
     * API Sprint 4 / V3 — le prestataire retenu démarre l'intervention (après solution proposée / intervention planifiée).
     * Renvoie : l'id_intervention créé (en chaîne) en cas de succès,
     * ou l'un des codes d'erreur : 'introuvable' | 'non_assigne' | 'deja_demarree'
     */
    public function demarrer(int $id_demande, int $id_profile, ?int $id_dispo = null): string {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare("SELECT statut, id_profile_retenu FROM demande WHERE id_demande = ? FOR UPDATE");
            $s->execute([$id_demande]);
            $demande = $s->fetch();

            if (!$demande) {
                $this->db->rollBack();
                return 'introuvable';
            }
            if ((int)$demande['id_profile_retenu'] !== $id_profile) {
                $this->db->rollBack();
                return 'non_assigne';
            }
            if ($demande['statut'] !== 'intervention_planifiee') {
                $this->db->rollBack();
                return 'deja_demarree';
            }

            $ins = $this->db->prepare("INSERT INTO intervention (resultat,date,id_profile,id_demande,id_dispo,statut) VALUES (?,?,?,?,?,?)");
            $ins->execute([null, date('Y-m-d'), $id_profile, $id_demande, $id_dispo, 'en_cours']);
            $id_intervention = (int)$this->db->lastInsertId();

            $upd = $this->db->prepare("UPDATE demande SET statut = 'intervention_en_cours' WHERE id_demande = ?");
            $upd->execute([$id_demande]);

            $this->db->commit();
            return (string)$id_intervention;
        } catch (Throwable $e) {
            $this->db->rollBack();
            logError($e);
            throw $e;
        }
    }

    /**
     * API Sprint 4 / V3 — le prestataire assigné clôture l'intervention et enregistre le résultat.
     * Renvoie : 'ok' | 'introuvable' | 'non_assigne' | 'deja_terminee'
     */
    public function terminer(int $id_intervention, int $id_profile, string $resultat): string {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare("SELECT id_demande, id_profile, statut FROM intervention WHERE id_intervention = ? FOR UPDATE");
            $s->execute([$id_intervention]);
            $interv = $s->fetch();

            if (!$interv) {
                $this->db->rollBack();
                return 'introuvable';
            }
            if ((int)$interv['id_profile'] !== $id_profile) {
                $this->db->rollBack();
                return 'non_assigne';
            }
            if ($interv['statut'] === 'terminee') {
                $this->db->rollBack();
                return 'deja_terminee';
            }

            $upd = $this->db->prepare("UPDATE intervention SET statut = 'terminee', resultat = ? WHERE id_intervention = ?");
            $upd->execute([$resultat, $id_intervention]);

            $updDem = $this->db->prepare("UPDATE demande SET statut = 'terminee' WHERE id_demande = ?");
            $updDem->execute([$interv['id_demande']]);

            $this->db->commit();
            return 'ok';
        } catch (Throwable $e) {
            $this->db->rollBack();
            logError($e);
            throw $e;
        }
    }

    /** Interventions terminées concernant un client, sans avis existant (pour le formulaire de dépôt d'avis). */
    public function termineesSansAvisPourClient(int $id_user): array {
        $s = $this->db->prepare("
            SELECT interv.*, interv.id_profile AS id_prestataire, u.nom, u.prenom, pp.bio AS specialite, pp.bio
            FROM intervention interv
            JOIN demande dem ON dem.id_demande = interv.id_demande
            JOIN prestataire_profile pp ON pp.id_profile = interv.id_profile
            JOIN \"user\" u ON u.id_user = pp.id_user
            LEFT JOIN avis a ON a.id_intervention = interv.id_intervention
            WHERE dem.id_user = ? AND interv.statut = 'terminee' AND a.id_avis IS NULL
            ORDER BY interv.date DESC
        ");
        $s->execute([$id_user]);
        return $s->fetchAll();
    }
}