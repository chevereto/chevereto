DROP TABLE IF EXISTS `%table_root_prefix%tenants`;
CREATE TABLE `%table_root_prefix%tenants` (
  `id` VARCHAR(16) NOT NULL,
  `hostname` VARCHAR(255) NOT NULL,
  `plan_id` VARCHAR(16) NULL,
  `limits` JSON DEFAULT NULL,
  `env` BLOB DEFAULT NULL,
  `is_enabled` TINYINT(1) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,
  `last_job_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (plan_id) REFERENCES `%table_root_prefix%tenants_plans` (id) ON DELETE SET NULL,
  UNIQUE KEY `hostname` (`hostname`),
  KEY `is_enabled_last_job_at` (`is_enabled`, `last_job_at`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
