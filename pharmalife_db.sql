-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 19 juin 2026 à 23:08
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `pharmalife_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `administrateurs`
--

CREATE TABLE `administrateurs` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `administrateurs`
--

INSERT INTO `administrateurs` (`id_admin`, `username`, `password`, `email`, `date_creation`) VALUES
(1, 'admin', '123456', 'admin@pharmalife.com', '2026-05-25 04:01:05');

-- --------------------------------------------------------

--
-- Structure de la table `medecins`
--

CREATE TABLE `medecins` (
  `id_medecin` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `matricule_medical` varchar(20) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `specialite` varchar(100) DEFAULT 'Généraliste',
  `telephone` varchar(15) DEFAULT NULL,
  `adresse_cabinet` text DEFAULT NULL,
  `ville` varchar(50) DEFAULT 'Skikda',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` varchar(20) NOT NULL DEFAULT 'en_attente',
  `document_justificatif` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `medecins`
--

INSERT INTO `medecins` (`id_medecin`, `id_utilisateur`, `matricule_medical`, `nom`, `prenom`, `specialite`, `telephone`, `adresse_cabinet`, `ville`, `created_at`, `statut`, `document_justificatif`) VALUES
(6, 6, '21/4500', 'Belaid', 'Amine', 'Cardiologue', '033712233', 'Avenue Didouche Mourad', 'Skikda', '2026-05-10 19:23:46', 'actif', NULL),
(7, 7, '21/3210', 'Hamidi', 'Karima', 'Pédiatre', '033715566', 'Cité 20 Août 1955', 'Skikda', '2026-05-10 19:23:46', 'actif', NULL),
(8, 8, '18/9874', 'Merzoug', 'Salim', 'Ophtalmologue', '033748899', 'Rue Bachir Boukadoum', 'Skikda', '2026-05-10 19:23:46', 'actif', NULL),
(9, 9, '22/1122', 'Mansouri', 'Feriel', 'Gynécologue', '033710011', 'Quartier Napolitain', 'Skikda', '2026-05-10 19:23:46', 'actif', NULL),
(10, 10, '15/6543', 'Kaci', 'Yacine', 'Généraliste', '033723344', 'Zighoud Youcef', 'Skikda', '2026-05-10 19:23:46', 'actif', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `medicaments`
--

CREATE TABLE `medicaments` (
  `id_medoc` int(11) NOT NULL,
  `nom_medicament` varchar(100) NOT NULL,
  `forme` enum('Gélule','Comprimé','Sirop','Injection','Pommade') DEFAULT 'Comprimé'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `medicaments`
--

INSERT INTO `medicaments` (`id_medoc`, `nom_medicament`, `forme`) VALUES
(1, 'Paracétamol 500mg', 'Comprimé'),
(2, 'Amoxicilline 1g', 'Gélule'),
(3, 'Spasfon', 'Comprimé'),
(4, 'Ventoline', 'Sirop'),
(5, 'Augmentin', 'Comprimé'),
(6, 'Dolirhume', 'Comprimé'),
(7, 'Voltarene', 'Pommade'),
(8, 'Doliprane 500mg', 'Gélule'),
(9, 'Doliprane 1g', 'Comprimé'),
(10, 'Efferalgan 1g', ''),
(11, 'Aspegic 500mg', ''),
(12, 'Profénid 100mg', 'Gélule'),
(13, 'Clamoxyl 1g', 'Gélule'),
(14, 'Amoxicilline 500mg', 'Gélule'),
(15, 'Zithromax 500mg', 'Comprimé'),
(16, 'Oroken 200mg', 'Comprimé'),
(17, 'Pyostacine 500mg', 'Comprimé'),
(18, 'Aulin 100mg', 'Comprimé'),
(19, 'Apranax 550mg', 'Comprimé'),
(20, 'Nurofen 400mg', ''),
(21, 'Celebrex 200mg', 'Gélule'),
(22, 'Diclofenac 50mg', 'Comprimé'),
(23, 'Inexium 40mg', 'Comprimé'),
(24, 'Mopral 20mg', 'Gélule'),
(25, 'Gaviscon', ''),
(26, 'Phosphate de l\'Aluminium', ''),
(27, 'Smecta', ''),
(28, 'Meteospasmyl', ''),
(29, 'Debridat', 'Comprimé'),
(30, 'Amlor 5mg', 'Gélule'),
(31, 'Co-Tarek', 'Comprimé'),
(32, 'Lasilix 40mg', 'Comprimé'),
(33, 'Kardegic 75mg', ''),
(34, 'Tritace 5mg', 'Comprimé'),
(35, 'Aprovel 150mg', 'Comprimé'),
(36, 'Glucophage 850mg', 'Comprimé'),
(37, 'Diamicron 60mg', 'Comprimé'),
(38, 'Daonil 5mg', 'Comprimé'),
(39, 'Januvia 100mg', 'Comprimé'),
(40, 'Primalan', 'Comprimé'),
(41, 'Clarityne', 'Comprimé'),
(42, 'Zyrtec 10mg', 'Comprimé'),
(43, 'Xyzall 5mg', 'Comprimé'),
(44, 'Solupred 20mg', ''),
(45, 'Cortancyl 5mg', 'Comprimé'),
(46, 'Celestene 0.05%', ''),
(47, 'Rhinathiol Adultes', 'Sirop'),
(48, 'Toplexil', 'Sirop'),
(49, 'Flixotide 125mg', ''),
(50, 'Maxilase', 'Sirop'),
(51, 'Humex Rhume', 'Comprimé'),
(52, 'Lexomil 6mg', ''),
(53, 'Xanax 0.5mg', 'Comprimé'),
(54, 'Lysanxia 10mg', 'Comprimé'),
(55, 'Stilnox 10mg', 'Comprimé'),
(56, 'Biafine', ''),
(57, 'Fucidine 2%', 'Pommade'),
(58, 'Diprosone 0.05%', ''),
(59, 'Ketoderm 2%', ''),
(60, 'Magné B6', 'Comprimé'),
(61, 'Tardyferon 80mg', 'Comprimé'),
(62, 'Speciafoldine 5mg', 'Comprimé'),
(63, 'Vitamin C 1000mg', '');

-- --------------------------------------------------------

--
-- Structure de la table `messages_contact`
--

CREATE TABLE `messages_contact` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date_envoi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messages_contact`
--

INSERT INTO `messages_contact` (`id`, `nom`, `email`, `message`, `date_envoi`) VALUES
(1, 'aefgf', 'dddddrdrd@hyk.ki', 'bonjour', '2026-06-10 05:45:42');

-- --------------------------------------------------------

--
-- Structure de la table `ordonnances`
--

CREATE TABLE `ordonnances` (
  `id_ordonnance` int(11) NOT NULL,
  `id_medecin` int(11) NOT NULL,
  `id_patient` int(11) NOT NULL,
  `medecin_nom` varchar(100) NOT NULL,
  `contenu_medocs` text NOT NULL,
  `qr_token` varchar(100) NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` enum('active','terminee') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `ordonnances`
--

INSERT INTO `ordonnances` (`id_ordonnance`, `id_medecin`, `id_patient`, `medecin_nom`, `contenu_medocs`, `qr_token`, `date_creation`, `statut`) VALUES
(1, 6, 1, '', '[{\"medicament\":\"Amoxicilline 1g\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"5 jours\"},{\"medicament\":\"Voltarene\",\"posologie\":\"1 comp x 2 \\/ jour\",\"duree\":\"6 jours\"}]', '1426f13c5eb8d030bf99900616e7700b', '2026-05-10 20:03:02', 'active'),
(2, 6, 1, '', '[{\"medicament\":\"Amoxicilline 1g\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"7 jours\"},{\"medicament\":\"Voltarene\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"5 jours\"}]', '0ec47f9911545c159f5eafdad0bb5dc0', '2026-05-10 20:43:51', 'active'),
(3, 6, 1, '', '[{\"medicament\":\"Spasfon\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"5 jours\"},{\"medicament\":\"Dolirhume\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"2 jours\"}]', '13e657a28b04107023aa537ad21ef6d0', '2026-05-10 21:12:22', 'active'),
(4, 6, 3, '', '[{\"medicament\":\"Voltarene\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"6 jours\"},{\"medicament\":\"Ventoline Aerosol\",\"posologie\":\"1 comp le matin\",\"duree\":\"5 jours\"}]', 'c9159b02b0f364b41ec86d51ed35ad88', '2026-05-11 09:39:59', 'active'),
(5, 6, 3, '', '[{\"medicament\":\"Voltarene\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"6 jours\"},{\"medicament\":\"Ventoline Aerosol\",\"posologie\":\"1 comp le matin\",\"duree\":\"5 jours\"}]', '106d8548044f090ced6e899d86d80aa1', '2026-05-11 09:40:43', 'active'),
(6, 6, 1, '', '[{\"medicament\":\"Amoxicilline 1g\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"7 jours\"}]', 'e93d48e86ca308bf9e0d843a90722aa2', '2026-05-11 14:10:45', 'active'),
(7, 6, 3, '', '[{\"medicament\":\"Paracétamol 500mg\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"6 jours\"},{\"medicament\":\"Spasfon\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"6 jours\"}]', '9c8b5df1cd81caa213c54c1add76338f', '2026-05-11 15:24:37', 'active'),
(8, 6, 2, '', '[{\"medicament\":\"Spasfon\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"5 jours\"},{\"medicament\":\"Augmentin\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"5 jours\"}]', 'a079a71877a11615470a8e497800538f', '2026-05-13 13:14:53', 'active'),
(9, 6, 2, '', '[{\"medicament\":\"Spasfon\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"7 jours\"},{\"medicament\":\"Augmentin\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"5 jours\"}]', 'c1043de177044d9f9853d38893a51151', '2026-05-13 13:32:03', 'active'),
(10, 6, 2, '', '[{\"medicament\":\"Augmentin\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"5 jours\"},{\"medicament\":\"Spasfon\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"3 jours\"}]', '206d24db30f8f354315a5a26ac1dad34', '2026-05-13 13:46:46', 'active'),
(11, 6, 2, '', '[{\"medicament\":\"Augmentin\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"5 jours\"}]', '26d16a15dbe55f0e22d9eb3f8bb290fb', '2026-05-13 15:18:07', 'active'),
(12, 6, 2, '', '[{\"medicament\":\"Ventoline\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"6 jours\"}]', '6b69ec1983416c19306809e2c1a39526', '2026-05-13 15:21:32', 'active'),
(13, 6, 2, '', '[{\"medicament\":\"Ventoline\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"4 jours\"},{\"medicament\":\"Voltarene\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"3 jours\"}]', '924615d43b2a4a4f09ce4a3123c30463', '2026-05-16 16:32:35', 'active'),
(14, 6, 1, '', '[{\"medicament\":\"Clamoxyl 1g\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"6 jours\"},{\"medicament\":\"Amlor 5mg\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"6 jours\"}]', 'b67da076a598f6f7f45b698ae6d47ac8', '2026-05-21 14:22:41', 'active'),
(15, 6, 1, '', '[{\"medicament\":\"Clarityne\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"7 jours\"},{\"medicament\":\"Doliprane 500mg\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"3 jours\"}]', 'f06da0dfa6dc77239b076b3c352ccbf6', '2026-05-24 12:32:37', 'active'),
(17, 6, 1, '', '[{\"medicament\":\"Co-Tarek\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"5 jours\"},{\"medicament\":\"Diprosone 0.05%\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"7 jours\"}]', '459d55d35f4d8617da2566e1bf8d6e41', '2026-05-24 14:07:49', 'active'),
(18, 6, 1, '', '[{\"medicament\":\"Clamoxyl 1g\",\"posologie\":\"1 comp x 3 \\/ jour\",\"duree\":\"5 jours\"}]', '64c81960279355bb2b23b50209cb1a0f', '2026-06-03 11:23:14', 'active'),
(19, 6, 1, '', '[{\"medicament\":\"Clarityne\",\"posologie\":\"1 comp x 2 \\/ jour\",\"duree\":\"3 jours\"}]', '7a36785cdea52864830f0195e41a14c9', '2026-06-04 09:35:50', 'active'),
(20, 6, 1, 'dr.amine.b@gmail.com', '[{\"nom_medicament\":\"Debridat (Comprimé)\",\"posologie\":\"1 comp 3x\\/jour\",\"duree\":\"7\"}]', '', '2026-06-05 21:32:42', ''),
(22, 6, 1, 'dr.amine.b@gmail.com', '[{\"nom_medicament\":\"Diclofenac 50mg (Comprimé)\",\"posologie\":\"1 comp 3x\\/jour\",\"duree\":\"7\"}]', '1e0f31e97692c72fc6f262bb11cda1b7', '2026-06-06 09:35:21', ''),
(23, 6, 1, 'dr.amine.b@gmail.com', '[{\"nom_medicament\":\"Daonil 5mg (Comprimé)\",\"posologie\":\"1 cuillère à café 3x\\/jour\",\"duree\":\"7\"}]', '638bd69ced6535573c92a9786adebba9', '2026-06-06 09:44:21', ''),
(24, 6, 1, 'dr.amine.b@gmail.com', '[{\"nom_medicament\":\"Biafine\",\"posologie\":\"1 application soir\",\"duree\":\"5\"},{\"nom_medicament\":\"Augmentin (Comprimé)\",\"posologie\":\"1 comp 3x\\/jour\",\"duree\":\"5\"}]', 'fa482daa23808ca4673e85fc69f67c08', '2026-06-12 15:46:22', ''),
(25, 6, 1, 'dr.amine.b@gmail.com', '[{\"nom_medicament\":\"Diclofenac 50mg (Comprimé)\",\"posologie\":\"1 comp 3x\\/jour\",\"duree\":\"5\"},{\"nom_medicament\":\"Augmentin (Comprimé)\",\"posologie\":\"1 comp 3x\\/jour\",\"duree\":\"5\"}]', '44238481a6879baa56c7f8fbae92f59c', '2026-06-18 23:28:39', ''),
(26, 6, 1, 'dr.amine.b@gmail.com', '[{\"nom_medicament\":\"Clamoxyl 1g (Gélule)\",\"posologie\":\"1 comp le matin\",\"duree\":\"7\"}]', 'a1ad20e5285bc9d938032d38557d11a7', '2026-06-19 00:05:01', ''),
(27, 6, 1, 'dr.amine.b@gmail.com', '[{\"nom_medicament\":\"Augmentin (Comprimé)\",\"posologie\":\"1 sachet 2x\\/jour\",\"duree\":\"5\"}]', '8fc78ba98736dcf74f96be1f9e5797d1', '2026-06-19 13:20:47', '');

-- --------------------------------------------------------

--
-- Structure de la table `patients`
--

CREATE TABLE `patients` (
  `id_patient` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `nss` char(15) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `telephone` varchar(15) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `ville` varchar(50) DEFAULT 'Skikda',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `poids` int(11) DEFAULT NULL,
  `document_justificatif` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `patients`
--

INSERT INTO `patients` (`id_patient`, `id_utilisateur`, `nss`, `password`, `nom`, `prenom`, `date_naissance`, `telephone`, `adresse`, `ville`, `created_at`, `poids`, `document_justificatif`) VALUES
(1, 16, '21 0045 8874 12', '123456', 'Messaoudi', 'Rabah', '1985-05-15', '0550112233', '', 'Skikda', '2026-05-10 19:49:42', 66, NULL),
(2, 17, '18 0098 7741 05', '123456', 'Boudiaf', 'Fouad', '1990-10-20', '0661445566', NULL, 'El Khroub', '2026-05-10 19:50:12', 64, NULL),
(3, 18, '22 0012 3365 09', '123456', 'Mansouri', 'Meriem', '1995-02-12', '0770889900', NULL, 'Skikda', '2026-05-10 19:50:39', 59, NULL),
(4, 19, '15 0055 4411 08', '123456', 'Haddad', 'Amine', '1982-12-30', '0540112244', NULL, 'Skikda', '2026-05-10 19:51:17', 102, NULL),
(5, 20, '20 0088 9922 03', '123456', 'Zaim', 'Samia', '1988-07-04', '0699887766', NULL, 'Azzaba', '2026-05-10 19:52:01', 71, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `pharmacies`
--

CREATE TABLE `pharmacies` (
  `id` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `nom_pharmacie` varchar(100) NOT NULL,
  `nom_pharmacien` varchar(50) DEFAULT NULL,
  `adresse` text NOT NULL,
  `telephone` varchar(15) DEFAULT NULL,
  `email_contact` varchar(100) DEFAULT NULL,
  `ville` varchar(50) DEFAULT 'Skikda',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` varchar(20) NOT NULL DEFAULT 'en_attente',
  `document_justificatif` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `pharmacies`
--

INSERT INTO `pharmacies` (`id`, `id_utilisateur`, `nom_pharmacie`, `nom_pharmacien`, `adresse`, `telephone`, `email_contact`, `ville`, `latitude`, `longitude`, `created_at`, `statut`, `document_justificatif`) VALUES
(1, 11, 'Pharmacie Centrale', 'Mr. Zeroual', 'Place du 1er Novembre', '033718877', NULL, 'Skikda', 36.87620000, 6.90110000, '2026-05-10 19:24:26', 'actif', NULL),
(2, 12, 'Pharmacie Les Arcades', 'Mme. Boudiaf', 'Rue de la Paix', '033714455', NULL, 'Skikda', 36.87800000, 6.90350000, '2026-05-10 19:24:26', 'actif', NULL),
(3, 13, 'Pharmacie Nour', 'Mr. Khelifi', 'Cité des 500 logements', '033756677', NULL, 'Skikda', 36.86900000, 6.91200000, '2026-05-10 19:24:26', 'actif', NULL),
(4, 14, 'Pharmacie de l\'Est', 'Mme. Touati', 'Avenue de la République', '033743322', NULL, 'Skikda', 36.88100000, 6.90800000, '2026-05-10 19:24:26', 'actif', NULL),
(5, 15, 'Pharmacie El Hayat', 'Mr. Belkacem', 'Quartier l\'Espérance', '033721100', NULL, 'Skikda', 36.87350000, 6.89500000, '2026-05-10 19:24:26', 'actif', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `id_pharmacie` int(11) NOT NULL,
  `nom_client` varchar(100) DEFAULT 'Patient Anonyme',
  `nss` varchar(15) DEFAULT NULL,
  `id_ordonnance` int(11) DEFAULT NULL,
  `telephone_client` varchar(20) DEFAULT NULL,
  `medicament_demande` varchar(255) NOT NULL,
  `statut` enum('en_attente','valide','annule') DEFAULT 'en_attente',
  `date_commande` timestamp NOT NULL DEFAULT current_timestamp(),
  `patient_a_vu` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`id`, `id_pharmacie`, `nom_client`, `nss`, `id_ordonnance`, `telephone_client`, `medicament_demande`, `statut`, `date_commande`, `patient_a_vu`) VALUES
(1, 1, 'Test Client', NULL, NULL, NULL, 'Paracétamol 500mg', 'valide', '2026-05-12 13:51:26', 0),
(2, 1, 'Messaoudi Rabah', '21 0045 8874 12', NULL, NULL, 'Augmentin', 'en_attente', '2026-05-12 14:54:17', 0),
(3, 1, 'Messaoudi Rabah', '21 0045 8874 12', NULL, NULL, 'Augmentin', 'en_attente', '2026-05-13 09:32:09', 0),
(4, 1, 'Messaoudi', '', NULL, NULL, 'Augmentin', 'en_attente', '2026-05-13 10:08:28', 0),
(5, 1, 'Messaoudi Rabah', '21 0045 8874 12', NULL, NULL, 'Augmentin', 'en_attente', '2026-05-13 10:29:40', 0),
(6, 1, 'Boudiaf Fouad', '18 0098 7741 05', NULL, NULL, 'Spasfon', 'en_attente', '2026-05-13 10:40:20', 0),
(7, 1, 'Messaoudi Rabah', '21 0045 8874 12', NULL, NULL, 'Augmentin', 'en_attente', '2026-05-13 11:27:10', 0),
(8, 1, 'Boudiaf Fouad', '18 0098 7741 05', NULL, NULL, 'Augmentin', 'en_attente', '2026-05-13 11:41:46', 0),
(9, 1, 'Boudiaf Fouad', '18 0098 7741 05', NULL, NULL, 'Augmentin', 'valide', '2026-05-13 12:24:52', 1),
(10, 1, 'Boudiaf Fouad', '18 0098 7741 05', NULL, NULL, 'Augmentin', 'valide', '2026-05-13 12:39:52', 0),
(11, 1, 'Boudiaf Fouad', '18 0098 7741 05', NULL, NULL, 'Augmentin', 'valide', '2026-05-13 12:53:38', 1),
(12, 1, 'Boudiaf Fouad', '18 0098 7741 05', NULL, NULL, 'Spasfon', 'valide', '2026-05-13 12:58:08', 1),
(13, 1, 'Boudiaf Fouad', '18 0098 7741 05', NULL, NULL, 'Augmentin', 'valide', '2026-05-13 13:11:21', 1),
(14, 1, '', '', NULL, NULL, 'Spasfon, Augmentin', 'valide', '2026-05-14 11:33:51', 0),
(15, 1, 'Boudiaf Fouad', '18 0098 7741 05', NULL, NULL, 'Augmentin', 'valide', '2026-05-14 12:06:27', 0),
(16, 1, 'Boudiaf Fouad', '18 0098 7741 05', NULL, NULL, 'Augmentin', 'valide', '2026-05-16 16:35:48', 0),
(17, 1, 'Messaoudi Rabah', '21 0045 8874 12', NULL, NULL, 'Augmentin', '', '2026-05-20 16:54:05', 0),
(18, 2, 'Rabah Messaoudi', '21 0045 8874 12', NULL, '0550112233', 'Biafine, Augmentin', 'en_attente', '2026-06-16 22:44:13', 0),
(19, 3, 'Rabah Messaoudi', '21 0045 8874 12', NULL, '0550112233', 'Biafine, Augmentin', '', '2026-06-16 22:50:35', 0),
(20, 3, 'Rabah Messaoudi', '21 0045 8874 12', NULL, '0550112233', 'Biafine, Augmentin', '', '2026-06-16 22:51:00', 0),
(21, 3, 'Rabah Messaoudi', '21 0045 8874 12', NULL, '0550112233', 'Biafine, Augmentin', '', '2026-06-16 22:59:17', 0),
(22, 1, 'Rabah Messaoudi', '21 0045 8874 12', NULL, '0550112233', 'Biafine, Augmentin', '', '2026-06-16 23:08:20', 0),
(23, 3, 'Rabah Messaoudi', '21 0045 8874 12', NULL, '0550112233', 'Biafine, Augmentin', '', '2026-06-16 23:33:40', 0),
(24, 4, 'Rabah Messaoudi', '21 0045 8874 12', NULL, '0550112233', 'Biafine, Augmentin', 'valide', '2026-06-18 13:10:20', 0),
(25, 2, 'Rabah Messaoudi', '21 0045 8874 12', 26, '0550112233', 'Biafine, Augmentin', 'en_attente', '2026-06-18 22:51:04', 0),
(26, 1, 'Rabah Messaoudi', '21 0045 8874 12', NULL, '0550112233', 'Biafine, Augmentin', '', '2026-06-18 23:21:53', 0),
(27, 5, 'Rabah Messaoudi', '21 0045 8874 12', NULL, '0550112233', 'Diclofenac 50mg, Augmentin', 'en_attente', '2026-06-18 23:29:13', 0),
(28, 1, 'Rabah Messaoudi', '21 0045 8874 12', NULL, '0550112233', 'Diclofenac 50mg, Augmentin', '', '2026-06-18 23:31:29', 0),
(29, 1, 'Rabah Messaoudi', '21 0045 8874 12', NULL, '0550112233', 'Diclofenac 50mg, Augmentin', '', '2026-06-18 23:32:52', 0),
(30, 1, 'Rabah Messaoudi', '21 0045 8874 12', NULL, '0550112233', 'Diclofenac 50mg, Augmentin', '', '2026-06-18 23:35:05', 0),
(31, 1, 'Rabah Messaoudi', '21 0045 8874 12', NULL, '0550112233', 'Diclofenac 50mg, Augmentin', '', '2026-06-18 23:36:50', 0),
(32, 1, 'Rabah Messaoudi', '21 0045 8874 12', NULL, '0550112233', 'Diclofenac 50mg, Augmentin', '', '2026-06-18 23:50:02', 0),
(33, 3, 'Rabah Messaoudi', '21 0045 8874 12', NULL, '0550112233', 'Daonil 5mg', '', '2026-06-18 23:57:12', 0),
(34, 3, 'Rabah Messaoudi', '21 0045 8874 12', 23, '0550112233', 'Daonil 5mg', 'annule', '2026-06-19 00:04:08', 0),
(35, 3, 'Rabah Messaoudi', '21 0045 8874 12', 26, '0550112233', 'Clamoxyl 1g', '', '2026-06-19 00:05:29', 0),
(36, 3, 'Rabah Messaoudi', '21 0045 8874 12', 26, '0550112233', 'Clamoxyl 1g', 'valide', '2026-06-19 00:08:44', 0),
(37, 3, 'Rabah Messaoudi', '21 0045 8874 12', 26, '0550112233', 'Clamoxyl 1g', 'valide', '2026-06-19 00:14:12', 0),
(38, 3, 'Rabah Messaoudi', '21 0045 8874 12', 26, '0550112233', 'Clamoxyl 1g', 'valide', '2026-06-19 00:22:17', 0);

-- --------------------------------------------------------

--
-- Structure de la table `stocks`
--

CREATE TABLE `stocks` (
  `id_stock` int(11) NOT NULL,
  `id_pharmacie` int(11) DEFAULT NULL,
  `id_medicament` int(11) DEFAULT NULL,
  `quantite` int(11) DEFAULT 0,
  `prix` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `stocks`
--

INSERT INTO `stocks` (`id_stock`, `id_pharmacie`, `id_medicament`, `quantite`, `prix`) VALUES
(1, 1, 8, 50, 420.00),
(2, 1, 9, 45, 850.00),
(3, 1, 10, 30, 650.00),
(4, 1, 13, 55, 450.00),
(5, 1, 14, 40, 720.00),
(6, 1, 18, 20, 1400.00),
(7, 1, 19, 55, 480.00),
(8, 1, 23, 30, 1100.00),
(9, 1, 25, 65, 350.00),
(10, 1, 27, 22, 540.00),
(11, 1, 35, 15, 1650.00),
(12, 1, 38, 40, 380.00),
(13, 1, 40, 25, 460.00),
(14, 2, 8, 35, 410.00),
(15, 2, 9, 40, 840.00),
(16, 2, 10, 15, 660.00),
(17, 2, 13, 20, 990.00),
(18, 2, 14, 30, 710.00),
(19, 2, 18, 15, 1380.00),
(20, 2, 19, 40, 490.00),
(21, 2, 23, 25, 1090.00),
(22, 2, 25, 50, 360.00),
(23, 2, 35, 18, 1600.00),
(24, 3, 8, 60, 430.00),
(25, 3, 9, 50, 860.00),
(26, 3, 13, 10, 970.00),
(27, 3, 14, 35, 730.00),
(28, 3, 19, 30, 470.00),
(29, 3, 23, 15, 1120.00),
(30, 3, 25, 80, 340.00),
(31, 3, 27, 30, 550.00),
(32, 3, 38, 25, 370.00),
(33, 3, 40, 20, 450.00),
(34, 5, 1, 70, 240.00),
(35, 5, 3, 35, 450.00),
(36, 5, 4, 25, 355.00),
(37, 5, 5, 105, 1220.00),
(38, 5, 6, 110, 150.00),
(39, 5, 7, 20, 840.00),
(40, 5, 8, 65, 415.00),
(41, 4, 1, 30, 255.00),
(42, 4, 2, 25, 620.00),
(43, 4, 4, 15, 340.00),
(44, 4, 5, 130, 1210.00),
(45, 4, 6, 90, 145.00),
(46, 4, 7, 35, 860.00),
(47, 4, 8, 20, 430.00),
(48, 3, 1, 60, 245.00),
(49, 3, 2, 40, 610.00),
(50, 3, 3, 50, 440.00),
(51, 3, 4, 30, 360.00),
(52, 3, 5, 95, 1190.00),
(53, 3, 6, 75, 160.00),
(54, 3, 8, 40, 425.00),
(55, 2, 1, 45, 260.00),
(56, 2, 2, 30, 590.00),
(57, 2, 3, 25, 460.00),
(58, 2, 5, 110, 1250.00),
(59, 2, 6, 100, 140.00),
(60, 2, 7, 15, 830.00),
(61, 2, 8, 50, 410.00),
(62, 1, 1, 50, 250.00),
(63, 1, 2, 35, 600.00),
(64, 1, 3, 40, 450.00),
(65, 1, 4, 20, 350.00),
(66, 1, 5, 160, 1200.00),
(67, 1, 6, 80, 150.00),
(68, 1, 7, 25, 850.00),
(69, 1, 8, 30, 420.00),
(70, 1, 43, 120, 800.00),
(71, 1, 31, 30, 760.00),
(72, 1, 29, 50, 120.00),
(73, 1, 45, 40, 340.00),
(74, 1, 11, 50, 230.00),
(75, 3, 56, 34, 450.00);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `utilisateur` varchar(50) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('medecin','patient','pharmacie') NOT NULL,
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `utilisateur`, `mot_de_passe`, `role`, `date_inscription`) VALUES
(6, 'dr.amine.b@gmail.com', '123456', 'medecin', '2026-05-10 19:22:44'),
(7, 'dr.karima.h@yahoo.fr', '$2y$10$7Z1...', 'medecin', '2026-05-10 19:22:44'),
(8, 'dr.merzoug.s@outlook.com', '$2y$10$7Z1...', 'medecin', '2026-05-10 19:22:44'),
(9, 'dr.feriel.m@gmail.com', '$2y$10$7Z1...', 'medecin', '2026-05-10 19:22:44'),
(10, 'dr.yacine.k@gmail.com', '$2y$10$7Z1...', 'medecin', '2026-05-10 19:22:44'),
(11, 'pharma.centrale.skikda@gmail.com', '123456', 'pharmacie', '2026-05-10 19:22:44'),
(12, 'pharma.lesarcades@gmail.com', '123456', 'pharmacie', '2026-05-10 19:22:44'),
(13, 'pharma.nour@yahoo.fr', '123456', 'pharmacie', '2026-05-10 19:22:44'),
(14, 'pharma.delest@gmail.com', '$2y$10$7Z1...', 'pharmacie', '2026-05-10 19:22:44'),
(15, 'pharma.elhayat@gmail.com', '123456', 'pharmacie', '2026-05-10 19:22:44'),
(16, 'rabah.patient@gmail.com', '$2y$10$7Z1...', 'patient', '2026-05-10 19:46:37'),
(17, 'fouad.skikda@yahoo.fr', '$2y$10$7Z1...', 'patient', '2026-05-10 19:46:37'),
(18, 'meriem.b@outlook.com', '$2y$10$7Z1...', 'patient', '2026-05-10 19:46:37'),
(19, 'amine.h@gmail.com', '123456', 'patient', '2026-05-10 19:46:37'),
(20, 'samia.v@gmail.com', '$2y$10$7Z1...', 'patient', '2026-05-10 19:46:37');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `administrateurs`
--
ALTER TABLE `administrateurs`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Index pour la table `medecins`
--
ALTER TABLE `medecins`
  ADD PRIMARY KEY (`id_medecin`),
  ADD UNIQUE KEY `matricule_medical` (`matricule_medical`),
  ADD KEY `fk_medecin_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `medicaments`
--
ALTER TABLE `medicaments`
  ADD PRIMARY KEY (`id_medoc`);

--
-- Index pour la table `messages_contact`
--
ALTER TABLE `messages_contact`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `ordonnances`
--
ALTER TABLE `ordonnances`
  ADD PRIMARY KEY (`id_ordonnance`),
  ADD UNIQUE KEY `qr_token` (`qr_token`),
  ADD KEY `id_patient` (`id_patient`),
  ADD KEY `fk_medecin_ordonnance` (`id_medecin`);

--
-- Index pour la table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id_patient`),
  ADD UNIQUE KEY `nss` (`nss`),
  ADD KEY `fk_patient_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `pharmacies`
--
ALTER TABLE `pharmacies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pharmacie_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`id_stock`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom_utilisateur` (`utilisateur`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `administrateurs`
--
ALTER TABLE `administrateurs`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `medecins`
--
ALTER TABLE `medecins`
  MODIFY `id_medecin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `medicaments`
--
ALTER TABLE `medicaments`
  MODIFY `id_medoc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT pour la table `messages_contact`
--
ALTER TABLE `messages_contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `ordonnances`
--
ALTER TABLE `ordonnances`
  MODIFY `id_ordonnance` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT pour la table `patients`
--
ALTER TABLE `patients`
  MODIFY `id_patient` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `pharmacies`
--
ALTER TABLE `pharmacies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT pour la table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id_stock` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `medecins`
--
ALTER TABLE `medecins`
  ADD CONSTRAINT `fk_medecin_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ordonnances`
--
ALTER TABLE `ordonnances`
  ADD CONSTRAINT `fk_medecin_ordonnance` FOREIGN KEY (`id_medecin`) REFERENCES `medecins` (`id_medecin`) ON DELETE CASCADE,
  ADD CONSTRAINT `ordonnances_ibfk_1` FOREIGN KEY (`id_patient`) REFERENCES `patients` (`id_patient`) ON DELETE CASCADE;

--
-- Contraintes pour la table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `fk_patient_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `pharmacies`
--
ALTER TABLE `pharmacies`
  ADD CONSTRAINT `fk_pharmacie_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
