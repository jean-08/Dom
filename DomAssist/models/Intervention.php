<?php
require_once __DIR__ . '/../config/database.php';
class Intervention {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function all(): array {
        return $this->db->query("SELECT i.*,u.nom,u.prenom FROM intervention i JOIN prestataire p ON i.id_prestataire=p.id_prestataire JOIN user u ON p.id_user=u.id_user ORDER BY i.date DESC")->fetchAll();
    }
    public function byPrestataire(int $id): array {
        $s = $this->db->prepare("SELECT * FROM intervention WHERE id_prestataire=? ORDER BY date DESC");
        $s->execute([$id]); return $s->fetchAll();
    }
    public function create(array $d): bool {
        $s = $this->db->prepare("INSERT INTO intervention (resultat,date,id_prestataire,id_demande,id_dispo) VALUES (?,?,?,?,?)");
        return $s->execute([$d['resultat'],date('Y-m-d'),$d['id_prestataire'],$d['id_demande'],$d['id_dispo']??null]);
    }
    public function update(int $id, string $resultat): bool {
        $s = $this->db->prepare("UPDATE intervention SET resultat=? WHERE id_intervention=?");
        return $s->execute([$resultat,$id]);
    }
}