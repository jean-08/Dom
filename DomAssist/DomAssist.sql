CREATE DATABASE IF NOT EXISTS DomAssist CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE DomAssist;

CREATE TABLE IF NOT EXISTS user (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    role ENUM('client','admin','prestataire') NOT NULL DEFAULT 'client',
    mot_de_passe VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS prestataire (
    id_prestataire INT AUTO_INCREMENT PRIMARY KEY,
    specialite VARCHAR(150) NOT NULL,
    id_user INT NOT NULL UNIQUE,
    FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS disponibilite (
    id_dispo INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    statut ENUM('libre','occupé') NOT NULL DEFAULT 'libre',
    id_prestataire INT NOT NULL,
    FOREIGN KEY (id_prestataire) REFERENCES prestataire(id_prestataire) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS service (
    id_service INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS avoir_une_competence (
    id_prestataire INT NOT NULL,
    id_service INT NOT NULL,
    niveau VARCHAR(50),
    PRIMARY KEY (id_prestataire, id_service),
    FOREIGN KEY (id_prestataire) REFERENCES prestataire(id_prestataire) ON DELETE CASCADE,
    FOREIGN KEY (id_service) REFERENCES service(id_service) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS demande (
    id_demande INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT NOT NULL,
    date DATE NOT NULL,
    statut ENUM('en_attente','acceptée','refusée','terminée') NOT NULL DEFAULT 'en_attente',
    adresse VARCHAR(255) NOT NULL,
    id_user INT NOT NULL,
    FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS diagnostic (
    id_diagnostic INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT NOT NULL,
    resultat TEXT,
    date DATE NOT NULL,
    id_demande INT NOT NULL UNIQUE,
    id_prestataire INT NOT NULL,
    FOREIGN KEY (id_demande) REFERENCES demande(id_demande) ON DELETE CASCADE,
    FOREIGN KEY (id_prestataire) REFERENCES prestataire(id_prestataire) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS produits (
    id_produit INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    statut ENUM('disponible','rupture') NOT NULL DEFAULT 'disponible'
);

CREATE TABLE IF NOT EXISTS solution (
    id_solution INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT NOT NULL,
    id_diagnostic INT NOT NULL,
    FOREIGN KEY (id_diagnostic) REFERENCES diagnostic(id_diagnostic) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS utiliser (
    id_solution INT NOT NULL,
    id_produit INT NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    PRIMARY KEY (id_solution, id_produit),
    FOREIGN KEY (id_solution) REFERENCES solution(id_solution) ON DELETE CASCADE,
    FOREIGN KEY (id_produit) REFERENCES produits(id_produit) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS appartenir (
    id_solution INT NOT NULL,
    id_service INT NOT NULL,
    PRIMARY KEY (id_solution, id_service),
    FOREIGN KEY (id_solution) REFERENCES solution(id_solution) ON DELETE CASCADE,
    FOREIGN KEY (id_service) REFERENCES service(id_service) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS intervention (
    id_intervention INT AUTO_INCREMENT PRIMARY KEY,
    resultat TEXT,
    date DATE NOT NULL,
    id_prestataire INT NOT NULL,
    id_demande INT NOT NULL,
    id_dispo INT,
    FOREIGN KEY (id_prestataire) REFERENCES prestataire(id_prestataire) ON DELETE CASCADE,
    FOREIGN KEY (id_demande) REFERENCES demande(id_demande) ON DELETE CASCADE,
    FOREIGN KEY (id_dispo) REFERENCES disponibilite(id_dispo) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS avis (
    id_avis INT AUTO_INCREMENT PRIMARY KEY,
    note TINYINT NOT NULL CHECK (note BETWEEN 1 AND 5),
    comment TEXT,
    id_user INT NOT NULL,
    id_prestataire INT NOT NULL,
    FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_prestataire) REFERENCES prestataire(id_prestataire) ON DELETE CASCADE
);

INSERT INTO user (nom, prenom, email, role, mot_de_passe) VALUES
('Admin', 'System', 'admin@domassist.com', 'admin', '$2y$10$Hg4Pml7FutOod.t3igcn1OlsfDSwGVO88PQbaY0hy2zTQoAAbFKd2');