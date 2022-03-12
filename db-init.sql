-- --------------------------------------------------------
-- Host:                         185.183.182.192
-- Server version:               5.5.60-MariaDB - MariaDB Server
-- Server OS:                    Linux
-- HeidiSQL Version:             10.1.0.5464
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table multitablepro.email_verifications
CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `verification_token` varchar(32) NOT NULL DEFAULT '0',
  `last_request_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`),
  UNIQUE KEY `verification_token` (`verification_token`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table multitablepro.email_verifications: ~0 rows (approximately)
/*!40000 ALTER TABLE `email_verifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_verifications` ENABLE KEYS */;

-- Dumping structure for table multitablepro.licenses
CREATE TABLE IF NOT EXISTS `licenses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `license_key` varchar(35) NOT NULL DEFAULT '0' COMMENT '6 chunks of 5 split by -',
  `product` int(10) unsigned NOT NULL DEFAULT '0',
  `owner_user` int(10) unsigned NOT NULL DEFAULT '0',
  `issued_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Note that renewed licenses will display the latest renewal date',
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`license_key`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;

-- Dumping data for table multitablepro.licenses: ~2 rows (approximately)
/*!40000 ALTER TABLE `licenses` DISABLE KEYS */;
INSERT INTO `licenses` (`id`, `license_key`, `product`, `owner_user`, `issued_at`, `expires_at`) VALUES
	(20, 'MTPRO-TILPK-973TQ-ZFXCW-6BPB7-ZXQSI', 2, 1, '2019-03-11 08:21:46', NULL);
INSERT INTO `licenses` (`id`, `license_key`, `product`, `owner_user`, `issued_at`, `expires_at`) VALUES
	(23, 'MTPRO-CRTGN-YNEK9-91UOG-BW8K6-4WYH5', 8, 2, '2019-04-04 13:03:09', '2019-05-05 13:03:09');
/*!40000 ALTER TABLE `licenses` ENABLE KEYS */;

-- Dumping structure for table multitablepro.products
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_group` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(64) NOT NULL,
  `description` varchar(256) DEFAULT NULL,
  `price` float unsigned NOT NULL DEFAULT '0',
  `discount_price` float unsigned DEFAULT NULL,
  `duration_days` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'how long is the license valid (0 is infinite)',
  `restrictions` varchar(256) DEFAULT NULL COMMENT 'string passed to software to implement any restrictions',
  `is_public` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

-- Dumping data for table multitablepro.products: ~7 rows (approximately)
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` (`id`, `product_group`, `name`, `description`, `price`, `discount_price`, `duration_days`, `restrictions`, `is_public`) VALUES
	(2, 1, 'MultiTable Pro (Private Edition)', 'Unrestricted version of MultiTable Pro', 0, NULL, 0, 'UNLIMITED_COMPUTERS:TRUE,BUILD_TYPE:INTERNAL', 0);
INSERT INTO `products` (`id`, `product_group`, `name`, `description`, `price`, `discount_price`, `duration_days`, `restrictions`, `is_public`) VALUES
	(3, 1, 'MultiTable Pro (Micro Stakes Monthly)', 'Allows you to play on tables with buyins up to $15 for a month.', 2.95, NULL, 31, 'MAX_STAKE:15', 1);
INSERT INTO `products` (`id`, `product_group`, `name`, `description`, `price`, `discount_price`, `duration_days`, `restrictions`, `is_public`) VALUES
	(4, 1, 'MultiTable Pro (Low Stakes Monthly)', 'Allows you to play on tables with buyins up to $50 for a month.', 9.95, NULL, 31, 'MAX_STAKE:50', 1);
INSERT INTO `products` (`id`, `product_group`, `name`, `description`, `price`, `discount_price`, `duration_days`, `restrictions`, `is_public`) VALUES
	(5, 1, 'MultiTable Pro (All Stakes Monthly)', 'Allows you to play on tables with any buyin for a month.', 24.95, NULL, 31, '', 1);
INSERT INTO `products` (`id`, `product_group`, `name`, `description`, `price`, `discount_price`, `duration_days`, `restrictions`, `is_public`) VALUES
	(6, 1, 'MultiTable Pro (Micro Stakes Annual)', 'Allows you to play on tables with buyins up to $15 for a year.', 24.95, NULL, 365, 'MAX_STAKE:15', 1);
INSERT INTO `products` (`id`, `product_group`, `name`, `description`, `price`, `discount_price`, `duration_days`, `restrictions`, `is_public`) VALUES
	(7, 1, 'MultiTable Pro (Low Stakes Annual)', 'Allows you to play on tables with buyins up to $50 for a year.', 99.95, NULL, 365, 'MAX_STAKE:50', 1);
INSERT INTO `products` (`id`, `product_group`, `name`, `description`, `price`, `discount_price`, `duration_days`, `restrictions`, `is_public`) VALUES
	(8, 1, 'MultiTable Pro (Pleb License)', 'Meme tester version of license. Here should be some info about what it can do. It''s 15NL.', 0, NULL, 31, 'BUILD_TYPE:INTERNAL,MAX_STAKE:15', 0);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;

-- Dumping structure for table multitablepro.product_groups
CREATE TABLE IF NOT EXISTS `product_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `license_prefix` varchar(5) NOT NULL COMMENT 'Length must be 5 or less (only tested with 5)',
  `short_name` varchar(64) NOT NULL COMMENT 'used to determine url and view',
  `full_name` varchar(64) NOT NULL DEFAULT '0',
  `seo_description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shortname` (`short_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table multitablepro.product_groups: ~1 rows (approximately)
/*!40000 ALTER TABLE `product_groups` DISABLE KEYS */;
INSERT INTO `product_groups` (`id`, `license_prefix`, `short_name`, `full_name`, `seo_description`) VALUES
	(1, 'MTPRO', 'multitable-pro', 'MultiTable Pro', NULL);
/*!40000 ALTER TABLE `product_groups` ENABLE KEYS */;

-- Dumping structure for table multitablepro.purchases
CREATE TABLE IF NOT EXISTS `purchases` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL,
  `product` int(10) unsigned NOT NULL,
  `time_purchased` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price_paid` float NOT NULL,
  `purchase_type` enum('BUY','UPGRADE','RENEW') NOT NULL DEFAULT 'BUY',
  `is_complete` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `payment_method` enum('FREE','PAYPAL') NOT NULL,
  `payment_reference` varchar(256) DEFAULT NULL COMMENT 'paypal reference id',
  `custom_order_description` varchar(256) DEFAULT NULL COMMENT 'only applies to orders where support had to intervene for some reason',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table multitablepro.purchases: ~0 rows (approximately)
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;

-- Dumping structure for table multitablepro.purchase_tokens
CREATE TABLE IF NOT EXISTS `purchase_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `purchase` int(10) unsigned NOT NULL,
  `complete_token` varchar(32) NOT NULL,
  `cancel_token` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table multitablepro.purchase_tokens: ~0 rows (approximately)
/*!40000 ALTER TABLE `purchase_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_tokens` ENABLE KEYS */;

-- Dumping structure for table multitablepro.settings
CREATE TABLE IF NOT EXISTS `settings` (
  `name` varchar(50) NOT NULL,
  `value` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table multitablepro.settings: ~13 rows (approximately)
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` (`name`, `value`) VALUES
	('api_admin_token', '');
INSERT INTO `settings` (`name`, `value`) VALUES
	('download_files_dir', '/home/multitablepro/public_html/downloads');
INSERT INTO `settings` (`name`, `value`) VALUES
	('email_pass', '');
INSERT INTO `settings` (`name`, `value`) VALUES
	('email_sender_name', 'MultiTable Pro');
INSERT INTO `settings` (`name`, `value`) VALUES
	('email_user', 'noreply@multitablepro.com');
INSERT INTO `settings` (`name`, `value`) VALUES
	('paypal_debug', '0');
INSERT INTO `settings` (`name`, `value`) VALUES
	('paypal_debug_clientid', '');
INSERT INTO `settings` (`name`, `value`) VALUES
	('paypal_debug_secret', '');
INSERT INTO `settings` (`name`, `value`) VALUES
	('paypal_live_clientid', '');
INSERT INTO `settings` (`name`, `value`) VALUES
	('paypal_live_secret', '');
INSERT INTO `settings` (`name`, `value`) VALUES
	('recaptcha_private', '');
INSERT INTO `settings` (`name`, `value`) VALUES
	('recaptcha_public', '');
INSERT INTO `settings` (`name`, `value`) VALUES
	('site_title', 'MultiTable Pro');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;

-- Dumping structure for table multitablepro.trials
CREATE TABLE IF NOT EXISTS `trials` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mac_address` varchar(17) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `product_group` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

-- Dumping data for table multitablepro.trials: ~10 rows (approximately)
/*!40000 ALTER TABLE `trials` DISABLE KEYS */;
INSERT INTO `trials` (`id`, `mac_address`, `expires_at`, `product_group`) VALUES
	(2, '123456789', '2019-04-11 16:43:42', 1);
/*!40000 ALTER TABLE `trials` ENABLE KEYS */;

-- Dumping structure for table multitablepro.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL,
  `pass_hash` varchar(255) NOT NULL,
  `first_name` varchar(64) NOT NULL,
  `last_name` varchar(64) NOT NULL,
  `role` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:user - 2:VIP(all free) - 3: support - 4:developer?(unused) - 5: admin',
  `register_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- Dumping structure for table multitablepro.version_info
CREATE TABLE IF NOT EXISTS `version_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_group` int(10) unsigned NOT NULL,
  `branch` enum('INTERNAL','BETA','RELEASE') NOT NULL DEFAULT 'INTERNAL',
  `version` varchar(32) NOT NULL COMMENT 'should match assembly version eg 1.0.0.1',
  `release_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `changelog` varchar(2048) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=latin1;

-- Dumping data for table multitablepro.version_info: ~3 rows (approximately)
/*!40000 ALTER TABLE `version_info` DISABLE KEYS */;
INSERT INTO `version_info` (`id`, `product_group`, `branch`, `version`, `release_date`, `changelog`) VALUES
	(23, 1, 'RELEASE', '1.0.0.1', '2019-03-19 13:27:10', 'This is an invalid version of the application used to test the update system.');
INSERT INTO `version_info` (`id`, `product_group`, `branch`, `version`, `release_date`, `changelog`) VALUES
	(25, 1, 'INTERNAL', '1.0.0.2', '2019-04-04 13:40:29', '- License system implemented\r\n- Put active slots in front when they overlap other slots\r\n- Trust poker client to handle BringToForeground\r\n- Bwin cash table name detection fixed\r\n- Bwin tourneys with round buyins now detect correctly\r\n- Fix issue with Force Table Position setting\r\n- API access complete\r\n- Restart the application when license expires, warn 30 minutes before this happens.\r\n- Check for internal builds only (temporary)');
INSERT INTO `version_info` (`id`, `product_group`, `branch`, `version`, `release_date`, `changelog`) VALUES
	(26, 1, 'INTERNAL', '1.0.0.3', '2019-04-05 12:54:55', '- Fix crash on fresh install');
/*!40000 ALTER TABLE `version_info` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;