DROP TABLE IF EXISTS `%table_prefix%uploads_chunks`;
CREATE TABLE `%table_prefix%uploads_chunks` (
  `upload_chunk_upload_id` INT UNSIGNED NOT NULL,
  `upload_chunk_index` INT UNSIGNED NOT NULL,
  `upload_chunk_path` VARCHAR(4096) DEFAULT NULL,
  `upload_chunk_date_gmt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`upload_chunk_upload_id`, `upload_chunk_index`),
  FOREIGN KEY (upload_chunk_upload_id) REFERENCES `%table_prefix%uploads` (upload_id) ON DELETE CASCADE,
  KEY `upload_chunk_date_gmt` (`upload_chunk_date_gmt`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
