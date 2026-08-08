# PROJECT_STATE.md — État Réel du Projet DomAssist (Audit V3)

> **Document de référence pour la prochaine phase de développement.**
> Date de l'audit : 6 Août 2026
> Référence métier officielle : `rapport.txt`

---

## 1. Architecture

### Organisation des dossiers

```
DomAssist/
├── config/
│   └── database.php                # Connexion Singleton PDO à PostgreSQL
├── models/                         # 15 Modèles (Façades V3 & Modèles Natifs V3)
│   ├── User.php                    # Gestion des comptes utilisateurs & suspensions
│   ├── SessionToken.php            # Tokens d'authentification API (Bearer)
│   ├── PrestataireProfile.php      # Profil natif V3 (bio, expérience, zone, statut)
│   ├── Prestataire.php             # Façade de rétrocompatibilité V3 vers prestataire_profile
│   ├── ServiceCategory.php         # Catalogue natif V3 des catégories de services
│   ├── Service.php                 # Façade de rétrocompatibilité V3 vers service_category
│   ├── Demande.php                 # Demande V3 (propositions, statuts, matching)
│   ├── Proposition.php             # Offre d'un prestataire sur une demande (Prix, Délais, Statuts)
│   ├── Diagnostic.php              # Constat technique post-engagement
│   ├── Solution.php                # Proposition de solution & validation client
│   ├── Intervention.php            # Planification et exécution d'intervention
│   ├── Avis.php                    # Évaluation post-intervention et réputation
│   ├── Disponibilite.php           # Plages de disponibilité prestataire
│   ├── Produit.php                 # Catalogue/Stock de matériel chantiers
│   └── Notification.php           # Modèle de notification in-app (non raccordé en BDD)
├── controllers/                    # 13 Contrôleurs HTML (Application Web)
│   ├── AuthController.php          # Inscription, connexion, déconnexion HTML
│   ├── DashboardController.php     # Tableau de bord unifié Client / Prestataire
│   ├── AdminController.php         # Administration (users, validations, suivis)
│   ├── DemandeController.php       # Workflow HTML des demandes (création, propositions, choix)
│   ├── PrestataireController.php   # Candidature, profil et compétences HTML
│   ├── DiagnosticController.php    # Saisie et affichage diagnostic HTML
│   ├── SolutionController.php      # Saisie et validation de solution HTML
│   ├── InterventionController.php  # Démarrage et clôture d'intervention HTML
│   ├── AvisController.php          # Dépôt et suivi réputation HTML
│   ├── DisponibiliteController.php # Gestion des créneaux horaires HTML
│   ├── ServiceController.php       # Administration des catégories de service HTML
│   ├── ProduitController.php       # Catalogue produits HTML
│   └── UserController.php          # Gestion du profil et des utilisateurs HTML
├── controllers_api/                # 12 Contrôleurs API JSON (Client mobile / decoupled UI)
│   ├── ApiAuth.php                 # Middleware de vérification du Token Bearer API
│   ├── ApiRequest.php              # Helper d'extraction du body JSON
│   ├── ApiResponse.php             # Helper de réponse uniforme JSON (success/error)
│   ├── AuthApiController.php       # endpoints /login, /register, /me, /logout
│   ├── DemandeApiController.php    # CRUD & transitions V3 (proposer, selectionner, etc.)
│   ├── PrestataireApiController.php# Candidature, statut, compétences V3
│   ├── DiagnosticApiController.php # Saisie & consultation API
│   ├── SolutionApiController.php   # Proposition & validation API
│   ├── InterventionApiController.php# Démarrage & clôture API
│   ├── AvisApiController.php       # Création & consultation API
│   ├── ServiceApiController.php    # Catalogue API
│   └── UserApiController.php       # Gestion utilisateur & suspension API
├── views/                          # Vues HTML (Layouts Bootstrap 5 & Partials)
│   ├── admin/                      # Vues spécifiques administration
│   ├── auth/                       # Formulaires login / register
│   ├── avis/                       # Formulaires avis & réputation
│   ├── components/                 # Composants réutilisables (navbar, sidebar, modal...)
│   ├── demande/                    # Fiches, listes et création de demandes
│   ├── diagnostic/                 # Saisie et affichage diagnostics
│   ├── disponibilite/              # Calendrier et gestion des créneaux
│   ├── errors/                     # Pages 404 & 500
│   ├── intervention/               # Liste et démarrage d'interventions
│   ├── layouts/                    # Header et Footer Bootstrap
│   ├── prestataire/                # Fiches profils et formulaire candidature
│   ├── produit/                    # Catalogue matériel
│   ├── service/                    # Catégories de services
│   ├── solution/                   # Saisie de solution
│   ├── user/                       # Édition du profil
│   └── dashboard.php               # Tableau de bord principal
├── public/                         # Assets statiques (CSS, JS, images)
├── utils/                          # Fonctions utilitaires
├── index.php                       # Routeur principal HTML
├── api.php                         # Routeur principal API JSON
├── DomAssist.sql                   # Schéma de base de données PostgreSQL V3 complet
├── CHANGELOG.md                    # Historique des migrations et étapes 1 à 13
├── PROMPT_NEXT.md                  # Registre d'état du workflow de développement
└── rapport.txt                     # Document d'architecture métier cible (Référence)
```

