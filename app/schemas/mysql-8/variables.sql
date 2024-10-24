DROP TABLE IF EXISTS `%table_prefix%variables`;
CREATE TABLE `%table_prefix%variables` (
  `variable_id` int(11) NOT NULL AUTO_INCREMENT,
  `variable_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `variable_datetime_utc` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `variable_value` text,
  `variable_type` enum('string','bool','int','float','array','object') DEFAULT 'string',
  PRIMARY KEY (`variable_id`),
  UNIQUE KEY `variable_name` (`variable_name`) USING BTREE
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
