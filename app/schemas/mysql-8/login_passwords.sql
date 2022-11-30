DROP TABLE IF EXISTS `%table_prefix%login_passwords`;
CREATE TABLE `%table_prefix%login_passwords` (
  `login_password_id` bigint(32) NOT NULL AUTO_INCREMENT,
  `login_password_user_id` bigint(32) NOT NULL,
  `login_password_date_gmt` datetime NOT NULL,
  `login_password_hash` mediumtext NOT NULL,
  PRIMARY KEY (`login_password_id`),
  UNIQUE KEY `login_password_user_id` (`login_password_user_id`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
