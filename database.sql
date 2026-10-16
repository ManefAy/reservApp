-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Oct 16, 2026 at 11:26 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gestion_reservations`
--

-- --------------------------------------------------------

--
-- Table structure for table `avis`
--

DROP TABLE IF EXISTS `avis`;
CREATE TABLE IF NOT EXISTS `avis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `note` tinyint NOT NULL,
  `commentaire` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `creneaux`
--

DROP TABLE IF EXISTS `creneaux`;
CREATE TABLE IF NOT EXISTS `creneaux` (
  `id` int NOT NULL AUTO_INCREMENT,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `libelle` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `creneaux`
--

INSERT INTO `creneaux` (`id`, `heure_debut`, `heure_fin`, `libelle`) VALUES
(1, '08:00:00', '10:00:00', '08h - 10h'),
(2, '10:00:00', '12:00:00', '10h - 12h'),
(3, '14:00:00', '16:00:00', '14h - 16h'),
(4, '16:00:00', '18:00:00', '16h - 18h');

-- --------------------------------------------------------

--
-- Table structure for table `employes`
--

DROP TABLE IF EXISTS `employes`;
CREATE TABLE IF NOT EXISTS `employes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `departement` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otp_codes`
--

DROP TABLE IF EXISTS `otp_codes`;
CREATE TABLE IF NOT EXISTS `otp_codes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateurs_id` int NOT NULL,
  `code` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `utilisateurs_id` (`utilisateurs_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE IF NOT EXISTS `reservations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `salle_id` int NOT NULL,
  `utilisateur_id` int NOT NULL,
  `date_reservation` date NOT NULL,
  `creneau_id` int NOT NULL,
  `statut` enum('confirmee','annulee') COLLATE utf8mb4_unicode_ci DEFAULT 'confirmee',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_reservation` (`salle_id`,`date_reservation`,`creneau_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `creneau_id` (`creneau_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salles`
--

DROP TABLE IF EXISTS `salles`;
CREATE TABLE IF NOT EXISTS `salles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `capacite` int NOT NULL,
  `type_salle` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `equipements` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `login` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('administrateur','employe','membre') COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employes`
--
ALTER TABLE `employes`
  ADD CONSTRAINT `employes_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`salle_id`) REFERENCES `salles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`creneau_id`) REFERENCES `creneaux` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