### Modules

1. **Authentification & Session** (Dual HTML Session & API Bearer Token)
2. **Gestion Utilisateurs & Modération** (Roles `client`/`admin`, Suspension & Motif)
3. **Candidature & Profils Prestataires** (Cycle `soumise` → `validee`/`rejetee`, Bio, Expérience, Zone)
4. **Catalogue de Services** (`service_category` seedé, compétences prestataires)
5. **Workflow Demandes V3** (Publication, Propositions concurrentielles, Sélection client, Engagement)
6. **Réalisation Post-Engagement** (Diagnostic → Solution & Matériel → Intervention → Avis & Réputation)
7. **Produits & Matériel Chantier** (Catalogue de produits pour les solutions)

### Dépendances

- **Langage** : PHP 8.1+
- **Base de données** : PostgreSQL (Extension `pdo_pgsql`)
- **Frontend HTML** : Vanilla CSS, Bootstrap 5.3, Bootstrap Icons
- **Sécurité** : `password_hash()` (BCRYPT), Tokens aléatoires cryptographiques (64 char hex), PDO Prepared Statements (0 injection SQL).

---

## 2. Fonctionnalités

| Fonctionnalité | État | Commentaires |
|---|---|---|
| Inscription & Connexion Utilisateur | **Terminée** | Fonctionnel en HTML et API JSON. |
| Authentification API (Token Bearer) | **Terminée** | Gestion des expirations et révocations via `session_token`. |
| Modification du Profil Utilisateur | **Terminée** | Nom, prénom, ville, téléphone, photo_url. |
| Compte Suspendu (Admin & Blocage) | **Partiellement terminée** | Statut `suspendu` géré en BDD et contrôleur ; écran dédié `/compte-suspendu` utilisateur à faire. |
| Candidature Prestataire (Bio, Expérience, Zone) | **Terminée** | Inscription enrichie V3 avec `experience_annees` et `zone_intervention`. |
| Validation / Rejet Candidature (Admin) | **Terminée** | Validation et motif de rejet gérés avec modales Bootstrap. |
| Gestion des Compétences Prestataire | **Terminée** | Liaison N-N entre `prestataire_profile` et `service_category`. |
| Catalogue des Catégories de Services | **Terminée** | 10 catégories initiales seedées, désactivation logique. |
| Publication de Demande Riche V3 | **Terminée** | Titre, catégorie, urgence, budget min/max, ville, créneaux. |
| Photos attachées à une demande | **Partiellement terminée** | Table `demande_media` présente en BDD ; upload HTML à finaliser. |
| Matching & Demandes Éligibles | **Terminée** | Fonction SQL PL/pgSQL `demandes_eligibles()` (compétences + statut `validee`). |
| Multi-propositions Prestataires | **Terminée** | Fonction SQL PL/pgSQL `envoyer_proposition()` (prix, délai, message). |
| Choix Prestataire par le Client | **Terminée** | Fonction SQL PL/pgSQL `selectionner_prestataire()` (décline les autres offres). |
| Confirmation d'Engagement Prestataire | **Terminée** | Fonction SQL PL/pgSQL `confirmer_engagement()`. |
| Désistement Prestataire | **Terminée** | Fonction SQL PL/pgSQL `desister_prestataire()`. |
| Diagnostic Technique Post-Engagement | **Terminée** | Saisie et consultation par le prestataire retenu. |
| Proposition & Validation Solution | **Terminée** | Validation client et gestion du matériel utilisé. |
| Intervention (Planification, Démarrage, Clôture)| **Terminée** | Statuts `planifiee`, `en_cours`, `terminee`. |
| Dépôt d'Avis & Calcul de Réputation | **Terminée** | Avis par intervention terminée ; note moyenne prestataire. |
| Dashboard Orienté Actions (Client / Prestataire) | **Terminée** | Visualisation et badges d'actions selon les statuts V3. |
| Dashboard & Suivis Admin | **Terminée** | Suivi demandes, interventions, prestataires en attente, utilisateurs. |
| Messagerie de discussion par demande | **Non commencée** | Identifiée dans `rapport.txt` §10 ; tables et UI à créer. |
| Centre de Notifications in-app | **Partiellement terminée** | Modèle `Notification.php` codé ; table BDD et UI non câblées. |
| Marketplace de Produits entre Particuliers | **Non commencée** | Identifiée dans `rapport.txt` §7 ; distinct des produits chantiers. |
| Signalements & Modération de Contenus | **Non commencée** | Identifiée dans `rapport.txt` §8 ; tables et UI à créer. |

