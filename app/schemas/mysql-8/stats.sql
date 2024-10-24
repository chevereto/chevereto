DROP TABLE IF EXISTS `%table_prefix%stats`;
CREATE TABLE `%table_prefix%stats` (
  `stat_id` bigint(32) NOT NULL AUTO_INCREMENT,
  `stat_type` enum('total','date') NOT NULL,
  `stat_date_gmt` date DEFAULT NULL,
  `stat_users` bigint(32) UNSIGNED NOT NULL DEFAULT '0',
  `stat_images` bigint(32) UNSIGNED NOT NULL DEFAULT '0',
  `stat_albums` bigint(32) UNSIGNED NOT NULL DEFAULT '0',
  `stat_tags` bigint(32) UNSIGNED NOT NULL DEFAULT '0',
  `stat_cron_runs` bigint(32) UNSIGNED NOT NULL DEFAULT '0',
  `stat_cron_time` bigint(32) UNSIGNED NOT NULL DEFAULT '0',
  `stat_image_views` bigint(32) UNSIGNED NOT NULL DEFAULT '0',
  `stat_album_views` bigint(32) UNSIGNED NOT NULL DEFAULT '0',
  `stat_image_likes` bigint(32) UNSIGNED NOT NULL DEFAULT '0',
  `stat_album_likes` bigint(32) UNSIGNED NOT NULL DEFAULT '0',
  `stat_disk_used` bigint(32) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`stat_id`),
  UNIQUE KEY `stat_date_gmt` (`stat_date_gmt`) USING BTREE,
  KEY `stat_type` (`stat_type`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8;
INSERT INTO `%table_prefix%stats` (stat_id, stat_type) VALUES (1, 'total');
