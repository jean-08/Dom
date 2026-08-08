# NEXT_PHASE_PLAN.md — Plan d'Action & Découpage de la Prochaine Phase DomAssist

> **Document de référence stratégique et technique pour la prochaine phase de développement.**
> Date d'établissement : 6 Août 2026
> Références : `PROJECT_STATE.md`, `rapport.txt`, `CHANGELOG.md`, `PROMPT_NEXT.md`

---

## 1. Vision Globale et Stratégie

Ce document définit le plan d'action exhaustif pour l'évolution de la plateforme **DomAssist**. Conformément aux recommandations de l'audit (`PROJECT_STATE.md`) et à la vision cible métier (`rapport.txt`), cette phase a pour objectif de transformer DomAssist d'un prototype fonctionnel vers une **place de marché de services à domicile hautement professionnelle, moderne, réactive et sécurisée**.

### Principes directeurs de cette phase :
1. **Zéro régression sur le Workflow Marketplace V3** (`OUVERTE` → `PROPOSITIONS` → `CHOIX` → `ENGAGEMENT` → `DIAGNOSTIC` → `SOLUTION` → `INTERVENTION` → `AVIS`).
2. **Découpage atomique** : chaque étape du Sprint Backlog est indépendante et réalisable dans le cadre de la version gratuite d'Antigravity (fenêtres d'exécution courtes, pas de surcharge de contexte).
3. **Qualité visuelle et UX Premium** : abandon des styles par défaut, palette de couleurs moderne, typographies modernes, illustrations SVG/libres de droits et composants interactifs fluides.
4. **Confiance et sécurité** : validation rigoureuse des profils prestataires (CV, Siret, zone), messagerie traçable rattachée aux demandes, centre de notifications in-app et profils publics transparents.

---

## 2. Catalogue Complet des Améliorations & Matrice d'Impact

| ID | Intitulé de l'amélioration | Catégorie | Priorité | Difficulté | Temps estimé |
|---|---|---|---|---|---|
| **IMP-01** | Correction de l'erreur d'envoi de demande | Bugfix / Noyau | **P1 (Critique)** | Faible | 30 min |
| **IMP-02** | Dossier candidature & profil prestataire étendu | Devenir Prestataire | **P2 (Haute)** | Moyenne | 2h30 |
| **IMP-03** | Gestion profil utilisateur complet & sécurité | Profil Utilisateur | **P3 (Haute)** | Faible - Moyenne | 2h00 |
| **IMP-04** | Amélioration du formulaire de demande client | Formulaire Demande | **P4 (Moyenne)** | Moyenne | 2h00 |
| **IMP-05** | Profils publics distincts Client vs Prestataire | Profils Publics | **P5 (Moyenne)** | Moyenne | 2h00 |
| **IMP-06** | Recadrage des avis sur le profil du prestataire | Avis & Réputation | **P6 (Moyenne)** | Faible | 1h30 |
| **IMP-07** | Messagerie privée rattachée à la demande | Communication | **P7 (Haute)** | Élevée | 3h30 |
| **IMP-08** | Centre de notifications in-app multi-événements | Notifications | **P8 (Moyenne)** | Moyenne | 2h30 |
| **IMP-09** | Refonte graphique & modernité de l'interface | UX / Frontend | **P9 (Moyenne)** | Moyenne | 3h00 |

---

## 3. Analyse Détaillée par Amélioration

### IMP-01 : Correction de l'Erreur lors de l'Envoi d'une Demande
* **Description** : Résolution du blocage survenu lors de la création d'une demande. Sécurisation de l'appel `Demande::create()` et de la fonction SQL `compute_demande_expiration()`.
* **Fichiers concernés** :
  - `models/Demande.php`
  - `controllers/DemandeController.php`
  - `controllers_api/DemandeApiController.php`
  - `DomAssist.sql`
* **Modules concernés** : Workflow Demande V3, API & HTML Controllers.
* **Dépendances** : Aucune.
* **Risques de régression** : Très faibles.
* **Difficulté** : Faible | **Temps estimé** : 30 min.

---

