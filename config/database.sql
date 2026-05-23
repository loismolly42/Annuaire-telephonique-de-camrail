-- =========================
-- BASE DE DONNÉES CAMRAIL ANNUAIRE
-- =========================

CREATE DATABASE IF NOT EXISTS camrail_annuaire;
USE camrail_annuaire;

-- =========================
-- TABLE SERVICES
-- =========================
CREATE TABLE Services (
  id_service INT AUTO_INCREMENT PRIMARY KEY,
  nom_service VARCHAR(100) NOT NULL,
  description TEXT
);

-- =========================
-- TABLE EMPLOYES
-- =========================
CREATE TABLE Employes (
  id_employe INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100),
  poste VARCHAR(100),
  email VARCHAR(150),
  photo VARCHAR(255),
  id_service INT,
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (id_service) REFERENCES Services(id_service)
);

-- =========================
-- TABLE NUMEROS
-- =========================
CREATE TABLE Numeros (
  id_numero INT AUTO_INCREMENT PRIMARY KEY,
  id_employe INT NOT NULL,
  type_numero ENUM('fixe','mobile','whatsapp','poste') DEFAULT 'mobile',
  numero VARCHAR(30) NOT NULL,
  principal BOOLEAN DEFAULT 0,

  FOREIGN KEY (id_employe) REFERENCES Employes(id_employe)
);

-- =========================
-- TABLE ROLES
-- =========================
CREATE TABLE Roles (
  id_role INT AUTO_INCREMENT PRIMARY KEY,
  nom_role VARCHAR(50)
);

-- =========================
-- TABLE UTILISATEURS
-- =========================
CREATE TABLE Utilisateurs (
  id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100),
  prenom VARCHAR(100),
  login VARCHAR(100) UNIQUE,
  mot_de_passe VARCHAR(255),
  id_role INT,
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (id_role) REFERENCES Roles(id_role)
);

-- =========================
-- TABLE PERMISSIONS
-- =========================
CREATE TABLE Permissions (
  id_permission INT AUTO_INCREMENT PRIMARY KEY,
  description_permission VARCHAR(150)
);

-- =========================
-- TABLE ROLE_PERMISSION
-- =========================
CREATE TABLE Role_Permission (
  id_role INT,
  id_permission INT,

  PRIMARY KEY (id_role, id_permission),

  FOREIGN KEY (id_role) REFERENCES Roles(id_role),
  FOREIGN KEY (id_permission) REFERENCES Permissions(id_permission)
);

-- =========================
-- TABLE FAVORIS
-- =========================
CREATE TABLE Favoris (
  id_favori INT AUTO_INCREMENT PRIMARY KEY,
  id_utilisateur INT NOT NULL,
  id_employe INT NOT NULL,
  date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (id_utilisateur) REFERENCES Utilisateurs(id_utilisateur),
  FOREIGN KEY (id_employe) REFERENCES Employes(id_employe)
);

-- =========================
-- TABLE HISTORIQUE
-- =========================
CREATE TABLE Historique (
  id_historique INT AUTO_INCREMENT PRIMARY KEY,

  action VARCHAR(50),
  table_cible ENUM('Employes','Numeros','Services','Utilisateurs'),
  id_cible INT,

  date_heure TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  id_utilisateur INT,

  FOREIGN KEY (id_utilisateur) REFERENCES Utilisateurs(id_utilisateur)
);

-- =========================
-- TABLE DEMANDES DE MODIFICATION
-- =========================
CREATE TABLE Demandes_Modification (
  id_demande INT AUTO_INCREMENT PRIMARY KEY,

  table_cible ENUM('Employes','Numeros','Services'),
  id_cible INT,

  champ_modifie VARCHAR(100),
  ancienne_valeur TEXT,
  nouvelle_valeur TEXT,

  statut ENUM('en_attente','valide','refuse') DEFAULT 'en_attente',

  type_validation ENUM('DIRECT','ADMIN') DEFAULT 'DIRECT',

  id_utilisateur INT,
  id_admin INT NULL,

  date_demande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  date_validation TIMESTAMP NULL,

  FOREIGN KEY (id_utilisateur) REFERENCES Utilisateurs(id_utilisateur),
  FOREIGN KEY (id_admin) REFERENCES Utilisateurs(id_utilisateur)
);
-- =========================
-- TABLE LOCALISATIONS
-- =========================

CREATE TABLE Localisations (

    id_localisation INT AUTO_INCREMENT PRIMARY KEY,

    nom_localisation VARCHAR(100) NOT NULL,
    
    description TEXT,

    ville VARCHAR(100),

    site VARCHAR(100),

    batiment VARCHAR(100),

    etage VARCHAR(50),

    bureau VARCHAR(50),

    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP

ALTER TABLE Employes
ADD id_localisation INT;

ALTER TABLE Employes
ADD CONSTRAINT fk_localisation
FOREIGN KEY (id_localisation)
REFERENCES Localisations(id_localisation);
);