DROP TABLE IF EXISTS `%table_prefix%likes`;
CREATE TABLE `%table_prefix%likes` (
  `like_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `like_date` DATETIME NOT NULL,
  `like_date_gmt` DATETIME NOT NULL,
  `like_user_id` INT UNSIGNED DEFAULT NULL,
  `like_content_type` ENUM('image','album') DEFAULT NULL,
  `like_content_id` INT UNSIGNED NOT NULL,
  `like_content_user_id` INT UNSIGNED DEFAULT NULL,
  `like_ip` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`like_id`),
  KEY `like_date_gmt` (`like_date_gmt`),
  KEY `like_user_id` (`like_user_id`),
  KEY `like_content_type` (`like_content_type`),
  KEY `like_content_id` (`like_content_id`),
  KEY `like_content_user_id` (`like_content_user_id`),
  KEY `like_ip` (`like_ip`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8;