### IMP-02 : Candidature & Profil Professionnel Prestataire Etendu ("Devenir Prestataire")
* **Description** : Renforcement du dossier de candidature pour permettre une sélection fine et rigoureuse des prestataires par les clients et l'administrateur.
* **Nouveaux champs proposés** :
  1. `cv_url` : Upload de CV au format PDF obligatoire (stocké dans `public/uploads/cvs/`).
  2. `lettre_motivation` : Description détaillée des compétences et parcours professionnel.
  3. `zone_couverture` : Zone d'intervention structurée (villes, départements, ou rayon en km autour d'une commune).
  4. `disponibilites_type` : Créneaux habituels (Semaine, Soirée, Week-end, 24/7).
  5. `accepte_urgences` : Booléen (`true`/`false`) pour les dépannages sous 48h/urgents.
  6. `moyen_deplacement` : Type de véhicule (Utilitaire, Véhicule léger, Deux-roues, Transports).
  7. `siret` / `assurance_pro` : Informations légales optionnelles/encouragées pour vérification administrative.
  8. `experience_annees` : Nombre d'années de pratique professionnelle.
* **Amélioration du parcours** : Permettre la **resoumission** d'une candidature après un rejet par l'administrateur avec conservation du motif de rejet.
* **Fichiers concernés** :
  - `DomAssist.sql`
  - `models/PrestataireProfile.php`
  - `models/Prestataire.php`
  - `controllers/PrestataireController.php`
  - `controllers_api/PrestataireApiController.php`
  - `views/prestataire/candidater.php`
  - `views/admin/prestataires-en-attente.php`
* **Modules concernés** : Candidature Prestataire, Administration & Validation, API Prestataire.
* **Dépendances** : `User`, `ServiceCategory`.
* **Risques de régression** : Faibles (la façade `Prestataire.php` isole la compatibilité V1/V3).
* **Difficulté** : Moyenne | **Temps estimé** : 2h30.

---

### IMP-03 : Profil Utilisateur Complet & Paramètres de Compte
* **Description** : Permettre aux utilisateurs (clients et prestataires) de maintenir un profil complet, sécurisé et personnalisé.
* **Fonctionnalités** :
  1. **Photo de profil / Avatar** : Upload sécurisé d'images (JPG, PNG, WEBP). Pour éviter les téléchargements d'images sous copyright, mise en place d'un générateur d'avatars libres de droits en fallback (ex: SVG DiceBear / BoringAvatars locaux ou avatars par défaut).
  2. **Bio / Présentation** : Court texte de présentation.
  3. **Téléphone principal & secondaire** : Champ optionnel avec masquage partiel hors engagement.
  4. **Email secondaire / Modifiable** : Modification de l'adresse email principale avec validation du mot de passe actuel.
  5. **Adresse structurée** : Rue, Ville, Code Postal (optionnel).
  6. **Gestion de sécurité** : Modification du mot de passe (Ancien mot de passe + validation BCRYPT du nouveau 8+ caractères).
  7. **Paramètres du compte** : Gestion des préférences et demandes d'export/suppression de données.
