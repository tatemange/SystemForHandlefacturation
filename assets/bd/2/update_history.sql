DROP TABLE IF EXISTS `HISTORIQUE`;
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
