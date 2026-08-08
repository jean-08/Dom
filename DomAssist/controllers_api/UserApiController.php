<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/SessionToken.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/ApiAuth.php';
require_once __DIR__ . '/ApiRequest.php';

/**
 * Gestion des comptes utilisateurs — Sprint 6 (supervision admin).
 * CDC §4.3 : "Suspendre un compte en cas d'abus."
 */
class UserApiController
{
    private User $user;
    private SessionToken $tokens;

    public function __construct()
    {
        $this->user   = new User();
        $this->tokens = new SessionToken();
    }

    /** GET ?action=user_list — admin uniquement. */
    public function list(): void
    {
        ApiAuth::requireAdmin();
        ApiResponse::success(['users' => $this->user->all()]);
    }

    /** POST ?action=user_suspendre  { id_user, motif? } */
    public function suspendre(): void
    {
        $admin = ApiAuth::requireAdmin();

        $d = ApiRequest::body();
        $id    = (int) ($d['id_user'] ?? 0);
        $motif = trim($d['motif'] ?? '');

        if (!$id) {
            ApiResponse::error('id_user requis.', 422);
        }
        if ($id === (int) $admin['id_user']) {
            ApiResponse::error('Vous ne pouvez pas suspendre votre propre compte.', 409);
        }
        if (!$this->user->find($id)) {
            ApiResponse::error('Utilisateur introuvable.', 404);
        }

        $this->user->suspendre($id, $motif);
        $this->tokens->revoquerTousPourUser($id); // déconnexion forcée immédiate (point b)

        ApiResponse::success(['message' => 'Compte suspendu.']);
    }

    /** POST ?action=user_reactiver  { id_user } */
    public function reactiver(): void
    {
        ApiAuth::requireAdmin();

        $id = (int) (ApiRequest::body()['id_user'] ?? 0);
        if (!$id) {
            ApiResponse::error('id_user requis.', 422);
        }
        if (!$this->user->find($id)) {
            ApiResponse::error('Utilisateur introuvable.', 404);
        }

        $this->user->reactiver($id);
        ApiResponse::success(['message' => 'Compte réactivé.']);
    }

    /** POST/PUT ?action=user_profile_update  { nom?, prenom?, email?, email_secondaire?, telephone?, adresse_rue?, ville?, bio?, photo_url? } */
    public function updateProfile(): void
    {
        $user = ApiAuth::requireAuth();
        $id_user = (int) $user['id_user'];
        $d = ApiRequest::body();

        $this->user->update($id_user, [
            'nom'              => trim($d['nom'] ?? '') ?: null,
            'prenom'           => trim($d['prenom'] ?? '') ?: null,
            'email'            => trim($d['email'] ?? '') ?: null,
            'email_secondaire' => trim($d['email_secondaire'] ?? '') ?: null,
            'telephone'        => trim($d['telephone'] ?? '') ?: null,
            'adresse_rue'      => trim($d['adresse_rue'] ?? '') ?: null,
            'ville'            => trim($d['ville'] ?? '') ?: null,
            'bio'              => trim($d['bio'] ?? '') ?: null,
            'photo_url'        => trim($d['photo_url'] ?? '') ?: null,
        ]);

        ApiResponse::success(['message' => 'Profil mis à jour avec succès.', 'user' => $this->user->find($id_user)]);
    }

    /** POST ?action=user_change_password { ancien_mot_de_passe, nouveau_mot_de_passe } */
    public function changePassword(): void
    {
        $user = ApiAuth::requireAuth();
        $id_user = (int) $user['id_user'];
        $d = ApiRequest::body();

        $ancien  = $d['ancien_mot_de_passe'] ?? '';
        $nouveau = $d['nouveau_mot_de_passe'] ?? '';

        if ($ancien === '' || $nouveau === '') {
            ApiResponse::error('Ancien et nouveau mot de passe sont requis.', 422);
        }

        if (mb_strlen($nouveau) < 8) {
            ApiResponse::error('Le nouveau mot de passe doit comporter au moins 8 caractères.', 422);
        }

        $userDb = $this->user->findWithPassword($id_user);
        if (!$userDb || !password_verify($ancien, $userDb['mot_de_passe'])) {
            ApiResponse::error('Ancien mot de passe incorrect.', 403);
        }

        $this->user->updatePassword($id_user, $nouveau);
        ApiResponse::success(['message' => 'Mot de passe modifié avec succès.']);
    }
}