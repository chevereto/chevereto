DROP TABLE IF EXISTS `%table_root_prefix%tenants_stats`;
CREATE TABLE `%table_root_prefix%tenants_stats` (
  `tenant_id` VARCHAR(16) NOT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `users` INT UNSIGNED NOT NULL DEFAULT '0',
  `files` INT UNSIGNED NOT NULL DEFAULT '0',
  `albums` INT UNSIGNED NOT NULL DEFAULT '0',
  `tags` INT UNSIGNED NOT NULL DEFAULT '0',
  `cron_time` INT UNSIGNED NOT NULL DEFAULT '0',
  `file_views` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `album_views` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `file_likes` INT UNSIGNED NOT NULL DEFAULT '0',
  `album_likes` INT UNSIGNED NOT NULL DEFAULT '0',
  `storage_used` BIGINT UNSIGNED NOT NULL DEFAULT '0',
  `admins` INT UNSIGNED NOT NULL DEFAULT '0',
  `managers` INT UNSIGNED NOT NULL DEFAULT '0',
  `pages` INT UNSIGNED NOT NULL DEFAULT '0',
  `storages` INT UNSIGNED NOT NULL DEFAULT '0',
  `categories` INT UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`tenant_id`),
  KEY `updated_at` (`updated_at`),
  FOREIGN KEY (tenant_id) REFERENCES `%table_root_prefix%tenants` (id) ON DELETE CASCADE
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
