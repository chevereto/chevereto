DROP TABLE IF EXISTS `%table_prefix%api_keys`;
CREATE TABLE `%table_prefix%api_keys` (
  `api_key_id` bigint(32) NOT NULL AUTO_INCREMENT,
  `api_key_user_id` bigint(32) DEFAULT NULL,
  `api_key_name` varchar(100) DEFAULT NULL,
  `api_key_date_gmt` datetime NOT NULL,
  `api_key_hash` mediumtext NOT NULL,
  PRIMARY KEY (`api_key_id`),
  KEY `api_key_user_id` (`api_key_user_id`),
  KEY `api_key_name` (`api_key_name`),
  KEY `api_key_date_gmt` (`api_key_date_gmt`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
