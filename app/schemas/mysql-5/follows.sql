DROP TABLE IF EXISTS `%table_prefix%follows`;
CREATE TABLE `%table_prefix%follows` (
  `follow_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `follow_date` DATETIME NOT NULL,
  `follow_date_gmt` DATETIME NOT NULL,
  `follow_user_id` INT UNSIGNED NOT NULL,
  `follow_followed_user_id` INT UNSIGNED NOT NULL,
  `follow_ip` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`follow_id`),
  KEY `follow_user_id` (`follow_user_id`),
  KEY `follow_followed_user_id` (`follow_followed_user_id`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
