<?php
require_once __DIR__ . '/../config/database.php';
class Service {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function all(): array {
        return $this->db->query("SELECT * FROM service")->fetchAll();
    }
    public function find(int $id): array|false {
        $s = $this->db->prepare("SELECT * FROM service WHERE id_service=?");
        $s->execute([$id]); return $s->fetch();
    }
    public function create(array $d): bool {
        $s = $this->db->prepare("INSERT INTO service (nom,description) VALUES (?,?)");
        return $s->execute([$d['nom'],$d['description']]);
    }
    public function delete(int $id): bool {
        $s = $this->db->prepare("DELETE FROM service WHERE id_service=?");
        return $s->execute([$id]);
    }
    public function byPrestataire(int $id): array {
        $s = $this->db->prepare("SELECT s.*,ac.niveau FROM service s JOIN avoir_une_competence ac ON s.id_service=ac.id_service WHERE ac.id_prestataire=?");
        $s->execute([$id]); return $s->fetchAll();
    }
    public function addCompetence(int $id_prest, int $id_serv, string $niveau): bool {
        $s = $this->db->prepare("INSERT IGNORE INTO avoir_une_competence (id_prestataire,id_service,niveau) VALUES (?,?,?)");
        return $s->execute([$id_prest,$id_serv,$niveau]);
    }
}