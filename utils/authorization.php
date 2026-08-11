<?php
/**
 * Authorization utility functions for IDOR protection.
 *
 * Provides simple helpers to ensure the current logged‑in user is the owner of a
 * resource or has admin privileges before performing sensitive actions.
 */

/**
 * Ensure the current session user matches the provided owner ID or is an admin.
 *
 * @param int|null $ownerId The user ID that should own the resource.
 * @param string $redirectAction Action to redirect to on failure.
 */
function requireOwnerOrAdmin(?int $ownerId, string $redirectAction = 'dashboard'): void
{
    $sessionUserId = $_SESSION['user']['id_user'] ?? null;
    $role = $_SESSION['user']['role'] ?? '';
    if ($role === 'admin') {
        return; // admins bypass owner check
    }
    if ($ownerId === null || $sessionUserId === null || $ownerId !== (int)$sessionUserId) {
        $_SESSION['error'] = 'Accès refusé : vous n\'êtes pas le propriétaire de cette ressource.';
        header('Location: index.php?action=' . $redirectAction);
        exit;
    }
}
?>
