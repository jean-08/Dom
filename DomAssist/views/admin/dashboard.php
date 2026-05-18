<?php require __DIR__ . '/../layout/header.php'; ?>

<style>
  .admin-tabs {
    display: flex !important;
    gap: 10px !important;
    margin: 0 0 20px 0 !important;
    padding: 0 !important;
    border-bottom: 3px solid #ddd !important;
    flex-wrap: wrap !important;
    background: transparent !important;
  }
  
  .admin-tab-btn {
    padding: 12px 20px !important;
    background: #f0f0f0 !important;
    border: none !important;
    cursor: pointer !important;
    border-radius: 4px 4px 0 0 !important;
    font-weight: 500 !important;
    transition: all 0.3s !important;
    color: #333 !important;
    font-size: 14px !important;
    margin: 0 !important;
    display: inline-block !important;
  }
  
  .admin-tab-btn:hover {
    background: #e0e0e0 !important;
  }
  
  .admin-tab-btn.active {
    background: #667eea !important;
    color: white !important;
    border-bottom: 3px solid #667eea !important;
  }
  
  .admin-tab-content {
    display: none !important;
    padding: 20px 0 !important;
    margin: 0 !important;
  }
  
  .admin-tab-content.active {
    display: block !important;
  }
  
  .admin-table {
    width: 100% !important;
    border-collapse: collapse !important;
    margin: 20px 0 !important;
    background: white !important;
  }
  
  .admin-table th {
    background: #667eea !important;
    color: white !important;
    padding: 12px !important;
    text-align: left !important;
    font-weight: 600 !important;
  }
  
  .admin-table td {
    padding: 12px !important;
    border-bottom: 1px solid #ddd !important;
  }
  
  .admin-table tr:hover {
    background: #f9f9f9 !important;
  }
</style>

<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 10px; margin-bottom: 30px; color: white;">
  <h1 style="margin: 0;">Tableau de Bord Admin</h1>
  <p style="margin: 5px 0 0 0; opacity: 0.9;">Bienvenue, <?= htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom']) ?></p>
</div>

<!-- Cartes de résumé -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
  <div style="background: #f0f4ff; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea;">
    <h3 style="margin: 0 0 10px 0; color: #667eea;">Utilisateurs</h3>
    <p style="margin: 0; font-size: 28px; font-weight: bold; color: #333;"><?= $totalUsers ?></p>
  </div>

  <div style="background: #fff4f0; padding: 20px; border-radius: 8px; border-left: 4px solid #ff6b6b;">
    <h3 style="margin: 0 0 10px 0; color: #ff6b6b;">Demandes</h3>
    <p style="margin: 0; font-size: 28px; font-weight: bold; color: #333;"><?= $totalDemandes ?></p>
  </div>

  <div style="background: #f0fff4; padding: 20px; border-radius: 8px; border-left: 4px solid #51cf66;">
    <h3 style="margin: 0 0 10px 0; color: #51cf66;">Services</h3>
    <p style="margin: 0; font-size: 28px; font-weight: bold; color: #333;"><?= $totalServices ?></p>
  </div>

  <div style="background: #fffef0; padding: 20px; border-radius: 8px; border-left: 4px solid #fcc419;">
    <h3 style="margin: 0 0 10px 0; color: #fcc419;">Interventions</h3>
    <p style="margin: 0; font-size: 28px; font-weight: bold; color: #333;"><?= $totalInterventions ?></p>
  </div>
</div>