---

## 3. Workflow métier

Le workflow réellement implémenté dans le code et la base de données est le **Workflow Marketplace V3** complet :

```
             [Client publie une demande riche]
                            │
                            ▼
                         OUVERTE
                            │
                            │  Prestataires éligibles consultent 
                            │  et envoient une PROPOSITION (prix, délai, message)
                            ▼
                      EN_DISCUSSION
                            │
                            │  Le client compare les offres et SÉLECTIONNE un prestataire
                            ▼
                    PRESTATAIRE_CHOISI  (les autres offres passent non_retenues)
                            │
                            │  Le prestataire retenu CONFIRME son engagement
                            ▼
                         ENGAGEE
                            │
                            │  Prestataire enregistre le DIAGNOSTIC
                            ▼
                    DIAGNOSTIC_PROPOSE
                            │
                            │  Prestataire propose une SOLUTION (+ matériel)
                            ▼
                    SOLUTION_PROPOSEE
                            │
                            │  Le client VALIDE la solution
                            ▼
                  INTERVENTION_PLANIFIEE
                            │
                            │  Prestataire DÉMARRE l'intervention
                            ▼
                  INTERVENTION_EN_COURS
                            │
                            │  Prestataire CLÔTURE l'intervention
                            ▼
                        TERMINEE
                            │
                            │  Le client dépose un AVIS
                            ▼
                        CLOTUREE

Statuts transverses gérés dans le code :
• ANNULEE_PAR_CLIENT     : Annulation par le client avant intervention
• ANNULEE_PAR_PRESTATAIRE: Désistement du prestataire après choix
• EXPIREE                : Expiration automatique sans proposition/choix
• SUSPENDUE_MODERATION   : Verrouillage par l'administration
```

---

## 4. API (API JSON Restful / Decoupled)

Toutes les API sont exposées via `api.php?action=<route>` et renvoient une structure JSON standard via `ApiResponse::success()` ou `ApiResponse::error()`.

