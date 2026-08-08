<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/SessionToken.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/ApiRequest.php';
require_once __DIR__ . '/ApiAuth.php';

class AuthApiController
{
    private User $user;
    private SessionToken $tokens;

    public function __construct()
    {
        $this->user   = new User();
        $this->tokens = new SessionToken();
    }

    public function login(): void
        {
            $d     = ApiRequest::body();
            $email = trim($d['email'] ?? '');
            $mdp   = $d['mot_de_passe'] ?? '';

            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $mdp === '') {
                ApiResponse::error('Email et mot de passe requis.', 422);
            }

            $u = $this->user->findByEmail($email);
            if (!$u || !password_verify($mdp, $u['mot_de_passe'])) {
                ApiResponse::error('Identifiants incorrects.', 401);
            }

            if (!empty($u['suspendu'])) {
                ApiResponse::error('Ce compte est suspendu. Contactez un administrateur.', 403);
            }

            $token = $this->tokens->creer((int) $u['id_user']);
            ApiResponse::success([
                'token' => $token,
                'user'  => [
                    'id_user' => (int) $u['id_user'],
                    'nom'     => $u['nom'],
                    'prenom'  => $u['prenom'],
                    'email'   => $u['email'],
                    'role'    => $u['role'],
                ],
            ]);
        }

    public function register(): void
    {
        $d = ApiRequest::body();
        $data = [
            'nom'          => trim($d['nom'] ?? ''),
            'prenom'       => trim($d['prenom'] ?? ''),
            'email'        => trim($d['email'] ?? ''),
            'role'         => 'client',   // TOUJOURS forcé
            'mot_de_passe' => $d['mot_de_passe'] ?? '',
        ];

        if ($data['nom'] === '' || $data['prenom'] === '' || $data['email'] === '' || $data['mot_de_passe'] === '') {
            ApiResponse::error('Tous les champs sont requis.', 422);
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            ApiResponse::error('Email invalide.', 422);
        }
        if (strlen($data['mot_de_passe']) < 8) {
            ApiResponse::error('Le mot de passe doit contenir au moins 8 caractères.', 422);
        }
        if ($this->user->findByEmail($data['email'])) {
            ApiResponse::error('Email déjà utilisé.', 409);
        }

        $this->user->create($data);
        $u = $this->user->findByEmail($data['email']);
        $token = $this->tokens->creer((int) $u['id_user']);

        ApiResponse::success([
            'token' => $token,
            'user'  => [
                'id_user' => (int) $u['id_user'],
                'nom'     => $u['nom'],
                'prenom'  => $u['prenom'],
                'email'   => $u['email'],
                'role'    => $u['role'],
            ],
        ], 201);
    }

    public function logout(): void
    {
        $token = ApiAuth::extraireToken();
        if ($token) {
            $this->tokens->revoquer($token);
        }
        ApiResponse::success(['message' => 'Déconnecté.']);
    }

    /** NOUVEAU — endpoint protégé de test */
    public function me(): void
    {
        $user = ApiAuth::requireAuth();
        ApiResponse::success(['user' => $user]);
    }
}