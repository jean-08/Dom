<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function all(): array {
        return $this->db->query("
            SELECT id_user, nom, prenom, email, email_secondaire, role, telephone, photo_url, avatar_type,
                   bio, adresse_rue, ville, suspendu, date_suspension, date_fin_suspension, motif_suspension, created_at
            FROM \"user\" ORDER BY created_at DESC
        ")->fetchAll();
    }

    public function find(int $id): array|false {
        $s = $this->db->prepare("
            SELECT id_user, nom, prenom, email, email_secondaire, role, telephone, photo_url, avatar_type,
                   bio, adresse_rue, ville, suspendu, date_suspension, date_fin_suspension, motif_suspension, created_at
            FROM \"user\" WHERE id_user = ?
        ");
        $s->execute([$id]);
        return $s->fetch();
    }

    /** Inclut mot_de_passe (pour vérification auth uniquement). */
    public function findByEmail(string $email): array|false {
        $s = $this->db->prepare("SELECT * FROM \"user\" WHERE email = ?");
        $s->execute([$email]);
        return $s->fetch();
    }

    public function findWithPassword(int $id): array|false {
        $s = $this->db->prepare("SELECT * FROM \"user\" WHERE id_user = ?");
        $s->execute([$id]);
        return $s->fetch();
    }

    public function create(array $d): bool {
        $mdp_hash = password_hash($d['mot_de_passe'], PASSWORD_BCRYPT);
        $s = $this->db->prepare("
            INSERT INTO \"user\" (nom, prenom, email, role, mot_de_passe, telephone, ville)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $s->execute([
            $d['nom'], $d['prenom'], $d['email'],
            $d['role'] ?? 'client',
            $mdp_hash,
            $d['telephone'] ?? null,
            $d['ville'] ?? null,
        ]);
    }

    public function update(int $id, array $d): bool {
        $s = $this->db->prepare("
            UPDATE \"user\"
            SET nom = COALESCE(?, nom),
                prenom = COALESCE(?, prenom),
                email = COALESCE(?, email),
                email_secondaire = COALESCE(?, email_secondaire),
                telephone = COALESCE(?, telephone),
                adresse_rue = COALESCE(?, adresse_rue),
                ville = COALESCE(?, ville),
                bio = COALESCE(?, bio),
                avatar_type = COALESCE(?, avatar_type),
                photo_url = COALESCE(?, photo_url)
            WHERE id_user = ?
        ");
        return $s->execute([
            $d['nom'] ?? null,
            $d['prenom'] ?? null,
            $d['email'] ?? null,
            $d['email_secondaire'] ?? null,
            $d['telephone'] ?? null,
            $d['adresse_rue'] ?? null,
            $d['ville'] ?? null,
            $d['bio'] ?? null,
            $d['avatar_type'] ?? null,
            $d['photo_url'] ?? null,
            $id,
        ]);
    }

    public function updatePhoto(int $id, string $photo_url): bool {
        $s = $this->db->prepare("UPDATE \"user\" SET photo_url = ? WHERE id_user = ?");
        return $s->execute([$photo_url, $id]);
    }

    public function updatePassword(int $id, string $nouveau_mdp): bool {
        $s = $this->db->prepare("UPDATE \"user\" SET mot_de_passe = ? WHERE id_user = ?");
        return $s->execute([password_hash($nouveau_mdp, PASSWORD_BCRYPT), $id]);
    }

    public function delete(int $id): bool {
        $s = $this->db->prepare("DELETE FROM \"user\" WHERE id_user = ?");
        return $s->execute([$id]);
    }

    public function lastId(): int { return (int)$this->db->lastInsertId(); }

    /** Suspension réversible avec motif et date de fin optionnelle. */
    public function suspendre(int $id, string $motif, ?string $date_fin = null): bool {
        $s = $this->db->prepare("
            UPDATE \"user\"
            SET suspendu = true, date_suspension = now(),
                date_fin_suspension = ?::timestamp, motif_suspension = ?
            WHERE id_user = ?
        ");
        return $s->execute([$date_fin, $motif !== '' ? $motif : null, $id]);
    }

    /** Réactivation d'un compte suspendu. */
    public function reactiver(int $id): bool {
        $s = $this->db->prepare("
            UPDATE \"user\"
            SET suspendu = false, date_suspension = null,
                date_fin_suspension = null, motif_suspension = null
            WHERE id_user = ?
        ");
        return $s->execute([$id]);
    }

    /** Tous les utilisateurs suspendus (pour le dashboard admin). */
    public function allSuspendus(): array {
        return $this->db->query("
            SELECT id_user, nom, prenom, email, role, date_suspension, date_fin_suspension, motif_suspension
            FROM \"user\" WHERE suspendu = true ORDER BY date_suspension DESC
        ")->fetchAll();
    }
}