* **Fichiers concernés** :
  - `DomAssist.sql`
  - `models/User.php`
  - `controllers/UserController.php`
  - `controllers_api/UserApiController.php`
  - `views/user/profile.php`
  - `utils/upload.php` (nouveau helper d'upload)
* **Modules concernés** : Authentification, Profil Utilisateur.
* **Dépendances** : Middleware Auth & SessionToken.
* **Risques de régression** : Faibles.
* **Difficulté** : Moyenne | **Temps estimé** : 2h00.

---

### IMP-04 : Amélioration du Formulaire de Demande Client
* **Description** : Optimisation de l'ergonomie et de la précision du formulaire de publication d'une demande pour accélérer le matching.
* **Modifications apportées** :
  1. **Code Postal optionnel** : Suppression du blocage strict du code postal ; l'adresse repose sur la `ville` (obligatoire) et l'adresse précise (transmise au prestataire une fois sélectionné).
  2. **Photos d'illustration du problème** : Upload multi-fichiers (1 à 5 photos) liées à `demande_media` pour permettre une meilleure évaluation par les prestataires.
  3. **Budget facultatif** : Champ Min / Max indicatif ou option "Demander estimation".
  4. **Disponibilité client** : Saisie de créneaux préférés (matin, après-midi, week-end, dates spécifiques).
  5. **Niveau d'urgence clair** : Badges `normal`, `sous_48h`, `urgent` (dépannage).
  6. **Précisions d'accès** : Ascenseur, stationnement, étage, présence d'animaux.
* **Fichiers concernés** :
  - `models/Demande.php`
  - `models/DemandeMedia.php`
  - `controllers/DemandeController.php`
  - `controllers_api/DemandeApiController.php`
  - `views/demande/create.php`
  - `views/demande/show.php`
* **Modules concernés** : Publication Demandes, Galerie Médias.
* **Dépendances** : IMP-01.
* **Risques de régression** : Faibles à moyens (vérifier les valeurs par défaut des colonnes BDD).
* **Difficulté** : Moyenne | **Temps estimé** : 2h00.

---

### IMP-05 : Profils Publics Distincts Client vs Prestataire
* **Description** : Mise en place de deux vues publiques adaptées aux rôles pour garantir la transparence et la sécurité.
* **Profil Public Client** (Vue sobre) :
  - Avatar / Photo, Prénom et initiale du Nom.
  - Date d'inscription ("Membre depuis ...").
  - Nombre de demandes publiées et taux de clôture.
  - Protection de la vie privée : téléphone et adresse exacte masqués au public.
* **Profil Public Prestataire** (Vue enrichie) :
  - Photo pro / Badge "Profil Vérifié par Admin".
  - Bio professionnelle & Années d'expérience.
  - Badges des compétences validées (`service_category`).
  - Zone d'intervention et moyen de déplacement.
  - Indication "Accepte les urgences".
  - Note globale de réputation (étoiles sur 5) et nombre d'avis réels.
  - Statistiques d'intervention (interventions réussies, taux de réponse).
  - Bouton d'action directe : *"Inviter sur ma demande"* ou *"Demander une proposition"*.
* **Fichiers concernés** :
  - `controllers/PrestataireController.php`
  - `controllers/UserController.php`
  - `views/prestataire/show.php`
  - `views/user/show_public.php` (nouvelle vue)
* **Modules concernés** : Profils Publics, Annuaire Prestataires.
* **Dépendances** : IMP-02, IMP-06.
* **Risques de régression** : Faibles.
* **Difficulté** : Moyenne | **Temps estimé** : 2h00.

---

### IMP-06 : Recadrage des Avis sur le Profil Prestataire
* **Description** : Revoir la visibilité des avis clients pour une meilleure ergonomie.
* **Impact UX & Architecture** :
  - **Tableau de Bord** (`views/dashboard.php`) : Allégement du dashboard en supprimant la liste exhaustive des avis. Conserver uniquement une carte synthétique récapitulative (Note moyenne, Nombre d'avis, lien direct vers le profil).
  - **Profil Prestataire** (`views/prestataire/show.php`) : Les avis deviennent le cœur de la preuve sociale sur la fiche du prestataire. Affichage détaillé avec note par étoiles, commentaire client, date et catégorie de prestation associée.
  - **Droit de réponse** : Possibilité optionnelle pour le prestataire de répondre une seule fois à un avis reçu.
* **Fichiers concernés** :
  - `views/dashboard.php`
  - `views/prestataire/show.php`
  - `models/Avis.php`
  - `controllers/AvisController.php`
* **Modules concernés** : Avis & Réputation, Profil Prestataire.
* **Dépendances** : IMP-05.
* **Risques de régression** : Très faibles.
* **Difficulté** : Faible | **Temps estimé** : 1h30.

---

### IMP-07 : Communication autour d'une Demande (Messagerie Privée Contextuelle)
* **Analyse comparative des approches** :
  1. *Commentaires publics* : ❌ Écarté (risque de fuite de données personnelles, spams, pollution visuelle).
  2. *Chat global utilisateur à utilisateur* : ❌ Écarté (hors contexte, difficile à tracer en cas de litige d'intervention).
  3. **Messagerie Privée Rattachée à la Demande (Recommandée)** : ✅ **Sélectionnée**.
     - *Fonctionnement* : Chaque proposition envoyée par un prestataire ouvre un fil de discussion privé unique entre ce prestataire et le client.
     - *Confidentialité* : Les autres prestataires ne voient pas les échanges des concurrents.
     - *Continuité post-sélection* : Lorsque le client sélectionne le prestataire retenu, ce fil devient le canal officiel pour échanger sur le diagnostic, la solution et les détails de l'intervention.
     - *Modération & Traçabilité* : En cas de litige, l'admin peut consulter l'historique infalsifiable des messages associés à la demande.
* **Fichiers concernés** :
  - `DomAssist.sql`
  - `models/MessageThread.php`
  - `models/Message.php`
  - `controllers/MessageController.php`
  - `controllers_api/MessageApiController.php`
  - `views/demande/show.php`
  - `public/js/chat.js`
* **Modules concernés** : Messagerie, Demandes V3.
* **Dépendances** : `Demande`, `Proposition`.
* **Risques de régression** : Moyens (nécessite des filtres de sécurité stricts contre l'IDOR).
* **Difficulté** : Élevée | **Temps estimé** : 3h30.

---

### IMP-08 : Centre de Notifications In-App Multi-Événements
* **Description** : Système de notifications en temps réel / in-app pour avertir les utilisateurs des étapes clés de leurs demandes et candidatures.
* **Événements notifiés** :
  - **Pour le Client** : Nouvelle proposition reçue, Nouveau message, Confirmation d'engagement prestataire, Diagnostic publié, Solution proposée, Intervention planifiée / terminée.
  - **Pour le Prestataire** : Candidature validée/rejetée, Offre retenue/non retenue par le client, Nouveau message client, Solution validée/refusée, Avis reçu.
  - **Pour l'Admin** : Nouvelle candidature prestataire en attente, Nouveau signalement.
* **Interface UI** :
  - Dropdown avec badge de messages non lus dans la navbar.
  - Page dédiée `/notifications` avec filtres et option "Tout marquer comme lu".
* **Fichiers concernés** :
  - `DomAssist.sql`
  - `models/Notification.php`
  - `controllers/NotificationController.php`
  - `controllers_api/NotificationApiController.php`
  - `views/components/navbar.php`
  - `views/notification/index.php`
* **Modules concernés** : Notifications, Layout global.
* **Dépendances** : Tous les contrôleurs métier.
* **Risques de régression** : Faibles (composant additionnel découplé).
* **Difficulté** : Moyenne | **Temps estimé** : 2h30.

---

### IMP-09 : Modernisation de l'Interface Web & Design System
* **Description** : Refonte visuelle pour apporter une esthétique moderne, épurée et professionnelle à DomAssist.
* **Éléments visuels & Ergonomie** :
  - **Design System CSS** : Variables CSS pour la palette de couleurs (Dark Navy `#0f172a`, Primary Indigo `#4f46e5`, Accent Teal `#14b8a6`, Neutral Gray `#f8fafc`).
  - **Typographie** : Intégration de typographies Google Fonts modernes (Inter / Outfit).
  - **Landing Page Moderne** : Page d'accueil attractive avec Hero Section, statistiques clés, présentation des garanties de confiance et des catégories phares.
  - **Ressources Visuelles** : Utilisation d'illustrations SVG libres de droits (Storyset / Undraw) et d'images libres de droits hébergées localement dans `public/images/`.
  - **Composants Bootstrap 5 enrichis** : Modales à coins arrondis, micro-interactions au survol, animations CSS subtiles.
* **Fichiers concernés** :
  - `public/css/style.css`
  - `views/layouts/header.php`
  - `views/layouts/footer.php`
  - `views/home.php`
  - `views/dashboard.php`
* **Modules concernés** : UI / UX Transversal.
* **Dépendances** : Aucune.
* **Risques de régression** : Faibles.
* **Difficulté** : Moyenne | **Temps estimé** : 3h00.

---

## 4. Modifications de Base de Données Requises (`DomAssist.sql`)

Toutes les évolutions SQL doivent être regroupées et intégrées dans le fichier unique **`DomAssist.sql`** (selon la règle d'architecture du projet).

```sql
-- 1. EXTENSION DE LA TABLE USER (AVATAR & EMAIL SECONDAIRE)
ALTER TABLE "user" 
  ADD COLUMN IF NOT EXISTS email_secondaire VARCHAR(150),
  ADD COLUMN IF NOT EXISTS adresse_rue VARCHAR(255),
  ADD COLUMN IF NOT EXISTS bio TEXT,
  ADD COLUMN IF NOT EXISTS avatar_type VARCHAR(50) DEFAULT 'generated';

-- Permettre le code postal nullable
ALTER TABLE "user" ALTER COLUMN code_postal DROP NOT NULL;

-- 2. EXTENSION DE LA TABLE PRESTATAIRE_PROFILE (CANDIDATURE ENRICHIE)
ALTER TABLE prestataire_profile
  ADD COLUMN IF NOT EXISTS cv_url VARCHAR(255),
  ADD COLUMN IF NOT EXISTS lettre_motivation TEXT,
  ADD COLUMN IF NOT EXISTS zone_couverture JSONB DEFAULT '{"rayon_km": 30, "villes": []}',
  ADD COLUMN IF NOT EXISTS disponibilites_type VARCHAR(100) DEFAULT 'Semaine et Week-end',
  ADD COLUMN IF NOT EXISTS accepte_urgences BOOLEAN DEFAULT false,
  ADD COLUMN IF NOT EXISTS moyen_deplacement VARCHAR(100) DEFAULT 'Vehicule personnel',
  ADD COLUMN IF NOT EXISTS siret VARCHAR(20),
  ADD COLUMN IF NOT EXISTS assurances_pro VARCHAR(255);

-- 3. CRÉATION DE LA TABLE NOTIFICATION
CREATE TABLE IF NOT EXISTS notification (
    id_notification SERIAL PRIMARY KEY,
    id_user INT NOT NULL REFERENCES "user"(id_user) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL,
    titre VARCHAR(150) NOT NULL,
    corps TEXT NOT NULL,
    lien_ressource VARCHAR(255),
    lu BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_notification_user ON notification(id_user, lu);

-- 4. CRÉATION DES TABLES DE MESSAGERIE PAR DEMANDE
CREATE TABLE IF NOT EXISTS message_thread (
    id_thread SERIAL PRIMARY KEY,
    id_demande INT NOT NULL REFERENCES demande(id_demande) ON DELETE CASCADE,
    id_profile_prestataire INT NOT NULL REFERENCES prestataire_profile(id_profile) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unique_demande_prestataire_thread UNIQUE (id_demande, id_profile_prestataire)
);

CREATE TABLE IF NOT EXISTS message (
    id_message SERIAL PRIMARY KEY,
    id_thread INT NOT NULL REFERENCES message_thread(id_thread) ON DELETE CASCADE,
    id_sender INT NOT NULL REFERENCES "user"(id_user) ON DELETE CASCADE,
    contenu TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL
);
CREATE INDEX IF NOT EXISTS idx_message_thread ON message(id_thread, created_at);

-- 5. EXTENSION DE LA TABLE AVIS (DROIT DE RÉPONSE PRESTATAIRE)
ALTER TABLE avis 
  ADD COLUMN IF NOT EXISTS reponse_prestataire TEXT,
  ADD COLUMN IF NOT EXISTS reponse_created_at TIMESTAMP;
```

---

## 5. Modifications API Requises (`api.php` & `controllers_api/`)

| Endpoint API | Méthode | Action / Description | Rôle requis |
|---|---|---|---|
| `/api.php?action=demande_create` | POST | Correction du payload & gestion d'expiration | Client |
| `/api.php?action=prestataire_candidater` | POST | Prise en charge des champs enrichis & upload CV PDF | Client |
| `/api.php?action=user_profile_update` | PUT/POST | Modification bio, adresse, email secondaire, avatar | Connecté |
| `/api.php?action=user_change_password` | POST | Changement sécurisé du mot de passe | Connecté |
| `/api.php?action=messages_thread` | GET | Extraction des messages d'un fil de discussion sur une demande | Client / Prestataire |
| `/api.php?action=message_send` | POST | Envoi d'un message texte dans le fil | Client / Prestataire |
| `/api.php?action=notifications_list` | GET | Récupération des notifications non lues | Connecté |
| `/api.php?action=notification_mark_read` | POST | Marquage d'une ou toutes les notifications comme lues | Connecté |
| `/api.php?action=avis_repondre` | POST | Dépôt de la réponse du prestataire à un avis | Prestataire retenu |

---

## 6. Sprint Backlog Détaillé (Optimisé pour Antigravity Gratuit)

Chaque étape ci-dessous est conçue pour être **courte, autonome et vérifiable immédiatement** via la commande `php -l`.

```
───────────────────────────────────────────────────────────────────────────────
SPRINT 1 : CORRECTIF CRITIQUE & MIGRATION BASE DE DONNÉES (SOCLE)
───────────────────────────────────────────────────────────────────────────────
  [x] Étape 1.1 : Correction du bug de création de demande dans models/Demande.php, DemandeController.php et DemandeApiController.php.
  [x] Étape 1.2 : Mise à jour du fichier DomAssist.sql (champs candidature, notifications, messagerie, avis, profil utilisateur).
  [x] Étape 1.3 : Vérification de la cohérence globale de DomAssist.sql et validation syntaxique.

───────────────────────────────────────────────────────────────────────────────
SPRINT 2 : DOSSIER & PARCOURS "DEVENIR PRESTATAIRE" ENRICHI
───────────────────────────────────────────────────────────────────────────────
  [x] Étape 2.1 : Mise à jour de models/PrestataireProfile.php et façade models/Prestataire.php.
  [x] Étape 2.2 : Refonte du formulaire HTML views/prestataire/candidater.php (CV PDF, zone, urgences, véhicule).
  [x] Étape 2.3 : Mise à jour de controllers/PrestataireController.php (gestion upload CV et resoumission après rejet).
  [x] Étape 2.4 : Adaptation de controllers_api/PrestataireApiController.php.
  [x] Étape 2.5 : Mise à jour de la vue admin views/admin/prestataires_en_attente.php pour afficher le CV et le dossier enrichi.

───────────────────────────────────────────────────────────────────────────────
SPRINT 3 : PROFIL UTILISATEUR & SÉCURITÉ DU COMPTE
───────────────────────────────────────────────────────────────────────────────
  [x] Étape 3.1 : Création de utils/upload.php (gestion sécurisée de l'upload d'images et fallback avatars SVG libres).
  [x] Étape 3.2 : Mise à jour de models/User.php pour intégrer la gestion des avatars, adresses et mots de passe.
  [x] Étape 3.3 : Refonte de la vue HTML views/user/profile.php (onglets profil, sécurité, paramètres).
  [x] Étape 3.4 : Adaptation de controllers/UserController.php et controllers_api/UserApiController.php.

───────────────────────────────────────────────────────────────────────────────
SPRINT 4 : AMÉLIORATION DU FORMULAIRE DE DEMANDE CLIENT
───────────────────────────────────────────────────────────────────────────────
  [x] Étape 4.1 : Création/Adaptation de models/DemandeMedia.php pour le stockage multi-photos.
  [x] Étape 4.2 : Modernisation du formulaire views/demande/create.php (code postal optionnel, upload photos, créneaux).
  [x] Étape 4.3 : Adaptation de DemandeController.php et DemandeApiController.php pour le traitement des médias.
  [x] Étape 4.4 : Mise à jour de views/demande/show.php (affichage de la galerie photos et des conditions d'accès).

───────────────────────────────────────────────────────────────────────────────
SPRINT 5 : PROFILS PUBLICS DISTINCTS CLIENT / PRESTATAIRE & RECADRAGE DES AVIS
───────────────────────────────────────────────────────────────────────────────
  [x] Étape 5.1 : Création de la vue publique client views/user/show_public.php (vue sobre et sécurisée).
  [x] Étape 5.2 : Enrichissement de la vue profil prestataire views/prestataire/show.php (badges, réputation, expérience, avis).
  [x] Étape 5.3 : Simplification de views/dashboard.php (retrait du listing d'avis, conservation de la carte synthétique).
  [x] Étape 5.4 : Ajout du droit de réponse du prestataire dans models/Avis.php et views/prestataire/show.php.

───────────────────────────────────────────────────────────────────────────────
SPRINT 6 : MESSAGERIE PRIVÉE RATTACHÉE À LA DEMANDE
───────────────────────────────────────────────────────────────────────────────
  [x] Étape 6.1 : Création des modèles models/MessageThread.php et models/Message.php.
  [x] Étape 6.2 : Création du contrôleur HTML controllers/MessageController.php et API controllers_api/MessageApiController.php.
  [x] Étape 6.3 : Déclaration des nouvelles routes dans index.php et api.php.
  [x] Étape 6.4 : Intégration du composant UI de discussion privée dans views/demande/show.php.

───────────────────────────────────────────────────────────────────────────────
SPRINT 7 : CENTRE DE NOTIFICATIONS IN-APP
───────────────────────────────────────────────────────────────────────────────
  [x] Étape 7.1 : Finalisation et câblage du modèle models/Notification.php.
  [x] Étape 7.2 : Création des aides au déclenchement de notifications lors des événements clés du workflow.
  [x] Étape 7.3 : Intégration du composant Badge & Dropdown dans views/components/navbar.php.
  [x] Étape 7.4 : Création de la page complète de gestion des notifications views/notification/index.php.

───────────────────────────────────────────────────────────────────────────────
SPRINT 8 : DESIGN SYSTEM & MODERNISATION GRAPHIQUE PREMIUM
───────────────────────────────────────────────────────────────────────────────
  [x] Étape 8.1 : Création du Design System moderne dans public/assets/css/style.css (variables CSS, typographies Google Fonts).
  [x] Étape 8.2 : Modernisation de views/layouts/header.php et views/layouts/footer.php.
  [x] Étape 8.3 : Création / Refonte de la Landing Page views/home.php avec illustrations SVG libres et visuels d'accueil.
  [x] Étape 8.4 : Contrôle visuel et validation de la cohérence graphique sur l'ensemble des écrans.
```

---

## 7. Gestion des Risques & Analyse de Régression

| Risque potentiel | Niveau de risque | Mesure de prévention / Mitigation |
|---|---|---|
| **Rupture des façades V1 (`Prestataire`, `Service`)** | Moyen | Conserver l'interface exacte des méthodes façades tout en les raccordant aux nouvelles colonnes de `DomAssist.sql`. |
| **Erreurs lors de l'upload de fichiers (CV, photos)** | Moyen | Vérifier les extensions de fichiers autorisées (`.pdf` pour CV ; `.jpg`, `.png`, `.webp` pour images), limiter la taille (max 5 Mo) et sécuriser les sous-dossiers `public/uploads/`. |
| **Failles IDOR sur la messagerie** | Élevé | Contrôler systématiquement dans `MessageThread` que l'utilisateur connecté est soit le Client auteur de la demande, soit le Prestataire concerné par la proposition. |
| **Incompatibilité SQL PostgreSQL** | Faible | Utiliser des requêtes `PDO` préparées standards et tester l'exécution sur PostgreSQL via `psql`. |
| **Ralentissement de l'UI avec les notifications** | Faible | Indexer la table `notification` sur `(id_user, lu)` et charger les notifications via un appel AJAX léger ou au chargement initial. |

---

## 8. Validation et Prochaine Étape

> **Aucun fichier du projet n'a été modifié lors de cette phase de planification.**
> Ce document `NEXT_PHASE_PLAN.md` constitue la feuille de route complète et validée pour la prochaine session de développement.
> Dès validation par l'utilisateur, l'exécution commencera par l'**Étape 1.1 du Sprint 1** (Correction du bug d'envoi de demande).
