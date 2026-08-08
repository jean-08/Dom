# BUG_ANALYSIS.md — Rapport d'Audit Complet et Diagnostic de DomAssist

> **Date d'audit initial** : 6 Août 2026
> **Date de stabilisation** : 7 Août 2026
> **Rôle** : Tech Lead / QA Engineer / Software Architect
> **Objectif** : Stabilisation complète du projet DomAssist avant toute reprise des développements.
> **État actuel** : ✅ **CYCLE DE VIE COMPLET FONCTIONNEL** — 15/15 étapes PASS — BUG-001 à BUG-006 corrigés.

---

### Résumé des bugs identifiés et corrigés (session 2026-08-07)

| ID | Gravité | Fichier | Description | Statut |
|----|---------|---------|-------------|--------|
| BUG-001 | 🔴 Critique | `DomAssist.sql` / DB | Hash bcrypt admin `$2a$` invalide → connexion impossible | ✅ Corrigé |
| BUG-002 | 🔴 Critique | `DomAssist.sql` / DB | `compute_demande_expiration` signature TIMESTAMP vs TIMESTAMPTZ | ✅ Corrigé |
| BUG-003 | 🔴 Critique | `models/Prestataire.php` | Cast JSONB/Boolean manquants → INSERT candidature échoue | ✅ Corrigé |
| BUG-004 | 🟠 Majeur | `controllers_api/DemandeApiController.php` | Match expressions décalées vs PL/pgSQL | ✅ Corrigé |
| BUG-005 | 🟠 Majeur | `models/Avis.php` | `lastId()` manquante → Fatal Error 500 sur `avis_create` | ✅ Corrigé |
| BUG-006 | 🟡 Moyen | `controllers_api/AvisApiController.php` | Clé `reponse_prestataire` vs `reponse` → HTTP 422 | ✅ Corrigé |

---


## 1. Phase 1 — Synthèse de l'Analyse Architecture & Code Source

Le projet **DomAssist** est structuré autour d'une architecture MVC en PHP 8 native avec une persistance PostgreSQL et un découplage entre les vues HTML (`index.php`) et l'API JSON Restful (`api.php`).

### Architecture globale
* **Front Controller HTML** : `index.php` s'appuie sur une session PHP classique (`$_SESSION['user']`) et dispatche vers 16 contrôleurs situés dans `controllers/`.
* **Front Controller API** : `api.php` gère l'authentification découpée via des tokens Bearer cryptographiques stockés dans la table `session_token` et dispatche vers 14 contrôleurs dans `controllers_api/`.
* **Persistance** : `config/database.php` implémente le pattern Singleton instanciant PDO PostgreSQL (`pgsql:host=localhost;dbname=domassist`).
* **Vues** : Templating PHP natif avec Bootstrap 5.3 dans `views/`, organisé par entités métier (`admin/`, `auth/`, `avis/`, `demande/`, `diagnostic/`, `disponibilite/`, `intervention/`, `prestataire/`, `produit/`, `service/`, `solution/`, `user/`).

### Graphe des Dépendances inter-modules
```
                   ┌────────────────────────┐
                   │     "user" (Table)     │
                   └───────────┬────────────┘
                               │
            ┌──────────────────┼──────────────────┐
            ▼                  ▼                  ▼
  ┌──────────────────┐ ┌───────────────┐ ┌─────────────────────┐
  │  session_token   │ │ prestataire_  │ │       demande       │
  └──────────────────┘ │    profile    │ └──────────┬──────────┘
                       └───────┬───────┘            │
                               │                    ▼
                               ▼             ┌───────────────┐
                       ┌───────────────┐     │  demande_     │
                       │  competence   │     │  media/event  │
                       └───────┬───────┘     └──────┬────────┘
                               │                    │
                               ▼                    ▼
                       ┌───────────────┐     ┌───────────────┐
                       │    service_   │     │  proposition  │
                       │   category    │     └──────┬────────┘
                       └───────────────┘            │
                                                    ▼
                                             ┌───────────────┐
                                             │  diagnostic   │
                                             └──────┬────────┘
                                                    │
                                                    ▼
                                             ┌───────────────┐
                                             │   solution    │
                                             └──────┬────────┘
                                                    │
                                                    ▼
                                             ┌───────────────┐
                                             │ intervention  │
                                             └──────┬────────┘
                                                    │
                                                    ▼
                                             ┌───────────────┐
                                             │     avis      │
                                             └───────────────┘
```

