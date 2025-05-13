DROP TABLE IF EXISTS `%table_prefix%requests`;
CREATE TABLE `%table_prefix%requests` (
  `request_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_type` ENUM('upload','signup','account-edit','account-password-forgot','account-password-reset','account-resend-activation','account-email-needed','account-change-email','account-activate','login', 'content-password', 'account-two-factor') NOT NULL,
  `request_user_id` INT UNSIGNED DEFAULT NULL,
  `request_content_id` INT UNSIGNED DEFAULT NULL,
  `request_ip` VARCHAR(255) NOT NULL,
  `request_date` DATETIME NOT NULL,
  `request_date_gmt` DATETIME NOT NULL,
  `request_result` ENUM('success','fail') NOT NULL,
  PRIMARY KEY (`request_id`),
  KEY `request_type` (`request_type`),
  KEY `request_user_id` (`request_user_id`),
  KEY `request_content_id` (`request_content_id`),
  KEY `request_ip` (`request_ip`),
  KEY `request_date_gmt` (`request_date_gmt`),
  KEY `request_result` (`request_result`),
  KEY `request_result_ip_type_date_gmt` (`request_result`, `request_type`, `request_ip`,`request_date_gmt`),
  KEY `request_user_id_result_type_ip` (`request_user_id`, `request_result`, `request_type`, `request_ip`)
) ENGINE=%table_engine% DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
