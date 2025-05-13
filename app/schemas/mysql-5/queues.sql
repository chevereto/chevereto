DROP TABLE IF EXISTS `%table_prefix%queues`;
CREATE TABLE `%table_prefix%queues` (
  `queue_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue_type` ENUM('storage-delete') NOT NULL,
  `queue_date_gmt` DATETIME NOT NULL,
  `queue_args` TEXT NOT NULL,
  `queue_join` INT UNSIGNED NOT NULL,
  `queue_attempts` VARCHAR(255) DEFAULT '0',
  `queue_status` ENUM('pending','failed') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`queue_id`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
