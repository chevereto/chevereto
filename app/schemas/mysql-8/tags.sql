DROP TABLE IF EXISTS `%table_prefix%tags`;
CREATE TABLE `%table_prefix%tags` (
  `tag_id` bigint(32) NOT NULL AUTO_INCREMENT,
  `tag_name` varchar(32) COLLATE utf8mb4_bin NOT NULL,
  `tag_description` text,
  `tag_user_id` bigint(32) NOT NULL,
  `tag_date_gmt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tag_files` bigint(32) NOT NULL DEFAULT 0,
  `tag_views` bigint(32) NOT NULL DEFAULT 0,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `tag_name` (`tag_name`) USING BTREE,
  KEY `tag_user_id` (`tag_user_id`),
  KEY `tag_date_gmt` (`tag_date_gmt`),
  KEY `tag_files` (`tag_files`),
  KEY `tag_views` (`tag_views`),
  KEY `tag_user_id_date_gmt` (`tag_user_id`,`tag_date_gmt`),
  KEY `tag_user_id_files` (`tag_user_id`,`tag_files`),
  KEY `tag_user_id_views` (`tag_user_id`,`tag_views`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
