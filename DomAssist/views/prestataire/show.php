<?php
/**
 * Public profile view for a prestataire.
 * This view is displayed to visitors (non‑admin, non‑owner) when they view a prestataire.
 * It expects the following variables from the controller:
 *   - $prestataire: associative array with prestataire details (name, bio, etc.)
 *   - $services: array of services offered by the prestataire
 *   - $avis: array of reviews/avis for the prestataire
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($prestataire['nom'] ?? 'Prestataire') ?> – Profil public</title>
    <link rel="stylesheet" href="/css/main.css" />
    <style>
        body {font-family: 'Inter', sans-serif; background: #f5f7fa; color: #333; margin:0;}
        .container {max-width: 900px; margin: 2rem auto; background: rgba(255,255,255,0.9); padding: 2rem; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.12);}
        h1 {font-size: 2rem; margin-bottom: .5rem;}
        .bio {margin-top: 1rem; line-height: 1.6;}
        .section {margin-top: 2rem;}
        .section h2 {font-size: 1.5rem; border-bottom: 2px solid #e0e0e0; padding-bottom: .3rem;}
        ul {list-style: none; padding:0;}
        li {margin: .5rem 0;}
        .review {background: #fff; padding:1rem; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.05);}
        .review + .review {margin-top:1rem;}
    </style>
</head>
<body>
<div class="container">
    <h1><?= htmlspecialchars($prestataire['nom'] ?? 'Nom inconnu') ?></h1>
    <?php if (!empty($prestataire['bio'])): ?>
        <div class="bio">
            <strong>À propos :</strong>
            <p><?= nl2br(htmlspecialchars($prestataire['bio'])) ?></p>
        </div>
    <?php endif; ?>

    <div class="section">
        <h2>Services proposés</h2>
        <?php if (!empty($services)): ?>
            <ul>
                <?php foreach ($services as $service): ?>
                    <li><?= htmlspecialchars($service['nom'] ?? $service['title'] ?? 'Service') ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucun service répertorié.</p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>Avis des clients</h2>
        <?php if (!empty($avis)): ?>
            <?php foreach ($avis as $a): ?>
                <div class="review">
                    <strong><?= htmlspecialchars($a['author'] ?? 'Client') ?></strong>
                    <p><?= nl2br(htmlspecialchars($a['commentaire'] ?? $a['content'] ?? '')) ?></p>
                    <?php if (!empty($a['note'])): ?>
                        <p>Note : <?= intval($a['note']) ?>/5</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun avis disponible.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
