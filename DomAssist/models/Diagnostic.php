<?php
require_once __DIR__ . '/../config/database.php';
class Diagnostic {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function find(int $id): array|false {
        $s = $this->db->prepare("SELECT * FROM diagnostic WHERE id_diagnostic=?");
        $s->execute([$id]); return $s->fetch();
    }
    public function byDemande(int $id_demande): array|false {
        $s = $this->db->prepare("SELECT * FROM diagnostic WHERE id_demande=?");
        $s->execute([$id_demande]); return $s->fetch();
    }
    public function create(array $d): bool {
        $s = $this->db->prepare("INSERT INTO diagnostic (description,resultat,date,id_demande,id_prestataire) VALUES (?,?,?,?,?)");
        return $s->execute([$d['description'],$d['resultat'],date('Y-m-d'),$d['id_demande'],$d['id_prestataire']]);
    }
    public function lastId(): int { return (int)$this->db->lastInsertId(); }
}