-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2025 at 06:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kislap`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `middleName` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `lastName`, `firstName`, `middleName`, `password`, `created_at`) VALUES
(1, 'admin', 'Administrator', 'System', NULL, '$2y$10$WaDhSPKA4EUkRN1kN1od/ONlIHMLNkAmgrPnL8QnG.JUiGShelHp6', '2025-10-22 12:04:45');

-- --------------------------------------------------------

--
-- Table structure for table `ai_temp_bookings`
--

CREATE TABLE `ai_temp_bookings` (
  `temp_booking_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `package_selection` int(11) DEFAULT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `event_time` time DEFAULT NULL,
  `event_location` text DEFAULT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `worker_proposed_price` decimal(10,2) DEFAULT NULL,
  `final_price` decimal(10,2) DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `worker_proposed_date` date DEFAULT NULL,
  `worker_proposed_time` time DEFAULT NULL,
  `worker_notes` text DEFAULT NULL,
  `deposit_amount` decimal(10,2) DEFAULT NULL,
  `deposit_paid` tinyint(1) DEFAULT 0,
  `deposit_paid_at` timestamp NULL DEFAULT NULL,
  `full_payment_paid` tinyint(1) DEFAULT 0,
  `full_payment_paid_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `rated_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `cancelled_by` enum('user','worker','system') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_temp_bookings`
--

INSERT INTO `ai_temp_bookings` (`temp_booking_id`, `conversation_id`, `worker_id`, `package_id`, `package_selection`, `event_type`, `event_date`, `event_time`, `event_location`, `budget`, `worker_proposed_price`, `final_price`, `special_requests`, `worker_proposed_date`, `worker_proposed_time`, `worker_notes`, `deposit_amount`, `deposit_paid`, `deposit_paid_at`, `full_payment_paid`, `full_payment_paid_at`, `completed_at`, `rated_at`, `cancellation_reason`, `cancelled_by`, `created_at`, `updated_at`) VALUES
(14, 14, 1, NULL, NULL, 'Wedding', '2024-12-25', NULL, 'Manika', 1000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2025-10-24 15:46:51', 0, '2025-10-24 15:47:49', '2025-10-24 15:47:49', '2025-10-24 16:15:06', NULL, NULL, '2025-10-24 15:39:51', '2025-10-24 16:15:06'),
(15, 15, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, '2025-10-24 16:15:21', '2025-10-24 16:15:21');

--
-- Triggers `ai_temp_bookings`
--
DELIMITER $$
CREATE TRIGGER `calculate_deposit_before_insert` BEFORE INSERT ON `ai_temp_bookings` FOR EACH ROW BEGIN
    IF NEW.final_price IS NOT NULL AND NEW.deposit_amount IS NULL THEN
        SET NEW.deposit_amount = NEW.final_price * 0.5;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `calculate_deposit_before_update` BEFORE UPDATE ON `ai_temp_bookings` FOR EACH ROW BEGIN
    IF NEW.final_price IS NOT NULL AND NEW.final_price != OLD.final_price THEN
        SET NEW.deposit_amount = NEW.final_price * 0.5;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `application`
--

CREATE TABLE `application` (
  `application_id` int(11) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `middleName` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phoneNumber` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application`
--

INSERT INTO `application` (`application_id`, `lastName`, `firstName`, `middleName`, `email`, `phoneNumber`, `password`, `address`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Mendiguarin', 'Gian', 'Lavandero', 'gian@gmail.com', '09462180052', '$2y$10$jY0JOh6F0ORL4F8hWjYiYO3Lm.keOCgSdBG40nHFdk6kJ6TEXH.2e', 'Naguilayan, Binmaley, Pangasinan', 'accepted', '2025-10-23 03:17:41', '2025-10-23 03:20:28'),
(2, 'Gonzales', 'Mikaella', 'N/A', 'Mikaella@gmail.com', '09293695376', '$2y$10$MFW/ynU1xAIV70jnRf9reuN8whSoPPtRedKYvmqlCIXdBm5bpOGGC', '231 Rivera, Dagupan, Pangasinan', 'accepted', '2025-10-23 03:19:58', '2025-10-23 03:20:23');

-- --------------------------------------------------------

--
-- Table structure for table `application_resume`
--

CREATE TABLE `application_resume` (
  `resume_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `resumeFilePath` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_resume`
--

INSERT INTO `application_resume` (`resume_id`, `application_id`, `resumeFilePath`, `uploaded_at`) VALUES
(1, 1, 'uploads/application/resumes/resume_application_number_1_pdf', '2025-10-23 03:17:41'),
(2, 2, 'uploads/application/resumes/resume_application_number_2_pdf', '2025-10-23 03:19:58');

-- --------------------------------------------------------

--
-- Table structure for table `application_works`
--

CREATE TABLE `application_works` (
  `work_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `worksFilePath` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application_works`
--

INSERT INTO `application_works` (`work_id`, `application_id`, `worksFilePath`, `uploaded_at`) VALUES
(1, 1, 'uploads/application/works/work_0_app_1.avif', '2025-10-23 03:17:41'),
(2, 1, 'uploads/application/works/work_1_app_1.avif', '2025-10-23 03:17:41'),
(3, 1, 'uploads/application/works/work_2_app_1.avif', '2025-10-23 03:17:41'),
(4, 1, 'uploads/application/works/work_3_app_1.avif', '2025-10-23 03:17:41'),
(5, 2, 'uploads/application/works/work_0_app_2.png', '2025-10-23 03:19:58'),
(6, 2, 'uploads/application/works/work_1_app_2.png', '2025-10-23 03:19:58'),
(7, 2, 'uploads/application/works/work_2_app_2.webp', '2025-10-23 03:19:58'),
(8, 2, 'uploads/application/works/work_3_app_2.webp', '2025-10-23 03:19:58');

-- --------------------------------------------------------

--
-- Table structure for table `booking_modifications`
--

CREATE TABLE `booking_modifications` (
  `modification_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `modified_by` enum('user','worker') NOT NULL,
  `modification_type` enum('price_change','date_change','time_change','package_change','location_change','status_change','notes_added') NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `booking_progress_view`
