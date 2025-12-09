DROP TABLE IF EXISTS `%table_root_prefix%tenants_variables`;
CREATE TABLE `%table_root_prefix%tenants_variables` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `datetime_utc` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `value` TEXT,
  `type` ENUM('string','bool','int','float','array','object') DEFAULT 'string',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
