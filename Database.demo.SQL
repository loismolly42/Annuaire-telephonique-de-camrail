-- =============================================================
-- BASE DE DONNÉES CAMRAIL ANNUAIRE — COMPLÈTE AVEC DONNÉES DÉMO
-- =============================================================
-- Importer : mysql -u root -p camrail_annuaire < database_demo.sql
-- Comptes :
--   Admin  → login: CR-000001  mot de passe: admin123
--   User   → login: CR-000002  mot de passe: user123
-- =============================================================

CREATE DATABASE IF NOT EXISTS camrail_annuaire CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE camrail_annuaire;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS Historique, Demandes_Modification, Favoris, Role_Permission,
                     Permissions, Numeros, Employes, Utilisateurs, Roles, Services;
SET FOREIGN_KEY_CHECKS = 1;

-- =========================
-- SERVICES (= Départements)
-- =========================
CREATE TABLE Services (
  id_service  INT AUTO_INCREMENT PRIMARY KEY,
  nom_service VARCHAR(100) NOT NULL,
  description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Services (nom_service, description) VALUES
  ('Opérations Ferroviaires', 'Supervision de la circulation, planification des horaires et gestion des incidents.'),
  ('Ingénierie & Infrastructure', 'Maintenance des voies, locomotives et équipements techniques du réseau.'),
  ('Ressources Humaines', 'Recrutement, formation, gestion des carrières et bien-être des employés.'),
  ('Logistique & Fret', 'Transport de marchandises, coordination des dépôts et optimisation des flux.'),
  ('Finance & Contrôle de Gestion', 'Budget, comptabilité, audit interne et reporting financier.'),
  ('Sécurité & Sûreté', 'Protocoles de sécurité ferroviaire, gestion des risques et protection des infrastructures.'),
  ('Systèmes d\'Information', 'Infrastructure IT, cybersécurité, développement des outils internes.'),
  ('Communication & Relations Publiques', 'Image de marque, relations presse, communication interne et externe.');

-- =========================
-- EMPLOYES
-- =========================
CREATE TABLE Employes (
  id_employe   INT AUTO_INCREMENT PRIMARY KEY,
  nom          VARCHAR(100) NOT NULL,
  prenom       VARCHAR(100),
  poste        VARCHAR(100),
  email        VARCHAR(150),
  photo        VARCHAR(255),
  id_service   INT,
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_service) REFERENCES Services(id_service) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Employes (nom, prenom, poste, email, photo, id_service) VALUES
  ('MBIDA',    'Jean-Paul',   'Directeur des Opérations Ferroviaires', 'j.mbida@camrail.cm',    'https://lh3.googleusercontent.com/aida-public/AB6AXuBVNReDPZU2mf5eAvny2lqUvdx79uk2zSO0711MBsDOTH8ThYkjc9udocCQPHSy4Zl1NVAEGIG2LainhixUmMbYifdFt2oAoeoJ_aSsSvBOrSETJOMSkcQ_tTpC0ELRmy9byqnLHiHd6fMoOFVw2PG2uEUFDFvjBCLjx4rav6LEkBg5qWTNSXU1uDrcuamAPrjRFt7FLiFElRsnJGmQ9eLDFz8KpU0Kbfy_L9ZV04mfdk8e3Hyi1q9GmM_ulxrTRk0Ss4UVZOWTqS4', 1),
  ('KOUAM',    'Jean-Pierre', 'Logistics Manager',                     'jp.kouam@camrail.cm',   'https://lh3.googleusercontent.com/aida-public/AB6AXuCukTL4w0-5HMlwHzovohG-9aYm8LiFGEPuBEImdkOYoeG48-uWefQLZldD_9FU-A35LG6S12GKmLFNprA0_zFBKPyWGqLqJBHp8CCDkfCdTp4JnZkmvxP0n0WeLYLFr_qv5d5Gp7Zgb5RIDtioocK2G1WlpcytXgIcBkuQYu00heir1GpeT2y0-wczOD1ITURxvFVtEAWxeZ6o19ccgZW60zGz0fMEl6QrGMz9Jyp4fpRrSCAAMxxxMtNiY9EU9AXPH1PR8PNuzwk', 4),
  ('ESSOMBA',  'Sarah',       'Responsable RH',                        's.essomba@camrail.cm',  'https://lh3.googleusercontent.com/aida-public/AB6AXuAPeni94D_bB8sj-3khi2GWueVGZAQ5R6jKQ5u3ig0CGf88d1Q_pt78cC8kfgSLDeA3YvzmErKHvLRi3Npt5_1h_tMwZEIcOtM3B9Z9n7xNhUSFtPmP3yAsmv8lhTUKlTrXL82sgjeeh6K8n1ZhcJnkM04vV3aT9h0dHf8WmpB_konk1iSwbU7qNwyro9WHw_20XnOZMT__G6l_5lpn4ujWb0Vhx6PkjCyyM_5xvqSnhURmAVsUZXWPr8h7_oK6M2U84faEYNNVzLg', 3),
  ('N\'DOUMBE','Amelie',      'Directrice des Opérations',             'a.ndoumbe@camrail.cm',  'https://lh3.googleusercontent.com/aida-public/AB6AXuBFRN9ih7MdEQbpWiHt0MbhU_rNB53ePr78Yi9QAdi3uZKlb1-7mqpdHxKwOF1uXvp0eAxjJi8MAu1xKm3zTYmOy918Z1U3NNC2Nw2EyxWY-Acinz0QWaVTohcO1dYVveTcUFLUMdg6NoUZuHUs1z5ettiSinJu7r8E3MSc6j5Uuvr8UBK9ckfQeTeRdZCK2V0QHWvv18cSYfUoPbYt-gDWGwe3S4xkPtxhQOFxHlDqaiSQJmR7FBqqkI0RzOfcaH6zRz4JwLiXuoQ', 1),
  ('BELINGA',  'Jean-Pierre', 'Inspecteur Sécurité',                   'jp.belinga@camrail.cm', 'https://lh3.googleusercontent.com/aida-public/AB6AXuCLaqVGLXjyF5TXXoDDp58ta38N1aGhK0CKCNY1wzcdXdbu5TTv9cpYifb_xp5Ov2JyMR8YkL11ux6OsAPaZG8e9ViS60UWTjSmK447M5Fc4RBbAjexGSCiJ4E7oroK8n_gRuFk7nqXRF35BQi1f2R8HKKMRXD-E_KIFW4pz1q62NvtptczylKa0DKtRERsazKTL8CAcYyyJfXqQ93FJ9VGiZZI2d-vlxE1aVlFzPSAH9XtrQST4y4thIZwE2B-H2bCuXc5ZvTWoo4', 6),
  ('TAGNE',    'Marc',        'Responsable Logistique',                'm.tagne@camrail.cm',    'https://lh3.googleusercontent.com/aida-public/AB6AXuCy804G9jlcfF-eFwrE-KwWPAWuZ8t_TrXoxP--PLxWXZURzJqZI6ldqG6fINoFBZRhv2jYhCmzEvMNyexYWw7HDnLHC4eeGKHkv4bo9xoFDnJmDmBvYEibgbU76RwRH5h-hxZijv2tAp2tq2sn_NcHotsKLfbL_Z0bLwQoUaSu8JHV04AJeXvYDM_Giyqt6vLy3TfeLvVnp0IhSovNsys0Tv0naSj0hmA5m_s1Y5Vhj135hasYsWl3YVLRUUmJTxvQQOu2yRq8knc', 4),
  ('MOUSSA',   'Lise',        'Coordinatrice de Flotte',               'l.moussa@camrail.cm',   NULL, 1),
  ('FAYE',     'Ibrahim',     'Ingénieur Ferroviaire Senior',          'i.faye@camrail.cm',     'https://lh3.googleusercontent.com/aida-public/AB6AXuBASc66T--gYCDkrOMpl3FzCq9rOyfqrKRdovqPQyaW2A3RP70loJj3YJtpi6BqxvoILn8feyscEMmRqMD17X0nZwVd2qpqmbtA9OAizv_gduYzjaO1VA_GOt7Y0W-oy-RyllUS0YpuFHq6m2OA0noBZP2HsIpnAGIE3brJ723GwQ1yc991ciSBNtxbrjZGL1Mpi8kFDWobm8RFV-T8ozDGNwsYy9y8oc7WXPY5cymd19l1TkLNxFT5nzSwdVq832fR39jAGyIAJpE', 2),
  ('FOKOUA',   'Alice',       'Directrice Générale Adjointe',          'a.fokoua@camrail.cm',   NULL, 5),
  ('BELLO',    'Amina',       'Chargée de Recrutement',                'am.bello@camrail.cm',   NULL, 3);

-- =========================
-- NUMEROS
-- =========================
CREATE TABLE Numeros (
  id_numero  INT AUTO_INCREMENT PRIMARY KEY,
  id_employe INT NOT NULL,
  type_numero ENUM('fixe','mobile','whatsapp','poste') DEFAULT 'mobile',
  numero     VARCHAR(30) NOT NULL,
  principal  BOOLEAN DEFAULT 0,
  FOREIGN KEY (id_employe) REFERENCES Employes(id_employe) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Numeros (id_employe, type_numero, numero, principal) VALUES
  (1, 'fixe',      '+237 233 45 67 89', 1),
  (1, 'poste',     '405',               0),
  (1, 'mobile',    '+237 677 88 99 00', 0),
  (2, 'mobile',    '+237 677 88 99 01', 1),
  (3, 'mobile',    '+237 699 11 22 33', 1),
  (3, 'whatsapp',  '+237 699 11 22 33', 0),
  (4, 'fixe',      '+237 233 10 20 30', 1),
  (5, 'mobile',    '+237 655 44 33 22', 1),
  (6, 'mobile',    '+237 677 12 34 56', 1),
  (7, 'mobile',    '+237 690 99 88 77', 1),
  (8, 'mobile',    '+237 655 66 77 88', 1);

-- =========================
-- ROLES
-- =========================
CREATE TABLE Roles (
  id_role  INT AUTO_INCREMENT PRIMARY KEY,
  nom_role VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Roles (nom_role) VALUES ('admin'), ('user');

-- =========================
-- UTILISATEURS
-- Mots de passe hashés avec password_hash($pwd, PASSWORD_DEFAULT) :
--   admin123  → stocké ci-dessous
--   user123   → stocké ci-dessous
-- Pour générer un nouveau hash PHP : echo password_hash('votre_mdp', PASSWORD_DEFAULT);
-- =========================
CREATE TABLE Utilisateurs (
  id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
  nom            VARCHAR(100),
  prenom         VARCHAR(100),
  login          VARCHAR(100) UNIQUE,
  mot_de_passe   VARCHAR(255),
  id_role        INT,
  date_creation  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_role) REFERENCES Roles(id_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Utilisateurs (nom, prenom, login, mot_de_passe, id_role) VALUES
  -- login: CR-000001 / mot de passe: admin123
  ('MBIDA', 'Jean-Paul', 'CR-000001',
   '$2y$10$TKh8H1.PfQx37YgCFWjlmOkVUM54M8S8F2JRd3yByNLSZRTR9bJm2', 1),
  -- login: CR-000002 / mot de passe: user123
  ('KOUAM', 'Jean-Pierre', 'CR-000002',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
  -- login: CR-000003 / mot de passe: sarah456
  ('ESSOMBA', 'Sarah', 'CR-000003',
   '$2y$10$vI8aWBnW3fID.ZQ4/zo1G.q1lRPvLm1oeWGK.0Ek7MeBjWGNhK1G', 2);

-- ⚠️  IMPORTANT : Les hashes ci-dessus sont des exemples.
--     Régénère-les avec ce script PHP avant import en production :
--
--     <?php
--     echo password_hash('admin123', PASSWORD_DEFAULT) . "\n";
--     echo password_hash('user123',  PASSWORD_DEFAULT) . "\n";
--     echo password_hash('sarah456', PASSWORD_DEFAULT) . "\n";

-- =========================
-- PERMISSIONS
-- =========================
CREATE TABLE Permissions (
  id_permission       INT AUTO_INCREMENT PRIMARY KEY,
  description_permission VARCHAR(150)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Permissions (description_permission) VALUES
  ('Voir le répertoire'),
  ('Ajouter un employé'),
  ('Modifier un employé'),
  ('Supprimer un employé'),
  ('Gérer les utilisateurs'),
  ('Voir le journal d\'activité'),
  ('Approuver les demandes de modification'),
  ('Exporter les données');

-- =========================
-- ROLE_PERMISSION
-- =========================
CREATE TABLE Role_Permission (
  id_role       INT,
  id_permission INT,
  PRIMARY KEY (id_role, id_permission),
  FOREIGN KEY (id_role)       REFERENCES Roles(id_role),
  FOREIGN KEY (id_permission) REFERENCES Permissions(id_permission)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin : toutes les permissions
INSERT INTO Role_Permission VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8);
-- User : seulement voir + modifier son propre profil
INSERT INTO Role_Permission VALUES (2,1),(2,3);

-- =========================
-- FAVORIS
-- =========================
CREATE TABLE Favoris (
  id_favori      INT AUTO_INCREMENT PRIMARY KEY,
  id_utilisateur INT NOT NULL,
  id_employe     INT NOT NULL,
  date_ajout     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_favori (id_utilisateur, id_employe),
  FOREIGN KEY (id_utilisateur) REFERENCES Utilisateurs(id_utilisateur) ON DELETE CASCADE,
  FOREIGN KEY (id_employe)     REFERENCES Employes(id_employe)         ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Quelques favoris de démo
INSERT INTO Favoris (id_utilisateur, id_employe) VALUES (2, 3), (2, 5), (2, 7);

-- =========================
-- HISTORIQUE
-- =========================
CREATE TABLE Historique (
  id_historique  INT AUTO_INCREMENT PRIMARY KEY,
  action         VARCHAR(50),
  table_cible    ENUM('Employes','Numeros','Services','Utilisateurs'),
  id_cible       INT,
  date_heure     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  id_utilisateur INT,
  FOREIGN KEY (id_utilisateur) REFERENCES Utilisateurs(id_utilisateur) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Quelques entrées de journal de démo
INSERT INTO Historique (action, table_cible, id_cible, id_utilisateur) VALUES
  ('LOGIN_SUCCESS',  'Utilisateurs', 1, 1),
  ('ADD_EMPLOYE',    'Employes',     9, 1),
  ('ADD_EMPLOYE',    'Employes',    10, 1),
  ('CHANGE_PASSWORD','Utilisateurs', 2, 2),
  ('LOGIN_SUCCESS',  'Utilisateurs', 2, 2);

-- =========================
-- DEMANDES DE MODIFICATION
-- =========================
CREATE TABLE Demandes_Modification (
  id_demande      INT AUTO_INCREMENT PRIMARY KEY,
  table_cible     ENUM('Employes','Numeros','Services'),
  id_cible        INT,
  champ_modifie   VARCHAR(100),
  ancienne_valeur TEXT,
  nouvelle_valeur TEXT,
  statut          ENUM('en_attente','valide','refuse') DEFAULT 'en_attente',
  type_validation ENUM('DIRECT','ADMIN') DEFAULT 'ADMIN',
  id_utilisateur  INT,
  id_admin        INT NULL,
  date_demande    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  date_validation TIMESTAMP NULL,
  FOREIGN KEY (id_utilisateur) REFERENCES Utilisateurs(id_utilisateur) ON DELETE SET NULL,
  FOREIGN KEY (id_admin)       REFERENCES Utilisateurs(id_utilisateur) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Quelques demandes de démo
INSERT INTO Demandes_Modification (table_cible, id_cible, champ_modifie, ancienne_valeur, nouvelle_valeur, statut, type_validation, id_utilisateur) VALUES
  ('Employes', 3, 'poste',  'Responsable RH', 'DRH Adjointe',          'en_attente', 'ADMIN', 3),
  ('Employes', 6, 'email',  'm.tagne@camrail.cm', 'marc.tagne@camrail.cm', 'en_attente', 'ADMIN', 2),
  ('Employes', 7, 'poste',  'Coordinatrice de Flotte', 'Chef de Flotte', 'valide', 'ADMIN', 2);

-- =========================
-- INDEX pour les performances
-- =========================
CREATE INDEX idx_employes_service ON Employes(id_service);
CREATE INDEX idx_employes_nom     ON Employes(nom, prenom);
CREATE INDEX idx_numeros_employe  ON Numeros(id_employe);
CREATE INDEX idx_historique_date  ON Historique(date_heure DESC);
CREATE INDEX idx_demandes_statut  ON Demandes_Modification(statut);

-- =========================
-- FIN
-- =========================
SELECT 'Base de données Camrail importée avec succès !' AS statut;
SELECT CONCAT(COUNT(*), ' employés chargés') AS employes FROM Employes;
SELECT CONCAT(COUNT(*), ' utilisateurs créés') AS utilisateurs FROM Utilisateurs;