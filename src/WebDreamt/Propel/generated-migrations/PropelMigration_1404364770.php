<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1404364770.
 * Generated on 2014-07-03 01:19:30 by john
 */
class PropelMigration_1404364770
{
    public $comment = '';

    public function preUp($manager)
    {
        // add the pre-migration code here
    }

    public function postUp($manager)
    {
        // add the post-migration code here
    }

    public function preDown($manager)
    {
        // add the pre-migration code here
    }

    public function postDown($manager)
    {
        // add the post-migration code here
    }

    /**
     * Get the SQL statements for the Up migration
     *
     * @return array list of the SQL strings to execute for the Up migration
     *               the keys being the datasources
     */
    public function getUpSQL()
    {
        return array (
  'default' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `groups`
(
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `permissions` TEXT,
    `created_at` DATETIME DEFAULT \'0000-00-00 00:00:00\' NOT NULL,
    `updated_at` DATETIME DEFAULT \'0000-00-00 00:00:00\' NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `groups_name_unique` (`name`)
) ENGINE=InnoDB;

CREATE TABLE `migrations`
(
    `migration` VARCHAR(255) NOT NULL,
    `batch` INTEGER NOT NULL
) ENGINE=InnoDB;

CREATE TABLE `throttle`
(
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int(10) unsigned NOT NULL,
    `ip_address` VARCHAR(255),
    `attempts` INTEGER DEFAULT 0 NOT NULL,
    `suspended` TINYINT DEFAULT 0 NOT NULL,
    `banned` TINYINT DEFAULT 0 NOT NULL,
    `last_attempt_at` DATETIME,
    `suspended_at` DATETIME,
    `banned_at` DATETIME,
    PRIMARY KEY (`id`),
    INDEX `fk_user_id` (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE `users`
(
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `permissions` TEXT,
    `activated` TINYINT DEFAULT 0 NOT NULL,
    `activation_code` VARCHAR(255),
    `activated_at` VARCHAR(255),
    `last_login` VARCHAR(255),
    `persist_code` VARCHAR(255),
    `reset_password_code` VARCHAR(255),
    `first_name` VARCHAR(255),
    `last_name` VARCHAR(255),
    `created_at` DATETIME DEFAULT \'0000-00-00 00:00:00\' NOT NULL,
    `updated_at` DATETIME DEFAULT \'0000-00-00 00:00:00\' NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `users_email_unique` (`email`),
    INDEX `users_activation_code_index` (`activation_code`),
    INDEX `users_reset_password_code_index` (`reset_password_code`)
) ENGINE=InnoDB;

CREATE TABLE `users_groups`
(
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int(10) unsigned NOT NULL,
    `group_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

    /**
     * Get the SQL statements for the Down migration
     *
     * @return array list of the SQL strings to execute for the Down migration
     *               the keys being the datasources
     */
    public function getDownSQL()
    {
        return array (
  'default' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `groups`;

DROP TABLE IF EXISTS `migrations`;

DROP TABLE IF EXISTS `throttle`;

DROP TABLE IF EXISTS `users`;

DROP TABLE IF EXISTS `users_groups`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}