DROP TABLE IF EXISTS `%table_prefix%two_factors`;
CREATE TABLE `%table_prefix%two_factors` (
  `two_factor_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `two_factor_user_id` INT UNSIGNED DEFAULT NULL,
  `two_factor_date_gmt` DATETIME NOT NULL,
  `two_factor_secret` TEXT NOT NULL,
  PRIMARY KEY (`two_factor_id`),
  KEY `two_factor_user_id` (`two_factor_user_id`),
  KEY `two_factor_date_gmt` (`two_factor_date_gmt`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
