DROP TABLE IF EXISTS `%table_prefix%stats`;
CREATE TABLE `%table_prefix%stats` (
  `stat_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `stat_type` ENUM('total','date') NOT NULL,
  `stat_date_gmt` date DEFAULT NULL,
  `stat_users` INT UNSIGNED NOT NULL DEFAULT '0',
  `stat_images` INT UNSIGNED NOT NULL DEFAULT '0',
  `stat_albums` INT UNSIGNED NOT NULL DEFAULT '0',
  `stat_tags` INT UNSIGNED NOT NULL DEFAULT '0',
  `stat_cron_runs` INT UNSIGNED NOT NULL DEFAULT '0',
  `stat_cron_time` INT UNSIGNED NOT NULL DEFAULT '0',
  `stat_image_views` INT UNSIGNED NOT NULL DEFAULT '0',
  `stat_album_views` INT UNSIGNED NOT NULL DEFAULT '0',
  `stat_image_likes` INT UNSIGNED NOT NULL DEFAULT '0',
  `stat_album_likes` INT UNSIGNED NOT NULL DEFAULT '0',
  `stat_disk_used` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`stat_id`),
  UNIQUE KEY `stat_date_gmt` (`stat_date_gmt`) USING BTREE,
  KEY `stat_type` (`stat_type`),
  KEY `stat_type_date_gmt` (`stat_type`, `stat_date_gmt` DESC)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8;
INSERT INTO `%table_prefix%stats` (stat_id, stat_type) VALUES (1, 'total');
