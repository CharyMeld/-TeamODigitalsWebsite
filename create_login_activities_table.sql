-- Create login_activities table
-- Run this SQL directly in your MySQL database

USE teamo_digital_solutions;

CREATE TABLE IF NOT EXISTS `login_activities` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NULL,
    `email` VARCHAR(255) NOT NULL,
    `status` ENUM('success', 'failed') NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `device_type` VARCHAR(255) NULL,
    `browser` VARCHAR(255) NULL,
    `platform` VARCHAR(255) NULL,
    `location` VARCHAR(255) NULL,
    `failure_reason` VARCHAR(255) NULL,
    `attempted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,

    INDEX `login_activities_user_id_index` (`user_id`),
    INDEX `login_activities_email_index` (`email`),
    INDEX `login_activities_status_index` (`status`),
    INDEX `login_activities_user_id_attempted_at_index` (`user_id`, `attempted_at`),
    INDEX `login_activities_email_status_attempted_at_index` (`email`, `status`, `attempted_at`),

    CONSTRAINT `login_activities_user_id_foreign`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verify table was created
SHOW TABLES LIKE 'login_activities';

-- Show table structure
DESCRIBE login_activities;
