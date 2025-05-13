DROP TABLE IF EXISTS `%table_prefix%deletions`;
CREATE TABLE `%table_prefix%deletions` (
  `deleted_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `deleted_date_gmt` DATETIME NOT NULL,
  `deleted_content_id` INT UNSIGNED NOT NULL,
  `deleted_content_date_gmt` DATETIME NOT NULL,
  `deleted_content_user_id` INT UNSIGNED DEFAULT NULL,
  `deleted_content_ip` VARCHAR(255) NOT NULL,
  `deleted_content_checksum` VARCHAR(32) DEFAULT NULL,
  `deleted_content_original_filename` VARCHAR(255) DEFAULT NULL,
  `deleted_content_views` INT UNSIGNED NOT NULL DEFAULT '0',
  `deleted_content_likes` INT UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`deleted_id`),
  KEY `deleted_content_id` (`deleted_content_id`),
  KEY `deleted_content_user_id` (`deleted_content_user_id`),
  KEY `deleted_content_ip` (`deleted_content_ip`),
  KEY `deleted_content_checksum` (`deleted_content_checksum`),
  KEY `deleted_content_views` (`deleted_content_views`),
  KEY `deleted_content_likes` (`deleted_content_likes`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
