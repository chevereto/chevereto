DROP TABLE IF EXISTS `%table_prefix%logins`;
CREATE TABLE `%table_prefix%logins` (
  `login_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `login_user_id` INT UNSIGNED NOT NULL,
  `login_type` ENUM('password','session','cookie','facebook','twitter','google','vk','cookie_facebook','cookie_twitter','cookie_google','cookie_vk') NOT NULL,
  `login_ip` VARCHAR(255) DEFAULT NULL,
  `login_hostname` TEXT,
  `login_date` DATETIME NOT NULL,
  `login_date_gmt` DATETIME NOT NULL,
  `login_resource_id` VARCHAR(255) DEFAULT NULL,
  `login_resource_name` TEXT,
  `login_resource_avatar` TEXT,
  `login_resource_url` TEXT,
  `login_secret` TEXT DEFAULT NULL COMMENT 'The secret part',
  `login_token_hash` TEXT COMMENT 'Hashed complement to secret if needed',
  PRIMARY KEY (`login_id`),
  KEY `login_user_id` (`login_user_id`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
