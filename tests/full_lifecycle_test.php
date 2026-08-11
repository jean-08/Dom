<?php
$baseUrl = 'http://127.0.0.1:8000';

function req($method, $path, $data = null, $token = null) {
    global $baseUrl;
    $ch = curl_init($baseUrl . $path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    $headers = [];
    if ($data !== null) {
        $json = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        $headers[] = 'Content-Type: application/json';
    }
    if ($token !== null) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => json_decode($res, true) ?? $res];
}

$db = new PDO("pgsql:host=localhost;dbname=domassist", "xyra", "a");
$steps = [];

// 1. Admin Login
$res = req('POST', '/api.php?action=login', ['email' => 'admin@domassist.com', 'mot_de_passe' => 'Admin1234']);
$adminToken = $res['body']['token'] ?? null;
$steps[] = ['step' => '1. Admin Login', 'pass' => ($res['code'] === 200 && $adminToken), 'code' => $res['code']];

// 2. Client Register
$clientEmail = 'cl_' . time() . '@test.com';
$res = req('POST', '/api.php?action=register', ['nom'=>'Client','prenom'=>'Alice','email'=>$clientEmail,'mot_de_passe'=>'Client1234','telephone'=>'0611223344','ville'=>'Paris']);
$clientToken = $res['body']['token'] ?? null;
$steps[] = ['step' => '2. Client Register', 'pass' => ($res['code'] === 201 && $clientToken), 'code' => $res['code']];

// 3. Prestataire Register
$prestEmail = 'pr_' . time() . '@test.com';
$res = req('POST', '/api.php?action=register', ['nom'=>'Prestataire','prenom'=>'Bob','email'=>$prestEmail,'mot_de_passe'=>'Prest1234','telephone'=>'0655667788','ville'=>'Paris']);
$prestToken = $res['body']['token'] ?? null;
$steps[] = ['step' => '3. Prestataire Register', 'pass' => ($res['code'] === 201 && $prestToken), 'code' => $res['code']];

// 4. Prestataire Candidater
$res = req('POST', '/api.php?action=prestataire_candidater', [
    'bio' => 'Plombier certifié 8 ans experience.',
    'experience_annees' => 8,
    'zone_intervention' => json_encode(['villes' => ['Paris'], 'rayon_km' => 15]),
    'accepte_urgences' => true
], $prestToken);
$steps[] = ['step' => '4. Prestataire Candidater', 'pass' => ($res['code'] === 201), 'code' => $res['code']];

// 5. Admin Approve Prestataire
$res = req('GET', '/api.php?action=prestataire_en_attente', null, $adminToken);
$pending = $res['body']['candidatures'] ?? [];
$profileId = null;
foreach ($pending as $p) {
    if (($p['email'] ?? '') === $prestEmail) {
        $profileId = $p['id_prestataire'] ?? $p['id_profile'] ?? null;
        break;
    }
}
if ($profileId) {
    $res = req('POST', '/api.php?action=prestataire_valider', ['id_prestataire' => $profileId], $adminToken);
    $steps[] = ['step' => '5. Admin Approve Prestataire', 'pass' => ($res['code'] === 200), 'code' => $res['code']];

    // Prestataire add competence (Plomberie id 1)
    $res = req('POST', '/api.php?action=prestataire_competence_ajouter', ['id_category' => 1], $prestToken);
    $steps[] = ['step' => '5b. Add Competence', 'pass' => ($res['code'] === 200 || $res['code'] === 201), 'code' => $res['code']];
}

// 6. Client Create Demande
$demandeId = null;
if ($clientToken) {
    $res = req('POST', '/api.php?action=demande_create', [
        'titre' => 'Fuite tuyau cuisine',
        'description' => 'Fuite sous évier demandant réparation rapide.',
        'id_category' => 1,
        'urgence' => 'urgent',
        'budget_min' => 60,
        'budget_max' => 120,
        'ville' => 'Paris'
    ], $clientToken);
    $demandeId = $res['body']['id_demande'] ?? null;
    $steps[] = ['step' => '6. Client Create Demande', 'pass' => ($res['code'] === 201 && $demandeId), 'code' => $res['code']];
}

