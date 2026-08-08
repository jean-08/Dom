<?php
require_once __DIR__ . '/../utils/logger.php';
require_once __DIR__ . '/../config/database.php';
class Diagnostic {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function find(int $id): array|false {
        $s = $this->db->prepare("SELECT * FROM diagnostic WHERE id_diagnostic=?");
        $s->execute([$id]);
        $row = $s->fetch();
        if ($row) {
            $row['id_prestataire'] = $row['id_profile'];
        }
        return $row;
    }
    public function byDemande(int $id_demande): array|false {
        $s = $this->db->prepare("SELECT * FROM diagnostic WHERE id_demande=?");
        $s->execute([$id_demande]);
        $row = $s->fetch();
        if ($row) {
            $row['id_prestataire'] = $row['id_profile'];
        }
        return $row;
    }
    public function create(array $d): bool {
        $id_profile = $d['id_profile'] ?? $d['id_prestataire'] ?? null;
        $s = $this->db->prepare("INSERT INTO diagnostic (description,resultat,date,id_demande,id_profile) VALUES (?,?,?,?,?)");
        return $s->execute([$d['description'],$d['resultat'],date('Y-m-d'),$d['id_demande'],$id_profile]);
    }
    public function lastId(): int { return (int)$this->db->lastInsertId(); }

    /**
     * API Sprint 4 / V3 — un prestataire propose un diagnostic sur UNE demande engagée qu'il a obtenue.
     * Transactionnel + verrouillage de ligne FOR UPDATE.
     *
     * Renvoie : 'ok' | 'introuvable' | 'non_assigne' | 'deja_diagnostique'
     */
    public function proposer(int $id_demande, int $id_profile, string $description, string $resultat): string {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare("SELECT statut, id_profile_retenu FROM demande WHERE id_demande = ? FOR UPDATE");
            $s->execute([$id_demande]);
            $demande = $s->fetch();

            if (!$demande) {
                $this->db->rollBack();
                return 'introuvable';
            }

            $idRetenu = (int)($demande['id_profile_retenu'] ?? 0);
            if ($idRetenu !== $id_profile || $demande['statut'] !== 'engagee') {
                $this->db->rollBack();
                return 'non_assigne';
            }

            $existant = $this->db->prepare("SELECT 1 FROM diagnostic WHERE id_demande = ?");
            $existant->execute([$id_demande]);
            if ($existant->fetch()) {
                $this->db->rollBack();
                return 'deja_diagnostique';
            }

            $ins = $this->db->prepare("INSERT INTO diagnostic (description,resultat,date,id_demande,id_profile) VALUES (?,?,?,?,?)");
            $ins->execute([$description, $resultat, date('Y-m-d'), $id_demande, $id_profile]);

            $upd = $this->db->prepare("UPDATE demande SET statut = 'diagnostic_propose' WHERE id_demande = ?");
            $upd->execute([$id_demande]);

            $this->db->commit();
            return 'ok';
        } catch (Throwable $e) {
            $this->db->rollBack();
            logError($e);
            throw $e;
        }
    }
}