SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

USE `test`;

DROP TABLE IF EXISTS `customer`;
CREATE TABLE `customer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `billing_street_address` varchar(255) DEFAULT NULL,
  `billing_city` varchar(100) DEFAULT NULL,
  `billing_state` enum('AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY') NOT NULL,
  `billing_zip` int(5) unsigned DEFAULT NULL,
  `customer_add_date` datetime DEFAULT NULL,
  `active` tinyint(1) unsigned DEFAULT NULL,
  `credit_customer` tinyint(1) unsigned DEFAULT NULL,
  `phone` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `customer_location`;
CREATE TABLE `customer_location` (
  `customer_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  `current_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`customer_id`,`location_id`),
  KEY `customer_id` (`customer_id`),
  KEY `location_id` (`location_id`),
  CONSTRAINT `customer_location_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
  CONSTRAINT `customer_location_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `location` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `driver`;
CREATE TABLE `driver` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `salary` decimal(10,2) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `permissions` text,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `groups_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `job`;
CREATE TABLE `job` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  `driver_id` int(10) unsigned DEFAULT NULL,
  `commission` decimal(10,2) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `location_id` (`location_id`),
  KEY `driver_id` (`driver_id`),
  CONSTRAINT `job_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
  CONSTRAINT `job_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `location` (`id`),
  CONSTRAINT `job_ibfk_3` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `location`;
CREATE TABLE `location` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `city` varchar(100) NOT NULL,
  `state` varchar(50) NOT NULL,
  `zip` int(10) unsigned NOT NULL,
  `street_address` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `service`;
CREATE TABLE `service` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `price` decimal(10,0) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `service_job`;
CREATE TABLE `service_job` (
  `service_id` int(11) NOT NULL,
  `job_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`service_id`,`job_id`),
  KEY `job_id` (`job_id`),
  CONSTRAINT `service_job_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `service` (`id`),
  CONSTRAINT `service_job_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `job` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `permissions` text,
  `activated` tinyint(4) NOT NULL DEFAULT '0',
  `activation_code` varchar(255) DEFAULT NULL,
  `activated_at` varchar(255) DEFAULT NULL,
  `last_login` varchar(255) DEFAULT NULL,
  `persist_code` varchar(255) DEFAULT NULL,
  `reset_password_code` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_activation_code_index` (`activation_code`),
  KEY `users_reset_password_code_index` (`reset_password_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `users_groups`;
CREATE TABLE `users_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `users_groups_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `users_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE `vehicles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `year` int(4) DEFAULT NULL,
  `make` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  `current_mileage` int(10) unsigned DEFAULT NULL,
  `license_plate` varchar(8) NOT NULL,
  `mileage_oil_last` int(10) unsigned DEFAULT NULL,
  `mileage_fuelfilter_last` int(10) unsigned DEFAULT NULL,
  `needs_service` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;