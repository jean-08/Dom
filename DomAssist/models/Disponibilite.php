<?php
require_once __DIR__ . '/../config/database.php';
class Disponibilite {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function byPrestataire(int $id): array {
        $s = $this->db->prepare("SELECT * FROM disponibilite WHERE id_prestataire=? ORDER BY date,heure_debut");
        $s->execute([$id]); return $s->fetchAll();
    }
    public function libres(int $id): array {
        $s = $this->db->prepare("SELECT * FROM disponibilite WHERE id_prestataire=? AND statut='libre' ORDER BY date,heure_debut");
        $s->execute([$id]); return $s->fetchAll();
    }
    public function create(array $d): bool {
        $s = $this->db->prepare("INSERT INTO disponibilite (date,heure_debut,heure_fin,statut,id_prestataire) VALUES (?,?,?,'libre',?)");
        return $s->execute([$d['date'],$d['heure_debut'],$d['heure_fin'],$d['id_prestataire']]);
    }
    public function setOccupe(int $id): bool {
        $s = $this->db->prepare("UPDATE disponibilite SET statut='occupé' WHERE id_dispo=?");
        return $s->execute([$id]);
    }
    public function delete(int $id): bool {
        $s = $this->db->prepare("DELETE FROM disponibilite WHERE id_dispo=?");
        return $s->execute([$id]);
    }
}