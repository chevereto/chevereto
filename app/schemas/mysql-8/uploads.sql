DROP TABLE IF EXISTS `%table_prefix%uploads`;
CREATE TABLE `%table_prefix%uploads` (
  `upload_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `upload_user_id` INT UNSIGNED DEFAULT NULL,
  `upload_uploader_ip` VARCHAR(255) NOT NULL,
  `upload_token` VARCHAR(64) NOT NULL,
  `upload_checksum` VARCHAR(32) NOT NULL,
  `upload_params` JSON NOT NULL,
  `upload_chunks` INT UNSIGNED NOT NULL,
  `upload_date_gmt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `upload_completed` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`upload_id`),
  KEY `upload_id_token` (`upload_id`, `upload_token`),
  KEY `upload_id_token_user_id` (`upload_id`, `upload_token`, `upload_user_id`),
  KEY `upload_date_gmt` (`upload_date_gmt`),
  KEY `upload_checksum_uploader_ip_date_gmt` (`upload_checksum`, `upload_uploader_ip`, `upload_date_gmt`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
