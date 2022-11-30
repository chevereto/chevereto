DROP TABLE IF EXISTS `%table_prefix%assets`;
CREATE TABLE `%table_prefix%assets` (
  `asset_id` bigint(32) NOT NULL AUTO_INCREMENT,
  `asset_key` varchar(255) NOT NULL,
  `asset_md5` varchar(32) NOT NULL,
  `asset_filename` varchar(255) NOT NULL,
  `asset_file_path` varchar(255) NOT NULL,
  `asset_blob` blob,
  PRIMARY KEY (`asset_id`),
  UNIQUE KEY `key` (`asset_key`) USING BTREE,
  KEY `md5` (`asset_md5`),
  KEY `filename` (`asset_filename`),
  KEY `file_path` (`asset_file_path`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