-- (See below for the actual view)
--
CREATE TABLE `booking_progress_view` (
`conversation_id` int(11)
,`user_id` int(11)
,`worker_id` int(11)
,`booking_status` enum('pending_ai','pending_worker','pending_confirmation','negotiating','confirmed','awaiting_deposit','deposit_paid','in_progress','awaiting_final_payment','completed','rated','cancelled','requires_info')
,`customer_first_name` varchar(100)
,`customer_last_name` varchar(100)
,`worker_first_name` varchar(100)
,`worker_last_name` varchar(100)
,`event_type` varchar(100)
,`event_date` date
,`final_price` decimal(10,2)
,`deposit_amount` decimal(10,2)
,`deposit_paid` tinyint(1)
,`deposit_paid_at` timestamp
,`full_payment_paid` tinyint(1)
,`full_payment_paid_at` timestamp
,`rated_at` timestamp
,`payment_status` varchar(22)
);

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `type` enum('ai','direct') DEFAULT 'direct',
  `booking_status` enum('pending_ai','pending_worker','pending_confirmation','negotiating','confirmed','awaiting_deposit','deposit_paid','in_progress','awaiting_final_payment','completed','rated','cancelled','requires_info') DEFAULT 'pending_ai',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`conversation_id`, `user_id`, `worker_id`, `type`, `booking_status`, `created_at`, `updated_at`) VALUES
