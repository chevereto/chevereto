DROP TABLE IF EXISTS `%table_prefix%login_providers`;
CREATE TABLE `%table_prefix%login_providers` (
  `login_provider_id` bigint(32) NOT NULL AUTO_INCREMENT,
  `login_provider_name` varchar(255) DEFAULT NULL,
  `login_provider_label` varchar(255) DEFAULT NULL,
  `login_provider_key_id` text DEFAULT NULL,
  `login_provider_key_secret` text DEFAULT NULL,
  `login_provider_is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`login_provider_id`),
  UNIQUE KEY `login_provider_name` (`login_provider_name`),
  KEY `login_provider_is_enabled` (`login_provider_is_enabled`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
INSERT INTO `%table_prefix%login_providers` VALUES ('1', 'facebook', 'Facebook', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('2', 'twitter', 'Twitter', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('3', 'google', 'Google', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('4', 'vkontakte', 'VK', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('5', 'apple', 'Apple', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('6', 'amazon', 'Amazon', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('7', 'bitbucket', 'BitBucket', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('8', 'discord', 'Discord', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('9', 'dribbble', 'Dribbble', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('10', 'dropbox', 'Dropbox', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('11', 'github', 'GitHub', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('12', 'gitlab', 'GitLab', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('13', 'instagram', 'Instagram', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('14', 'linkedin', 'LinkedIn', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('15', 'mailru', 'Mailru', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('16', 'medium', 'Medium', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('17', 'odnoklassniki', 'Odnoklassniki', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('18', 'orcid', 'ORCID', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('19', 'reddit', 'Reddit', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('20', 'spotify', 'Spotify', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('21', 'steam', 'Steam', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('22', 'strava', 'Strava', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('23', 'telegram', 'Telegram', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('24', 'tumblr', 'Tumblr', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('25', 'twitchtv', 'Twitch', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('26', 'wechat', 'WeChat', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('27', 'wordpress', 'WordPress', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('28', 'yandex', 'Yandex', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('29', 'yahoo', 'Yahoo', null, null, '0');
INSERT INTO `%table_prefix%login_providers` VALUES ('30', 'qq', 'QQ', null, null, '0');
