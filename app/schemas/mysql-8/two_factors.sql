DROP TABLE IF EXISTS `%table_prefix%two_factors`;
CREATE TABLE `%table_prefix%two_factors` (
  `two_factor_id` bigint(32) NOT NULL AUTO_INCREMENT,
  `two_factor_user_id` bigint(32) DEFAULT NULL,
  `two_factor_date_gmt` datetime NOT NULL,
  `two_factor_secret` mediumtext NOT NULL,
  PRIMARY KEY (`two_factor_id`),
  KEY `two_factor_user_id` (`two_factor_user_id`),
  KEY `two_factor_date_gmt` (`two_factor_date_gmt`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
