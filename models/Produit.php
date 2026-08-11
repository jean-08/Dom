<?php
require_once __DIR__ . '/../config/database.php';
class Produit {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function all(): array {
        return $this->db->query("SELECT * FROM produits")->fetchAll();
    }
    public function find(int $id): array|false {
        $s = $this->db->prepare("SELECT * FROM produits WHERE id_produit=?");
        $s->execute([$id]); return $s->fetch();
    }
    public function create(array $d): bool {
        $s = $this->db->prepare("INSERT INTO produits (nom,prix,stock,statut) VALUES (?,?,?,?)");
        return $s->execute([$d['nom'],$d['prix'],$d['stock'],'disponible']);
    }
    public function updateStock(int $id, int $stock): bool {
        $statut = $stock > 0 ? 'disponible' : 'rupture';
        $s = $this->db->prepare("UPDATE produits SET stock=?,statut=? WHERE id_produit=?");
        return $s->execute([$stock,$statut,$id]);
    }
    public function delete(int $id): bool {
        $s = $this->db->prepare("DELETE FROM produits WHERE id_produit=?");
        return $s->execute([$id]);
    }
}