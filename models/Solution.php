<?php
require_once __DIR__ . '/../utils/logger.php';
require_once __DIR__ . '/../config/database.php';
class Solution {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function byDiagnostic(int $id): array {
        $s = $this->db->prepare("SELECT * FROM solution WHERE id_diagnostic=?");
        $s->execute([$id]); return $s->fetchAll();
    }

    /** API Sprint 4 / V3 — solution + infos de la demande parente (propriétaire, statut), pour les contrôles d'autorisation. */
    public function find(int $id_solution): array|false {
        $s = $this->db->prepare("
            SELECT sol.*, dem.id_demande, dem.id_user AS id_user_demande, dem.statut AS statut_demande
            FROM solution sol
            JOIN diagnostic diag ON diag.id_diagnostic = sol.id_diagnostic
            JOIN demande dem ON dem.id_demande = diag.id_demande
            WHERE sol.id_solution = ?
        ");
        $s->execute([$id_solution]);
        return $s->fetch();
    }

    public function create(array $d): bool {
        $s = $this->db->prepare("INSERT INTO solution (description,id_diagnostic) VALUES (?,?)");
        $res = $s->execute([$d['description'],$d['id_diagnostic']]);
        if ($res) {
            $diagStmt = $this->db->prepare("SELECT id_demande FROM diagnostic WHERE id_diagnostic = ?");
            $diagStmt->execute([$d['id_diagnostic']]);
            $diag = $diagStmt->fetch();
            if ($diag) {
                $updDem = $this->db->prepare("UPDATE demande SET statut = 'solution_proposee' WHERE id_demande = ?");
                $updDem->execute([$diag['id_demande']]);
            }
        }
        return $res;
    }
    public function lastId(): int { return (int)$this->db->lastInsertId(); }
    public function addProduit(int $id_sol, int $id_prod, int $qte): bool {
        try {
            $s = $this->db->prepare("INSERT INTO utiliser (id_solution,id_produit,quantite) VALUES (?,?,?) ON CONFLICT DO NOTHING");
            return $s->execute([$id_sol,$id_prod,$qte]);
        } catch (Throwable $e) {
            logError($e);
            return false;
        }
    }

    /**
     * API Sprint 4 / V3 — le client valide une solution proposée sur SA demande.
     * Renvoie : 'ok' | 'introuvable' | 'refuse' | 'deja_validee'
     */
    public function valider(int $id_solution, int $id_user): string {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare("
                SELECT sol.id_solution, sol.validee_par_client, dem.id_demande, dem.id_user AS id_user_demande, dem.statut
                FROM solution sol
                JOIN diagnostic diag ON diag.id_diagnostic = sol.id_diagnostic
                JOIN demande dem ON dem.id_demande = diag.id_demande
                WHERE sol.id_solution = ?
                FOR UPDATE OF dem
            ");
            $s->execute([$id_solution]);
            $row = $s->fetch();

            if (!$row) {
                $this->db->rollBack();
                return 'introuvable';
            }
            if ((int)$row['id_user_demande'] !== $id_user) {
                $this->db->rollBack();
                return 'refuse';
            }
            if ($row['validee_par_client'] || $row['statut'] === 'intervention_planifiee' || $row['statut'] === 'intervention_en_cours' || $row['statut'] === 'terminee' || $row['statut'] === 'cloturee') {
                $this->db->rollBack();
                return 'deja_validee';
            }
            if ($row['statut'] !== 'solution_proposee') {
                $this->db->rollBack();
                return 'refuse';
            }

            $upd = $this->db->prepare("UPDATE solution SET validee_par_client = true, date_validation = now() WHERE id_solution = ?");
            $upd->execute([$id_solution]);

            $updDem = $this->db->prepare("UPDATE demande SET statut = 'intervention_planifiee' WHERE id_demande = ?");
            $updDem->execute([$row['id_demande']]);

            $this->db->commit();
            return 'ok';
        } catch (Throwable $e) {
            $this->db->rollBack();
            logError($e);
            throw $e;
        }
    }
    /**
     * Le client refuse une solution proposée sur SA demande.
     * Renvoie : 'ok' | 'introuvable' | 'refuse' | 'deja_traitee'
     */
    public function refuser(int $id_solution, int $id_user): string {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare("
                SELECT sol.id_solution, sol.validee_par_client, sol.refusee_par_client,
                       dem.id_demande, dem.id_user AS id_user_demande, dem.statut
                FROM solution sol
                JOIN diagnostic diag ON diag.id_diagnostic = sol.id_diagnostic
                JOIN demande dem ON dem.id_demande = diag.id_demande
                WHERE sol.id_solution = ?
                FOR UPDATE OF dem
            ");
            $s->execute([$id_solution]);
            $row = $s->fetch();

            if (!$row) {
                $this->db->rollBack();
                return 'introuvable';
            }
            if ((int)$row['id_user_demande'] !== $id_user) {
                $this->db->rollBack();
                return 'refuse';
            }
            if ($row['validee_par_client'] || $row['refusee_par_client'] || $row['statut'] !== 'solution_proposee') {
                $this->db->rollBack();
                return 'deja_traitee';
            }

            $upd = $this->db->prepare("UPDATE solution SET refusee_par_client = true WHERE id_solution = ?");
            $upd->execute([$id_solution]);

            $updDem = $this->db->prepare("UPDATE demande SET statut = 'diagnostic_propose' WHERE id_demande = ?");
            $updDem->execute([$row['id_demande']]);

            $this->db->commit();
            return 'ok';
        } catch (Throwable $e) {
            $this->db->rollBack();
            logError($e);
            throw $e;
        }
    }
}