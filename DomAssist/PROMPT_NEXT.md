# PROMPT_NEXT.md - Contexte de Reprise DomAssist

Ce fichier permet a une autre IA de reprendre le travail immediatement.
Derniere mise a jour : 2026-08-07 20:53

---

## ETAT ACTUEL : PROJET STABLE

La phase de stabilisation est TERMINEE.
Tous les bugs critiques ont ete corriges.
Le cycle de vie complet V3 Marketplace fonctionne de bout en bout (17/17 tests PASS).

---

## Infrastructure

- Projet : /home/xyra/Dom/DomAssist
- Langage : PHP 8, PostgreSQL, MVC natif
- Serveur dev : php -S 127.0.0.1:8000 (lancer depuis /home/xyra/Dom/DomAssist)
- DB : pgsql:host=localhost;dbname=domassist user=xyra password=a
- Admin : admin@domassist.com / Admin1234

---

## Bugs Corriges (tous corriges)

| ID | Fichier | Description |
|----|---------|-------------|
| BUG-001 | DomAssist.sql + DB | Hash bcrypt admin invalide -> corrige |
| BUG-002 | DomAssist.sql + DB | compute_demande_expiration TIMESTAMPTZ -> corrige |
| BUG-003 | models/Prestataire.php | Cast JSONB/Boolean manquants -> corrige |
| BUG-004 | controllers_api/DemandeApiController.php | Match expressions PL/pgSQL -> corrige |
| BUG-005 | models/Avis.php | lastId() manquante -> corrige |
| BUG-006 | controllers_api/AvisApiController.php | Cle reponse normalisee -> corrige |

---

## Tests Effectues et Valides

- Full lifecycle API (17/17 PASS) : Admin -> Register -> Candidature -> Demande -> Proposition -> Selection -> Engagement -> Diagnostic -> Solution -> Validation -> Intervention -> Avis -> Reponse
- Routes HTML admin (13/13 PASS)
- Routes HTML client (11/11 PASS)
- Routes HTML prestataire (8/8 PASS)
- Securite et permissions (7/7 PASS)
- Syntaxe PHP : 0 erreur sur 49 fichiers

---

## Script de Test de Non-Regression

/home/xyra/.gemini/antigravity-cli/brain/389ddc41-75e3-44e7-a399-1d000f7b4e2d/scratch/full_lifecycle_test.php
Commande : php full_lifecycle_test.php (depuis /home/xyra/Dom/DomAssist)

---

## Fonctions PL/pgSQL (signatures exactes)

- compute_demande_expiration(INT, TIMESTAMPTZ) -> TIMESTAMPTZ
- demandes_eligibles(INT) -> TABLE
- envoyer_proposition(id_demande, id_profile, message, prix, delai) -> 'ok'|'introuvable'|'mauvais_statut'|'prestataire_non_valide'|'deja_proposee'
- selectionner_prestataire(id_demande, id_user, id_proposition) -> 'ok'|'introuvable'|'non_autorise'|'mauvais_statut'|'proposition_introuvable'|'proposition_non_disponible'
- confirmer_engagement(id_demande, id_profile) -> 'ok'|'introuvable'|'mauvais_statut'|'non_autorise'
- desister_prestataire(id_demande, id_profile) -> 'ok'|'introuvable'|'mauvais_statut'|'non_autorise'

---

## Prochaines Actions Possibles

Le projet est stable. Les developpements peuvent reprendre selon NEXT_PHASE_PLAN.md.

Avant de reprendre, verifier manuellement dans un navigateur :
1. Upload de fichier (avatar profil, CV prestataire)
2. Messagerie en temps reel
3. Affichage des notifications in-app

---

## Fichiers de Suivi

- CHANGELOG.md : historique des modifications
- BUG_ANALYSIS.md : analyse complete des bugs (BUG-001 a BUG-006)
- TEST_PROGRESS.md : resultats de tous les tests
- NEXT_PHASE_PLAN.md : plan de developpement futur
