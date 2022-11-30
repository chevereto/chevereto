DROP TABLE IF EXISTS `%table_prefix%login_cookies`;
CREATE TABLE `%table_prefix%login_cookies` (
  `login_cookie_id` bigint(32) NOT NULL AUTO_INCREMENT,
  `login_cookie_user_id` bigint(32) NOT NULL,
  `login_cookie_connection_id` bigint(32) DEFAULT 0,
  `login_cookie_date_gmt` datetime NOT NULL,
  `login_cookie_ip` varchar(255) DEFAULT NULL,
  `login_cookie_user_agent` mediumtext NOT NULL,
  `login_cookie_hash` mediumtext NOT NULL,
  PRIMARY KEY (`login_cookie_id`),
  UNIQUE KEY `login_cookie_unique` (`login_cookie_user_id`,`login_cookie_connection_id`,`login_cookie_date_gmt`),
  KEY `login_cookie_user_id_date_gmt` (`login_cookie_user_id`, `login_cookie_date_gmt`),
  KEY `login_cookie_user_id` (`login_cookie_user_id`),
  KEY `login_cookie_ip` (`login_cookie_ip`),
  KEY `login_cookie_connection_id` (`login_cookie_connection_id`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