| Action API | Méthode | Authentification | Rôle requis | État |
|---|---|---|---|---|
| `login` | POST | Publique | Visiteur | **Fonctionnelle** |
| `register` | POST | Publique | Visiteur | **Fonctionnelle** |
| `logout` | POST | Token Bearer | Connecté | **Fonctionnelle** |
| `me` | GET | Token Bearer | Connecté | **Fonctionnelle** |
| `prestataire_candidater` | POST | Token Bearer | Client | **Fonctionnelle** |
| `prestataire_mon_statut` | GET | Token Bearer | Client | **Fonctionnelle** |
| `prestataire_en_attente` | GET | Token Bearer | Admin | **Fonctionnelle** |
| `prestataire_valider` | POST | Token Bearer | Admin | **Fonctionnelle** |
| `prestataire_rejeter` | POST | Token Bearer | Admin | **Fonctionnelle** |
| `prestataire_suspendre` | POST | Token Bearer | Admin | **Fonctionnelle** |
| `prestataire_competences_mes` | GET | Token Bearer | Prestataire | **Fonctionnelle** |
| `prestataire_competences` | GET | Token Bearer | Tous | **Fonctionnelle** |
| `prestataire_competence_ajouter` | POST | Token Bearer | Prestataire | **Fonctionnelle** |
| `prestataire_competence_retirer` | POST | Token Bearer | Prestataire | **Fonctionnelle** |
| `service_list` | GET | Publique | Visiteur / Tous | **Fonctionnelle** |
| `service_show` | GET | Publique | Tous | **Fonctionnelle** |
| `service_create` | POST | Token Bearer | Admin | **Fonctionnelle** |
| `service_update` | PUT | Token Bearer | Admin | **Fonctionnelle** |
| `service_delete` | DELETE | Token Bearer | Admin | **Fonctionnelle** |
| `demande_disponibles` | GET | Token Bearer | Prestataire Validé | **Fonctionnelle** |
| `demande_create` | POST | Token Bearer | Client | **Fonctionnelle** |
| `demande_proposer` | POST | Token Bearer | Prestataire Validé | **Fonctionnelle** |
| `demande_selectionner` | POST | Token Bearer | Client (Auteur) | **Fonctionnelle** |
| `demande_confirmer_engagement` | POST | Token Bearer | Prestataire Retenu | **Fonctionnelle** |
| `demande_desister` | POST | Token Bearer | Prestataire Retenu | **Fonctionnelle** |
| `demande_annuler` | POST | Token Bearer | Client (Auteur) | **Fonctionnelle** |
| `demande_show` | GET | Token Bearer | Tous (Accès filtré) | **Fonctionnelle** |
| `demande_mes` | GET | Token Bearer | Client / Prestataire | **Fonctionnelle** |
| `diagnostic_proposer` | POST | Token Bearer | Prestataire Retenu | **Fonctionnelle** |
| `diagnostic_show` | GET | Token Bearer | Client / Prestataire | **Fonctionnelle** |
| `solution_proposer` | POST | Token Bearer | Prestataire Retenu | **Fonctionnelle** |
| `solution_show` | GET | Token Bearer | Client / Prestataire | **Fonctionnelle** |
| `solution_valider` | POST | Token Bearer | Client (Auteur) | **Fonctionnelle** |
| `intervention_demarrer` | POST | Token Bearer | Prestataire Retenu | **Fonctionnelle** |
| `intervention_terminer` | POST | Token Bearer | Prestataire Retenu | **Fonctionnelle** |
| `intervention_show` | GET | Token Bearer | Concerné | **Fonctionnelle** |
| `intervention_mes` | GET | Token Bearer | Client / Prestataire | **Fonctionnelle** |
| `avis_create` | POST | Token Bearer | Client | **Fonctionnelle** |
| `avis_prestataire` | GET | Publique | Tous | **Fonctionnelle** |
| `avis_ma_reputation` | GET | Token Bearer | Prestataire Validé | **Fonctionnelle** |
| `user_list` | GET | Token Bearer | Admin | **Fonctionnelle** |
| `user_suspendre` | POST | Token Bearer | Admin | **Fonctionnelle** |
| `user_reactiver` | POST | Token Bearer | Admin | **Fonctionnelle** |

---

## 5. Pages Web

Toutes les vues HTML sont gérées via `index.php?action=<route>` avec layout responsive Bootstrap 5.

