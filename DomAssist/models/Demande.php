<?php
require_once __DIR__ . '/../config/database.php';
class Demande {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function all(): array {
        return $this->db->query("SELECT d.*,u.nom,u.prenom FROM demande d JOIN user u ON d.id_user=u.id_user ORDER BY d.date DESC")->fetchAll();
    }
    public function byUser(int $id_user): array {
        $s = $this->db->prepare("SELECT * FROM demande WHERE id_user=? ORDER BY date DESC");
        $s->execute([$id_user]); return $s->fetchAll();
    }
    public function find(int $id): array|false {
        $s = $this->db->prepare("SELECT d.*,u.nom,u.prenom FROM demande d JOIN user u ON d.id_user=u.id_user WHERE d.id_demande=?");
        $s->execute([$id]); return $s->fetch();
    }
    public function create(array $d): bool {
        $s = $this->db->prepare("INSERT INTO demande (description,date,statut,adresse,id_user) VALUES (?,?,?,?,?)");
        return $s->execute([$d['description'],date('Y-m-d'),'en_attente',$d['adresse'],$d['id_user']]);
    }
    public function updateStatut(int $id, string $statut): bool {
        $s = $this->db->prepare("UPDATE demande SET statut=? WHERE id_demande=?");
        return $s->execute([$statut,$id]);
    }
    public function delete(int $id): bool {
        $s = $this->db->prepare("DELETE FROM demande WHERE id_demande=?");
        return $s->execute([$id]);
    }
    public function lastId(): int { return (int)$this->db->lastInsertId(); }
}