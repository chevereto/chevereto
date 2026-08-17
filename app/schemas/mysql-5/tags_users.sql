DROP TABLE IF EXISTS `%table_prefix%tags_users`;
CREATE TABLE `%table_prefix%tags_users` (
  `tag_user_tag_id` INT UNSIGNED NOT NULL,
  `tag_user_user_id` INT UNSIGNED NOT NULL,
  `tag_user_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `tag_user_last_used_datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tag_user_tag_id`, `tag_user_user_id`),
  FOREIGN KEY (tag_user_tag_id) REFERENCES `%table_prefix%tags` (tag_id) ON DELETE CASCADE,
  FOREIGN KEY (tag_user_user_id) REFERENCES `%table_prefix%users` (user_id) ON DELETE CASCADE,
  KEY `tag_user_count` (`tag_user_count`),
  KEY `tag_user_last_used_datetime` (`tag_user_last_used_datetime`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