---

## 2. Phase 2 — Bilan de Vérification Fonctionnelle

| Module / Fonctionnalité | État | Constat & Dysfonctionnements majeurs |
|---|---|---|
| **Connexion / Inscription** | OK | Authentification HTML et API JSON opérationnelles pour clients et prestataires. |
| **Tableau de bord** | OK | Widgets d'action fonctionnels selon le rôle et les statuts V3. |
| **Création & Consultation de demande** | OK | Formulaire complet V3 (titre, urgence, budget, ville, photos). |
| **Candidature Prestataire** | PARTIEL | Enregistrement OK, mais l'upload de CV rencontre un problème de chemin statique. |
| **Profil Prestataire (Public)** | BUGGUÉ | Absence de vue `views/prestataire/show.php` dédiée (chargement fallback de `edit.php`). |
| **Diagnostic Post-Engagement** | OK | Transaction et fonctions SQL respectées. |
| **Solution & Matériel Chantier** | **CRITIQUE** | **Crash SQL PHP (PDOException)** : tables `produit` et `utiliser` absentes du schéma BDD. |
| **Intervention (Planification / Clôture)** | OK | Workflow `planifiee` → `en_cours` → `terminee` fonctionnel. |
| **Avis & Réputation** | OK | Dépôt unique d'avis et droit de réponse prestataire fonctionnels. |
| **Modification Profil & Mots de Passe** | **CRITIQUE** | Le changement de mot de passe administrateur échoue systématiquement (`User::find()` sans champ `mot_de_passe`). |
| **Upload de Fichiers (Photos/CV/Avatars)** | BUGGUÉ | Incohérence des chemins relatifs (`uploads/` vs `public/uploads/`) générant des erreurs HTTP 404 sur le navigateur. |
| **Centre de Notifications** | PARTIEL | Les notifications sont écrites en BDD mais aucun badge dynamique ni dropdown n'apparaît dans la Navbar. |

---

## 3. Phase 3 & 4 — Diagnostic Base de Données et Schéma PostgreSQL

L'inspection directe de la base de données PostgreSQL `domassist` révèle **14 tables existantes** :
`avis`, `competence`, `demande`, `demande_event`, `demande_media`, `diagnostic`, `disponibilite`, `intervention`, `prestataire_profile`, `proposition`, `service_category`, `session_token`, `solution`, `"user"`.

### Anomalies de Schéma BDD identifiées :
1. **Absence totale des tables Produit / Matériel** : Les tables `produit` (catalogue matériel) et `utiliser` (liaison N-N entre `solution` et `produit`) sont référencées dans les modèles PHP (`models/Produit.php`, `models/Solution.php`), mais sont **totalement absentes** de `DomAssist.sql` et de la base de données PostgreSQL.
2. **Décalage de colonnes entre la Façade `Prestataire` et `PrestataireProfile`** :
   - `Prestataire.php` mappe `id_prestataire` vers `id_profile` et `statut_validation` vers `statut`.
   - Certaines requêtes directes en SQL oublient l'un ou l'autre alias, ce qui peut générer des erreurs `Undefined array key` selon l'ordre d'appel des méthodes.

---

## 4. Phase 5 — Grille d'Audit des Endpoints API JSON