| Route Action | Rôle & Description | État |
|---|---|---|
| `login` | Page de connexion HTML | **Fonctionnelle** |
| `register` | Page d'inscription HTML | **Fonctionnelle** |
| `logout` | Action de déconnexion et destruction de session | **Fonctionnelle** |
| `dashboard` | Tableau de bord unifié (Client & Prestataire) | **Fonctionnelle** |
| `admin_dashboard` | Vue synthétique d'administration | **Fonctionnelle** |
| `profile` | Modification du profil utilisateur | **Fonctionnelle** |
| `users` | Liste des utilisateurs (Admin) | **Fonctionnelle** |
| `user_edit` | Édition d'un utilisateur par l'admin | **Fonctionnelle** |
| `user_delete` | Suppression d'un utilisateur | **Fonctionnelle** |
| `prestataires` | Annuaire des prestataires validés | **Fonctionnelle** |
| `prestataire_candidater` | Formulaire de candidature (bio, expérience, zone) | **Fonctionnelle** |
| `prestataire_show` | Fiche détaillée d'un profil prestataire | **Fonctionnelle** |
| `prestataire_edit` | Édition des infos prestataire | **Fonctionnelle** |
| `prestataire_delete` | Suppression de profil prestataire | **Fonctionnelle** |
| `prestataire_add_competence` | Ajout d'une catégorie au profil | **Fonctionnelle** |
| `demandes` | Liste des demandes du client / globales | **Fonctionnelle** |
| `demande_create` | Formulaire de création de demande riche V3 | **Fonctionnelle** |
| `demande_show` | Fiche détaillée demande (offres, timeline, actions) | **Fonctionnelle** |
| `demande_delete` | Suppression d'une demande par l'admin | **Fonctionnelle** |
| `demande_annuler` | Annulation d'une demande par le client | **Fonctionnelle** |
| `demandes_disponibles` | Liste des opportunités pour prestataires | **Fonctionnelle** |
| `demande_proposer` | Modal / Formulaire d'envoi d'offre prestataire | **Fonctionnelle** |
| `demande_selectionner` | Action client de sélection de prestataire | **Fonctionnelle** |
| `demande_confirmer_engagement`| Confirmation de prise en charge par le prestataire | **Fonctionnelle** |
| `demande_desister` | Action de désistement prestataire | **Fonctionnelle** |
| `demande_update_statut` | Force-update de statut (Admin) | **Fonctionnelle** |
| `diagnostic_create` | Formulaire de saisie de diagnostic | **Fonctionnelle** |
| `diagnostic_show` | Consultation du diagnostic | **Fonctionnelle** |
| `solution_create` | Proposition de solution et sélection de matériel | **Fonctionnelle** |
| `solution_valider` | Validation de solution par le client | **Fonctionnelle** |
| `interventions` | Liste des interventions | **Fonctionnelle** |
| `intervention_create` | Planification / Démarrage d'intervention | **Fonctionnelle** |
| `intervention_terminer` | Clôture d'intervention et saisie du résultat | **Fonctionnelle** |
| `disponibilites` | Gestion des plages horaires prestataire | **Fonctionnelle** |
| `disponibilite_create` | Ajout d'un créneau libre | **Fonctionnelle** |
| `disponibilite_delete` | Suppression d'un créneau | **Fonctionnelle** |
| `services` | Gestion admin des catégories de service | **Fonctionnelle** |
| `service_create` | Création d'une catégorie | **Fonctionnelle** |
| `service_delete` | Désactivation logique d'une catégorie | **Fonctionnelle** |
| `produits` | Gestion du catalogue de matériel | **Fonctionnelle** |
| `produit_create` | Ajout de matériel | **Fonctionnelle** |
| `produit_delete` | Suppression de matériel | **Fonctionnelle** |
| `avis_create` | Dépôt d'avis client | **Fonctionnelle** |
| `avis_delete` | Modération d'un avis (Admin) | **Fonctionnelle** |
| `reputation` | Consultation de la note et avis prestataire | **Fonctionnelle** |
| `admin_users` | Modération des comptes et suspensions | **Fonctionnelle** |
| `admin_profile` | Modification mot de passe admin | **Fonctionnelle** |
| `admin_suivi_demandes` | Suivi global des demandes | **Fonctionnelle** |
| `admin_suivi_services` | Suivi global des services | **Fonctionnelle** |
| `admin_suivi_interventions` | Suivi des interventions | **Fonctionnelle** |
| `admin_prestataires_en_attente`| File de validation des candidatures prestataires | **Fonctionnelle** |
| `admin_valider_prestataire` | Validation d'une candidature | **Fonctionnelle** |
| `admin_rejeter_prestataire` | Rejet avec motif | **Fonctionnelle** |
| `admin_suspend_user` | Action de suspension d'utilisateur | **Fonctionnelle** |
| `admin_reactivate_user` | Action de réactivation d'utilisateur | **Fonctionnelle** |

