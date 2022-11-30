DROP TABLE IF EXISTS `%table_prefix%images_hash`;
CREATE TABLE `%table_prefix%images_hash` (
  `image_hash_image_id` bigint(32) NOT NULL,
  `image_hash_hash` mediumtext NOT NULL,
  PRIMARY KEY (`image_hash_image_id`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
