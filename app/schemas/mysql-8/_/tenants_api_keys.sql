DROP TABLE IF EXISTS `%table_root_prefix%tenants_api_keys`;
CREATE TABLE `%table_root_prefix%tenants_api_keys` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME NULL,
  `hash` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `expires_at` (`expires_at`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
