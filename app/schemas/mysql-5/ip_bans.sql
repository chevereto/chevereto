DROP TABLE IF EXISTS `%table_prefix%ip_bans`;
CREATE TABLE `%table_prefix%ip_bans` (
  `ip_ban_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_ban_date` DATETIME NOT NULL,
  `ip_ban_date_gmt` DATETIME NOT NULL,
  `ip_ban_expires` DATETIME DEFAULT NULL,
  `ip_ban_expires_gmt` DATETIME DEFAULT NULL,
  `ip_ban_ip` VARCHAR(255) NOT NULL,
  `ip_ban_message` TEXT,
  PRIMARY KEY (`ip_ban_id`),
  UNIQUE KEY `ip_ban_ip` (`ip_ban_ip`(191)) USING BTREE,
  KEY `ip_ban_ip_expires_gmt_id` (`ip_ban_ip`, `ip_ban_expires_gmt`, `ip_ban_id` DESC)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
