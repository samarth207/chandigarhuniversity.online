-- Database setup for Chandigarh University Online leads
-- Run this on your Hostinger MySQL database

CREATE TABLE IF NOT EXISTS `leads` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_name` VARCHAR(100) NOT NULL,
    `student_email` VARCHAR(150) NOT NULL,
    `student_mobile` VARCHAR(20) NOT NULL,
    `student_program` VARCHAR(50) NOT NULL,
    `student_source` VARCHAR(50) DEFAULT 'Website',
    `student_ip` VARCHAR(45) DEFAULT NULL,
    `country_code` VARCHAR(10) DEFAULT '+91',
    `city` VARCHAR(100) DEFAULT NULL,
    `param1` VARCHAR(255) DEFAULT NULL,
    `param2` VARCHAR(255) DEFAULT NULL,
    `param3` VARCHAR(255) DEFAULT NULL,
    `page` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email` (`student_email`),
    INDEX `idx_mobile` (`student_mobile`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
