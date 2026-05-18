<?php
require_once __DIR__ . '/../config/database.php';
class Solution {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function byDiagnostic(int $id): array {
        $s = $this->db->prepare("SELECT * FROM solution WHERE id_diagnostic=?");
        $s->execute([$id]); return $s->fetchAll();
    }
    public function create(array $d): bool {
        $s = $this->db->prepare("INSERT INTO solution (description,id_diagnostic) VALUES (?,?)");
        return $s->execute([$d['description'],$d['id_diagnostic']]);
    }
    public function lastId(): int { return (int)$this->db->lastInsertId(); }
    public function addProduit(int $id_sol, int $id_prod, int $qte): bool {
        $s = $this->db->prepare("INSERT IGNORE INTO utiliser (id_solution,id_produit,quantite) VALUES (?,?,?)");
        return $s->execute([$id_sol,$id_prod,$qte]);
    }
}