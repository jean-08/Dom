<?php
require_once __DIR__ . '/../utils/logger.php';
require_once __DIR__ . '/../config/database.php';
class Avis {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function lastId(): int { return (int)$this->db->lastInsertId(); }


    public function byPrestataire(int $id): array {
        return $this->byProfile($id);
    }

    public function byProfile(int $id): array {
        $s = $this->db->prepare("
            SELECT a.*, a.id_profile AS id_prestataire, u.nom, u.prenom
            FROM avis a
            JOIN \"user\" u ON a.id_user = u.id_user
            WHERE a.id_profile = ?
            ORDER BY a.id_avis DESC
        ");
        $s->execute([$id]);
        return $s->fetchAll();
    }

    public function find(int $id): array|false {
        $s = $this->db->prepare("SELECT a.*, a.id_profile AS id_prestataire FROM avis a WHERE id_avis=?");
        $s->execute([$id]);
        $row = $s->fetch();
        if ($row) {
            $row['id_prestataire'] = $row['id_profile'];
        }
        return $row;
    }

    public function byIntervention(int $id_intervention): array|false {
        $s = $this->db->prepare("SELECT a.*, a.id_profile AS id_prestataire FROM avis a WHERE id_intervention=?");
        $s->execute([$id_intervention]);
        $row = $s->fetch();
        if ($row) {
            $row['id_prestataire'] = $row['id_profile'];
        }
        return $row;
    }

    /**
     * Note moyenne + nombre d'avis pour un profil prestataire (Sprint 5 — réputation V3).
     */
    public function reputation(int $id_profile): array {
        $s = $this->db->prepare("
            SELECT COALESCE(AVG(note), 0)::float AS note_moyenne, COUNT(*) AS nombre_avis
            FROM avis WHERE id_profile = ?
        ");
        $s->execute([$id_profile]);
        return $s->fetch();
    }

    /**
     * API Sprint 5 / V3 — un client crée un avis sur UNE intervention terminée le concernant.
     * Transactionnel + verrouillage FOR UPDATE.
     *
     * Renvoie : 'ok' | 'introuvable' | 'refuse' | 'non_terminee' | 'deja_avise'
     */
    public function creerPourIntervention(int $id_intervention, int $id_user, int $note, string $comment): string {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare("
                SELECT interv.id_intervention, interv.statut, interv.id_profile, dem.id_user AS id_user_demande
                FROM intervention interv
                JOIN demande dem ON dem.id_demande = interv.id_demande
                WHERE interv.id_intervention = ?
                FOR UPDATE OF interv
            ");
            $s->execute([$id_intervention]);
            $interv = $s->fetch();

            if (!$interv) {
                $this->db->rollBack();
                return 'introuvable';
            }
            if ((int) $interv['id_user_demande'] !== $id_user) {
                $this->db->rollBack();
                return 'refuse';
            }
            if ($interv['statut'] !== 'terminee') {
                $this->db->rollBack();
                return 'non_terminee';
            }

            $existant = $this->db->prepare("SELECT 1 FROM avis WHERE id_intervention = ?");
            $existant->execute([$id_intervention]);
            if ($existant->fetch()) {
                $this->db->rollBack();
                return 'deja_avise';
            }

            $id_profile = (int) ($interv['id_profile'] ?? $interv['id_prestataire'] ?? 0);
            $ins = $this->db->prepare("
                INSERT INTO avis (note, comment, id_user, id_profile, id_intervention)
                VALUES (?, ?, ?, ?, ?)
            ");
            $ins->execute([$note, $comment !== '' ? $comment : null, $id_user, $id_profile, $id_intervention]);

            // Clôturer la demande automatiquement après le dépôt de l'avis
            $updDem = $this->db->prepare("
                UPDATE demande
                SET statut = 'cloturee', closed_at = now()
                WHERE id_demande = (
                    SELECT id_demande FROM intervention WHERE id_intervention = ?
                ) AND statut = 'terminee'
            ");
            $updDem->execute([$id_intervention]);

            $this->db->commit();
            return 'ok';
        } catch (Throwable $e) {
            $this->db->rollBack();
            logError($e);
            throw $e;
        }
    }

    public function delete(int $id): bool {
        $s = $this->db->prepare("DELETE FROM avis WHERE id_avis=?");
        return $s->execute([$id]);
    }

    /**
     * Dépôt d'une réponse par le prestataire concerné à un avis client (Sprint 5).
     */
    public function repondre(int $id_avis, int $id_profile, string $reponse): string {
        $avis = $this->find($id_avis);
        if (!$avis) {
            return 'introuvable';
        }
        if ((int)($avis['id_profile'] ?? 0) !== $id_profile) {
            return 'non_autorise';
        }
        if (!empty($avis['reponse_prestataire'])) {
            return 'deja_repondu';
        }

        $s = $this->db->prepare("
            UPDATE avis
            SET reponse_prestataire = ?, reponse_created_at = NOW()
            WHERE id_avis = ? AND id_profile = ?
        ");
        $s->execute([$reponse, $id_avis, $id_profile]);
        return 'ok';
    }
}