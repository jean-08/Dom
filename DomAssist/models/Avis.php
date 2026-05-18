<?php
require_once __DIR__ . '/../config/database.php';
class Avis {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function byPrestataire(int $id): array {
        $s = $this->db->prepare("SELECT a.*,u.nom,u.prenom FROM avis a JOIN user u ON a.id_user=u.id_user WHERE a.id_prestataire=?");
        $s->execute([$id]); return $s->fetchAll();
    }
    public function create(array $d): bool {
        $s = $this->db->prepare("INSERT INTO avis (note,comment,id_user,id_prestataire) VALUES (?,?,?,?)");
        return $s->execute([$d['note'],$d['comment'],$d['id_user'],$d['id_prestataire']]);
    }
    public function delete(int $id): bool {
        $s = $this->db->prepare("DELETE FROM avis WHERE id_avis=?");
        return $s->execute([$id]);
    }
}