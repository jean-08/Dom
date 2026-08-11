<?php
require_once __DIR__ . '/../models/SessionToken.php';
require_once __DIR__ . '/ApiResponse.php';

class ApiAuth
{
    public static function extraireToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? (function_exists('getallheaders') ? (getallheaders()['Authorization'] ?? null) : null);

        if (!$header || !preg_match('/Bearer\s+(.+)/i', $header, $m)) {
            return null;
        }
        return trim($m[1]);
    }

    public static function requireAuth(): array
    {
        $token = self::extraireToken();
        if (!$token) {
            ApiResponse::error('Authentification requise.', 401);
        }
        $user = (new SessionToken())->verifier($token);
        if (!$user) {
            ApiResponse::error('Token invalide ou expiré.', 401);
        }
        if (!empty($user['suspendu'])) {
            ApiResponse::error(
                'Votre compte est suspendu. Motif : ' . ($user['motif_suspension'] ?? 'non spécifié'),
                403,
                ['suspendu' => true, 'motif' => $user['motif_suspension'] ?? null]
            );
        }
        return $user;
    }

    /** À utiliser en tête des actions réservées à l'administrateur. */
    public static function requireAdmin(): array
    {
        $user = self::requireAuth();
        if ($user['role'] !== 'admin') {
            ApiResponse::error('Accès réservé aux administrateurs.', 403);
        }
        return $user;
    }
}