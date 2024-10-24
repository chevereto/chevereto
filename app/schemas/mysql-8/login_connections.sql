DROP TABLE IF EXISTS `%table_prefix%login_connections`;
CREATE TABLE `%table_prefix%login_connections` (
  `login_connection_id` bigint(32) NOT NULL AUTO_INCREMENT,
  `login_connection_user_id` bigint(32) NOT NULL,
  `login_connection_provider_id` bigint(32) NOT NULL,
  `login_connection_date_gmt` datetime NOT NULL,
  `login_connection_resource_id` varchar(255) NOT NULL,
  `login_connection_resource_name` text,
  `login_connection_token` text NOT NULL COMMENT 'Ciphertext',
  PRIMARY KEY (`login_connection_id`),
  UNIQUE KEY `login_connection_unique` (`login_connection_user_id`,`login_connection_provider_id`),
  KEY `login_connection_user_id` (`login_connection_user_id`),
  KEY `login_connection_date_gmt` (`login_connection_date_gmt`),
  KEY `login_connection_provider_id` (`login_connection_provider_id`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;

