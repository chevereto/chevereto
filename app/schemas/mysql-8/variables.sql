DROP TABLE IF EXISTS `%table_prefix%variables`;
CREATE TABLE `%table_prefix%variables` (
  `variable_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `variable_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `variable_datetime_utc` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `variable_value` TEXT,
  `variable_type` ENUM('string','bool','int','float','array','object') DEFAULT 'string',
  PRIMARY KEY (`variable_id`),
  UNIQUE KEY `variable_name` (`variable_name`) USING BTREE
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
