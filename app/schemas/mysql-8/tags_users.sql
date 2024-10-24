DROP TABLE IF EXISTS `%table_prefix%tags_users`;
CREATE TABLE `%table_prefix%tags_users` (
  `tag_user_tag_id` bigint(32) NOT NULL,
  `tag_user_user_id` bigint(32) NOT NULL,
  `tag_user_count` int(11) NOT NULL DEFAULT 0,
  `tag_user_last_used_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tag_user_tag_id) REFERENCES `%table_prefix%tags` (tag_id) ON DELETE CASCADE,
  FOREIGN KEY (tag_user_user_id) REFERENCES `%table_prefix%users` (user_id) ON DELETE CASCADE,
  UNIQUE INDEX `tag_user_UNIQUE` (`tag_user_tag_id` ASC, `tag_user_user_id` ASC) VISIBLE,
  KEY `tag_user_count` (`tag_user_count`),
  KEY `tag_user_last_used_datetime` (`tag_user_last_used_datetime`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
