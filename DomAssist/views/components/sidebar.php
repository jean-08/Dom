<?php
/**
 * Attend (optionnel) : $active => clé de l'item de menu actif.
 * Utilise $_SESSION['user'] pour adapter le menu au rôle.
 */
$da_user   = $_SESSION['user'] ?? null;
$da_role   = $da_user['role'] ?? 'client';
$da_active = $active ?? '';

function da_nav_item(string $key, string $active, string $action, string $icon, string $label): void {
    $isActive = $key === $active;
    printf(
        '<a class="nav-link%s" href="index.php?action=%s"><i class="bi %s"></i><span>%s</span></a>',
        $isActive ? ' active' : '',
        $action,
        $icon,
        htmlspecialchars($label)
    );
}
?>
<div class="da-sidebar" id="daSidebar">
  <div class="brand">
    <i class="bi bi-house-gear-fill"></i>
    <span>DomAssist</span>
  </div>

  <?php if ($da_role === 'admin'): ?>
    <div class="nav-section-title">Administration</div>
    <?php da_nav_item('admin_dashboard', $da_active, 'admin_dashboard', 'bi-speedometer2', 'Dashboard'); ?>
    <?php da_nav_item('admin_users', $da_active, 'admin_users', 'bi-people-fill', 'Utilisateurs'); ?>
    <?php da_nav_item('admin_prestataires', $da_active, 'admin_prestataires_en_attente', 'bi-person-badge', 'Prestataires'); ?>
    <?php da_nav_item('admin_demandes', $da_active, 'admin_suivi_demandes', 'bi-clipboard2-check', 'Demandes'); ?>
    <?php da_nav_item('admin_services', $da_active, 'services', 'bi-tags-fill', 'Catégories'); ?>
    <?php da_nav_item('admin_interventions', $da_active, 'admin_suivi_interventions', 'bi-tools', 'Interventions'); ?>
    <?php da_nav_item('admin_produits', $da_active, 'produits', 'bi-box-seam', 'Produits'); ?>
    <div class="nav-section-title">Compte</div>
    <?php da_nav_item('admin_profile', $da_active, 'admin_profile', 'bi-gear-fill', 'Paramètres'); ?>
  <?php else: ?>
    <div class="nav-section-title">Espace client</div>
    <?php da_nav_item('dashboard', $da_active, 'dashboard', 'bi-speedometer2', 'Dashboard'); ?>
    <?php da_nav_item('demande_create', $da_active, 'demande_create', 'bi-plus-circle', 'Nouvelle demande'); ?>
    <?php da_nav_item('demandes', $da_active, 'demandes', 'bi-list-check', 'Mes demandes'); ?>
    <?php da_nav_item('prestataires', $da_active, 'prestataires', 'bi-person-lines-fill', 'Prestataires'); ?>

    <?php if (!empty($da_isPrestataire)): ?>
      <div class="nav-section-title">Espace prestataire</div>
      <?php da_nav_item('prestataire_demandes', $da_active, 'demandes_disponibles', 'bi-inboxes', 'Demandes disponibles'); ?>
      <?php da_nav_item('interventions', $da_active, 'interventions', 'bi-tools', 'Mes interventions'); ?>
      <?php da_nav_item('disponibilites', $da_active, 'disponibilites', 'bi-calendar3', 'Disponibilités'); ?>
      <?php da_nav_item('reputation', $da_active, 'reputation', 'bi-star-fill', 'Réputation'); ?>
    <?php else: ?>
      <div class="nav-section-title">Devenir prestataire</div>
      <?php da_nav_item('prestataire_candidater', $da_active, 'prestataire_candidater', 'bi-briefcase-fill', 'Candidater'); ?>
    <?php endif; ?>

    <div class="nav-section-title">Compte</div>
    <?php da_nav_item('profile', $da_active, 'profile', 'bi-person-fill-gear', 'Profil'); ?>
  <?php endif; ?>

  <div class="mt-auto"></div>
  <a class="nav-link" href="index.php?action=logout" data-confirm="Se déconnecter ?">
    <i class="bi bi-box-arrow-right"></i><span>Déconnexion</span>
  </a>
</div>
<div class="da-overlay" id="daOverlay"></div>
