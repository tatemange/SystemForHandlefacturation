-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : ven. 21 nov. 2025 à 10:26
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
-- Structure de la table `CAISSE`
--

CREATE TABLE `CAISSE` (
  `id_caisse` int(11) NOT NULL,
  `intitule_caisse` varchar(100) NOT NULL,
  `responsable` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Structure de la table `ENREGISTRER`
--

CREATE TABLE `ENREGISTRER` (
  `id_caisse` int(11) NOT NULL,
  `id_reglement` int(11) NOT NULL,
  `status` enum('VALIDE','EN_ATTENTE','ANNULE') NOT NULL DEFAULT 'EN_ATTENTE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `HISTORIQUE`
--

CREATE TABLE `HISTORIQUE` (
  `id_historique` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(50) NOT NULL COMMENT 'CLIENT, DOCUMENT, REGLEMENT, SERVICE',
  `entity_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL COMMENT 'CREATE, READ, UPDATE, DELETE',
  `details` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date_action` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_historique`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Structure de la table `SERVICE_PRODUIT`
--

CREATE TABLE `SERVICE_PRODUIT` (
  `id` int(11) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `prix_de_vente` decimal(12,2) NOT NULL,
  `prix_achat` decimal(12,2) DEFAULT NULL,
  `quantite_stock` int(11) DEFAULT NULL,
  `est_service` tinyint(1) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




-- 
-- table admin
-- 

CREATE TABLE `ADMIN` (
  `id_admin` INT(11) NOT NULL,
  `nom` VARCHAR(50) NOT NULL,
  `prenom` VARCHAR(50) DEFAULT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `telephone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `mot_de_passe` VARCHAR(255) NOT NULL,
  `role` ENUM('SUPER_ADMIN', 'ADMIN', 'GESTIONNAIRE') NOT NULL DEFAULT 'ADMIN',
  `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dernier_login` DATETIME DEFAULT NULL,
  `status` ENUM('ACTIF', 'INACTIF', 'SUSPENDU') NOT NULL DEFAULT 'ACTIF'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `ADMIN`
  ADD PRIMARY KEY (`id_admin`),
  ADD KEY `idx_admin_username` (`username`),
  ADD KEY `idx_admin_email` (`email`);

ALTER TABLE `ADMIN`
  MODIFY `id_admin` INT(11) NOT NULL AUTO_INCREMENT;


--
-- Index pour les tables déchargées
--

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
  ADD PRIMARY KEY (`id_historique`),
  ADD KEY `HISTORIQUE_reglement_FK` (`id_reglement`);

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
-- AUTO_INCREMENT pour la table `CAISSE`
--
ALTER TABLE `CAISSE`
  MODIFY `id_caisse` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `CLIENT`
--
ALTER TABLE `CLIENT`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `DETAIL_DOCUMENT`
--
ALTER TABLE `DETAIL_DOCUMENT`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `DOCUMENT`
--
ALTER TABLE `DOCUMENT`
  MODIFY `id_document` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `HISTORIQUE`
--
ALTER TABLE `HISTORIQUE`
  MODIFY `id_historique` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `REGLEMENT`
--
ALTER TABLE `REGLEMENT`
  MODIFY `id_reglement` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `SERVICE_PRODUIT`
--
ALTER TABLE `SERVICE_PRODUIT`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- Contraintes pour la table `HISTORIQUE`
--
ALTER TABLE `HISTORIQUE`
  ADD CONSTRAINT `HISTORIQUE_reglement_FK` FOREIGN KEY (`id_reglement`) REFERENCES `REGLEMENT` (`id_reglement`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `REGLEMENT`
--
ALTER TABLE `REGLEMENT`
  ADD CONSTRAINT `REGLEMENT_client_FK` FOREIGN KEY (`id_client`) REFERENCES `CLIENT` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