| Endpoint API | Méthode | Auth | Statut | Remarques / Erreurs |
|---|---|---|---|---|
| `/api.php?action=login` | POST | Publique | **Fonctionnel** | Reçoit JSON, renvoie Bearer Token 64 char. |
| `/api.php?action=register` | POST | Publique | **Fonctionnel** | Inscription client uniquement. |
| `/api.php?action=logout` | POST | Bearer | **Fonctionnel** | Révocation du token en BDD. |
| `/api.php?action=me` | GET | Bearer | **Fonctionnel** | Retourne l'utilisateur connecté. |
| `/api.php?action=prestataire_candidater` | POST | Bearer | **Fonctionnel** | Soumission du dossier prestataire V3. |
| `/api.php?action=prestataire_mon_statut` | GET | Bearer | **Fonctionnel** | Statut du dossier prestataire. |
| `/api.php?action=prestataire_en_attente` | GET | Admin | **Fonctionnel** | File d'attente de modération admin. |
| `/api.php?action=prestataire_valider` | POST | Admin | **Fonctionnel** | Validation candidature. |
| `/api.php?action=prestataire_rejeter` | POST | Admin | **Fonctionnel** | Rejet avec motif. |
| `/api.php?action=service_list` | GET | Publique | **Fonctionnel** | Catalogue des catégories de service. |
| `/api.php?action=demande_disponibles` | GET | Bearer | **Fonctionnel** | Demandes éligibles au matching prestataires. |
| `/api.php?action=demande_create` | POST | Bearer | **Fonctionnel** | Publication d'une demande V3. |
| `/api.php?action=demande_proposer` | POST | Bearer | **Fonctionnel** | Proposition tarifaire/délai d'un prestataire. |
| `/api.php?action=demande_selectionner` | POST | Bearer | **Fonctionnel** | Sélection du prestataire par le client. |
| `/api.php?action=demande_confirmer_engagement`| POST| Bearer | **Fonctionnel** | Confirmation d'engagement prestataire. |
| `/api.php?action=diagnostic_proposer` | POST | Bearer | **Fonctionnel** | Enregistrement du diagnostic technique. |
| `/api.php?action=solution_proposer` | POST | Bearer | **CRASH (500)** | **Échec** si des produits sont rattachés (Table `utiliser` introuvable en BDD). |
| `/api.php?action=solution_valider` | POST | Bearer | **Fonctionnel** | Validation de la solution par le client. |
| `/api.php?action=intervention_demarrer` | POST | Bearer | **Fonctionnel** | Démarrage d'intervention post-validation. |
| `/api.php?action=intervention_terminer` | POST | Bearer | **Fonctionnel** | Clôture d'intervention. |
| `/api.php?action=avis_create` | POST | Bearer | **Fonctionnel** | Dépôt d'un avis client post-intervention. |
| `/api.php?action=avis_repondre` | POST | Bearer | **Fonctionnel** | Droit de réponse prestataire. |

---

## 5. Phase 6 — Registre Détaillé des Bugs (BUG-001 à BUG-010)

### BUG-001
* **Priorité** : **CRITIQUE**
* **Description** : Crash SQL (Erreur 500 / `PDOException: relation "produit" does not exist`) lors de l'accès au catalogue produits ou lors de la création d'une solution avec matériel.
* **Étapes pour reproduire** :
  1. Se connecter en tant qu'administrateur ou prestataire.
  2. Naviguer vers `index.php?action=produits` ou valider une solution dans `index.php?action=solution_create&id_diagnostic=N`.
  3. Observation : Erreur bloquante `PDOException: SQLSTATE[42P01]: Undefined table: 7 ERROR: relation "produit" does not exist`.
