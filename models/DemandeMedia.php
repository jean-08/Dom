<?php
require_once __DIR__ . '/../config/database.php';

class DemandeMedia {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    /** Ajouter une photo ou document à une demande. */
    public function add(int $id_demande, string $url, string $type = 'image', int $ordre = 0): bool {
        $s = $this->db->prepare("
            INSERT INTO demande_media (id_demande, type, url, ordre)
            VALUES (?, ?, ?, ?)
        ");
        return $s->execute([$id_demande, $type, $url, $ordre]);
    }

    /** Obtenir tous les médias d'une demande. */
    public function byDemande(int $id_demande): array {
        $s = $this->db->prepare("
            SELECT * FROM demande_media
            WHERE id_demande = ?
            ORDER BY ordre ASC, created_at ASC
        ");
        $s->execute([$id_demande]);
        return $s->fetchAll();
    }

    /** Supprimer un média. */
    public function delete(int $id_media): bool {
        $s = $this->db->prepare("DELETE FROM demande_media WHERE id_media = ?");
        return $s->execute([$id_media]);
    }

    /** Ajouter un lot de médias (ex: photos uploadées lors de la création). */
    public function addBatch(int $id_demande, array $urls, string $type = 'image'): int {
        $added = 0;
        foreach ($urls as $index => $url) {
            if (!empty($url) && $this->add($id_demande, $url, $type, $index)) {
                $added++;
            }
        }
        return $added;
    }
}