// 7. Prestataire Submit Proposition
if ($prestToken && $demandeId) {
    $res = req('POST', '/api.php?action=demande_proposer', [
        'id_demande' => $demandeId,
        'message' => 'Disponible dans 2 heures.',
        'prix_indicatif' => 90.00,
        'delai_estime' => '2h'
    ], $prestToken);
    
    $stmt = $db->prepare("SELECT id_proposition FROM proposition WHERE id_demande = ? ORDER BY id_proposition DESC LIMIT 1");
    $stmt->execute([$demandeId]);
    $propId = $stmt->fetchColumn();

    $steps[] = ['step' => '7. Prestataire Submit Proposition', 'pass' => ($res['code'] === 201 && $propId), 'code' => $res['code']];
}

// 8. Client Select Prestataire
if ($clientToken && $demandeId && !empty($propId)) {
    $res = req('POST', '/api.php?action=demande_selectionner', [
        'id_demande' => $demandeId,
        'id_proposition' => (int) $propId
    ], $clientToken);
    $steps[] = ['step' => '8. Client Select Prestataire', 'pass' => ($res['code'] === 200), 'code' => $res['code']];
}

// 9. Prestataire Confirm Engagement
if ($prestToken && $demandeId) {
    $res = req('POST', '/api.php?action=demande_confirmer_engagement', ['id_demande' => $demandeId], $prestToken);
    $steps[] = ['step' => '9. Confirm Engagement', 'pass' => ($res['code'] === 200), 'code' => $res['code']];
}

// 10. Diagnostic Proposer
$diagId = null;
if ($prestToken && $demandeId) {
    $res = req('POST', '/api.php?action=diagnostic_proposer', [
        'id_demande' => $demandeId,
        'description' => 'Joint d étanchéité usé à remplacer.',
        'resultat' => 'Remplacement joint nécessaire.'
    ], $prestToken);
    $diagId = $res['body']['id_diagnostic'] ?? null;
    $steps[] = ['step' => '10. Diagnostic Proposer', 'pass' => ($res['code'] === 201 && $diagId), 'code' => $res['code']];
}

// 11. Solution Proposer
$solId = null;
if ($prestToken && $diagId) {
    $res = req('POST', '/api.php?action=solution_proposer', [
        'id_diagnostic' => $diagId,
        'description' => 'Pose d un nouveau joint teflon et serrage.'
    ], $prestToken);
    $solId = $res['body']['id_solution'] ?? null;
    $steps[] = ['step' => '11. Solution Proposer', 'pass' => ($res['code'] === 201 && $solId), 'code' => $res['code']];
}

// 12. Solution Valider by Client
if ($clientToken && $solId) {
    $res = req('POST', '/api.php?action=solution_valider', ['id_solution' => $solId], $clientToken);
    $steps[] = ['step' => '12. Solution Valider', 'pass' => ($res['code'] === 200), 'code' => $res['code']];
}

// 13. Intervention Demarrer & Terminer
$intervId = null;
if ($prestToken && $demandeId) {
    $res = req('POST', '/api.php?action=intervention_demarrer', ['id_demande' => $demandeId], $prestToken);
    $intervId = $res['body']['id_intervention'] ?? null;
    $steps[] = ['step' => '13a. Intervention Demarrer', 'pass' => (($res['code'] === 200 || $res['code'] === 201) && $intervId) ? true : false, 'code' => $res['code']];

    if ($intervId) {
        $res = req('POST', '/api.php?action=intervention_terminer', [
            'id_intervention' => $intervId,
            'resultat' => 'Intervention terminée avec succès, fuite résolue.'
        ], $prestToken);
        $steps[] = ['step' => '13b. Intervention Terminer', 'pass' => ($res['code'] === 200), 'code' => $res['code']];
    }
}

// 14. Client Create Avis
if ($clientToken && $intervId) {
    $res = req('POST', '/api.php?action=avis_create', [
        'id_intervention' => $intervId,
        'note' => 5,
        'comment' => 'Excellent travail, très rapide !'
    ], $clientToken);
    $avisId = $res['body']['id_avis'] ?? null;
    $steps[] = ['step' => '14. Client Create Avis', 'pass' => ($res['code'] === 201 && $avisId), 'code' => $res['code']];

    if ($prestToken && $avisId) {
        $res = req('POST', '/api.php?action=avis_repondre', [
            'id_avis' => $avisId,
            'reponse' => 'Merci beaucoup pour votre confiance !'
        ], $prestToken);
        $steps[] = ['step' => '15. Prestataire Reply Avis', 'pass' => ($res['code'] === 200), 'code' => $res['code']];
    }
}

echo json_encode($steps, JSON_PRETTY_PRINT) . "\n";
