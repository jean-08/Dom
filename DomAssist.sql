-- ==========================================================================
-- DomAssist — Schéma de base de données PostgreSQL (v3)
-- Workflow cible : Publication → Propositions → Choix client
--                  → Confirmation prestataire → Diagnostic → Solution
--                  → Intervention → Avis
--
-- Import :
--   createdb -U <user> domassist
--   psql -U <user> -d domassist -f DomAssist.sql
--
-- Le mot "user" étant réservé sous PostgreSQL, il est systématiquement quoté.
-- ==========================================================================

-- ==========================================================================
-- TYPES ÉNUMÉRÉS
-- ==========================================================================

-- Rôle global du compte (client = tout inscrit ; admin = modérateur plateforme)
CREATE TYPE role_user AS ENUM ('client', 'admin');

-- Statuts du profil de candidature prestataire
CREATE TYPE statut_profile AS ENUM (
    'brouillon',
    'soumise',
    'en_revue',
    'validee',
    'rejetee',
    'suspendue'
);

-- Statuts du cycle de vie d'une demande (workflow V3)
CREATE TYPE statut_demande AS ENUM (
    'ouverte',
    'en_discussion',
    'prestataire_choisi',
    'engagee',
    'diagnostic_propose',
    'solution_proposee',
    'intervention_planifiee',
    'intervention_en_cours',
    'terminee',
    'cloturee',
    'annulee_par_client',
    'annulee_par_prestataire',
    'expiree',
    'suspendue_moderation'
);

-- Niveau d'urgence d'une demande
CREATE TYPE urgence_demande AS ENUM ('normal', 'sous_48h', 'urgent');

-- Statuts d'une proposition prestataire
CREATE TYPE statut_proposition AS ENUM (
    'envoyee',
    'retiree',
    'retenue',
    'non_retenue',
    'expiree'
);

-- Statuts d'une intervention
CREATE TYPE statut_intervention AS ENUM ('planifiee', 'en_cours', 'terminee');

-- Disponibilités prestataire
CREATE TYPE statut_dispo AS ENUM ('libre', 'occupé');


-- ==========================================================================
-- COMPTES UTILISATEURS
-- ==========================================================================

