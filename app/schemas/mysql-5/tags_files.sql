DROP TABLE IF EXISTS `%table_prefix%tags_files`;
CREATE TABLE `%table_prefix%tags_files` (
  `tag_file_tag_id` bigint(32) NOT NULL,
  `tag_file_file_id` bigint(32) NOT NULL,
  FOREIGN KEY (tag_file_tag_id) REFERENCES `%table_prefix%tags` (tag_id) ON DELETE CASCADE,
  FOREIGN KEY (tag_file_file_id) REFERENCES `%table_prefix%images` (image_id) ON DELETE CASCADE,
  UNIQUE INDEX `tag_file_UNIQUE` (`tag_file_tag_id` ASC, `tag_file_file_id` ASC) VISIBLE
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
