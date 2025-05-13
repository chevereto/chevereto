DROP TABLE IF EXISTS `%table_prefix%locks`;
CREATE TABLE `%table_prefix%locks` (
  `lock_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lock_name` VARCHAR(255) NOT NULL,
  `lock_date_gmt` DATETIME NOT NULL,
  `lock_expires_gmt` DATETIME DEFAULT NULL,
  PRIMARY KEY (`lock_id`),
  KEY `lock_date_gmt` (`lock_date_gmt`),
  KEY `lock_expires_gmt` (`lock_expires_gmt`),
  UNIQUE KEY `lock_name` (`lock_name`(191)) USING BTREE
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
