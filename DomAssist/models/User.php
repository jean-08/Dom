<?php
require_once __DIR__ . '/../config/database.php';
class User {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function all(): array {
        return $this->db->query("SELECT id_user,nom,prenom,email,role FROM user")->fetchAll();
    }
    public function find(int $id): array|false {
        $s = $this->db->prepare("SELECT id_user,nom,prenom,email,role FROM user WHERE id_user=?");
        $s->execute([$id]); return $s->fetch();
    }
    public function findByEmail(string $email): array|false {
        $s = $this->db->prepare("SELECT * FROM user WHERE email=?");
        $s->execute([$email]); return $s->fetch();
    }
    public function create(array $d): bool {
        $mdp_hash = password_hash($d['mot_de_passe'], PASSWORD_BCRYPT);
        $s = $this->db->prepare("INSERT INTO user (nom,prenom,email,role,mot_de_passe) VALUES (?,?,?,?,?)");
        return $s->execute([$d['nom'],$d['prenom'],$d['email'],$d['role'],$mdp_hash]);
    }
    public function update(int $id, array $d): bool {
        $s = $this->db->prepare("UPDATE user SET nom=?,prenom=?,email=?,role=? WHERE id_user=?");
        return $s->execute([$d['nom'],$d['prenom'],$d['email'],$d['role'],$id]);
    }
    public function delete(int $id): bool {
        $s = $this->db->prepare("DELETE FROM user WHERE id_user=?");
        return $s->execute([$id]);
    }
    public function lastId(): int { return (int)$this->db->lastInsertId(); }
}