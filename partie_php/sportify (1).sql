-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 28 mai 2025 à 16:49
-- Version du serveur : 9.1.0
-- Version de PHP : 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `sportify`
--

-- --------------------------------------------------------

--
-- Structure de la table `activites_sportives`
--

DROP TABLE IF EXISTS `activites_sportives`;
CREATE TABLE IF NOT EXISTS `activites_sportives` (
  `id_activite` int NOT NULL AUTO_INCREMENT,
  `nom_activite` varchar(100) NOT NULL,
  `id_categorie` int DEFAULT NULL,
  `description` text,
  `prix` decimal(10,2) DEFAULT '0.00',
  `duree_seance` int DEFAULT '60',
  `actif` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_activite`),
  KEY `id_categorie` (`id_categorie`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `activites_sportives`
--

INSERT INTO `activites_sportives` (`id_activite`, `nom_activite`, `id_categorie`, `description`, `prix`, `duree_seance`, `actif`) VALUES
(1, 'Musculation', 1, 'Toutes les activités de musculation', 14.99, 60, 1),
(2, 'Fitness', 1, 'Séances de fitness pour tous niveaux', 14.99, 60, 1),
(3, 'Biking', 1, 'Cours de vélo en salle', 14.99, 60, 1),
(4, 'Cardio-Training', 1, 'Exercices cardio intenses', 14.99, 60, 1),
(5, 'Cours Collectifs', 1, 'Yoga, dance et autres cours de groupe en salle', 14.99, 60, 1),
(6, 'Basketball', 2, 'Entraînement collectif et individuel', 24.99, 120, 1),
(7, 'Football', 2, 'Techniques et tactiques de football', 24.99, 120, 1),
(8, 'Rugby', 2, 'Préparation physique et jeu en équipe', 24.99, 120, 1),
(9, 'Tennis', 2, 'Exercices cardio intenses', 24.99, 120, 1),
(10, 'Natation', 2, 'Perfectionnement en nage libre, dos, etc.', 24.99, 120, 1),
(11, 'Plongeon', 2, 'Techniques de plongeon et de saut', 24.99, 120, 1);

-- --------------------------------------------------------

--
-- Structure de la table `cartes_paiement`
--

DROP TABLE IF EXISTS `cartes_paiement`;
CREATE TABLE IF NOT EXISTS `cartes_paiement` (
  `id_carte` int NOT NULL AUTO_INCREMENT,
  `id_client` int UNSIGNED NOT NULL,
  `type_carte` enum('visa','mastercard','amex','paypal') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `numero_carte_masque` varchar(20) NOT NULL,
  `cryptogramme` char(3) NOT NULL DEFAULT '000',
  `nom_carte` varchar(255) NOT NULL,
  `date_expiration` date DEFAULT NULL,
  `date_ajout` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_carte`),
  KEY `fk_cartes_client` (`id_client`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `cartes_paiement`
--

INSERT INTO `cartes_paiement` (`id_carte`, `id_client`, `type_carte`, `numero_carte_masque`, `cryptogramme`, `nom_carte`, `date_expiration`, `date_ajout`) VALUES
(5, 19, 'visa', '4452033010650535', '120', 'Michel', '0000-00-00', '2025-05-28 14:36:26');

-- --------------------------------------------------------

--
-- Structure de la table `categories_sport`
--

DROP TABLE IF EXISTS `categories_sport`;
CREATE TABLE IF NOT EXISTS `categories_sport` (
  `id_categorie` int NOT NULL AUTO_INCREMENT,
  `nom_categorie` enum('activites_sportives','sports_competition','salle_sport') NOT NULL,
  `description` text,
  PRIMARY KEY (`id_categorie`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `categories_sport`
--

INSERT INTO `categories_sport` (`id_categorie`, `nom_categorie`, `description`) VALUES
(1, 'activites_sportives', 'Toutes les activités disponibles pour les athlètes de tout niveau.'),
(2, 'sports_competition', 'Sports de compétition pour les athlètes plus habiles.');

-- --------------------------------------------------------

--
-- Structure de la table `clients`
--

DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
  `id_client` int NOT NULL,
  `adresse_ligne1` varchar(255) DEFAULT NULL,
  `adresse_ligne2` varchar(255) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `code_postal` varchar(10) DEFAULT NULL,
  `pays` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `carte_etudiant` varchar(50) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  PRIMARY KEY (`id_client`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `clients`
--

INSERT INTO `clients` (`id_client`, `adresse_ligne1`, `adresse_ligne2`, `ville`, `code_postal`, `pays`, `telephone`, `carte_etudiant`, `date_naissance`) VALUES
(16, '10 rue Sextius Michel', NULL, 'PARIS', '75015', 'France', '+33626364320', '2112', '2004-02-02'),
(17, '1 Rue cool', '2 Rue style', 'Paris', '75010', 'France', '+33 02030405', '3150', '2002-02-22'),
(18, '10 rue de france', NULL, 'Paris', '75011', 'France', '+33 0000000000', '1234', '2001-12-04'),
(19, '1 rue de michel', NULL, 'Paris', '75010', 'France', '+33 00002200', '1234567', '2002-02-01');

-- --------------------------------------------------------

--
-- Structure de la table `coachs`
--

DROP TABLE IF EXISTS `coachs`;
CREATE TABLE IF NOT EXISTS `coachs` (
  `id_coach` int NOT NULL,
  `bureau` varchar(100) DEFAULT NULL,
  `telephone_bureau` varchar(20) DEFAULT NULL,
  `specialite_principale` varchar(100) DEFAULT NULL,
  `photo_url` varchar(500) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `cv_xml` text,
  `statut_disponibilite` enum('disponible','occupe','absent') DEFAULT 'disponible',
  `date_embauche` date DEFAULT NULL,
  PRIMARY KEY (`id_coach`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `coachs`
--

INSERT INTO `coachs` (`id_coach`, `bureau`, `telephone_bureau`, `specialite_principale`, `photo_url`, `video_url`, `cv_xml`, `statut_disponibilite`, `date_embauche`) VALUES
(2, 'SC-01', '+33 6 12 34 56 01', 'Musculation', NULL, NULL, '<cv id=\"1\">\n    <Nom>Dupont</Nom>\n    <Prenom>Alice</Prenom>\n    <Discipline>Musculation</Discipline>\n    <Email>alice.dupont@omneseducation.fr</Email>\n    <Telephone>+33 6 12 34 56 01</Telephone>\n    <Adresse>\n      <Rue>10 Rue de la Liberté</Rue>\n      <CodePostal>75001</CodePostal>\n      <Ville>Paris</Ville>\n    </Adresse>\n    <Formation>DEJEPS Activités Gymniques</Formation>\n    <Experience>5 ans d\'expérience</Experience>\n  </cv>', 'disponible', '2025-05-27'),
(3, 'SC-02', '+33 6 12 34 56 02', 'Fitness', NULL, NULL, '<cv id=\"2\">\n    <Nom>Martin</Nom>\n    <Prenom>Bob</Prenom>\n    <Discipline>Fitness</Discipline>\n    <Email>bob.martin@omneseducation.fr</Email>\n    <Telephone>+33 6 12 34 56 02</Telephone>\n    <Adresse>\n      <Rue>22 Avenue Victor Hugo</Rue>\n      <CodePostal>69002</CodePostal>\n      <Ville>Lyon</Ville>\n    </Adresse>\n    <Formation>Licence STAPS</Formation>\n    <Experience>4 ans d\'expérience</Experience>\n  </cv>', 'disponible', '2025-05-27'),
(4, 'SC-03', '+33 6 12 34 56 03', 'Biking', NULL, NULL, '<cv id=\"3\">\n    <Nom>Durand</Nom>\n    <Prenom>Chloé</Prenom>\n    <Discipline>Biking</Discipline>\n    <Email>chloe.durand@omneseducation.fr</Email>\n    <Telephone>+33 6 12 34 56 03</Telephone>\n    <Adresse>\n      <Rue>5 Place Bellecour</Rue>\n      <CodePostal>69002</CodePostal>\n      <Ville>Lyon</Ville>\n    </Adresse>\n    <Formation>BPJEPS Activités Cyclistes</Formation>\n    <Experience>3 ans d\'expérience</Experience>\n  </cv>', 'disponible', '2025-05-27'),
(5, 'SC-04', '+33 6 12 34 56 04', 'Cardio-Training', NULL, NULL, '<cv id=\"4\">\n    <Nom>Bernard</Nom>\n    <Prenom>David</Prenom>\n    <Discipline>Cardio-Training</Discipline>\n    <Email>david.bernard@omneseducation.fr</Email>\n    <Telephone>+33 6 12 34 56 04</Telephone>\n    <Adresse>\n      <Rue>14 Rue Faidherbe</Rue>\n      <CodePostal>59000</CodePostal>\n      <Ville>Lille</Ville>\n    </Adresse>\n    <Formation>DEJEPS Performance Sportive</Formation>\n    <Experience>6 ans d\'expérience</Experience>\n  </cv>', 'disponible', '2025-05-27'),
(6, 'SC-05', '+33 6 12 34 56 05', 'Cours Collectifs', NULL, NULL, '<cv id=\"5\">\n    <Nom>Lefebvre</Nom>\n    <Prenom>Emilie</Prenom>\n    <Discipline>Cours Collectifs</Discipline>\n    <Email>emilie.lefebvre@omneseducation.fr</Email>\n    <Telephone>+33 6 12 34 56 05</Telephone>\n    <Adresse>\n      <Rue>8 Rue du Fort</Rue>\n      <CodePostal>34000</CodePostal>\n      <Ville>Montpellier</Ville>\n    </Adresse>\n    <Formation>Formation Yoga Alliance</Formation>\n    <Experience>5 ans d\'expérience</Experience>\n  </cv>', 'disponible', '2025-05-27'),
(7, 'SC-06', '+33 6 12 34 56 06', 'Basketball', NULL, NULL, '<cv id=\"6\">\n    <Nom>Moreau</Nom>\n    <Prenom>François</Prenom>\n    <Discipline>Basketball</Discipline>\n    <Email>francois.moreau@omneseducation.fr</Email>\n    <Telephone>+33 6 12 34 56 06</Telephone>\n    <Adresse>\n      <Rue>30 Rue de la République</Rue>\n      <CodePostal>13001</CodePostal>\n      <Ville>Marseille</Ville>\n    </Adresse>\n    <Formation>Licence STAPS Mention Basket</Formation>\n    <Experience>7 ans d\'expérience</Experience>\n  </cv>', 'disponible', '2025-05-27'),
(8, 'SC-07', '+33 6 12 34 56 07', 'Football', NULL, NULL, '<cv id=\"7\">\n    <Nom>Petit</Nom>\n    <Prenom>Gabrielle</Prenom>\n    <Discipline>Football</Discipline>\n    <Email>gabrielle.petit@omneseducation.fr</Email>\n    <Telephone>+33 6 12 34 56 07</Telephone>\n    <Adresse>\n      <Rue>12 Boulevard Haussmann</Rue>\n      <CodePostal>75009</CodePostal>\n      <Ville>Paris</Ville>\n    </Adresse>\n    <Formation>DEJEPS Football</Formation>\n    <Experience>6 ans d\'expérience</Experience>\n  </cv>', 'disponible', '2025-05-27'),
(9, 'SC-08', '+33 6 12 34 56 08', 'Rugby', NULL, NULL, '<cv id=\"8\">\n    <Nom>Dubois</Nom>\n    <Prenom>Hugo</Prenom>\n    <Discipline>Rugby</Discipline>\n    <Email>hugo.dubois@omneseducation.fr</Email>\n    <Telephone>+33 6 12 34 56 08</Telephone>\n    <Adresse>\n      <Rue>18 Allée de la Gare</Rue>\n      <CodePostal>31000</CodePostal>\n      <Ville>Toulouse</Ville>\n    </Adresse>\n    <Formation>BPJEPS Rugby</Formation>\n    <Experience>4 ans d\'expérience</Experience>\n  </cv>', 'disponible', '2025-05-27'),
(10, 'SC-09', '+33 6 12 34 56 09', 'Tennis', NULL, NULL, '<cv id=\"9\">\n    <Nom>Laurent</Nom>\n    <Prenom>Isabelle</Prenom>\n    <Discipline>Tennis</Discipline>\n    <Email>isabelle.laurent@omneseducation.fr</Email>\n    <Telephone>+33 6 12 34 56 09</Telephone>\n    <Adresse>\n      <Rue>2 Rue Gambetta</Rue>\n      <CodePostal>33000</CodePostal>\n      <Ville>Bordeaux</Ville>\n    </Adresse>\n    <Formation>DEJEPS Tennis</Formation>\n    <Experience>5 ans d\'expérience</Experience>\n  </cv>', 'disponible', '2025-05-27'),
(11, 'SC-10', '+33 6 12 34 56 10', 'Natation', NULL, NULL, '<cv id=\"10\">\n    <Nom>Garnier</Nom>\n    <Prenom>Julien</Prenom>\n    <Discipline>Natation</Discipline>\n    <Email>julien.garnier@omneseducation.fr</Email>\n    <Telephone>+33 6 12 34 56 10</Telephone>\n    <Adresse>\n      <Rue>45 Quai de la Douane</Rue>\n      <CodePostal>67000</CodePostal>\n      <Ville>Strasbourg</Ville>\n    </Adresse>\n    <Formation>BPJEPS Natation</Formation>\n    <Experience>5 ans d\'expérience</Experience>\n  </cv>', 'disponible', '2025-05-27'),
(12, 'SC-11', '+33 6 12 34 56 11', 'Plongeon', NULL, NULL, '<cv id=\"11\">\n    <Nom>Roy</Nom>\n    <Prenom>Karine</Prenom>\n    <Discipline>Plongeon</Discipline>\n    <Email>karine.roy@omneseducation.fr</Email>\n    <Telephone>+33 6 12 34 56 11</Telephone>\n    <Adresse>\n      <Rue>7 Rue de Sèze</Rue>\n      <CodePostal>33000</CodePostal>\n      <Ville>Bordeaux</Ville>\n    </Adresse>\n    <Formation>DEJEPS Plongeon</Formation>\n    <Experience>4 ans d\'expérience</Experience>\n  </cv>', 'disponible', '2025-05-27'),
(13, 'SC-12', '+33 6 12 34 56 12', 'Zumba', NULL, NULL, '<cv id=\"12\">\n    <Nom>Fontaine</Nom>\n    <Prenom>Laurent</Prenom>\n    <Discipline>Zumba</Discipline>\n    <Email>laurent.fontaine@omneseducation.fr</Email>\n    <Telephone>+33 6 12 34 56 12</Telephone>\n    <Adresse>\n      <Rue>3 Rue de la Sorbonne</Rue>\n      <CodePostal>75005</CodePostal>\n      <Ville>Paris</Ville>\n    </Adresse>\n    <Formation>Diplôme Zumba Instructor</Formation>\n    <Experience>3 ans d\'expérience</Experience>\n  </cv>', 'disponible', '2025-05-27');

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

DROP TABLE IF EXISTS `paiements`;
CREATE TABLE IF NOT EXISTS `paiements` (
  `id_paiement` int NOT NULL AUTO_INCREMENT,
  `id_client` int NOT NULL,
  `id_rdv` int NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `type_carte` enum('visa','mastercard','amex') NOT NULL,
  `numero_carte_masque` varchar(20) NOT NULL,
  `statut_paiement` enum('en_attente','reussi','echec') DEFAULT 'en_attente',
  `date_paiement` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reference_transaction` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_paiement`),
  UNIQUE KEY `reference_transaction` (`reference_transaction`),
  KEY `id_client` (`id_client`),
  KEY `fk_paiements_rdv` (`id_rdv`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rendez_vous`
--

DROP TABLE IF EXISTS `rendez_vous`;
CREATE TABLE IF NOT EXISTS `rendez_vous` (
  `id_rdv` int NOT NULL AUTO_INCREMENT,
  `id_client` int NOT NULL,
  `id_coach` int NOT NULL,
  `id_activite` int DEFAULT NULL,
  `date_rdv` datetime NOT NULL,
  `duree` int DEFAULT '60',
  `statut` enum('confirme','annule','termine','en_attente') DEFAULT 'en_attente',
  `lieu` varchar(255) DEFAULT NULL,
  `notes` text,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_rdv`),
  KEY `id_client` (`id_client`),
  KEY `id_coach` (`id_coach`),
  KEY `id_activite` (`id_activite`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `salle_sport`
--

DROP TABLE IF EXISTS `salle_sport`;
CREATE TABLE IF NOT EXISTS `salle_sport` (
  `id_salle` int NOT NULL AUTO_INCREMENT,
  `numero_salle` varchar(10) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `description` text,
  `actif` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_salle`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `salle_sport`
--

INSERT INTO `salle_sport` (`id_salle`, `numero_salle`, `telephone`, `email`, `description`, `actif`) VALUES
(1, 'SP-01', '0614243355', 'salle.sport@omneseducation.fr', 'La salle de mega salle de sport de Omnes!', 1);

-- --------------------------------------------------------

--
-- Structure de la table `services_salle_sport`
--

DROP TABLE IF EXISTS `services_salle_sport`;
CREATE TABLE IF NOT EXISTS `services_salle_sport` (
  `id_service` int NOT NULL AUTO_INCREMENT,
  `id_salle` int NOT NULL,
  `nom_service` varchar(255) NOT NULL,
  `description` text,
  `actif` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_service`),
  KEY `id_salle` (`id_salle`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id_utilisateur` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(191) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `type_utilisateur` enum('admin','coach','client') NOT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actif` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_utilisateur`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id_utilisateur`, `nom`, `prenom`, `email`, `mot_de_passe`, `type_utilisateur`, `date_creation`, `actif`) VALUES
(2, 'Dupont', 'Alice', 'alice.dupont@omneseducation.fr', '$2y$12$BfKvueotOnSNkuu85K//u.0Jb0dI8e7XgvjkZPgildDT3pqpxZPlq', 'coach', '2025-05-27 11:27:39', 1),
(3, 'Martin', 'Bob', 'bob.martin@omneseducation.fr', '$2y$12$6SuHgpqHOUyFZjJlYzlET.ISN4fUYrcouGQJvw17iJb6DZvO/1MoK', 'coach', '2025-05-27 11:27:40', 1),
(4, 'Durand', 'Chloé', 'chloe.durand@omneseducation.fr', '$2y$12$dsiNkSuT8C0DJ0jbTUTMWO3NkuZdNsv.c43nbf4OfOdnx.bVeZRmO', 'coach', '2025-05-27 11:27:40', 1),
(5, 'Bernard', 'David', 'david.bernard@omneseducation.fr', '$2y$12$yYF6h58I.bzNh.mdPCgmteo9tdxzaFdefterwPyGXEtyNCfLN4EUa', 'coach', '2025-05-27 11:27:41', 1),
(6, 'Lefebvre', 'Emilie', 'emilie.lefebvre@omneseducation.fr', '$2y$12$tQhaFRaO8LmlqXzC2uISnuZa.1wSxhmNUAgOKPjo1yK2A2JpIKvpC', 'coach', '2025-05-27 11:27:41', 1),
(7, 'Moreau', 'François', 'francois.moreau@omneseducation.fr', '$2y$12$lO328tfmjXmy7c4b/lHiMO2/5rFYM/dTp..6XLKFcqH0unO7HKcOy', 'coach', '2025-05-27 11:27:42', 1),
(8, 'Petit', 'Gabrielle', 'gabrielle.petit@omneseducation.fr', '$2y$12$/bABovheK/z6jfaY9n27/e3RaNN8JQxGI44sPSv55c6/gN6Q4myuG', 'coach', '2025-05-27 11:27:42', 1),
(9, 'Dubois', 'Hugo', 'hugo.dubois@omneseducation.fr', '$2y$12$GEkFV/6Wg0czGlRp38y4DuXwiFyMRK0BOWzNf2jA1tDhjSm8L.1S.', 'coach', '2025-05-27 11:27:42', 1),
(10, 'Laurent', 'Isabelle', 'isabelle.laurent@omneseducation.fr', '$2y$12$LrvR36ku3hTKC8cyXtLlle3pmgTmRwH1jLeJI0it3mOZ4g0kX7xIq', 'coach', '2025-05-27 11:27:43', 1),
(11, 'Garnier', 'Julien', 'julien.garnier@omneseducation.fr', '$2y$12$hm1S/GeRFEGoeVr/1qbYVOj5mXCGpbm9kkwUfDQxrat8RLbGIUHby', 'coach', '2025-05-27 11:27:43', 1),
(12, 'Roy', 'Karine', 'karine.roy@omneseducation.fr', '$2y$12$81Dv.yVW/8JA70qeExgLpuOn2zrf9FzvrOLUtDXW6906yzW9JOlx2', 'coach', '2025-05-27 11:27:44', 1),
(13, 'Fontaine', 'Laurent', 'laurent.fontaine@omneseducation.fr', '$2y$12$VpY/OxaU/swx9/i8DfK19e/nLM/nZAP2ZmLV90wBqU9.FpXVnm6JG', 'coach', '2025-05-27 11:27:44', 1),
(14, 'ADMIN', 'ADMIN', 'admin@omneseducation.fr', 'admin1234', 'admin', '2025-05-27 11:32:23', 1),
(16, 'Tung Tung Tung', 'Sahur', 'sahur.tung@omneseducation.fr', '$2y$12$Bjcee4zkg4oa3fMwsV2EdubyMnb2PQ.GOdzzWKChG7RjMevQLDLae', 'client', '2025-05-27 16:41:23', 1),
(17, 'Wipliez', 'Aarron', 'aaron.wipliez@omneseducation.fr', '$2y$12$t1hdHmx3AZ6.W9iZRd7cZObgSMI1LZ04C68zBcpyG6d0ZG8Q1vlwq', 'client', '2025-05-27 17:02:44', 1),
(18, 'Michel', 'Michelet', 'michel@omneseducation.fr', '$2y$12$csrCUnbpW4qV15IAd90pEOr39h98c.OfoB21nB6nmlWo/DXV2wRA.', 'client', '2025-05-28 10:21:30', 1),
(19, 'robert', 'michel', 'rmichel@omneseducation.fr', '$2y$12$lW99zciXyZdiqw/UWbnLEOxngT33u6NjBszqjAnaRjS05LqxZRYni', 'client', '2025-05-28 12:12:23', 1);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `cartes_paiement`
--
ALTER TABLE `cartes_paiement`
  ADD CONSTRAINT `fk_cartes_client` FOREIGN KEY (`id_client`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