<!-- Onglets de gestion -->
<div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
  
  <div class="admin-tabs">
    <button class="admin-tab-btn active" onclick="switchTab(event, 'users')">👥 Utilisateurs</button>
    <button class="admin-tab-btn" onclick="switchTab(event, 'demandes')">📋 Demandes</button>
    <button class="admin-tab-btn" onclick="switchTab(event, 'services')">⚙️ Services</button>
    <button class="admin-tab-btn" onclick="switchTab(event, 'interventions')">🔧 Interventions</button>
    <button class="admin-tab-btn" onclick="switchTab(event, 'profile')">🔐 Mon Profil</button>
  </div>

  <!-- Onglet Utilisateurs -->
  <div id="users" class="admin-tab-content active">
    <h2>Gestion des Utilisateurs</h2>
    <table class="admin-table">
      <tr>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Email</th>
        <th>Rôle</th>
        <th>Actions</th>
      </tr>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['nom']) ?></td>
          <td><?= htmlspecialchars($u['prenom']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['role']) ?></td>
          <td>
            <?php if ((int)$u['id_user'] !== (int)($_SESSION['user']['id_user'] ?? 0)): ?>
              <a href="index.php?action=admin_delete_user&id=<?= (int)$u['id_user'] ?>" onclick="return confirm('Supprimer cet utilisateur ?')" style="color: #ff6b6b; text-decoration: none;">🗑️ Supprimer</a>
            <?php else: ?>
              <span style="color: #999;">Vous</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>

  <!-- Onglet Demandes -->
  <div id="demandes" class="admin-tab-content">
    <h2>Suivi des Demandes</h2>
    <p style="color: #666; margin-bottom: 20px;">Consultation uniquement</p>
    <table class="admin-table">
      <tr>
        <th>ID</th>
        <th>Description</th>
        <th>Date</th>
        <th>Statut</th>
        <th>Adresse</th>
        <th>Utilisateur</th>
      </tr>
      <?php foreach ($demandes as $d): ?>
        <tr>
          <td><?= (int)$d['id_demande'] ?></td>
          <td><?= htmlspecialchars(substr($d['description'], 0, 50)) ?>...</td>
          <td><?= htmlspecialchars($d['date']) ?></td>
          <td><span style="background: #e7f5ff; padding: 5px 10px; border-radius: 3px;"><?= htmlspecialchars($d['statut']) ?></span></td>
          <td><?= htmlspecialchars($d['adresse']) ?></td>
          <td><?= htmlspecialchars($d['nom'] ?? 'N/A') ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>

  <!-- Onglet Services -->
  <div id="services" class="admin-tab-content">
    <h2>Suivi des Services</h2>
    <p style="color: #666; margin-bottom: 20px;">Consultation uniquement</p>
      <table class="admin-table">
        <tr>
          <th>Description</th>
          <th>Date</th>
          <th>Statut</th>
          <th>Adresse</th>
          <th>Utilisateur</th>
        </tr>
        <?php if (empty($demandes)): ?>
          <tr><td colspan="5" style="text-align:center; color:#999; padding:20px;">Aucune demande enregistrée.</td></tr>
        <?php else: ?>
          <?php foreach ($demandes as $d): ?>
            <tr>
              <td><?= htmlspecialchars(substr($d['description'], 0, 50)) ?>...</td>
              <td><?= htmlspecialchars($d['date']) ?></td>
              <td><span style="background: #e7f5ff; padding: 5px 10px; border-radius: 3px;"><?= htmlspecialchars($d['statut']) ?></span></td>
              <td><?= htmlspecialchars($d['adresse']) ?></td>
              <td><?= htmlspecialchars($d['nom'] ?? 'N/A') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </table>
  </div>

  <!-- Onglet Interventions -->
  <div id="interventions" class="admin-tab-content">
    <h2>Suivi des Interventions</h2>
    <p style="color: #666; margin-bottom: 20px;">Consultation uniquement</p>
    <table class="admin-table">
      <tr>
        <th>ID</th>
        <th>Résultat</th>
        <th>Date</th>
        <th>Prestataire</th>
      </tr>
      <?php foreach ($interventions as $i): ?>
        <tr>
          <td><?= (int)$i['id_intervention'] ?></td>
          <td><?= htmlspecialchars(substr($i['resultat'] ?? '', 0, 50)) ?></td>
          <td><?= htmlspecialchars($i['date']) ?></td>
          <td><?= htmlspecialchars(($i['nom'] ?? '') . ' ' . ($i['prenom'] ?? '')) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>

  <!-- Onglet Profil -->
  <div id="profile" class="admin-tab-content">
    <h2>Mon Profil Admin</h2>
    <p style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
      ℹ️ Vous ne pouvez modifier que votre mot de passe.
    </p>

    <div style="max-width: 400px;">
      <h3>Informations personnelles</h3>
      <p><strong>Nom :</strong> <?= htmlspecialchars($_SESSION['user']['nom']) ?></p>
      <p><strong>Prénom :</strong> <?= htmlspecialchars($_SESSION['user']['prenom']) ?></p>
      <p><strong>Email :</strong> <?= htmlspecialchars($_SESSION['user']['email']) ?></p>
      <p><strong>Rôle :</strong> <?= htmlspecialchars($_SESSION['user']['role']) ?></p>

      <hr>

      <h3>Changer le mot de passe</h3>
      
      <form method="POST" action="index.php?action=admin_profile">
        <label>Ancien mot de passe</label>
        <input type="password" name="ancien_mdp" required style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;">

        <label>Nouveau mot de passe</label>
        <input type="password" name="nouveau_mdp" required style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;">

        <label>Confirmer le mot de passe</label>
        <input type="password" name="confirmer_mdp" required style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;">

        <button type="submit" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer;">Modifier le mot de passe</button>
      </form>
    </div>
  </div>

</div>

<script>
  function switchTab(evt, tabName) {
    var i, tabcontent, tabbuttons;
    
    tabcontent = document.getElementsByClassName("admin-tab-content");
    for (i = 0; i < tabcontent.length; i++) {
      tabcontent[i].classList.remove("active");
    }
    
    tabbuttons = document.getElementsByClassName("admin-tab-btn");
    for (i = 0; i < tabbuttons.length; i++) {
      tabbuttons[i].classList.remove("active");
    }
    
    document.getElementById(tabName).classList.add("active");
    evt.currentTarget.classList.add("active");
  }
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