(14, 1, 1, 'direct', 'completed', '2025-10-24 15:39:51', '2025-10-24 15:47:49'),
(15, 1, 1, 'ai', '', '2025-10-24 16:15:21', '2025-10-24 16:15:21');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_type` enum('user','worker','ai') NOT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `conversation_id`, `sender_id`, `sender_type`, `message_text`, `is_read`, `sent_at`) VALUES
(72, 14, 0, '', 'Hi! I\'m here to help you book a photographer. 📸\n\nWhat type of event are you planning?\n(e.g., Wedding, Birthday, Portrait, Corporate)', 0, '2025-10-24 15:39:51'),
(73, 14, 0, 'ai', 'Hi! I\'m Kislap\'s AI assistant. I\'m here to help you get started with your booking by asking a few quick questions. What kind of **service** are you looking for? (e.g., event, portrait, product, etc.)', 0, '2025-10-24 15:39:51'),
(74, 14, 1, 'user', 'Wedding', 0, '2025-10-24 15:40:06'),
(75, 14, 0, '', 'Great! When is your **Wedding** scheduled?\n(e.g., December 25, 2024 or 12/25/2024)', 0, '2025-10-24 15:40:07'),
(76, 14, 1, 'user', 'December 25', 0, '2025-10-24 15:40:11'),
(77, 14, 0, '', 'Perfect! Where will the event take place?\n(e.g., Manila, Quezon City, or specific venue)', 0, '2025-10-24 15:40:11'),
(78, 14, 1, 'user', 'Manika', 0, '2025-10-24 15:40:16'),
(79, 14, 0, '', 'Almost done! What\'s your budget for photography?\n(e.g., 10000 or 15000)', 0, '2025-10-24 15:40:16'),
(80, 14, 1, 'user', '1000', 0, '2025-10-24 15:40:18'),
(81, 14, 0, '', 'Great! I have all the details:\n\n📸 Event: Wedding\n📅 Date: December 25, 2025\n📍 Location: Manika\n💰 Budget: ₱1,000.00\n\nUnfortunately, there are no available packages at the moment. Would you like to speak with the photographer directly?', 0, '2025-10-24 15:40:18'),
(82, 14, 1, 'user', 'yes', 0, '2025-10-24 15:40:26'),
(83, 14, 0, '', 'Great! I have all the details:\n\n📸 Event: Wedding\n📅 Date: December 25, 2025\n📍 Location: Manika\n💰 Budget: ₱1,000.00\n\nUnfortunately, there are no available packages at the moment. Would you like to speak with the photographer directly?', 0, '2025-10-24 15:40:26'),
(84, 14, 1, 'user', 'yes', 0, '2025-10-24 15:40:35'),
(85, 14, 0, '', 'Great! I have all the details:\n\n📸 Event: Wedding\n📅 Date: December 25, 2025\n📍 Location: Manika\n💰 Budget: ₱1,000.00\n\nUnfortunately, there are no available packages at the moment. Would you like to speak with the photographer directly?', 0, '2025-10-24 15:40:35'),
(86, 14, 1, 'user', 'I would like to talk to a human agent', 0, '2025-10-24 15:40:37'),
(87, 14, 0, '', 'I\'ll connect you with the photographer now. They\'ll respond to you shortly! 👋', 0, '2025-10-24 15:40:37'),
(88, 14, 1, 'worker', '✅ I\'ve accepted your booking! Please proceed with the 50% down payment to confirm.', 0, '2025-10-24 15:46:27'),
(89, 14, 1, 'user', '✅ Down payment of ₱500.00 has been processed successfully! The photographer has been notified.', 0, '2025-10-24 15:46:51'),
(90, 14, 1, 'user', '🎉 Final payment of ₱500.00 has been processed. Thank you for using our service! Please rate your experience.', 0, '2025-10-24 15:47:49'),
(91, 14, 1, 'user', '⭐ I\'ve rated this service 5/5 stars. Review: Mika was great', 0, '2025-10-24 16:15:06'),
(92, 15, 0, '', 'Hi! I\'m here to help you book a photographer. 📸\n\nWhat type of event are you planning?\n(e.g., Wedding, Birthday, Portrait, Corporate)', 0, '2025-10-24 16:15:21'),
(93, 15, 0, 'ai', 'Hi! I\'m Kislap\'s AI assistant. I\'m here to help you get started with your booking by asking a few quick questions. What kind of **service** are you looking for? (e.g., event, portrait, product, etc.)', 0, '2025-10-24 16:15:21');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `package_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_hours` int(11) DEFAULT NULL,
  `photo_count` int(11) DEFAULT NULL,
  `delivery_days` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`package_id`, `worker_id`, `name`, `description`, `price`, `duration_hours`, `photo_count`, `delivery_days`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'Basic portriat sessin', 'nothing', 1000.00, 3, 30, 3, 'active', '2025-10-23 03:32:50', '2025-10-23 03:32:50');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `rating_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`rating_id`, `conversation_id`, `user_id`, `worker_id`, `rating`, `review`, `created_at`) VALUES
