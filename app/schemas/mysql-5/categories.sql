DROP TABLE IF EXISTS `%table_prefix%categories`;
CREATE TABLE `%table_prefix%categories` (
  `category_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(32) NOT NULL,
  `category_url_key` VARCHAR(32) COLLATE utf8mb4_bin NOT NULL,
  `category_description` TEXT,
  PRIMARY KEY (`category_id`),
  KEY `category_name` (`category_name`),
  UNIQUE KEY `url_key` (`category_url_key`) USING BTREE
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