CREATE TABLE IF NOT EXISTS "user" (
    id_user           SERIAL PRIMARY KEY,
    nom               VARCHAR(100) NOT NULL,
    prenom            VARCHAR(100) NOT NULL,
    email             VARCHAR(150) NOT NULL UNIQUE,
    email_secondaire  VARCHAR(150) NULL,
    mot_de_passe      VARCHAR(255) NOT NULL,
    role              role_user NOT NULL DEFAULT 'client',
    telephone         VARCHAR(30) NULL,
    photo_url         VARCHAR(500) NULL,
    avatar_type       VARCHAR(50) NOT NULL DEFAULT 'generated',
    bio               TEXT NULL,
    adresse_rue       VARCHAR(255) NULL,
    ville             VARCHAR(100) NULL,
    suspendu          BOOLEAN NOT NULL DEFAULT false,
    date_suspension   TIMESTAMP NULL,
    motif_suspension  TEXT NULL,
    date_fin_suspension TIMESTAMP NULL,
    created_at        TIMESTAMP NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_user_email     ON "user"(email);
CREATE INDEX IF NOT EXISTS idx_user_suspendu  ON "user"(suspendu);


-- ==========================================================================
-- AUTHENTIFICATION API
-- ==========================================================================

CREATE TABLE IF NOT EXISTS session_token (
    id_token         SERIAL PRIMARY KEY,
    token            VARCHAR(64) NOT NULL UNIQUE,
    id_user          INT NOT NULL REFERENCES "user"(id_user) ON DELETE CASCADE,
    date_creation    TIMESTAMP NOT NULL DEFAULT now(),
    date_expiration  TIMESTAMP NOT NULL,
    revoque          BOOLEAN NOT NULL DEFAULT false
);

CREATE INDEX IF NOT EXISTS idx_session_token_token ON session_token(token);
CREATE INDEX IF NOT EXISTS idx_session_token_user  ON session_token(id_user);


-- ==========================================================================
-- CATÉGORIES DE SERVICES (seedées — cf. rapport §6)
-- ==========================================================================

CREATE TABLE IF NOT EXISTS service_category (
    id_category  SERIAL PRIMARY KEY,
    code         VARCHAR(50) NOT NULL UNIQUE,
    libelle      VARCHAR(150) NOT NULL,
    actif        BOOLEAN NOT NULL DEFAULT true,
    ordre        SMALLINT NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_service_category_actif ON service_category(actif);

-- Catalogue initial (10 catégories de base)
INSERT INTO service_category (code, libelle, ordre) VALUES
    ('plomberie',       'Plomberie',            1),
    ('electricite',     'Électricité',           2),
    ('serrurerie',      'Serrurerie',            3),
    ('peinture',        'Peinture & Décoration', 4),
    ('jardinage',       'Jardinage & Espaces verts', 5),
    ('menage',          'Ménage & Entretien',    6),
    ('climatisation',   'Climatisation & Chauffage', 7),
    ('maconnerie',      'Maçonnerie & Gros œuvre', 8),
    ('demenagement',    'Déménagement',          9),
    ('informatique',    'Informatique & Dépannage électronique', 10)
ON CONFLICT (code) DO NOTHING;


-- ==========================================================================
-- PROFILS PRESTATAIRES (V3 — candidature enrichie)
-- ==========================================================================

CREATE TABLE IF NOT EXISTS prestataire_profile (
    id_profile          SERIAL PRIMARY KEY,
    id_user             INT NOT NULL UNIQUE REFERENCES "user"(id_user) ON DELETE CASCADE,
    bio                 TEXT NULL,
    lettre_motivation   TEXT NULL,
    experience_annees   SMALLINT NULL CHECK (experience_annees >= 0),
    zone_intervention   JSONB NULL,       -- ex. {"villes": ["Paris", "Vincennes"], "rayon_km": 30}
    disponibilites_type VARCHAR(100) NULL DEFAULT 'Semaine et Week-end',
    accepte_urgences    BOOLEAN NOT NULL DEFAULT false,
    moyen_deplacement   VARCHAR(100) NULL DEFAULT 'Vehicule personnel',
    siret               VARCHAR(20) NULL,
    assurances_pro      VARCHAR(255) NULL,
    document_cv_url     VARCHAR(500) NULL,
    statut              statut_profile NOT NULL DEFAULT 'brouillon',
    motif_rejet         TEXT NULL,
    date_soumission     TIMESTAMP NULL,
    date_validation     TIMESTAMP NULL,
    cgu_acceptees_at    TIMESTAMP NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_profile_user   ON prestataire_profile(id_user);
CREATE INDEX IF NOT EXISTS idx_profile_statut ON prestataire_profile(statut);

-- Compétences : lien N–N entre profil et catégorie de service
CREATE TABLE IF NOT EXISTS competence (
    id_profile   INT NOT NULL REFERENCES prestataire_profile(id_profile) ON DELETE CASCADE,
    id_category  INT NOT NULL REFERENCES service_category(id_category) ON DELETE CASCADE,
    niveau       VARCHAR(50) NULL,
    PRIMARY KEY (id_profile, id_category)
);

CREATE INDEX IF NOT EXISTS idx_competence_category ON competence(id_category);


-- ==========================================================================
-- DISPONIBILITÉS PRESTATAIRE
-- ==========================================================================

CREATE TABLE IF NOT EXISTS disponibilite (
    id_dispo      SERIAL PRIMARY KEY,
    id_profile    INT NOT NULL REFERENCES prestataire_profile(id_profile) ON DELETE CASCADE,
    date          DATE NOT NULL,
    heure_debut   TIME NOT NULL,
    heure_fin     TIME NOT NULL,
    statut        statut_dispo NOT NULL DEFAULT 'libre'
);

CREATE INDEX IF NOT EXISTS idx_dispo_profile ON disponibilite(id_profile);


-- ==========================================================================
-- DEMANDES (V3 — enrichies)
-- ==========================================================================

CREATE TABLE IF NOT EXISTS demande (
    id_demande             SERIAL PRIMARY KEY,
    id_user                INT NOT NULL REFERENCES "user"(id_user) ON DELETE CASCADE,
    titre                  VARCHAR(255) NOT NULL,
    description            TEXT NOT NULL,
    id_category            INT NOT NULL REFERENCES service_category(id_category) ON DELETE RESTRICT,
    urgence                urgence_demande NOT NULL DEFAULT 'normal',
    budget_min             DECIMAL(10,2) NULL,
    budget_max             DECIMAL(10,2) NULL,
    disponibilites_client  TEXT NULL,
    -- Localisation structurée
    adresse                VARCHAR(255) NULL,
    ville                  VARCHAR(100) NOT NULL,
    code_postal            VARCHAR(10) NULL DEFAULT '00000',
    -- Contact
    telephone_contact      VARCHAR(30) NULL,
    -- Workflow
    statut                 statut_demande NOT NULL DEFAULT 'ouverte',
    id_profile_retenu      INT NULL REFERENCES prestataire_profile(id_profile) ON DELETE SET NULL,
    -- Dates
    created_at             TIMESTAMP NOT NULL DEFAULT now(),
    published_at           TIMESTAMP NULL,
    closed_at              TIMESTAMP NULL,
    expires_at             TIMESTAMP NULL
);

CREATE INDEX IF NOT EXISTS idx_demande_user       ON demande(id_user);
CREATE INDEX IF NOT EXISTS idx_demande_statut     ON demande(statut);
CREATE INDEX IF NOT EXISTS idx_demande_category   ON demande(id_category);
CREATE INDEX IF NOT EXISTS idx_demande_profile_retenu ON demande(id_profile_retenu);
CREATE INDEX IF NOT EXISTS idx_demande_expires_at ON demande(expires_at);

-- Médias attachés à une demande (photos)
CREATE TABLE IF NOT EXISTS demande_media (
    id_media    SERIAL PRIMARY KEY,
    id_demande  INT NOT NULL REFERENCES demande(id_demande) ON DELETE CASCADE,
    type        VARCHAR(20) NOT NULL DEFAULT 'image',  -- 'image' | 'doc'
    url         VARCHAR(500) NOT NULL,
    ordre       SMALLINT NOT NULL DEFAULT 0,
    created_at  TIMESTAMP NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_demande_media_demande ON demande_media(id_demande);

-- Journal d'événements append-only (historique / timeline)
CREATE TABLE IF NOT EXISTS demande_event (
    id_event    SERIAL PRIMARY KEY,
    id_demande  INT NOT NULL REFERENCES demande(id_demande) ON DELETE CASCADE,
    id_actor    INT NULL REFERENCES "user"(id_user) ON DELETE SET NULL,
    type        VARCHAR(60) NOT NULL,   -- CREEE, PROPOSITION_RECUE, PRESTATAIRE_SELECTIONNE, etc.
    payload     JSONB NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_event_demande    ON demande_event(id_demande);
CREATE INDEX IF NOT EXISTS idx_event_created_at ON demande_event(created_at);


-- ==========================================================================
-- PROPOSITIONS (entité centrale du workflow V3)
-- ==========================================================================

CREATE TABLE IF NOT EXISTS proposition (
    id_proposition  SERIAL PRIMARY KEY,
    id_demande      INT NOT NULL REFERENCES demande(id_demande) ON DELETE CASCADE,
    id_profile      INT NOT NULL REFERENCES prestataire_profile(id_profile) ON DELETE CASCADE,
    message         TEXT NOT NULL,
    prix_indicatif  DECIMAL(10,2) NULL,
    delai_texte     VARCHAR(255) NULL,
    statut          statut_proposition NOT NULL DEFAULT 'envoyee',
    created_at      TIMESTAMP NOT NULL DEFAULT now(),
    decided_at      TIMESTAMP NULL,
    -- Un prestataire ne peut avoir qu'une proposition active par demande
    UNIQUE (id_demande, id_profile)
);

CREATE INDEX IF NOT EXISTS idx_proposition_demande ON proposition(id_demande);
CREATE INDEX IF NOT EXISTS idx_proposition_profile ON proposition(id_profile);
CREATE INDEX IF NOT EXISTS idx_proposition_statut  ON proposition(statut);


-- ==========================================================================
-- DIAGNOSTIC, SOLUTION, INTERVENTION (sous-workflow post-engagement)
-- ==========================================================================

CREATE TABLE IF NOT EXISTS diagnostic (
    id_diagnostic  SERIAL PRIMARY KEY,
    id_demande     INT NOT NULL UNIQUE REFERENCES demande(id_demande) ON DELETE CASCADE,
    id_profile     INT NOT NULL REFERENCES prestataire_profile(id_profile) ON DELETE CASCADE,
    description    TEXT NOT NULL,
    resultat       TEXT NULL,
    date           DATE NOT NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS solution (
    id_solution          SERIAL PRIMARY KEY,
    id_diagnostic        INT NOT NULL REFERENCES diagnostic(id_diagnostic) ON DELETE CASCADE,
    description          TEXT NOT NULL,
    validee_par_client   BOOLEAN NOT NULL DEFAULT false,
    refusee_par_client   BOOLEAN NOT NULL DEFAULT false,
    date_validation      TIMESTAMP NULL,
    created_at           TIMESTAMP NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_solution_diagnostic ON solution(id_diagnostic);

-- Catalogue des produits / matériel de chantier
CREATE TABLE IF NOT EXISTS produits (
    id_produit  SERIAL PRIMARY KEY,
    nom         VARCHAR(150) NOT NULL,
    prix        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock       INT NOT NULL DEFAULT 0,
    statut      VARCHAR(50) NOT NULL DEFAULT 'disponible',
    created_at  TIMESTAMP NOT NULL DEFAULT now()
);

-- Liaison N-N entre solution et produits utilisés
CREATE TABLE IF NOT EXISTS utiliser (
    id_solution INT NOT NULL REFERENCES solution(id_solution) ON DELETE CASCADE,
    id_produit  INT NOT NULL REFERENCES produits(id_produit) ON DELETE CASCADE,
    quantite    INT NOT NULL DEFAULT 1,
    PRIMARY KEY (id_solution, id_produit)
);

CREATE OR REPLACE VIEW produit AS SELECT * FROM produits;
-- Ensure product and usage tables exist (idempotent)
CREATE TABLE IF NOT EXISTS produits (
    id_produit SERIAL PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    prix DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    statut VARCHAR(50) NOT NULL DEFAULT 'disponible',
    created_at TIMESTAMP NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS utiliser (
    id_solution INT NOT NULL REFERENCES solution(id_solution) ON DELETE CASCADE,
    id_produit INT NOT NULL REFERENCES produits(id_produit) ON DELETE CASCADE,
    quantite INT NOT NULL DEFAULT 1,
    PRIMARY KEY (id_solution, id_produit)
);

CREATE TABLE IF NOT EXISTS intervention (
    id_intervention  SERIAL PRIMARY KEY,
    id_demande       INT NOT NULL REFERENCES demande(id_demande) ON DELETE CASCADE,
    id_profile       INT NOT NULL REFERENCES prestataire_profile(id_profile) ON DELETE CASCADE,
    id_dispo         INT NULL REFERENCES disponibilite(id_dispo) ON DELETE SET NULL,
    date             DATE NOT NULL,
    resultat         TEXT NULL,
    statut           statut_intervention NOT NULL DEFAULT 'planifiee',
    created_at       TIMESTAMP NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_intervention_demande ON intervention(id_demande);
CREATE INDEX IF NOT EXISTS idx_intervention_profile ON intervention(id_profile);


-- ==========================================================================
-- AVIS (liés à une intervention terminée — rapport §12 / UC12)
-- ==========================================================================

CREATE TABLE IF NOT EXISTS avis (
    id_avis              SERIAL PRIMARY KEY,
    id_user              INT NOT NULL REFERENCES "user"(id_user) ON DELETE CASCADE,
    id_profile           INT NOT NULL REFERENCES prestataire_profile(id_profile) ON DELETE CASCADE,
    id_intervention      INT NULL REFERENCES intervention(id_intervention) ON DELETE CASCADE,
    note                 SMALLINT NOT NULL CHECK (note BETWEEN 1 AND 5),
    comment              TEXT NULL,
    reponse_prestataire  TEXT NULL,
    reponse_created_at   TIMESTAMP NULL,
    created_at           TIMESTAMP NOT NULL DEFAULT now()
);

-- Un seul avis par intervention terminée
CREATE UNIQUE INDEX IF NOT EXISTS uq_avis_intervention ON avis(id_intervention);
CREATE INDEX IF NOT EXISTS idx_avis_profile ON avis(id_profile);


-- ==========================================================================
-- MESSAGERIE & NOTIFICATIONS IN-APP
-- ==========================================================================

CREATE TABLE IF NOT EXISTS notification (
    id_notification  SERIAL PRIMARY KEY,
    id_user          INT NOT NULL REFERENCES "user"(id_user) ON DELETE CASCADE,
    type             VARCHAR(50) NOT NULL,
    titre            VARCHAR(150) NOT NULL,
    corps            TEXT NOT NULL,
    lien_ressource   VARCHAR(255) NULL,
    lu               BOOLEAN NOT NULL DEFAULT false,
    created_at       TIMESTAMP NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_notification_user ON notification(id_user, lu);

CREATE TABLE IF NOT EXISTS message_thread (
    id_thread               SERIAL PRIMARY KEY,
    id_demande              INT NOT NULL REFERENCES demande(id_demande) ON DELETE CASCADE,
    id_profile_prestataire  INT NOT NULL REFERENCES prestataire_profile(id_profile) ON DELETE CASCADE,
    created_at              TIMESTAMP NOT NULL DEFAULT now(),
    CONSTRAINT unique_demande_prestataire_thread UNIQUE (id_demande, id_profile_prestataire)
);

CREATE INDEX IF NOT EXISTS idx_thread_demande ON message_thread(id_demande);
CREATE INDEX IF NOT EXISTS idx_thread_profile ON message_thread(id_profile_prestataire);

CREATE TABLE IF NOT EXISTS message (
    id_message  SERIAL PRIMARY KEY,
    id_thread   INT NOT NULL REFERENCES message_thread(id_thread) ON DELETE CASCADE,
    id_sender   INT NOT NULL REFERENCES "user"(id_user) ON DELETE CASCADE,
    contenu     TEXT NOT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT now(),
    read_at     TIMESTAMP NULL
);

CREATE INDEX IF NOT EXISTS idx_message_thread ON message(id_thread, created_at);


-- ==========================================================================
-- FONCTIONS MÉTIER (workflow V3 — atomiques avec verrouillage transactionnel)
-- ==========================================================================

-- ---------------------------------------------------------------------------
-- Calcul de la date d'expiration d'une demande selon son urgence
-- ---------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION compute_demande_expiration(p_id_category INT, p_published_at TIMESTAMPTZ)
RETURNS TIMESTAMPTZ AS $$
BEGIN
    -- Règle simple V1 : expiration à 30 jours par défaut
    RETURN p_published_at + INTERVAL '30 days';
END;
$$ LANGUAGE plpgsql IMMUTABLE;

-- ---------------------------------------------------------------------------
-- Demandes éligibles pour un prestataire (matching compétences + statut)
-- Remplace l'ancienne demandes_disponibles()
-- ---------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION demandes_eligibles(p_id_profile INT)
RETURNS TABLE (
    id_demande    INT,
    titre         VARCHAR(255),
    description   TEXT,
    ville         VARCHAR(100),
    code_postal   VARCHAR(10),
    urgence       urgence_demande,
    budget_min    DECIMAL(10,2),
    budget_max    DECIMAL(10,2),
    id_category   INT,
    category_libelle VARCHAR(150),
    created_at    TIMESTAMP,
    expires_at    TIMESTAMP,
    id_user       INT,
    client_nom    VARCHAR(100),
    client_prenom VARCHAR(100)
) AS $$
BEGIN
    -- Le profil doit être validé et non suspendu
    IF NOT EXISTS (
        SELECT 1 FROM prestataire_profile
        WHERE id_profile = p_id_profile AND statut = 'validee'
    ) THEN
        RETURN;
    END IF;

    RETURN QUERY
    SELECT
        d.id_demande, d.titre, d.description, d.ville, d.code_postal,
        d.urgence, d.budget_min, d.budget_max,
        d.id_category, sc.libelle,
        d.created_at, d.expires_at,
        d.id_user, u.nom, u.prenom
    FROM demande d
    JOIN service_category sc ON sc.id_category = d.id_category
    JOIN "user" u ON u.id_user = d.id_user
    WHERE d.statut IN ('ouverte', 'en_discussion')
      -- Le prestataire n'a pas déjà une proposition active ou retirée définitivement
      AND NOT EXISTS (
          SELECT 1 FROM proposition p
          WHERE p.id_demande = d.id_demande
            AND p.id_profile = p_id_profile
            AND p.statut NOT IN ('retiree', 'expiree')
      )
    ORDER BY d.urgence DESC, d.created_at ASC;
END;
$$ LANGUAGE plpgsql STABLE;

-- ---------------------------------------------------------------------------
-- Envoyer une proposition (atomique — un seul par couple demande/prestataire)
-- ---------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION envoyer_proposition(
    p_id_demande   INT,
    p_id_profile   INT,
    p_message      TEXT,
    p_prix         DECIMAL(10,2),
    p_delai        VARCHAR(255)
) RETURNS TEXT AS $$
DECLARE
    v_statut   statut_demande;
    v_actor_id INT;
BEGIN
    -- Vérifier que la demande est ouverte ou en_discussion
    SELECT statut INTO v_statut FROM demande WHERE id_demande = p_id_demande FOR UPDATE;
    IF NOT FOUND THEN RETURN 'introuvable'; END IF;
    IF v_statut NOT IN ('ouverte', 'en_discussion') THEN RETURN 'mauvais_statut'; END IF;

    -- Vérifier que le prestataire est validé
    IF NOT EXISTS (
        SELECT 1 FROM prestataire_profile WHERE id_profile = p_id_profile AND statut = 'validee'
    ) THEN RETURN 'prestataire_non_valide'; END IF;

    -- Récupérer l'id_user pour le journal
    SELECT id_user INTO v_actor_id FROM prestataire_profile WHERE id_profile = p_id_profile;

    -- Insérer la proposition (UNIQUE sur id_demande, id_profile)
    INSERT INTO proposition (id_demande, id_profile, message, prix_indicatif, delai_texte)
    VALUES (p_id_demande, p_id_profile, p_message, p_prix, p_delai)
    ON CONFLICT (id_demande, id_profile) DO NOTHING;

    IF NOT FOUND THEN RETURN 'deja_proposee'; END IF;

    -- Passer la demande en EN_DISCUSSION si elle était OUVERTE
    UPDATE demande SET statut = 'en_discussion'
    WHERE id_demande = p_id_demande AND statut = 'ouverte';

    -- Journaliser
    INSERT INTO demande_event (id_demande, id_actor, type, payload)
    VALUES (p_id_demande, v_actor_id, 'PROPOSITION_RECUE',
            jsonb_build_object('id_profile', p_id_profile));

    RETURN 'ok';
END;
$$ LANGUAGE plpgsql;

-- ---------------------------------------------------------------------------
-- Sélectionner un prestataire parmi les propositions (action client)
-- ---------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION selectionner_prestataire(
    p_id_demande     INT,
    p_id_user        INT,   -- client propriétaire de la demande
    p_id_proposition INT
) RETURNS TEXT AS $$
DECLARE
    v_statut       statut_demande;
    v_owner        INT;
    v_prop_statut  statut_proposition;
    v_id_profile   INT;
BEGIN
    SELECT statut, id_user INTO v_statut, v_owner
    FROM demande WHERE id_demande = p_id_demande FOR UPDATE;

    IF NOT FOUND THEN RETURN 'introuvable'; END IF;
    IF v_owner <> p_id_user THEN RETURN 'non_autorise'; END IF;
    IF v_statut NOT IN ('ouverte', 'en_discussion') THEN RETURN 'mauvais_statut'; END IF;

    SELECT statut, id_profile INTO v_prop_statut, v_id_profile
    FROM proposition WHERE id_proposition = p_id_proposition AND id_demande = p_id_demande;

    IF NOT FOUND THEN RETURN 'proposition_introuvable'; END IF;
    IF v_prop_statut <> 'envoyee' THEN RETURN 'proposition_non_disponible'; END IF;

    -- Retenir la proposition choisie
    UPDATE proposition SET statut = 'retenue', decided_at = now()
    WHERE id_proposition = p_id_proposition;

    -- Décliner toutes les autres propositions envoyées
    UPDATE proposition SET statut = 'non_retenue', decided_at = now()
    WHERE id_demande = p_id_demande
      AND id_proposition <> p_id_proposition
      AND statut = 'envoyee';

    -- Mettre à jour la demande
    UPDATE demande
    SET statut = 'prestataire_choisi', id_profile_retenu = v_id_profile
    WHERE id_demande = p_id_demande;

    -- Journaliser
    INSERT INTO demande_event (id_demande, id_actor, type, payload)
    VALUES (p_id_demande, p_id_user, 'PRESTATAIRE_SELECTIONNE',
            jsonb_build_object('id_proposition', p_id_proposition, 'id_profile', v_id_profile));

    RETURN 'ok';
END;
$$ LANGUAGE plpgsql;

-- ---------------------------------------------------------------------------
-- Confirmer l'engagement (prestataire retenu confirme)
-- ---------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION confirmer_engagement(
    p_id_demande INT,
    p_id_profile INT
) RETURNS TEXT AS $$
DECLARE
    v_statut statut_demande;
    v_retenu INT;
    v_actor  INT;
BEGIN
    SELECT statut, id_profile_retenu INTO v_statut, v_retenu
    FROM demande WHERE id_demande = p_id_demande FOR UPDATE;

    IF NOT FOUND THEN RETURN 'introuvable'; END IF;
    IF v_statut <> 'prestataire_choisi' THEN RETURN 'mauvais_statut'; END IF;
    IF v_retenu <> p_id_profile THEN RETURN 'non_autorise'; END IF;

    UPDATE demande SET statut = 'engagee' WHERE id_demande = p_id_demande;

    SELECT id_user INTO v_actor FROM prestataire_profile WHERE id_profile = p_id_profile;

    INSERT INTO demande_event (id_demande, id_actor, type)
    VALUES (p_id_demande, v_actor, 'ENGAGEMENT_CONFIRME');

    RETURN 'ok';
END;
$$ LANGUAGE plpgsql;

-- ---------------------------------------------------------------------------
-- Désistement du prestataire après sélection (avant engagement)
-- La demande repasse OUVERTE pour permettre une nouvelle sélection
-- ---------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION desister_prestataire(
    p_id_demande INT,
    p_id_profile INT
) RETURNS TEXT AS $$
DECLARE
    v_statut statut_demande;
    v_retenu INT;
    v_actor  INT;
    v_count  INT;
BEGIN
    SELECT statut, id_profile_retenu INTO v_statut, v_retenu
    FROM demande WHERE id_demande = p_id_demande FOR UPDATE;

    IF NOT FOUND THEN RETURN 'introuvable'; END IF;
    IF v_statut NOT IN ('prestataire_choisi', 'engagee') THEN RETURN 'mauvais_statut'; END IF;
    IF v_retenu <> p_id_profile THEN RETURN 'non_autorise'; END IF;

    -- Le prestataire qui se désiste voit sa proposition marquée comme 'retiree'
    UPDATE proposition SET statut = 'retiree', decided_at = now()
    WHERE id_demande = p_id_demande AND id_profile = p_id_profile AND statut = 'retenue';

    -- Restaurer les autres propositions qui avaient été écartées
    UPDATE proposition SET statut = 'envoyee', decided_at = NULL
    WHERE id_demande = p_id_demande AND statut = 'non_retenue';

    -- Reouvrir la demande après désistement du prestataire
    v_statut := 'ouverte';
    UPDATE demande
    SET statut = v_statut,
        id_profile_retenu = NULL,
        closed_at = NULL
    WHERE id_demande = p_id_demande;

    SELECT id_user INTO v_actor FROM prestataire_profile WHERE id_profile = p_id_profile;

    INSERT INTO demande_event (id_demande, id_actor, type)
    VALUES (p_id_demande, v_actor, 'DESISTEMENT_PRESTATAIRE');

    RETURN 'ok';
END;
$$ LANGUAGE plpgsql;


-- ==========================================================================
-- COMMENTAIRES PUBLICS SUR LES DEMANDES
-- ==========================================================================

CREATE TABLE IF NOT EXISTS commentaire_demande (
    id_commentaire  SERIAL PRIMARY KEY,
    id_demande      INT NOT NULL REFERENCES demande(id_demande) ON DELETE CASCADE,
    id_user         INT NOT NULL REFERENCES "user"(id_user) ON DELETE CASCADE,
    contenu         TEXT NOT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_commentaire_demande ON commentaire_demande(id_demande, created_at);

-- ==========================================================================
-- DONNÉES INITIALES
-- ==========================================================================

-- Compte administrateur par défaut
-- Mot de passe : Admin1234 (bcrypt $2a$12$...)
INSERT INTO "user" (nom, prenom, email, role, mot_de_passe) VALUES
    ('Admin', 'System', 'admin@domassist.com', 'admin',
     '$2y$12$dVcIeDIPD.HyeFoh6zBu2OLph/DLwrXU1JyfSORYSSlvR7aEYuqK.')
ON CONFLICT (email) DO NOTHING;