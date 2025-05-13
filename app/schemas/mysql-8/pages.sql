DROP TABLE IF EXISTS `%table_prefix%pages`;
CREATE TABLE `%table_prefix%pages` (
  `page_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_url_key` VARCHAR(32) DEFAULT NULL,
  `page_type` ENUM('internal','link') NOT NULL DEFAULT 'internal',
  `page_file_path` VARCHAR(255) DEFAULT NULL,
  `page_link_url` TEXT,
  `page_icon` VARCHAR(255) DEFAULT NULL,
  `page_title` VARCHAR(255) NOT NULL,
  `page_description` TEXT,
  `page_keywords` TEXT,
  `page_is_active` TINYINT UNSIGNED NOT NULL DEFAULT '1',
  `page_is_link_visible` TINYINT UNSIGNED NOT NULL DEFAULT '1',
  `page_attr_target` ENUM('_self','_blank') DEFAULT '_self',
  `page_attr_rel` VARCHAR(255) DEFAULT NULL,
  `page_sort_display` INT UNSIGNED DEFAULT NULL,
  `page_internal` VARCHAR(255) DEFAULT NULL,
  `page_code` TEXT,
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `page_internal` (`page_internal`),
  KEY `page_url_key` (`page_url_key`),
  KEY `page_type` (`page_type`),
  KEY `page_is_active` (`page_is_active`),
  KEY `page_is_link_visible` (`page_is_link_visible`),
  KEY `page_sort_display` (`page_sort_display`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
INSERT INTO `%table_prefix%pages` VALUES ('1', 'tos', 'internal', null, null, 'fas fa-landmark', 'Terms of service', null, null, '1', '1', '_self', null, '1', 'tos', null);
INSERT INTO `%table_prefix%pages` VALUES ('2', 'privacy', 'internal', null, null, 'fas fa-lock', 'Privacy', null, null, '1', '1', '_self', null, '2', 'privacy', null);
INSERT INTO `%table_prefix%pages` VALUES ('3', 'contact', 'internal', null, null, 'fas fa-at', 'Contact', null, null, '1', '1', '_self', null, '3', 'contact', null);