(2, 14, 1, 1, 5, 'Mika was great', '2025-10-24 16:15:06');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `middleName` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phoneNumber` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `profilePhotoUrl` varchar(255) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `lastName`, `firstName`, `middleName`, `email`, `phoneNumber`, `password`, `address`, `profilePhotoUrl`, `createdAt`) VALUES
(1, 'Cabubas', 'Cj', 'Caguiat', 'cjplaysgames83@gmail.com', '09462190052', '$2y$10$jEht2ADCmI1OwGJslsjGDO7ygpNuGmpGvmgkAC40gFEyz8jnW76wO', '231, Tapuac District Dagupan City', NULL, '2025-10-23 03:13:51');

-- --------------------------------------------------------

--
-- Table structure for table `workers`
--

CREATE TABLE `workers` (
  `worker_id` int(11) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `status` enum('active','suspended','banned') NOT NULL DEFAULT 'active',
  `lastName` varchar(100) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `middleName` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phoneNumber` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `specialty` varchar(100) DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `bio` text DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `rating_average` decimal(3,2) DEFAULT 0.00,
  `total_ratings` int(11) DEFAULT 0,
  `total_bookings` int(11) DEFAULT 0,
  `total_earnings` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `average_rating` decimal(3,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workers`
--

INSERT INTO `workers` (`worker_id`, `application_id`, `status`, `lastName`, `firstName`, `middleName`, `email`, `phoneNumber`, `password`, `address`, `specialty`, `experience_years`, `bio`, `profile_photo`, `rating_average`, `total_ratings`, `total_bookings`, `total_earnings`, `created_at`, `updated_at`, `average_rating`) VALUES
(1, 2, 'active', 'Gonzales', 'Mikaella', 'N/A', 'Mikaella@gmail.com', '09293695376', '$2y$10$MFW/ynU1xAIV70jnRf9reuN8whSoPPtRedKYvmqlCIXdBm5bpOGGC', '231 Rivera, Dagupan, Pangasinan', 'event', 0, 'I do video edditng', NULL, 0.00, 1, 0, 0.00, '2025-10-23 03:20:23', '2025-10-24 16:15:06', 5.00),
(2, 1, 'suspended', 'Mendiguarin', 'Gian', 'Lavandero', 'gian@gmail.com', '09462180052', '$2y$10$jY0JOh6F0ORL4F8hWjYiYO3Lm.keOCgSdBG40nHFdk6kJ6TEXH.2e', 'Naguilayan, Binmaley, Pangasinan', 'product', 0, 'Im unique', 'uploads/workers/2/worker2_profile_photo.jpg', 0.00, 1, 0, 0.00, '2025-10-23 03:20:28', '2025-10-24 12:42:57', 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `worker_availability`
--

CREATE TABLE `worker_availability` (
  `availability_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `max_bookings` int(11) DEFAULT 1,
  `current_bookings` int(11) DEFAULT 0,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `worker_earnings_view`
-- (See below for the actual view)
--
CREATE TABLE `worker_earnings_view` (
`worker_id` int(11)
,`firstName` varchar(100)
,`lastName` varchar(100)
,`completed_bookings` bigint(21)
,`total_deposits` decimal(32,2)
,`total_earnings` decimal(32,2)
,`average_rating` decimal(14,4)
,`total_ratings` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `worker_settings`
--

CREATE TABLE `worker_settings` (
  `setting_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `auto_accept_bookings` tinyint(1) DEFAULT 0,
  `require_deposit` tinyint(1) DEFAULT 1,
  `deposit_percentage` decimal(5,2) DEFAULT 30.00,
  `min_notice_days` int(11) DEFAULT 7,
  `max_advance_booking_days` int(11) DEFAULT 365,
  `cancellation_policy` text DEFAULT NULL,
  `terms_and_conditions` text DEFAULT NULL,
  `working_hours_start` time DEFAULT '09:00:00',
  `working_hours_end` time DEFAULT '18:00:00',
  `working_days` varchar(50) DEFAULT 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `worker_works`
--

CREATE TABLE `worker_works` (
  `work_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `worker_works`
--

INSERT INTO `worker_works` (`work_id`, `worker_id`, `image_path`, `uploaded_at`) VALUES
(1, 1, 'uploads/workers/1/worker1_work1.png', '2025-10-23 03:20:23'),
(2, 1, 'uploads/workers/1/worker1_work2.png', '2025-10-23 03:20:23'),
(3, 1, 'uploads/workers/1/worker1_work3.webp', '2025-10-23 03:20:23'),
(4, 1, 'uploads/workers/1/worker1_work4.webp', '2025-10-23 03:20:23'),
(10, 2, 'uploads/workers/2/worker2_work68f9a1c10d044.avif', '2025-10-23 03:32:17');

-- --------------------------------------------------------

--
-- Structure for view `booking_progress_view`
--
DROP TABLE IF EXISTS `booking_progress_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `booking_progress_view`  AS SELECT `c`.`conversation_id` AS `conversation_id`, `c`.`user_id` AS `user_id`, `c`.`worker_id` AS `worker_id`, `c`.`booking_status` AS `booking_status`, `u`.`firstName` AS `customer_first_name`, `u`.`lastName` AS `customer_last_name`, `w`.`firstName` AS `worker_first_name`, `w`.`lastName` AS `worker_last_name`, `tb`.`event_type` AS `event_type`, `tb`.`event_date` AS `event_date`, `tb`.`final_price` AS `final_price`, `tb`.`deposit_amount` AS `deposit_amount`, `tb`.`deposit_paid` AS `deposit_paid`, `tb`.`deposit_paid_at` AS `deposit_paid_at`, `tb`.`full_payment_paid` AS `full_payment_paid`, `tb`.`full_payment_paid_at` AS `full_payment_paid_at`, `tb`.`rated_at` AS `rated_at`, CASE WHEN `tb`.`full_payment_paid` = 1 THEN 'Completed' WHEN `tb`.`deposit_paid` = 1 THEN 'Deposit Paid' WHEN `c`.`booking_status` = 'confirmed' THEN 'Awaiting Deposit' WHEN `c`.`booking_status` = 'negotiating' THEN 'Negotiating' ELSE `c`.`booking_status` END AS `payment_status` FROM (((`conversations` `c` join `user` `u` on(`c`.`user_id` = `u`.`user_id`)) join `workers` `w` on(`c`.`worker_id` = `w`.`worker_id`)) left join `ai_temp_bookings` `tb` on(`c`.`conversation_id` = `tb`.`conversation_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `worker_earnings_view`
--
DROP TABLE IF EXISTS `worker_earnings_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `worker_earnings_view`  AS SELECT `w`.`worker_id` AS `worker_id`, `w`.`firstName` AS `firstName`, `w`.`lastName` AS `lastName`, count(case when `tb`.`full_payment_paid` = 1 then 1 end) AS `completed_bookings`, sum(case when `tb`.`deposit_paid` = 1 then `tb`.`deposit_amount` else 0 end) AS `total_deposits`, sum(case when `tb`.`full_payment_paid` = 1 then `tb`.`final_price` else 0 end) AS `total_earnings`, avg(`r`.`rating`) AS `average_rating`, count(`r`.`rating_id`) AS `total_ratings` FROM ((`workers` `w` left join `ai_temp_bookings` `tb` on(`w`.`worker_id` = `tb`.`worker_id`)) left join `ratings` `r` on(`w`.`worker_id` = `r`.`worker_id`)) GROUP BY `w`.`worker_id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`);

--
-- Indexes for table `ai_temp_bookings`
--
ALTER TABLE `ai_temp_bookings`
  ADD PRIMARY KEY (`temp_booking_id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `idx_conversation` (`conversation_id`),
  ADD KEY `idx_event_date` (`event_date`),
  ADD KEY `idx_worker_date` (`worker_id`,`event_date`),
  ADD KEY `idx_deposit_status` (`deposit_paid`,`deposit_paid_at`),
  ADD KEY `idx_payment_status` (`full_payment_paid`,`full_payment_paid_at`);

--
-- Indexes for table `application`
--
ALTER TABLE `application`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `application_resume`
--
ALTER TABLE `application_resume`
  ADD PRIMARY KEY (`resume_id`),
  ADD KEY `idx_application` (`application_id`);

--
-- Indexes for table `application_works`
--
ALTER TABLE `application_works`
  ADD PRIMARY KEY (`work_id`),
  ADD KEY `idx_application` (`application_id`);

--
-- Indexes for table `booking_modifications`
--
ALTER TABLE `booking_modifications`
  ADD PRIMARY KEY (`modification_id`),
  ADD KEY `idx_conversation` (`conversation_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`conversation_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_worker` (`worker_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_booking_status` (`booking_status`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_conversation` (`conversation_id`),
  ADD KEY `idx_sender` (`sender_id`,`sender_type`),
  ADD KEY `idx_sent_at` (`sent_at`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`package_id`),
  ADD KEY `idx_worker` (`worker_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `unique_rating` (`conversation_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `worker_id` (`worker_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phoneNumber` (`phoneNumber`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phoneNumber`);

--
-- Indexes for table `workers`
--
ALTER TABLE `workers`
  ADD PRIMARY KEY (`worker_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phoneNumber` (`phoneNumber`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phoneNumber`),
  ADD KEY `idx_specialty` (`specialty`);

--
-- Indexes for table `worker_availability`
--
ALTER TABLE `worker_availability`
  ADD PRIMARY KEY (`availability_id`),
  ADD UNIQUE KEY `unique_worker_date` (`worker_id`,`date`),
  ADD KEY `idx_worker_date` (`worker_id`,`date`),
  ADD KEY `idx_date` (`date`);

--
-- Indexes for table `worker_settings`
--
ALTER TABLE `worker_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `unique_worker_settings` (`worker_id`);

--
-- Indexes for table `worker_works`
--
ALTER TABLE `worker_works`
  ADD PRIMARY KEY (`work_id`),
  ADD KEY `idx_worker` (`worker_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ai_temp_bookings`
--
ALTER TABLE `ai_temp_bookings`
  MODIFY `temp_booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `application`
--
ALTER TABLE `application`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `application_resume`
--
ALTER TABLE `application_resume`
  MODIFY `resume_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `application_works`
--
ALTER TABLE `application_works`
  MODIFY `work_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `booking_modifications`
--
ALTER TABLE `booking_modifications`
  MODIFY `modification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `workers`
--
ALTER TABLE `workers`
  MODIFY `worker_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `worker_availability`
--
ALTER TABLE `worker_availability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `worker_settings`
--
ALTER TABLE `worker_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `worker_works`
--
ALTER TABLE `worker_works`
  MODIFY `work_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_temp_bookings`
--
ALTER TABLE `ai_temp_bookings`
  ADD CONSTRAINT `ai_temp_bookings_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`conversation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_temp_bookings_ibfk_2` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_temp_bookings_ibfk_3` FOREIGN KEY (`package_id`) REFERENCES `packages` (`package_id`) ON DELETE SET NULL;

--
-- Constraints for table `application_resume`
--
ALTER TABLE `application_resume`
  ADD CONSTRAINT `application_resume_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `application` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `application_works`
--
ALTER TABLE `application_works`
  ADD CONSTRAINT `application_works_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `application` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_modifications`
--
ALTER TABLE `booking_modifications`
  ADD CONSTRAINT `booking_modifications_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`conversation_id`) ON DELETE CASCADE;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`conversation_id`) ON DELETE CASCADE;

--
-- Constraints for table `packages`
--
ALTER TABLE `packages`
  ADD CONSTRAINT `packages_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`conversation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_3` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE;

--
-- Constraints for table `workers`
--
ALTER TABLE `workers`
  ADD CONSTRAINT `workers_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `application` (`application_id`) ON DELETE SET NULL;

--
-- Constraints for table `worker_availability`
--
ALTER TABLE `worker_availability`
  ADD CONSTRAINT `worker_availability_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE;

--
-- Constraints for table `worker_settings`
--
ALTER TABLE `worker_settings`
  ADD CONSTRAINT `worker_settings_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE;

--
-- Constraints for table `worker_works`
--
ALTER TABLE `worker_works`
  ADD CONSTRAINT `worker_works_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
