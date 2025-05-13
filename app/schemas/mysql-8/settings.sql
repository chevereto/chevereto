DROP TABLE IF EXISTS `%table_prefix%settings`;
CREATE TABLE `%table_prefix%settings` (
  `setting_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `setting_value` TEXT,
  `setting_default` TEXT,
  `setting_typeset` ENUM('string','bool') DEFAULT 'string',
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_name` (`setting_name`) USING BTREE
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
