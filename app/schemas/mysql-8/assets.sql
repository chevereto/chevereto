DROP TABLE IF EXISTS `%table_prefix%assets`;
CREATE TABLE `%table_prefix%assets` (
  `asset_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_key` VARCHAR(255) NOT NULL,
  `asset_checksum` VARCHAR(32) NOT NULL,
  `asset_filename` VARCHAR(255) NOT NULL,
  `asset_file_path` VARCHAR(255) NOT NULL,
  `asset_blob` blob,
  PRIMARY KEY (`asset_id`),
  UNIQUE KEY `key` (`asset_key`) USING BTREE,
  KEY `filename` (`asset_filename`),
  KEY `file_path` (`asset_file_path`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