* **Fichiers concernés** :
  - [`models/Produit.php`](file:///home/xyra/Dom/DomAssist/models/Produit.php)
  - [`models/Solution.php`](file:///home/xyra/Dom/DomAssist/models/Solution.php) (Ligne 32)
  - [`controllers/ProduitController.php`](file:///home/xyra/Dom/DomAssist/controllers/ProduitController.php)
  - [`controllers/SolutionController.php`](file:///home/xyra/Dom/DomAssist/controllers/SolutionController.php)
  - [`DomAssist.sql`](file:///home/xyra/Dom/DomAssist/DomAssist.sql)
* **Cause probable** : Les tables `produit` et `utiliser` ont été omises dans la création initiale du fichier de migration `DomAssist.sql`.
* **Impact** : Impossibilité d'utiliser le module de matériel et de finaliser des solutions nécessitant des produits.
* **Proposition de correction** : Ajouter les requêtes `CREATE TABLE IF NOT EXISTS produit (...)` et `CREATE TABLE IF NOT EXISTS utiliser (...)` dans `DomAssist.sql` et exécuter le script SQL sur PostgreSQL.

---

### BUG-002
* **Priorité** : **CRITIQUE**
* **Description** : Impossibilité de modifier le mot de passe administrateur dans l'espace d'administration.
* **Étapes pour reproduire** :
  1. Se connecter avec le compte administrateur `admin@domassist.com`.
  2. Aller sur `index.php?action=admin_profile`.
  3. Saisir l'ancien mot de passe, le nouveau mot de passe et soumettre le formulaire.
  4. Observation : Un message d'erreur "Ancien mot de passe incorrect" s'affiche systématiquement.
* **Fichiers concernés** :
  - [`controllers/AdminController.php`](file:///home/xyra/Dom/DomAssist/controllers/AdminController.php) (Lignes 103 & 121)
  - [`models/User.php`](file:///home/xyra/Dom/DomAssist/models/User.php) (Ligne 18)
* **Cause probable** : `AdminController::profile()` fait appel à `$this->user->find($id)`. Pour des raisons de sécurité, `User::find()` exclut le champ `mot_de_passe` du `SELECT`. Lors de l'exécution de `password_verify($ancien_mdp, $admin['mot_de_passe'])`, `$admin['mot_de_passe']` vaut `null`, entraînant l'échec systématique de la vérification.
* **Impact** : Bloque la sécurisation du compte administrateur.
* **Proposition de correction** : Dans `AdminController.php`, remplacer la méthode `$this->user->find($id)` par `$this->user->findWithPassword($id)` lors du traitement du changement de mot de passe.

---

### BUG-003
* **Priorité** : **MAJEUR**
* **Description** : Absence de fichier de vue dédié pour la consultation publique des profils prestataires (`views/prestataire/show.php`).
* **Étapes pour reproduire** :
  1. Se connecter en client ou visiteur.
  2. Cliquer sur la fiche d'un prestataire depuis la liste ou une demande (`index.php?action=prestataire_show&id=N`).
  3. Observation : Le contrôleur charge `views/prestataire/edit.php` et utilise un contournement conditionnel (`$isPublicProfile`). Cela mélange la vue d'édition admin et la vue publique client.
* **Fichiers concernés** :
  - [`controllers/PrestataireController.php`](file:///home/xyra/Dom/DomAssist/controllers/PrestataireController.php) (Ligne 130)
  - [`views/prestataire/edit.php`](file:///home/xyra/Dom/DomAssist/views/prestataire/edit.php)
* **Cause probable** : La vue publique n'a pas été isolée dans son propre fichier lors du refactoring V3.
* **Impact** : Risque d'incohérence visuelle et d'exposition de champs de modification réservés aux administrateurs.
* **Proposition de correction** : Créer le fichier `views/prestataire/show.php` propre à la consultation publique et mettre à jour `PrestataireController::show()` pour le charger.

---

### BUG-004
* **Priorité** : **MAJEUR**
* **Description** : Erreur 404 sur le chargement des images uploadées (avatars utilisateurs, photos de demandes, CVs prestataires).
* **Étapes pour reproduire** :
  1. Publier une demande avec une photo d'illustration ou modifier la photo de profil utilisateur.
  2. Naviguer sur la fiche de la demande (`index.php?action=demande_show&id=N`).
  3. Observation : L'image apparaît brisée sur le navigateur. La console réseau (Network) indique une erreur HTTP 404 pour l'URL `uploads/demandes/demande_...jpg`.
* **Fichiers concernés** :
  - [`utils/upload.php`](file:///home/xyra/Dom/DomAssist/utils/upload.php) (Lignes 34 & 43)
  - [`controllers/UserController.php`](file:///home/xyra/Dom/DomAssist/controllers/UserController.php)
  - [`controllers/DemandeController.php`](file:///home/xyra/Dom/DomAssist/controllers/DemandeController.php)
* **Cause probable** : `UploadHelper::uploadImage()` enregistre les fichiers sous `public/uploads/...` sur le disque mais retourne la chaîne `'uploads/...'` sans le préfixe `public/`. Si le serveur web s'exécute avec pour racine le dossier du projet, le lien relatif relatif pointe vers une adresse inexistante.
* **Impact** : Rupture visuelle majeure sur les fiches de demandes et les profils utilisateurs.
* **Proposition de correction** : Normaliser la génération du chemin relatif dans `UploadHelper` pour renvoyer `public/uploads/...` (ou ajuster le routage d'accès aux assets statiques).

---

### BUG-005
* **Priorité** : **MAJEUR**
* **Description** : Absence de badge d'indicateur de notifications non lues dans la barre de navigation (Navbar).
* **Étapes pour reproduire** :
  1. Se connecter avec le compte d'un utilisateur destinataire d'une notification (ex: client lorsqu'une proposition arrive).
  2. Observer la barre de navigation en haut de l'écran.
  3. Observation : La cloche de notification ne signale pas le nombre de notifications non lues.
* **Fichiers concernés** :
  - [`views/components/navbar.php`](file:///home/xyra/Dom/DomAssist/views/components/navbar.php)
  - [`models/Notification.php`](file:///home/xyra/Dom/DomAssist/models/Notification.php)
  - [`controllers/NotificationController.php`](file:///home/xyra/Dom/DomAssist/controllers/NotificationController.php)
* **Cause probable** : Le composant `navbar.php` n'interroge pas le modèle `Notification::countUnread()` pour afficher le badge dynamique.
* **Impact** : L'utilisateur n'est pas informé en temps réel des avancées de ses demandes (propositions, validations, etc.).
* **Proposition de correction** : Appeler `Notification::countUnread()` dans le composant `navbar.php` et afficher un badge Bootstrap visuel.

---

### BUG-006
* **Priorité** : **MOYEN**
* **Description** : Informations de suspension insuffisantes sur la page dédiée `/compte-suspendu`.
* **Étapes pour reproduire** :
  1. Suspendre un compte utilisateur depuis l'espace admin avec un motif explicite.
  2. Se connecter avec ce compte suspendu.
  3. Observation : L'utilisateur est bien redirigé sur `index.php?action=compte_suspendu`, mais la page affiche un message générique sans indiquer le motif ni la date de fin de suspension.
* **Fichiers concernés** :
  - [`views/auth/suspended.php`](file:///home/xyra/Dom/DomAssist/views/auth/suspended.php)
  - [`controllers/AuthController.php`](file:///home/xyra/Dom/DomAssist/controllers/AuthController.php)
* **Cause probable** : Les variables `$user['motif_suspension']` et `$user['date_fin_suspension']` ne sont pas injectées dans la vue par `AuthController::suspended()`.
* **Impact** : Expérience utilisateur dégradée pour les comptes modérés.
* **Proposition de correction** : Récupérer les informations complètes du compte suspendu via `User::find()` et les transmettre à `views/auth/suspended.php`.

---

### BUG-007
* **Priorité** : **MOYEN**
* **Description** : Incohérence de traitement du fichier CV lors de la candidature prestataire.
* **Étapes pour reproduire** :
  1. Soumettre une candidature prestataire avec un fichier CV.
  2. Observation : `PrestataireController::candidater()` gère l'upload manuellement sans passer par `UploadHelper`, effectuant des validations d'extension simplifiées et réitérant le problème de chemin d'accès d'asset (BUG-004).
* **Fichiers concernés** :
  - [`controllers/PrestataireController.php`](file:///home/xyra/Dom/DomAssist/controllers/PrestataireController.php) (Lignes 53-76)
  - [`utils/upload.php`](file:///home/xyra/Dom/DomAssist/utils/upload.php)
* **Cause probable** : Code d'upload du CV dupliqué et non harmonisé avec le helper global.
* **Impact** : Maintenance complexe et vulnérabilité potentielle sur la validation de fichiers documentaires.
* **Proposition de correction** : Étendre `UploadHelper` pour gérer les documents PDF et l'utiliser dans `PrestataireController`.

---

### BUG-008
* **Priorité** : **MINEUR**
* **Description** : Incohérence des clés de tableaux associatifs entre la Façade `Prestataire` et le Modèle natif `PrestataireProfile`.
* **Étapes pour reproduire** :
  1. Appeler tour à tour `$prestataire->findByUser()` et `$prestataireProfile->findByUser()`.
  2. Comparer les structures renvoyées.
  3. Observation : L'un renvoie la clé `id_prestataire` et l'autre `id_profile`, ce qui peut provoquer des alertes PHP Notices si une vue bascule d'un modèle à l'autre.
* **Fichiers concernés** :
  - [`models/Prestataire.php`](file:///home/xyra/Dom/DomAssist/models/Prestataire.php)
  - [`models/PrestataireProfile.php`](file:///home/xyra/Dom/DomAssist/models/PrestataireProfile.php)
* **Cause probable** : Rétrocompatibilité incomplète lors du passage du modèle V1 au schéma V3.
* **Impact** : Risque mineur de régression lors des manipulations de données prestataires.
* **Proposition de correction** : Ajouter systématiquement les alias `id_profile` et `id_prestataire` dans les deux modèles.

---

### BUG-009
* **Priorité** : **MOYEN**
* **Description** : Configuration CORS permissive sur `api.php` risquant des blocages navigateur en mode authentifié.
* **Étapes pour reproduire** :
  1. Lancer des requêtes HTTP API depuis une origine cliente distincte avec l'en-tête `Authorization: Bearer <token>`.
  2. Observation : L'en-tête `Access-Control-Allow-Origin: *` est retourné, ce qui est incompatible avec `Access-Control-Allow-Credentials: true` sur certains navigateurs stricts.
* **Fichiers concernés** :
  - [`api.php`](file:///home/xyra/Dom/DomAssist/api.php) (Lignes 12-14)
* **Cause probable** : Configuration CORS générique de développement.
* **Impact** : Problèmes d'intégration avec des clients web externes découplés.
* **Proposition de correction** : Paramétrer le header d'origine dynamiquement ou via une variable de configuration.

---

### BUG-010
* **Priorité** : **MINEUR**
* **Description** : Absence d'intégration de la pagination sur les tables d'administration à fort volume.
* **Étapes pour reproduire** :
  1. Se connecter en administrateur et aller sur la liste des utilisateurs (`index.php?action=admin_users`).
  2. Observation : Tous les utilisateurs sont affichés sur une seule page sans pagination, bien qu'un composant réutilisable `views/components/pagination.php` existe.
* **Fichiers concernés** :
  - [`controllers/AdminController.php`](file:///home/xyra/Dom/DomAssist/controllers/AdminController.php)
  - [`views/admin/users.php`](file:///home/xyra/Dom/DomAssist/views/admin/users.php)
* **Cause probable** : Le composant de pagination n'a pas été raccordé au contrôleur d'administration.
* **Impact** : Baisse de lisibilité et de performance quand la base de données grandit.
* **Proposition de correction** : Intégrer les paramètres `page` et `limit` dans `AdminController::users()` et inclure `views/components/pagination.php`.

---

## 6. Phase 7 — Plan de Correction Séquentiel & Stratégie de Stabilisation

Pour garantir une stabilisation sans régression, les corrections devront être appliquées selon l'ordre strict de dépendance ci-dessous, en traitant prioritairement la persistance BDD et les failles bloquantes.

### Ordre d'exécution préconisé :

```
┌────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 1 (BDD) : BUG-001 — Restauration tables produit & utiliser      │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 2 (SÉCURITÉ) : BUG-002 — Correction changement mot de passe admin│
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 3 (STORAGE) : BUG-004 & BUG-007 — Unification Upload & Assets   │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 4 (VUES) : BUG-003 — Création de la vue publique show.php        │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 5 (UX/NOTIFS) : BUG-005 & BUG-006 — Dynamic Navbar & Suspend View│
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│ ÉTAPE 6 (POLISH & API) : BUG-008, BUG-009, BUG-010 — Aliases, CORS, Paging│
└────────────────────────────────────────────────────────────────────────┘
```

### Justification de la stratégie :
1. **Étape 1 (Base de données)** : Doit impérativement intervenir en premier car sans les tables `produit` et `utiliser`, plusieurs fonctionnalités et endpoints API échouent brutalement sur des exceptions PDO non rattrapées.
2. **Étape 2 (Sécurité & Administration)** : Corrige immédiatement l'impossibilité pour l'administrateur de gérer son propre mot de passe.
3. **Étape 3 (Gestion des fichiers)** : Règle définitivement la rupture visuelle des images et documents uploadés sur l'ensemble de l'application.
4. **Étapes 4 & 5 (Expérience Utilisateur & Vues)** : Stabilisent le rendu des profils et le système d'information de l'utilisateur (notifications, suspensions).
5. **Étape 6 (Finitions API & Ergonomie)** : Assure la propreté du code, l'unification des modèles et l'ergonomie globale du système.

---

> **STATUT AUDIT** : Terminé.  
> Aucun fichier source du projet n'a été altéré durant cette phase de diagnostic.  
> Le projet est désormais prêt pour la phase de correction séquentielle bug par bug.
