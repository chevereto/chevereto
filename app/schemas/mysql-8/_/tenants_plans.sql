DROP TABLE IF EXISTS `%table_root_prefix%tenants_plans`;
CREATE TABLE `%table_root_prefix%tenants_plans` (
  `id` VARCHAR(16) NOT NULL,
  `limits` JSON DEFAULT NULL,
  `env` BLOB DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8mb4;
