-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : sam. 27 déc. 2025 à 11:21
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `facturation`
--

-- --------------------------------------------------------

--
-- Structure de la table `ADMIN`
--

CREATE TABLE `ADMIN` (
  `id_admin` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('SUPER_ADMIN','ADMIN','GESTIONNAIRE') NOT NULL DEFAULT 'ADMIN',
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  `dernier_login` datetime DEFAULT NULL,
  `status` enum('ACTIF','INACTIF','SUSPENDU') NOT NULL DEFAULT 'ACTIF'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ADMIN`
--

INSERT INTO `ADMIN` (`id_admin`, `nom`, `prenom`, `username`, `telephone`, `email`, `mot_de_passe`, `role`, `date_creation`, `dernier_login`, `status`) VALUES
(1, 'Tatem', 'ange', 'dxc', '677685522', NULL, '$2y$10$Z7mWos7MLsWOQAA/SZY0ZuwCdQhkwdxaCBjZaqpp2HymUq1jIK1p.', 'ADMIN', '2025-12-25 20:33:23', '2025-12-27 10:00:57', 'ACTIF');

-- --------------------------------------------------------

--
-- Structure de la table `CAISSE`
--

CREATE TABLE `CAISSE` (
  `id_caisse` int(11) NOT NULL,
  `intitule_caisse` varchar(100) NOT NULL,
  `responsable` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `CAISSE`
--

INSERT INTO `CAISSE` (`id_caisse`, `intitule_caisse`, `responsable`) VALUES
(1, 'Caisse Principale', 'Admin');

-- --------------------------------------------------------

--
-- Structure de la table `CLIENT`
--

CREATE TABLE `CLIENT` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `numero_telephone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `solde` decimal(12,2) NOT NULL DEFAULT 0.00,
  `dette` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `CLIENT`
--

INSERT INTO `CLIENT` (`id`, `nom`, `prenom`, `numero_telephone`, `email`, `solde`, `dette`) VALUES
(2, 'Tatem', 'ANGE ULRICH', '677685522', 'TADEMS@gmail.comm', 0.00, 0.00),
(3, 'kd', 'kd', '2873733', 'Tatem@yahoo.co', 7322.00, 0.00),
(4, 'yolande', 'jordani', '677689944', 'jj@gmail.com', 0.00, 0.00);

-- --------------------------------------------------------

--
-- Structure de la table `DETAIL_DOCUMENT`
--

CREATE TABLE `DETAIL_DOCUMENT` (
  `id_detail` int(11) NOT NULL,
  `id_document` int(11) NOT NULL,
  `id_service_produit` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1,
  `prix_unitaire` decimal(12,2) NOT NULL,
  `montant` decimal(14,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `DETAIL_DOCUMENT`
--

INSERT INTO `DETAIL_DOCUMENT` (`id_detail`, `id_document`, `id_service_produit`, `quantite`, `prix_unitaire`, `montant`) VALUES
(1, 3, 1, 2, 2500.00, 5000.00),
(2, 5, 1, 2, 2500.00, 5000.00),
(3, 7, 1, 1, 1000.00, 1000.00),
(4, 8, 1, 1, 1000.00, 1000.00),
(5, 9, 2, 10, 200.00, 2000.00),
(6, 10, 3, 2, 2000.00, 4000.00),
(7, 11, 5, 1, 5000.00, 5000.00),
(8, 12, 6, 1, 2000.00, 2000.00),
(9, 13, 5, 1, 5000.00, 5000.00),
(10, 13, 2, 4, 200.00, 800.00),
(11, 14, 6, 1, 2000.00, 2000.00),
(12, 15, 5, 1, 5000.00, 5000.00);

-- --------------------------------------------------------

--
-- Structure de la table `DOCUMENT`
--

CREATE TABLE `DOCUMENT` (
  `id_document` int(11) NOT NULL,
  `numero_d` varchar(50) NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  `montant_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('EN_COURS','PAYE','IMPAYE') NOT NULL DEFAULT 'EN_COURS',
  `reference_doc` varchar(255) DEFAULT NULL,
  `id_client` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `DOCUMENT`
--

INSERT INTO `DOCUMENT` (`id_document`, `numero_d`, `date_creation`, `montant_total`, `status`, `reference_doc`, `id_client`) VALUES
(3, 'TEST-694d95e2ea9b1', '2025-12-25 20:52:02', 5000.00, 'PAYE', NULL, 2),
(5, 'TEST-2101-16', '2025-12-25 21:01:36', 5000.00, 'PAYE', NULL, 3),
(7, 'TEST-FAC-8303', '2025-12-25 21:13:48', 1000.00, 'PAYE', NULL, 3),
(8, 'FAC-20251225-C0C6', '2025-12-25 21:17:22', 1000.00, 'PAYE', NULL, 3),
(9, 'FAC-20251225-1249', '2025-12-25 21:18:11', 2000.00, 'PAYE', NULL, 2),
(10, 'FAC-20251225-0706', '2025-12-25 21:18:49', 4000.00, 'PAYE', NULL, 3),
(11, 'FAC-20251227-480B', '2025-12-27 04:17:20', 5000.00, 'PAYE', NULL, 2),
(12, 'FAC-20251227-17CA', '2025-12-27 04:17:32', 2000.00, 'PAYE', NULL, 3),
(13, 'FAC-20251227-B8DD', '2025-12-27 04:18:01', 5800.00, 'EN_COURS', NULL, 3),
(14, 'FAC-20251227-D8CB', '2025-12-27 10:16:00', 2000.00, 'EN_COURS', NULL, 3),
(15, 'FAC-20251227-9832', '2025-12-27 10:22:12', 5000.00, 'EN_COURS', NULL, 3);

-- --------------------------------------------------------

--
-- Structure de la table `ENREGISTRER`
--

CREATE TABLE `ENREGISTRER` (
  `id_caisse` int(11) NOT NULL,
  `id_reglement` int(11) NOT NULL,
  `status` enum('VALIDE','EN_ATTENTE','ANNULE') NOT NULL DEFAULT 'EN_ATTENTE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ENREGISTRER`
--

INSERT INTO `ENREGISTRER` (`id_caisse`, `id_reglement`, `status`) VALUES
(1, 1, 'VALIDE'),
(1, 2, 'VALIDE'),
(1, 3, 'VALIDE'),
(1, 4, 'VALIDE');

-- --------------------------------------------------------

--
-- Structure de la table `HISTORIQUE`
--

CREATE TABLE `HISTORIQUE` (
  `id_historique` int(11) NOT NULL,
  `entity_type` varchar(50) NOT NULL COMMENT 'CLIENT, DOCUMENT, REGLEMENT, SERVICE',
  `entity_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL COMMENT 'CREATE, READ, UPDATE, DELETE',
  `details` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date_action` datetime NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `HISTORIQUE`
--

INSERT INTO `HISTORIQUE` (`id_historique`, `entity_type`, `entity_id`, `action`, `details`, `user_id`, `date_action`, `ip_address`) VALUES
(1, 'REGLEMENT', 4, 'CREATE', 'Enregistrement règlement: 2000 FCFA (Mode: CASH)', 1, '2025-12-27 04:32:47', NULL),
(2, 'CREDIT', 0, '7322', '4', NULL, '2025-12-27 04:33:04', NULL),
(3, 'REGLEMENT', 4, 'UPDATE', 'Validation règlement', 1, '2025-12-27 04:33:04', NULL),
(4, 'SERVICE', 7, 'CREATE', 'Création: android', 1, '2025-12-27 04:34:09', NULL),
(5, 'DOCUMENT', 11, 'UPDATE', 'Changement statut: PAYE', 1, '2025-12-27 04:34:39', NULL),
(6, 'DOCUMENT', 13, 'READ', 'Consultation de la facture FAC-20251227-B8DD', 1, '2025-12-27 04:34:59', NULL),
(7, 'DOCUMENT', 12, 'READ', 'Consultation de la facture FAC-20251227-17CA', 1, '2025-12-27 04:35:00', NULL),
(8, 'DOCUMENT', 12, 'READ', 'Consultation de la facture FAC-20251227-17CA', 1, '2025-12-27 04:35:01', NULL),
(9, 'DOCUMENT', 12, 'READ', 'Consultation de la facture FAC-20251227-17CA', 1, '2025-12-27 04:35:01', NULL),
(10, 'DOCUMENT', 12, 'READ', 'Consultation de la facture FAC-20251227-17CA', 1, '2025-12-27 04:35:02', NULL),
(11, 'DOCUMENT', 12, 'READ', 'Consultation de la facture FAC-20251227-17CA', 1, '2025-12-27 04:35:02', NULL),
(12, 'DOCUMENT', 12, 'READ', 'Consultation de la facture FAC-20251227-17CA', 1, '2025-12-27 04:35:02', NULL),
(13, 'DOCUMENT', 12, 'READ', 'Consultation de la facture FAC-20251227-17CA', 1, '2025-12-27 04:35:02', NULL),
(14, 'DOCUMENT', 10, 'READ', 'Consultation de la facture FAC-20251225-0706', 1, '2025-12-27 04:35:04', NULL),
(15, 'DOCUMENT', 10, 'READ', 'Consultation de la facture FAC-20251225-0706', 1, '2025-12-27 04:35:05', NULL),
(16, 'DOCUMENT', 10, 'READ', 'Consultation de la facture FAC-20251225-0706', 1, '2025-12-27 04:35:05', NULL),
(17, 'DOCUMENT', 9, 'READ', 'Consultation de la facture FAC-20251225-1249', 1, '2025-12-27 04:35:15', NULL),
(18, 'DOCUMENT', 9, 'READ', 'Consultation de la facture FAC-20251225-1249', 1, '2025-12-27 04:35:16', NULL),
(19, 'DOCUMENT', 9, 'READ', 'Consultation de la facture FAC-20251225-1249', 1, '2025-12-27 04:35:16', NULL),
(20, 'DOCUMENT', 9, 'READ', 'Consultation de la facture FAC-20251225-1249', 1, '2025-12-27 04:35:16', NULL),
(21, 'DOCUMENT', 9, 'READ', 'Consultation de la facture FAC-20251225-1249', 1, '2025-12-27 04:35:17', NULL),
(22, 'DOCUMENT', 8, 'READ', 'Consultation de la facture FAC-20251225-C0C6', 1, '2025-12-27 04:35:17', NULL),
(23, 'DOCUMENT', 8, 'READ', 'Consultation de la facture FAC-20251225-C0C6', 1, '2025-12-27 04:35:18', NULL),
(24, 'DOCUMENT', 8, 'READ', 'Consultation de la facture FAC-20251225-C0C6', 1, '2025-12-27 04:35:19', NULL),
(25, 'DOCUMENT', 8, 'READ', 'Consultation de la facture FAC-20251225-C0C6', 1, '2025-12-27 04:35:19', NULL),
(26, 'DOCUMENT', 12, 'READ', 'Consultation de la facture FAC-20251227-17CA', 1, '2025-12-27 10:01:11', NULL),
(27, 'DOCUMENT', 12, 'READ', 'Consultation de la facture FAC-20251227-17CA', 1, '2025-12-27 10:01:12', NULL),
(28, 'DOCUMENT', 11, 'READ', 'Consultation de la facture FAC-20251227-480B', 1, '2025-12-27 10:01:13', NULL),
(29, 'DOCUMENT', 11, 'READ', 'Consultation de la facture FAC-20251227-480B', 1, '2025-12-27 10:01:13', NULL),
(30, 'DOCUMENT', 11, 'READ', 'Consultation de la facture FAC-20251227-480B', 1, '2025-12-27 10:01:13', NULL),
(31, 'CLIENT', 3, 'UPDATE', 'Mise à jour Client ID: 3. Nom: kd', 1, '2025-12-27 10:02:39', NULL),
(32, 'DOCUMENT', 14, 'CREATE', 'Création facture N° FAC-20251227-D8CB. Montant: 2000', 1, '2025-12-27 10:16:00', NULL),
(33, 'DOCUMENT', 12, 'READ', 'Consultation de la facture FAC-20251227-17CA', 1, '2025-12-27 10:16:05', NULL),
(34, 'DOCUMENT', 12, 'READ', 'Consultation de la facture FAC-20251227-17CA', 1, '2025-12-27 10:16:06', NULL),
(35, 'DOCUMENT', 9, 'READ', 'Consultation de la facture FAC-20251225-1249', 1, '2025-12-27 10:16:08', NULL),
(36, 'DOCUMENT', 9, 'READ', 'Consultation de la facture FAC-20251225-1249', 1, '2025-12-27 10:16:08', NULL),
(37, 'DOCUMENT', 9, 'READ', 'Consultation de la facture FAC-20251225-1249', 1, '2025-12-27 10:16:09', NULL),
(38, 'DOCUMENT', 15, 'CREATE', 'Création facture N° FAC-20251227-9832. Montant: 5000', 1, '2025-12-27 10:22:12', NULL),
(39, 'DOCUMENT', 10, 'READ', 'Consultation de la facture FAC-20251225-0706', 1, '2025-12-27 10:22:26', NULL),
(40, 'DOCUMENT', 10, 'READ', 'Consultation de la facture FAC-20251225-0706', 1, '2025-12-27 10:22:27', NULL),
(41, 'DOCUMENT', 10, 'READ', 'Consultation de la facture FAC-20251225-0706', 1, '2025-12-27 10:22:27', NULL),
(42, 'DOCUMENT', 10, 'READ', 'Consultation de la facture FAC-20251225-0706', 1, '2025-12-27 10:22:27', NULL),
(43, 'DOCUMENT', 14, 'READ', 'Consultation de la facture FAC-20251227-D8CB', 1, '2025-12-27 10:22:28', NULL),
(44, 'DOCUMENT', 14, 'READ', 'Consultation de la facture FAC-20251227-D8CB', 1, '2025-12-27 10:22:29', NULL),
(45, 'DOCUMENT', 14, 'READ', 'Consultation de la facture FAC-20251227-D8CB', 1, '2025-12-27 10:22:29', NULL),
(46, 'DOCUMENT', 14, 'READ', 'Consultation de la facture FAC-20251227-D8CB', 1, '2025-12-27 10:22:29', NULL),
(47, 'DOCUMENT', 14, 'READ', 'Consultation de la facture FAC-20251227-D8CB', 1, '2025-12-27 10:22:29', NULL),
(48, 'DOCUMENT', 14, 'READ', 'Consultation de la facture FAC-20251227-D8CB', 1, '2025-12-27 10:22:29', NULL),
(49, 'CLIENT', 4, 'CREATE', 'Nom: yolande, Prénom: jordani', 1, '2025-12-27 10:35:03', NULL),
(50, 'CLIENT', 2, 'UPDATE', 'Mise à jour Client ID: 2. Nom: Tatem', 1, '2025-12-27 10:37:41', NULL),
(51, 'DOCUMENT', 12, 'READ', 'Consultation de la facture FAC-20251227-17CA', 1, '2025-12-27 10:37:54', NULL),
(52, 'DOCUMENT', 11, 'READ', 'Consultation de la facture FAC-20251227-480B', 1, '2025-12-27 10:37:55', NULL),
(53, 'DOCUMENT', 11, 'READ', 'Consultation de la facture FAC-20251227-480B', 1, '2025-12-27 10:37:55', NULL),
(54, 'DOCUMENT', 11, 'READ', 'Consultation de la facture FAC-20251227-480B', 1, '2025-12-27 10:37:55', NULL),
(55, 'DOCUMENT', 11, 'READ', 'Consultation de la facture FAC-20251227-480B', 1, '2025-12-27 10:37:56', NULL),
(56, 'DOCUMENT', 11, 'READ', 'Consultation de la facture FAC-20251227-480B', 1, '2025-12-27 10:37:56', NULL),
(57, 'DOCUMENT', 11, 'READ', 'Consultation de la facture FAC-20251227-480B', 1, '2025-12-27 10:37:56', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `REGLEMENT`
--

CREATE TABLE `REGLEMENT` (
  `id_reglement` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `montant` decimal(14,2) NOT NULL,
  `date_reglement` datetime NOT NULL DEFAULT current_timestamp(),
  `mode_paiement` enum('ESPICES','CASH','MOBILE_MONEY','BANK_TRANSFER','CHEQUE','AUTRE') DEFAULT 'CASH',
  `reference` varchar(255) DEFAULT NULL,
  `id_document` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `REGLEMENT`
--

INSERT INTO `REGLEMENT` (`id_reglement`, `id_client`, `montant`, `date_reglement`, `mode_paiement`, `reference`, `id_document`) VALUES
(1, 3, 5000.00, '2025-12-25 21:35:54', 'CASH', '1', 10),
(2, 3, 200.00, '2025-12-25 21:47:07', 'CASH', '1', 8),
(3, 3, 122.00, '2025-12-25 21:47:33', 'CASH', '1', 8),
(4, 3, 2000.00, '2025-12-27 04:32:47', 'CASH', '1', 12);

-- --------------------------------------------------------

--
-- Structure de la table `SERVICE_PRODUIT`
--

CREATE TABLE `SERVICE_PRODUIT` (
  `id` int(11) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `prix_de_vente` decimal(12,2) NOT NULL,
  `prix_achat` decimal(12,2) DEFAULT NULL,
  `est_service` tinyint(1) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `quantite_stock` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `SERVICE_PRODUIT`
--

INSERT INTO `SERVICE_PRODUIT` (`id`, `libelle`, `prix_de_vente`, `prix_achat`, `est_service`, `description`, `quantite_stock`) VALUES
(1, 'tomate rouge', 100.00, 100.00, 0, 'tomate de cuisine', 131),
(2, 'banane', 200.00, 190.00, 0, 'banane cauchon', 85),
(3, 'pc', 2200.00, 1290.00, 0, 'pc portable', 12),
(4, 'iphone', 50000.00, 30000.00, 0, 'iphone venant de chine', 45),
(5, 'reparation iphone', 5000.00, 0.00, 1, 'reparation des iphone et pc', 1),
(6, 'reparation pc', 2000.00, 1000.00, 1, 'corriger les bugs', 0),
(7, 'android', 25000.00, 10000.00, 0, 'code c', 100);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `ADMIN`
--
ALTER TABLE `ADMIN`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_admin_username` (`username`),
  ADD KEY `idx_admin_email` (`email`);

--
-- Index pour la table `CAISSE`
--
ALTER TABLE `CAISSE`
  ADD PRIMARY KEY (`id_caisse`);

--
-- Index pour la table `CLIENT`
--
ALTER TABLE `CLIENT`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_client_email` (`email`);

--
-- Index pour la table `DETAIL_DOCUMENT`
--
ALTER TABLE `DETAIL_DOCUMENT`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `DETAIL_DOCUMENT_document_FK` (`id_document`),
  ADD KEY `DETAIL_DOCUMENT_service_FK` (`id_service_produit`);

--
-- Index pour la table `DOCUMENT`
--
ALTER TABLE `DOCUMENT`
  ADD PRIMARY KEY (`id_document`),
  ADD KEY `DOCUMENT_client_FK` (`id_client`),
  ADD KEY `idx_document_numero` (`numero_d`);

--
-- Index pour la table `ENREGISTRER`
--
ALTER TABLE `ENREGISTRER`
  ADD PRIMARY KEY (`id_caisse`,`id_reglement`),
  ADD KEY `ENREGISTRER_reglement_FK` (`id_reglement`);

--
-- Index pour la table `HISTORIQUE`
--
ALTER TABLE `HISTORIQUE`
  ADD PRIMARY KEY (`id_historique`);

--
-- Index pour la table `REGLEMENT`
--
ALTER TABLE `REGLEMENT`
  ADD PRIMARY KEY (`id_reglement`),
  ADD KEY `REGLEMENT_client_FK` (`id_client`),
  ADD KEY `idx_reglement_date` (`date_reglement`);

--
-- Index pour la table `SERVICE_PRODUIT`
--
ALTER TABLE `SERVICE_PRODUIT`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `ADMIN`
--
ALTER TABLE `ADMIN`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `CAISSE`
--
ALTER TABLE `CAISSE`
  MODIFY `id_caisse` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `CLIENT`
--
ALTER TABLE `CLIENT`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `DETAIL_DOCUMENT`
--
ALTER TABLE `DETAIL_DOCUMENT`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `DOCUMENT`
--
ALTER TABLE `DOCUMENT`
  MODIFY `id_document` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `HISTORIQUE`
--
ALTER TABLE `HISTORIQUE`
  MODIFY `id_historique` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT pour la table `REGLEMENT`
--
ALTER TABLE `REGLEMENT`
  MODIFY `id_reglement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `SERVICE_PRODUIT`
--
ALTER TABLE `SERVICE_PRODUIT`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `DETAIL_DOCUMENT`
--
ALTER TABLE `DETAIL_DOCUMENT`
  ADD CONSTRAINT `DETAIL_DOCUMENT_document_FK` FOREIGN KEY (`id_document`) REFERENCES `DOCUMENT` (`id_document`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `DETAIL_DOCUMENT_service_FK` FOREIGN KEY (`id_service_produit`) REFERENCES `SERVICE_PRODUIT` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `DOCUMENT`
--
ALTER TABLE `DOCUMENT`
  ADD CONSTRAINT `DOCUMENT_client_FK` FOREIGN KEY (`id_client`) REFERENCES `CLIENT` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `ENREGISTRER`
--
ALTER TABLE `ENREGISTRER`
  ADD CONSTRAINT `ENREGISTRER_caisse_FK` FOREIGN KEY (`id_caisse`) REFERENCES `CAISSE` (`id_caisse`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ENREGISTRER_reglement_FK` FOREIGN KEY (`id_reglement`) REFERENCES `REGLEMENT` (`id_reglement`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `REGLEMENT`
--
ALTER TABLE `REGLEMENT`
  ADD CONSTRAINT `REGLEMENT_client_FK` FOREIGN KEY (`id_client`) REFERENCES `CLIENT` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
