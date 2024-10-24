DROP TABLE IF EXISTS `%table_prefix%tags_albums`;
CREATE TABLE `%table_prefix%tags_albums` (
  `tag_album_tag_id` bigint(32) NOT NULL,
  `tag_album_album_id` bigint(32) NOT NULL,
  `tag_album_user_id` bigint(32) NOT NULL,
  `tag_album_count` int(11) NOT NULL DEFAULT 0,
  `tag_album_last_used_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tag_album_tag_id) REFERENCES `%table_prefix%tags` (tag_id) ON DELETE CASCADE,
  FOREIGN KEY (tag_album_album_id) REFERENCES `%table_prefix%albums` (album_id) ON DELETE CASCADE,
  FOREIGN KEY (tag_album_user_id) REFERENCES `%table_prefix%users` (user_id) ON DELETE CASCADE,
  UNIQUE INDEX `tag_album_UNIQUE` (`tag_album_tag_id` ASC, `tag_album_album_id` ASC, `tag_album_user_id` ASC) VISIBLE,
  KEY `tag_album_count` (`tag_album_count`),
  KEY `tag_album_last_used_datetime` (`tag_album_last_used_datetime`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
