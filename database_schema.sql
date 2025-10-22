-- ============================================
-- KISLAP DATABASE SCHEMA
-- Complete database recreation script
-- ============================================

-- Drop existing tables if they exist (in correct order to avoid foreign key issues)
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `ai_temp_bookings`;
DROP TABLE IF EXISTS `conversations`;
DROP TABLE IF EXISTS `packages`;
DROP TABLE IF EXISTS `worker_works`;
DROP TABLE IF EXISTS `workers`;
DROP TABLE IF EXISTS `application_works`;
DROP TABLE IF EXISTS `application_resume`;
DROP TABLE IF EXISTS `application`;
DROP TABLE IF EXISTS `admin`;
DROP TABLE IF EXISTS `user`;

-- ============================================
-- USER TABLE (for clients/customers)
-- ============================================
CREATE TABLE `user` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `lastName` VARCHAR(100) NOT NULL,
    `firstName` VARCHAR(100) NOT NULL,
    `middleName` VARCHAR(100),
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `phoneNumber` VARCHAR(20) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `address` TEXT,
    `profilePhotoUrl` VARCHAR(255),
    `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_phone` (`phoneNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ADMIN TABLE
-- ============================================
CREATE TABLE `admin` (
    `admin_id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) UNIQUE NOT NULL,
    `lastName` VARCHAR(100) NOT NULL,
    `firstName` VARCHAR(100) NOT NULL,
    `middleName` VARCHAR(100),
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- APPLICATION TABLE (for photographer applications)
-- ============================================
CREATE TABLE `application` (
    `application_id` INT AUTO_INCREMENT PRIMARY KEY,
    `lastName` VARCHAR(100) NOT NULL,
    `firstName` VARCHAR(100) NOT NULL,
    `middleName` VARCHAR(100),
    `email` VARCHAR(255) NOT NULL,
    `phoneNumber` VARCHAR(20) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `address` TEXT,
    `status` ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- APPLICATION RESUME TABLE
-- ============================================
CREATE TABLE `application_resume` (
    `resume_id` INT AUTO_INCREMENT PRIMARY KEY,
    `application_id` INT NOT NULL,
    `resumeFilePath` VARCHAR(255) NOT NULL,
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`application_id`) REFERENCES `application`(`application_id`) ON DELETE CASCADE,
    INDEX `idx_application` (`application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- APPLICATION WORKS TABLE (portfolio samples)
-- ============================================
CREATE TABLE `application_works` (
    `work_id` INT AUTO_INCREMENT PRIMARY KEY,
    `application_id` INT NOT NULL,
    `worksFilePath` VARCHAR(255) NOT NULL,
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`application_id`) REFERENCES `application`(`application_id`) ON DELETE CASCADE,
    INDEX `idx_application` (`application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- WORKERS TABLE (approved photographers)
-- ============================================
CREATE TABLE `workers` (
    `worker_id` INT AUTO_INCREMENT PRIMARY KEY,
    `application_id` INT,
    `lastName` VARCHAR(100) NOT NULL,
    `firstName` VARCHAR(100) NOT NULL,
    `middleName` VARCHAR(100),
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `phoneNumber` VARCHAR(20) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `address` TEXT,
    `specialty` VARCHAR(100),
    `experience_years` INT DEFAULT 0,
    `bio` TEXT,
    `profile_photo` VARCHAR(255),
    `rating_average` DECIMAL(3,2) DEFAULT 0.00,
    `total_ratings` INT DEFAULT 0,
    `total_bookings` INT DEFAULT 0,
    `total_earnings` DECIMAL(10,2) DEFAULT 0.00,
    `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`application_id`) REFERENCES `application`(`application_id`) ON DELETE SET NULL,
    INDEX `idx_email` (`email`),
    INDEX `idx_phone` (`phoneNumber`),
    INDEX `idx_status` (`status`),
    INDEX `idx_specialty` (`specialty`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- WORKER WORKS TABLE (portfolio images)
-- ============================================
CREATE TABLE `worker_works` (
    `work_id` INT AUTO_INCREMENT PRIMARY KEY,
    `worker_id` INT NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`worker_id`) REFERENCES `workers`(`worker_id`) ON DELETE CASCADE,
    INDEX `idx_worker` (`worker_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PACKAGES TABLE (service packages)
-- ============================================
CREATE TABLE `packages` (
    `package_id` INT AUTO_INCREMENT PRIMARY KEY,
    `worker_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10,2) NOT NULL,
    `duration_hours` INT,
    `photo_count` INT,
    `delivery_days` INT,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`worker_id`) REFERENCES `workers`(`worker_id`) ON DELETE CASCADE,
    INDEX `idx_worker` (`worker_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CONVERSATIONS TABLE (chat/messaging)
-- ============================================
CREATE TABLE `conversations` (
    `conversation_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `worker_id` INT NOT NULL,
    `type` ENUM('ai', 'direct') DEFAULT 'direct',
    `booking_status` ENUM('pending_ai', 'pending_worker', 'pending_confirmation', 'confirmed', 'cancelled', 'completed', 'requires_info', 'negotiating') DEFAULT 'pending_ai',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `user`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`worker_id`) REFERENCES `workers`(`worker_id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_worker` (`worker_id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_booking_status` (`booking_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MESSAGES TABLE
-- ============================================
CREATE TABLE `messages` (
    `message_id` INT AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` INT NOT NULL,
    `sender_id` INT NOT NULL,
    `sender_type` ENUM('user', 'worker', 'ai') NOT NULL,
    `message_text` TEXT NOT NULL,
    `is_read` BOOLEAN DEFAULT FALSE,
    `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`conversation_id`) ON DELETE CASCADE,
    INDEX `idx_conversation` (`conversation_id`),
    INDEX `idx_sender` (`sender_id`, `sender_type`),
    INDEX `idx_sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- AI TEMP BOOKINGS TABLE (for AI chat bookings)
-- ============================================
CREATE TABLE `ai_temp_bookings` (
    `temp_booking_id` INT AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` INT NOT NULL,
    `worker_id` INT NOT NULL,
    `package_id` INT,
    `event_type` VARCHAR(100),
    `event_date` DATE,
    `event_time` TIME,
    `event_location` TEXT,
    `budget` DECIMAL(10,2),
    `available_packages` TEXT,
    `special_requests` TEXT,
    `worker_proposed_price` DECIMAL(10,2),
    `worker_proposed_date` DATE,
    `worker_proposed_time` TIME,
    `worker_notes` TEXT,
    `deposit_amount` DECIMAL(10,2),
    `deposit_paid` BOOLEAN DEFAULT FALSE,
    `deposit_paid_at` TIMESTAMP NULL,
    `final_price` DECIMAL(10,2),
    `cancellation_reason` TEXT,
    `cancelled_by` ENUM('user', 'worker', 'system') NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`conversation_id`) ON DELETE CASCADE,
    FOREIGN KEY (`worker_id`) REFERENCES `workers`(`worker_id`) ON DELETE CASCADE,
    FOREIGN KEY (`package_id`) REFERENCES `packages`(`package_id`) ON DELETE SET NULL,
    INDEX `idx_conversation` (`conversation_id`),
    INDEX `idx_event_date` (`event_date`),
    INDEX `idx_worker_date` (`worker_id`, `event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT DEFAULT ADMIN ACCOUNT
-- Password: admin123 (hashed)
-- ============================================
INSERT INTO `admin` (`username`, `lastName`, `firstName`, `middleName`, `password`) 
VALUES ('admin', 'Administrator', 'System', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ============================================
-- WORKER AVAILABILITY TABLE (for calendar management)
-- ============================================
CREATE TABLE `worker_availability` (
    `availability_id` INT AUTO_INCREMENT PRIMARY KEY,
    `worker_id` INT NOT NULL,
    `date` DATE NOT NULL,
    `is_available` BOOLEAN DEFAULT TRUE,
    `start_time` TIME,
    `end_time` TIME,
    `max_bookings` INT DEFAULT 1,
    `current_bookings` INT DEFAULT 0,
    `notes` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`worker_id`) REFERENCES `workers`(`worker_id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_worker_date` (`worker_id`, `date`),
    INDEX `idx_worker_date` (`worker_id`, `date`),
    INDEX `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BOOKING MODIFICATIONS LOG TABLE
-- ============================================
CREATE TABLE `booking_modifications` (
    `modification_id` INT AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` INT NOT NULL,
    `modified_by` ENUM('user', 'worker') NOT NULL,
    `modification_type` ENUM('price_change', 'date_change', 'time_change', 'package_change', 'location_change', 'status_change', 'notes_added') NOT NULL,
    `old_value` TEXT,
    `new_value` TEXT,
    `reason` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`conversation_id`) ON DELETE CASCADE,
    INDEX `idx_conversation` (`conversation_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- WORKER SETTINGS TABLE (for business preferences)
-- ============================================
CREATE TABLE `worker_settings` (
    `setting_id` INT AUTO_INCREMENT PRIMARY KEY,
    `worker_id` INT NOT NULL,
    `auto_accept_bookings` BOOLEAN DEFAULT FALSE,
    `require_deposit` BOOLEAN DEFAULT TRUE,
    `deposit_percentage` DECIMAL(5,2) DEFAULT 30.00,
    `min_notice_days` INT DEFAULT 7,
    `max_advance_booking_days` INT DEFAULT 365,
    `cancellation_policy` TEXT,
    `terms_and_conditions` TEXT,
    `working_hours_start` TIME DEFAULT '09:00:00',
    `working_hours_end` TIME DEFAULT '18:00:00',
    `working_days` VARCHAR(50) DEFAULT 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`worker_id`) REFERENCES `workers`(`worker_id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_worker_settings` (`worker_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SCHEMA CREATION COMPLETE
-- ============================================