---

## 6. Base de données (Schéma PostgreSQL V3)

Le fichier `DomAssist.sql` contient 12 tables structurées :

1. `"user"` : Comptes utilisateurs (clients & admins), authentification, téléphones, photos et état de suspension (`suspendu`, `motif_suspension`).
2. `session_token` : Sessions API Bearer avec date d'expiration et révocation.
3. `service_category` : Catalogue seedé des 10 catégories de service (remplace la table legacy `service`).
4. `prestataire_profile` : Dossiers prestataires enrichis (bio, expérience, zone JSONB, statut `brouillon`/`soumise`/`validee`/`rejetee`/`suspendue`).
5. `competence` : Table de liaison N-N profil ↔ catégorie de service.
6. `disponibilite` : Créneaux horaires des prestataires (`libre`/`occupe`).
7. `demande` : Entité centrale des besoins clients (14 statuts V3, urgence, budget, ville, `id_profile_retenu`, dates d'expiration).
8. `demande_media` : Stockage des URLs de photos attachées aux demandes.
9. `demande_event` : Journal append-only (historique/timeline d'une demande).
10. `proposition` : Offres financières et délais envoyés par les prestataires sur une demande (1 seule proposition active par prestataire/demande).
11. `diagnostic` : Constat technique établi par le prestataire retenu post-engagement.
12. `solution` : Proposition d'intervention et matériel utilisé.
13. `intervention` : Exécution du service (`planifiee`, `en_cours`, `terminee`).
14. `avis` : Évaluation 1 à 5 étoiles et commentaires laissés par les clients.

---

## 7. Bugs connus & Anomalies constatées

*(Aucun bug bloquant de syntaxe ou de crash SQL ne subsiste suite aux audits 1 à 13)*.

1. **Erreur d'attribut image `260805_18h56m15s_screenshot.png` (Résolu)** :
   - *Problème historique* : L'image montrait l'erreur `relation "prestataire" does not exist`.
   - *Analyse* : Le problème provenait de l'ancienne requête SQL V1 de `Intervention::all()`. Il a été corrigé lors de l'Étape 10 (`JOIN prestataire_profile pp`).
2. **Alertes de suspension utilisateur côté HTML** :
   - Lorsqu'un utilisateur suspendu tente de naviguer sur l'interface Web HTML, il est redirigé vers le dashboard sans vue spécifique expliquant la cause/durée de sa suspension.
3. **Validation des images côté UI** :
   - L'upload direct de fichier photo pour `demande_media` repose actuellement sur des URLs textuelles et non un multipart uploader nativement branché.

---

## 8. Dette technique

1. **Façades de compatibilité (`Prestataire.php` & `Service.php`)** :
   - Ces deux modèles traduisent les anciens noms de colonnes V1 (`id_prestataire`, `statut_validation`, `specialite`, `id_service`) vers les nouvelles structures V3. Ils fonctionnent parfaitement mais gagneraient à être progressivement remplacés par des appels directes à `PrestataireProfile` et `ServiceCategory`.
2. **Modèle `Notification.php` orphelin** :
   - Le fichier `models/Notification.php` contient une logique PHP complète pour les notifications in-app, mais la table `notification` n'a pas été incluse dans le fichier DDL `DomAssist.sql`.
3. **Table legacy `produit` / `utiliser`** :
   - Le catalogue matériel utilisé par la solution s'appuie sur une structure simplifiée.

---

## 9. Différences avec le rapport métier (`rapport.txt`)

| Section du rapport | Rapport Cible (`rapport.txt`) | Implémentation Actuelle | Statut de Conformité |
|---|---|---|---|
| **Partie 3 — Workflow Demande** | Publication → Propositions → Choix → Engagement → Diagnostic → Solution → Intervention → Avis | Entièrement implémenté en SQL V3 et PHP | **100% Conforme** |
| **Partie 4 — Demande Riche** | Titre, Urgence, Budget, Ville, Médias, Timeline | Tous les champs présents en BDD, Formulaires et Vues | **Conforme** (Upload direct médias en V2) |
| **Partie 5 — Profil Prestataire** | Bio, Expérience, Zone d'intervention, Candidature admin | Dossier complet, Statuts `soumise`/`validee`/`rejetee` | **100% Conforme** |
| **Partie 6 — Catégories** | Seed de 10 catégories, désactivation admin | Table `service_category` seedée et gérée | **100% Conforme** |
| **Partie 7 — Marketplace Produits** | Vente d'outillages/matériel entre particuliers | Module `Produit` limité au matériel chantiers pour `Solution` | **Partiellement Conforme** (Marketplace C2C à créer) |
| **Partie 8 — Signalements** | Modération de contenus/utilisateurs abusifs | Non implémenté dans le code actuel | **Non commencé** |
| **Partie 9 — Écran Suspension** | Page dédiée compte suspendu avec motif & contact | Suspension BDD active ; écran dédié à créer | **Partiellement Conforme** |
| **Partie 10 — Messagerie** | Fil de discussion texte rattaché à la demande | Non implémenté dans le code actuel | **Non commencé** |
| **Partie 11 — Notifications** | Notifications in-app et emails transactionnels | Modèle PHP existant, table BDD et UI non câblées | **Partiellement Conforme** |
| **Partie 12 — Dashboards** | Files de travail orientées actions | Dashboard unifié V3 avec alertes et actions directes | **100% Conforme** |

---

## 10. Recommandations pour la prochaine phase de développement

Pour poursuivre le développement du projet DomAssist dans un ordre métier et technique logique, nous recommandons le plan d'action suivant :

### Phase A — Finalisation des fonctionnalités transverses de confiance (Haute Priorité)
1. **Implémentation de l'Écran Compte Suspendu (Rapport §9)** :
   - Créer la vue `views/auth/suspended.php` affichant le motif et la date de fin de suspension.
   - Ajouter un middleware/filtre dans `index.php` et `ApiAuth.php` redirigeant automatiquement les comptes suspendus vers cet écran.
2. **Intégration de la Messagerie par Demande (Rapport §10)** :
   - Créer les tables SQL `message_thread` et `message`.
   - Permettre au client et aux prestataires ayant fait une proposition d'échanger des messages texte avant et après sélection.

### Phase B — Expérience Utilisateur & Médias (Moyenne Priorité)
3. **Upload de Photos pour Demandes et Profils (Rapport §4 & §5)** :
   - Brancher l'upload réel de fichiers (images JPG/PNG) vers `public/uploads/` pour les demandes (`demande_media`) et les photos de profil.
4. **Activation du Centre de Notifications (Rapport §11)** :
   - Ajouter la table `notification` dans `DomAssist.sql`.
   - Brancher le déclenchement de notifications lors de la réception d'une proposition, sélection ou validation.

### Phase C — Modules Optionnels & Marketplace (Basse Priorité)
5. **Module de Signalement et Modération (Rapport §8)** :
   - Créer la table `signalement` et les écrans admin associés.
6. **Marketplace de Produits entre Particuliers (Rapport §7)** :
   - Créer les entités et vues d'annonces de matériel d'occasion entre utilisateurs.
