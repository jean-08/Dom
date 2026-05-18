<?php
require_once __DIR__ . '/../config/database.php';
class Prestataire {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function all(): array {
        return $this->db->query("SELECT p.*,u.nom,u.prenom,u.email FROM prestataire p JOIN user u ON p.id_user=u.id_user")->fetchAll();
    }
    public function find(int $id): array|false {
        $s = $this->db->prepare("SELECT p.*,u.nom,u.prenom,u.email FROM prestataire p JOIN user u ON p.id_user=u.id_user WHERE p.id_prestataire=?");
        $s->execute([$id]); return $s->fetch();
    }
    public function findByUser(int $id_user): array|false {
        $s = $this->db->prepare("SELECT * FROM prestataire WHERE id_user=?");
        $s->execute([$id_user]); return $s->fetch();
    }
    public function create(array $d): bool {
        $s = $this->db->prepare("INSERT INTO prestataire (specialite,id_user) VALUES (?,?)");
        return $s->execute([$d['specialite'],$d['id_user']]);
    }
    public function update(int $id, array $d): bool {
        $s = $this->db->prepare("UPDATE prestataire SET specialite=? WHERE id_prestataire=?");
        return $s->execute([$d['specialite'],$id]);
    }
    public function delete(int $id): bool {
        $s = $this->db->prepare("DELETE FROM prestataire WHERE id_prestataire=?");
        return $s->execute([$id]);
    }
}