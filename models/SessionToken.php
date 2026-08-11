<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/User.php';

class SessionToken
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function creer(int $idUser): string
    {
        $token = bin2hex(random_bytes(32));
        $stmt = $this->db->prepare("
            INSERT INTO session_token (token, id_user, date_expiration)
            VALUES (:token, :id_user, NOW() + INTERVAL '7 days')
        ");
        $stmt->execute([':token' => $token, ':id_user' => $idUser]);
        return $token;
    }

    public function revoquer(string $token): bool
    {
        $stmt = $this->db->prepare("
            UPDATE session_token SET revoque = TRUE WHERE token = :token
        ");
        return $stmt->execute([':token' => $token]);
    }

    public function trouver(string $token): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM session_token
            WHERE token = :token AND revoque = FALSE AND date_expiration > NOW()
        ");
        $stmt->execute([':token' => $token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /** Utilisé par ApiAuth::requireAuth() */
    public function verifier(string $token): ?array
    {
        $session = $this->trouver($token);
        if (!$session) return null;

        $user = (new User())->find((int) $session['id_user']);
        if (!$user) return null;

        // Garde-fou défense en profondeur : un compte suspendu ne doit jamais
        // passer, même si un token actif a survécu à revoquerTousPourUser().
        if (!empty($user['suspendu'])) {
            return null;
        }

        return [
            'id_user' => (int) $user['id_user'],
            'nom'     => $user['nom'],
            'prenom'  => $user['prenom'],
            'email'   => $user['email'],
            'role'    => $user['role'],
        ];
    }
    /** Révoque tous les tokens actifs d'un user (déconnexion forcée — suspension de compte). */
    public function revoquerTousPourUser(int $idUser): bool
    {
        $stmt = $this->db->prepare("
            UPDATE session_token SET revoque = TRUE
            WHERE id_user = :id_user AND revoque = FALSE
        ");
        return $stmt->execute([':id_user' => $idUser]);
    }
}