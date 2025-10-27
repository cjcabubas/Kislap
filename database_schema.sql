-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2025 at 04:49 PM
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
(1, 1, 1, NULL, NULL, 'Wedding', '2025-12-28', NULL, 'Dagupan City, Pangasinan', 10000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, '2025-10-27 14:14:51', '2025-10-27 14:19:29');

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
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `application`
--

INSERT INTO `application` (`application_id`, `lastName`, `firstName`, `middleName`, `email`, `phoneNumber`, `password`, `address`, `status`, `rejection_reason`, `created_at`, `updated_at`) VALUES
(1, 'Navarro', 'Miguel', 'Santos', 'miguel.navarro@gmail.com', '09171234567', '$2y$10$WfQwbX19RhDSNmUxV0FIKeFkNUJs6iy9berhaoGS5vvqBGlpNO5eO', '12 Rizal St., Brgy. San Jose, Lucena City, Quezon', 'pending', NULL, '2025-10-27 12:10:20', '2025-10-27 12:10:20'),
(2, 'Flores', 'Anna', NULL, 'anna.flores@yahoo.com', '09281234678', '$2y$10$yM7CO8CRnXz0dw3tvXp7xufRNsxvDw17DxbcjGUePXEDymaWvj2EC', '45 Mabini Ave., Brgy. Poblacion, Iloilo City, Iloilo', 'pending', NULL, '2025-10-27 12:14:33', '2025-10-27 12:14:33'),
(3, 'Cruz', 'Diego', 'Ramos', 'diego.cruz@outlook.com', '09391234789', '$2y$10$OWgqbgTuqN.LqPTb7Y3zlu3PvSuEGeJ.zGCi5Ov4NyBUvauyb0z5.', '78 Magsaysay Rd., Brgy. Centro, Tagbilaran, Bohol', 'pending', NULL, '2025-10-27 12:16:16', '2025-10-27 12:16:16'),
(4, 'Mercado', 'Beatrice', 'Gomez', 'bea.mercado@mail.com', '09181234890', '$2y$10$rsJIq5UikdexzEnCtEVfJOj2w0lH0czsaVk06Khaomm.djsKziDX.', '101 San Miguel St., Brgy. Burgos, Batangas City, Batangas', 'pending', NULL, '2025-10-27 12:19:11', '2025-10-27 12:19:11'),
(5, 'Ilagan', 'Rafael', 'Torres', 'rafael.ilagan@protonmail.com', '09271234901', '$2y$10$G.87VjREljAzBMX5zVaV3u8GVbPv0pEQJOeeZ1FxP82Gnk0DP0E1W', '9 Laurel Lane, Brgy. San Isidro, Cagayan de Oro, Misamis Oriental', 'pending', NULL, '2025-10-27 12:23:17', '2025-10-27 12:23:17'),
(6, 'Valdez', 'Camille', NULL, 'camille.valdez@gmail.com', '09371234560', '$2y$10$SWMs88YwEDYF3heLzEI0AOID21d0qcZnRs/2u4HgOhcc2bjtXSEsO', '222 Mango St., Brgy. Pasonanca, Zamboanga City, Zamboanga del Sur', 'pending', NULL, '2025-10-27 12:25:19', '2025-10-27 12:25:19'),
(7, 'Pineda', 'Arnel', 'Bautista', 'arnel.pineda@yahoo.com', '09191234561', '$2y$10$wVYNjkkONA69HvN33Qpc/ePovUvcsU/hEXevW7RGd4vMVKEXDYBXy', '33 Acacia Dr., Brgy. San Roque, Naga City, Camarines Sur', 'accepted', NULL, '2025-10-27 12:55:16', '2025-10-27 14:13:24'),
(8, 'Aquino', 'Liza', 'dela Cruz', 'liza.aquino@icloud.com', '09291234562', '$2y$10$7ogiDcSrkZQ.gseekxlYFu7KGu3nQ1PJFzR48VVTJUZxOUAKxzTjS', '7 Coconut Rd., Brgy. San Antonio, Laoag City, Ilocos Norte', 'accepted', NULL, '2025-10-27 13:00:51', '2025-10-27 14:13:19'),
(9, 'Santos', 'Marco', 'Perez', 'marco.santos@ymail.com', '09391234563', '$2y$10$StezBCsrjo/f1dF4iiaXGO8Aam665PxmSXyeVtYVXSD2T24lBBSpq', '150 Bamboo St., Brgy. San Vicente, Puerto Princesa, Palawan', 'accepted', NULL, '2025-10-27 13:05:42', '2025-10-27 14:13:01'),
(10, 'Ortega', 'Nelia', 'Ramos', 'nelia.ortega@zoho.com', '09171234564', '$2y$10$H/J3coqb7KKdjZ4UwmDBQu5XNfkPgW0fuJUwLq4mRy0ZzLsTd8kY6', '56 Sampaguita Ln., Brgy. Divisoria, Tarlac City, Tarlac', 'accepted', NULL, '2025-10-27 14:05:25', '2025-10-27 14:12:57'),
(11, 'Reyes', 'Carlo', 'Alonzo', 'carlo.reyes@gmail.com', '09281234565', '$2y$10$7oefTFOYDubZ./S5lALSLueTYTt9jTocYMYL0sj/YgVt/b0kHdrIS', '88 Bago Blvd., Brgy. Banilad, Cebu City, Cebu', 'accepted', NULL, '2025-10-27 14:07:08', '2025-10-27 14:12:46'),
(12, 'Gonzaga', 'Melinda', 'Santos', 'melinda.gonzaga@mail.com', '09371234566', '$2y$10$F2hpK8VIR.jlGuzH/k8sbO7/PDwAyR.X2H6JtmqmVAj3Oj.qyzfHa', '14 Lapu-Lapu St., Brgy. Durano, Mandaue City, Cebu', 'accepted', NULL, '2025-10-27 14:11:00', '2025-10-27 14:12:53');

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
(1, 1, 'uploads/application/resumes/resume_app_1_1761567020_3c29f35a4b558067.pdf', '2025-10-27 12:10:20'),
(2, 2, 'uploads/application/resumes/resume_app_2_1761567273_757f8c581a303548.pdf', '2025-10-27 12:14:33'),
(3, 3, 'uploads/application/resumes/resume_app_3_1761567376_9ea2f7bc8ceb9fc3.pdf', '2025-10-27 12:16:16'),
(4, 4, 'uploads/application/resumes/resume_app_4_1761567551_620c25b6dff040a3.pdf', '2025-10-27 12:19:11'),
(5, 5, 'uploads/application/resumes/resume_app_5_1761567797_4801da6b996982d1.pdf', '2025-10-27 12:23:17'),
(6, 6, 'uploads/application/resumes/resume_app_6_1761567919_f4baffd1f622ccf0.pdf', '2025-10-27 12:25:19'),
(7, 7, 'uploads/application/resumes/resume_app_7_1761569716_15ab903bbbf4f134.pdf', '2025-10-27 12:55:16'),
(8, 8, 'uploads/application/resumes/resume_app_8_1761570051_1779ea639a825921.pdf', '2025-10-27 13:00:51'),
(9, 9, 'uploads/application/resumes/resume_app_9_1761570342_e3bac9fbd92d1568.pdf', '2025-10-27 13:05:42'),
(10, 10, 'uploads/application/resumes/resume_app_10_1761573925_de67423d0d806f4d.pdf', '2025-10-27 14:05:25'),
(11, 11, 'uploads/application/resumes/resume_app_11_1761574028_2a2d4f3ea15caf16.pdf', '2025-10-27 14:07:08'),
(12, 12, 'uploads/application/resumes/resume_app_12_1761574260_7f09569f0ef3f0af.pdf', '2025-10-27 14:11:00');

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
(1, 1, 'uploads/application/works/work_0_app_1_1761567020_ea71ec91da3402a6.avif', '2025-10-27 12:10:20'),
(2, 1, 'uploads/application/works/work_1_app_1_1761567020_9b6c055e80db673b.avif', '2025-10-27 12:10:20'),
(3, 1, 'uploads/application/works/work_2_app_1_1761567020_b6f8a55a2eea1d83.avif', '2025-10-27 12:10:20'),
(4, 1, 'uploads/application/works/work_3_app_1_1761567020_aed93445d18074b1.avif', '2025-10-27 12:10:20'),
(5, 1, 'uploads/application/works/work_4_app_1_1761567020_9b0f077829c5c056.avif', '2025-10-27 12:10:20'),
(6, 1, 'uploads/application/works/work_5_app_1_1761567020_8431bd661d6aee39.avif', '2025-10-27 12:10:20'),
(7, 1, 'uploads/application/works/work_6_app_1_1761567020_0335510137110e5c.avif', '2025-10-27 12:10:20'),
(8, 1, 'uploads/application/works/work_7_app_1_1761567020_2d6e04fa1de29b6b.avif', '2025-10-27 12:10:20'),
(9, 2, 'uploads/application/works/work_0_app_2_1761567273_9651c9c591810ae5.avif', '2025-10-27 12:14:33'),
(10, 2, 'uploads/application/works/work_1_app_2_1761567273_bfbc530cc1c03783.avif', '2025-10-27 12:14:33'),
(11, 2, 'uploads/application/works/work_2_app_2_1761567273_cc5b0b45a3b77827.avif', '2025-10-27 12:14:33'),
(12, 2, 'uploads/application/works/work_3_app_2_1761567273_86d1eec24edc7bad.avif', '2025-10-27 12:14:33'),
(13, 2, 'uploads/application/works/work_4_app_2_1761567273_e823657742c5983b.avif', '2025-10-27 12:14:33'),
(14, 2, 'uploads/application/works/work_5_app_2_1761567273_c919621a510fab95.avif', '2025-10-27 12:14:33'),
(15, 2, 'uploads/application/works/work_6_app_2_1761567273_0be80f0fcab56cdc.avif', '2025-10-27 12:14:33'),
(16, 3, 'uploads/application/works/work_0_app_3_1761567376_cee918eb3556a817.avif', '2025-10-27 12:16:16'),
(17, 3, 'uploads/application/works/work_1_app_3_1761567376_11c6f164206de3c5.avif', '2025-10-27 12:16:16'),
(18, 3, 'uploads/application/works/work_2_app_3_1761567376_b97743bb2c26ade3.avif', '2025-10-27 12:16:16'),
(19, 3, 'uploads/application/works/work_3_app_3_1761567376_03423c4782f0ef5d.avif', '2025-10-27 12:16:16'),
(20, 3, 'uploads/application/works/work_4_app_3_1761567376_8867737d496572f6.avif', '2025-10-27 12:16:16'),
(21, 3, 'uploads/application/works/work_5_app_3_1761567376_c85e9664c6a39a22.avif', '2025-10-27 12:16:16'),
(22, 3, 'uploads/application/works/work_6_app_3_1761567376_ff300a6a107f3206.avif', '2025-10-27 12:16:16'),
(23, 3, 'uploads/application/works/work_7_app_3_1761567376_1b7a8c2d0ee6b139.avif', '2025-10-27 12:16:16'),
(24, 4, 'uploads/application/works/work_0_app_4_1761567551_53f23008419f1138.avif', '2025-10-27 12:19:11'),
(25, 4, 'uploads/application/works/work_1_app_4_1761567551_9fc78a0ecd3ba7d5.avif', '2025-10-27 12:19:11'),
(26, 4, 'uploads/application/works/work_2_app_4_1761567551_d7b08d2c098a6222.avif', '2025-10-27 12:19:11'),
(27, 4, 'uploads/application/works/work_3_app_4_1761567551_89f6d3ebd1e10b6e.avif', '2025-10-27 12:19:11'),
(28, 4, 'uploads/application/works/work_4_app_4_1761567551_c2927450ac3eb26a.avif', '2025-10-27 12:19:11'),
(29, 4, 'uploads/application/works/work_5_app_4_1761567551_d983a5bea59aa81e.avif', '2025-10-27 12:19:11'),
(30, 5, 'uploads/application/works/work_0_app_5_1761567797_c666d825b00a6312.avif', '2025-10-27 12:23:17'),
(31, 5, 'uploads/application/works/work_1_app_5_1761567797_994e65476da1ce67.avif', '2025-10-27 12:23:17'),
(32, 5, 'uploads/application/works/work_2_app_5_1761567797_d7c70aae141a848b.avif', '2025-10-27 12:23:17'),
(33, 5, 'uploads/application/works/work_3_app_5_1761567797_7ff421d285d5c6a1.avif', '2025-10-27 12:23:17'),
(34, 5, 'uploads/application/works/work_4_app_5_1761567797_f26df7d3c9ae96b0.avif', '2025-10-27 12:23:17'),
(35, 5, 'uploads/application/works/work_5_app_5_1761567797_f3c4441ad915f8d9.avif', '2025-10-27 12:23:17'),
(36, 6, 'uploads/application/works/work_0_app_6_1761567919_a3c676d6d7f5fde6.avif', '2025-10-27 12:25:19'),
(37, 6, 'uploads/application/works/work_1_app_6_1761567919_53ac42556ed8d50b.avif', '2025-10-27 12:25:19'),
(38, 6, 'uploads/application/works/work_2_app_6_1761567919_d61e82c43bf80135.avif', '2025-10-27 12:25:19'),
(39, 6, 'uploads/application/works/work_3_app_6_1761567919_a331391476fc8284.avif', '2025-10-27 12:25:19'),
(40, 6, 'uploads/application/works/work_4_app_6_1761567919_eb288333e3963c6a.avif', '2025-10-27 12:25:19'),
(41, 6, 'uploads/application/works/work_5_app_6_1761567919_c18d8426c2ca4493.avif', '2025-10-27 12:25:19'),
(42, 6, 'uploads/application/works/work_6_app_6_1761567919_9a5ca0a159f45ab6.avif', '2025-10-27 12:25:19'),
(43, 6, 'uploads/application/works/work_7_app_6_1761567919_c91bee4536538734.avif', '2025-10-27 12:25:19'),
(44, 7, 'uploads/application/works/work_0_app_7_1761569716_06984ab5c3b2a654.avif', '2025-10-27 12:55:16'),
(45, 7, 'uploads/application/works/work_1_app_7_1761569716_faf913c50cb8bee9.avif', '2025-10-27 12:55:16'),
(46, 7, 'uploads/application/works/work_2_app_7_1761569716_56fcd13e464ae3e6.avif', '2025-10-27 12:55:16'),
(47, 7, 'uploads/application/works/work_3_app_7_1761569716_3e4cd20eb9790c21.avif', '2025-10-27 12:55:16'),
(48, 7, 'uploads/application/works/work_4_app_7_1761569716_f753bbf35d344477.avif', '2025-10-27 12:55:16'),
(49, 7, 'uploads/application/works/work_5_app_7_1761569716_ec3b70ac46fb327e.avif', '2025-10-27 12:55:16'),
(50, 7, 'uploads/application/works/work_6_app_7_1761569716_97fe1aaa6cd9e8c4.avif', '2025-10-27 12:55:16'),
(51, 8, 'uploads/application/works/work_0_app_8_1761570051_2b65c6bfe8e8eef9.avif', '2025-10-27 13:00:51'),
(52, 8, 'uploads/application/works/work_1_app_8_1761570051_837ba92f7474b6b6.avif', '2025-10-27 13:00:51'),
(53, 8, 'uploads/application/works/work_2_app_8_1761570051_671ec4684d37a672.avif', '2025-10-27 13:00:51'),
(54, 8, 'uploads/application/works/work_3_app_8_1761570051_6c33af1ffe05077f.avif', '2025-10-27 13:00:51'),
(55, 8, 'uploads/application/works/work_4_app_8_1761570051_6cb7541dd2f5cbec.avif', '2025-10-27 13:00:51'),
(56, 8, 'uploads/application/works/work_5_app_8_1761570051_ee1107e1cfa6b423.avif', '2025-10-27 13:00:51'),
(57, 9, 'uploads/application/works/work_0_app_9_1761570342_5077bb334538a834.avif', '2025-10-27 13:05:42'),
(58, 9, 'uploads/application/works/work_1_app_9_1761570342_a1e3ff2c3c02c7d3.avif', '2025-10-27 13:05:42'),
(59, 9, 'uploads/application/works/work_2_app_9_1761570342_4a07083e8f3148cd.avif', '2025-10-27 13:05:42'),
(60, 9, 'uploads/application/works/work_3_app_9_1761570342_0834d13b19af569a.avif', '2025-10-27 13:05:42'),
(61, 9, 'uploads/application/works/work_4_app_9_1761570342_9db1ffb3b6ea9ff4.avif', '2025-10-27 13:05:42'),
(62, 9, 'uploads/application/works/work_5_app_9_1761570342_3c903b48cc69f6dd.avif', '2025-10-27 13:05:42'),
(63, 10, 'uploads/application/works/work_0_app_10_1761573925_4243870bd703e7c2.avif', '2025-10-27 14:05:25'),
(64, 10, 'uploads/application/works/work_1_app_10_1761573925_5b9c15b6c93a91e2.avif', '2025-10-27 14:05:25'),
(65, 10, 'uploads/application/works/work_2_app_10_1761573925_ef490686ccbfb9b3.avif', '2025-10-27 14:05:25'),
(66, 10, 'uploads/application/works/work_3_app_10_1761573925_b7706c03533da434.avif', '2025-10-27 14:05:25'),
(67, 10, 'uploads/application/works/work_4_app_10_1761573925_26dfa68eecf35a11.avif', '2025-10-27 14:05:25'),
(68, 10, 'uploads/application/works/work_5_app_10_1761573925_4af3e3c16aa5db31.avif', '2025-10-27 14:05:25'),
(69, 11, 'uploads/application/works/work_0_app_11_1761574028_3d7399288a2b1692.avif', '2025-10-27 14:07:08'),
(70, 11, 'uploads/application/works/work_1_app_11_1761574028_1e9457592421ec32.avif', '2025-10-27 14:07:08'),
(71, 11, 'uploads/application/works/work_2_app_11_1761574028_c535e5fdffb4454d.avif', '2025-10-27 14:07:08'),
(72, 11, 'uploads/application/works/work_3_app_11_1761574028_d3fa0dcc9f6c9fea.avif', '2025-10-27 14:07:08'),
(73, 11, 'uploads/application/works/work_4_app_11_1761574028_772d303d8773b5c9.avif', '2025-10-27 14:07:08'),
(74, 12, 'uploads/application/works/work_0_app_12_1761574260_168d661e77db2bb3.avif', '2025-10-27 14:11:00'),
(75, 12, 'uploads/application/works/work_1_app_12_1761574260_879c4a0fa3db3795.avif', '2025-10-27 14:11:00'),
(76, 12, 'uploads/application/works/work_2_app_12_1761574260_474b799b4f161d6a.avif', '2025-10-27 14:11:00'),
(77, 12, 'uploads/application/works/work_3_app_12_1761574260_dcac47a183e7ce42.avif', '2025-10-27 14:11:00'),
(78, 12, 'uploads/application/works/work_4_app_12_1761574260_397b7f09589a14b1.avif', '2025-10-27 14:11:00');

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
(1, 1, 1, 'ai', '', '2025-10-27 14:14:51', '2025-10-27 14:14:51');

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
(1, 1, 0, '', 'Hi! I\'m here to help you book a photographer. 📸\n\nWhat type of event are you planning?\n(e.g., Wedding, Birthday, Portrait, Corporate)', 0, '2025-10-27 14:14:51'),
(2, 1, 1, 'user', 'Wedding', 0, '2025-10-27 14:15:09'),
(3, 1, 0, '', 'Great! When is your **Wedding** scheduled?\n(e.g., December 25, 2024 or 12/25/2024)', 0, '2025-10-27 14:15:09'),
(4, 1, 1, 'user', 'December 28 2025', 0, '2025-10-27 14:15:19'),
(5, 1, 0, '', 'Perfect! Where will the event take place?\n(e.g., Manila, Quezon City, or specific venue)', 0, '2025-10-27 14:15:19'),
(6, 1, 1, 'user', 'Dagupan City, Pangasinan', 0, '2025-10-27 14:15:30'),
(7, 1, 0, '', 'Almost done! What\'s your budget for photography?\n(e.g., 10000 or 15000)', 0, '2025-10-27 14:15:30'),
(8, 1, 1, 'user', '10000', 0, '2025-10-27 14:19:29'),
(9, 1, 0, '', 'Great! I have all the details:\n\n📸 Event: Wedding\n📅 Date: December 28, 2025\n📍 Location: Dagupan City, Pangasinan\n💰 Budget: ₱10,000.00\n\nUnfortunately, there are no available packages at the moment. Would you like to speak with the photographer directly?', 0, '2025-10-27 14:19:29'),
(10, 1, 1, 'user', 'Sure', 0, '2025-10-27 14:19:34'),
(11, 1, 0, '', 'Great! I have all the details:\n\n📸 Event: Wedding\n📅 Date: December 28, 2025\n📍 Location: Dagupan City, Pangasinan\n💰 Budget: ₱10,000.00\n\nUnfortunately, there are no available packages at the moment. Would you like to speak with the photographer directly?', 0, '2025-10-27 14:19:34'),
(12, 1, 1, 'worker', 'Hello?', 0, '2025-10-27 14:46:19');

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
(1, 3, 'aaa', 'aaa', 1.00, 1, 1, 1, 'active', '2025-10-27 15:14:19', '2025-10-27 15:14:19');

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
(1, 'Cabubas', 'Christian Joseph', 'Caguiat', 'cjplaysgames83@gmail.com', '09193695376', '$2y$10$/BRthBHQgQFQqEL/mMF5JOilbtlpKeKkyrkYHAnj4tde1b9qmjuv.', '231, Tapuac District, Dagupan City, Pangasinan', NULL, '2025-10-26 15:59:38'),
(2, 'Ari', 'Lester', '', 'lesterari@outlook.com', '09991234343', '$2y$10$6LHYPqglERjRqggtZz7S0uJB4u0XZVl9Xk0ZIlE05rt0IHrbhMShm', '445, Las Vegas Street, Binmaley, Pangasinan', NULL, '2025-10-27 09:51:31'),
(3, 'Balonzo', 'Mildred', 'Torio', 'balonzomildred@gmail.com', '09291113534', '$2y$10$iRrdRV2pRDgwx8hemgU4K.V5CRHmZrSxxMDypooHH9F7/p2nVWt02', '424 Fiesta Communities, Mexico, Pampanga', NULL, '2025-10-27 09:55:02'),
(4, 'Morales', 'Antonio', 'Villanueva', 'antonio.morales@live.com', '09371234567', '$2y$10$y4Nch5rbjrE2Ha3nkz0uVuSEmdCvg2BLTbAvDbowjVVtRaMjKxnbi', '7816 Batangas St., Barangay San Isidro, Batangas City, Batangas', NULL, '2025-10-27 10:17:48'),
(5, 'Vargas', 'Clara', 'Cruz', 'clara.vargas@rocketmail.com', '09241234567', '$2y$10$aIBp84z1G13B5lczGdJxEeiYb0166GNbK6pTzjwl2ghWgQAKgPs1a', '4532 Pine Ave., Barangay Langtang, Tarlac City, Tarlac', NULL, '2025-10-27 10:18:26'),
(6, 'Delos Santos', 'Roberto', 'Castillo', 'roberto.delossantos@icloud.com', '09152345678', '$2y$10$vWjJbv0G7YY0am1qILYD9eOAecWslLKD0JHWUidW7RM0U2kQpV5ke', '8901 Pineapple St., Barangay Gubat, Legazpi City, Albay', NULL, '2025-10-27 10:19:04'),
(7, 'Fernandez', 'Edgar', 'Reyes', 'edgar.fernandez@zoho.com', '09161123456', '$2y$10$eZ094N454UBMQbGqpc0/ceMopqdAsCR2.qHWr6sJXZ0QYo.LufsAC', '2347 Mangga St., Barangay Poblacion, Davao City, Davao del Sur', NULL, '2025-10-27 10:19:47'),
(8, 'Esteban', 'Julia', 'Solis', 'julia.esteban@gmail.com', '09251823456', '$2y$10$P7Fp8rJxLBpyRAorK75Xuulx73L0NFuuuVmP0y.0aEC8P1gw4.NL2', '5432 Sampaguita Rd., Barangay Kauswagan, Cagayan de Oro, Misamis Oriental', NULL, '2025-10-27 10:20:40'),
(9, 'Bautista', 'Raul', 'Perez', 'raul.bautista@hotmail.com', '09231123456', '$2y$10$055.5M8toGGANGNHkjYqiO21h5tT7ILJwrPpcl4jkdFebhYDjTrNi', '1134 Rosas Ave., Barangay Panacan, Davao City, Davao del Sur', NULL, '2025-10-27 10:21:29'),
(10, 'Alonzo', 'Hazel', 'Martinez', 'hazel.alonzo@aol.com', '09191456789', '$2y$10$4J0jTrEMtj5Z.pRunpT6cO0uPd5TbHlNn/om7i6SPPGAbCDgJpAmK', '8973 Zinnia St., Barangay Banilad, Cebu City, Cebu', NULL, '2025-10-27 10:22:03'),
(11, 'Cruz', 'Isabel', 'Del Rosario', 'isabel.cruz@gmail.com', '09361122334', '$2y$10$rZPRXnnHh8Ju0NrwzfQhge80sbZUgBf/QeD8wjv5ALjZNS7g9.XZW', '6785 Jasmine St., Barangay Talamban, Cebu City, Cebu', NULL, '2025-10-27 10:22:45');

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
(1, 11, 'active', 'Reyes', 'Carlo', 'Alonzo', 'carlo.reyes@gmail.com', '09281234565', '$2y$10$7oefTFOYDubZ./S5lALSLueTYTt9jTocYMYL0sj/YgVt/b0kHdrIS', '88 Bago Blvd., Brgy. Banilad, Cebu City, Cebu', 'creative', 12, 'I am a creative/conceptual artist', NULL, 0.00, 0, 0, 0.00, '2025-10-27 14:12:46', '2025-10-27 14:35:31', 0.00),
(2, 12, 'active', 'Gonzaga', 'Melinda', 'Santos', 'melinda.gonzaga@mail.com', '09371234566', '$2y$10$F2hpK8VIR.jlGuzH/k8sbO7/PDwAyR.X2H6JtmqmVAj3Oj.qyzfHa', '14 Lapu-Lapu St., Brgy. Durano, Mandaue City, Cebu', '', 0, NULL, NULL, 0.00, 0, 0, 0.00, '2025-10-27 14:12:53', '2025-10-27 14:12:53', 0.00),
(3, 10, 'active', 'Ortega', 'Nelia', 'Ramos', 'nelia.ortega@zoho.com', '09171234564', '$2y$10$H/J3coqb7KKdjZ4UwmDBQu5XNfkPgW0fuJUwLq4mRy0ZzLsTd8kY6', '56 Sampaguita Ln., Brgy. Divisoria, Tarlac City, Tarlac', 'event', 0, 'AAA', NULL, 0.00, 0, 0, 0.00, '2025-10-27 14:12:57', '2025-10-27 15:14:19', 0.00),
(4, 9, 'active', 'Santos', 'Marco', 'Perez', 'marco.santos@ymail.com', '09391234563', '$2y$10$StezBCsrjo/f1dF4iiaXGO8Aam665PxmSXyeVtYVXSD2T24lBBSpq', '150 Bamboo St., Brgy. San Vicente, Puerto Princesa, Palawan', '', 0, NULL, NULL, 0.00, 0, 0, 0.00, '2025-10-27 14:13:01', '2025-10-27 14:13:01', 0.00),
(5, 8, 'active', 'Aquino', 'Liza', 'dela Cruz', 'liza.aquino@icloud.com', '09291234562', '$2y$10$7ogiDcSrkZQ.gseekxlYFu7KGu3nQ1PJFzR48VVTJUZxOUAKxzTjS', '7 Coconut Rd., Brgy. San Antonio, Laoag City, Ilocos Norte', '', 0, NULL, NULL, 0.00, 0, 0, 0.00, '2025-10-27 14:13:19', '2025-10-27 14:13:19', 0.00),
(6, 7, 'active', 'Pineda', 'Arnel', 'Bautista', 'arnel.pineda@yahoo.com', '09191234561', '$2y$10$wVYNjkkONA69HvN33Qpc/ePovUvcsU/hEXevW7RGd4vMVKEXDYBXy', '33 Acacia Dr., Brgy. San Roque, Naga City, Camarines Sur', '', 0, NULL, NULL, 0.00, 0, 0, 0.00, '2025-10-27 14:13:24', '2025-10-27 14:13:24', 0.00);

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
(1, 1, 'uploads/workers/1/worker1_work1.avif', '2025-10-27 14:12:46'),
(2, 1, 'uploads/workers/1/worker1_work2.avif', '2025-10-27 14:12:46'),
(3, 1, 'uploads/workers/1/worker1_work3.avif', '2025-10-27 14:12:46'),
(4, 1, 'uploads/workers/1/worker1_work4.avif', '2025-10-27 14:12:46'),
(5, 1, 'uploads/workers/1/worker1_work5.avif', '2025-10-27 14:12:46'),
(6, 2, 'uploads/workers/2/worker2_work1.avif', '2025-10-27 14:12:53'),
(7, 2, 'uploads/workers/2/worker2_work2.avif', '2025-10-27 14:12:53'),
(8, 2, 'uploads/workers/2/worker2_work3.avif', '2025-10-27 14:12:53'),
(9, 2, 'uploads/workers/2/worker2_work4.avif', '2025-10-27 14:12:53'),
(10, 2, 'uploads/workers/2/worker2_work5.avif', '2025-10-27 14:12:53'),
(11, 3, 'uploads/workers/3/worker3_work1.avif', '2025-10-27 14:12:57'),
(12, 3, 'uploads/workers/3/worker3_work2.avif', '2025-10-27 14:12:57'),
(13, 3, 'uploads/workers/3/worker3_work3.avif', '2025-10-27 14:12:57'),
(14, 3, 'uploads/workers/3/worker3_work4.avif', '2025-10-27 14:12:57'),
(15, 3, 'uploads/workers/3/worker3_work5.avif', '2025-10-27 14:12:57'),
(16, 3, 'uploads/workers/3/worker3_work6.avif', '2025-10-27 14:12:57'),
(17, 4, 'uploads/workers/4/worker4_work1.avif', '2025-10-27 14:13:01'),
(18, 4, 'uploads/workers/4/worker4_work2.avif', '2025-10-27 14:13:01'),
(19, 4, 'uploads/workers/4/worker4_work3.avif', '2025-10-27 14:13:01'),
(20, 4, 'uploads/workers/4/worker4_work4.avif', '2025-10-27 14:13:01'),
(21, 4, 'uploads/workers/4/worker4_work5.avif', '2025-10-27 14:13:01'),
(22, 4, 'uploads/workers/4/worker4_work6.avif', '2025-10-27 14:13:01'),
(23, 5, 'uploads/workers/5/worker5_work1.avif', '2025-10-27 14:13:19'),
(24, 5, 'uploads/workers/5/worker5_work2.avif', '2025-10-27 14:13:19'),
(25, 5, 'uploads/workers/5/worker5_work3.avif', '2025-10-27 14:13:19'),
(26, 5, 'uploads/workers/5/worker5_work4.avif', '2025-10-27 14:13:19'),
(27, 5, 'uploads/workers/5/worker5_work5.avif', '2025-10-27 14:13:19'),
(28, 5, 'uploads/workers/5/worker5_work6.avif', '2025-10-27 14:13:19'),
(29, 6, 'uploads/workers/6/worker6_work1.avif', '2025-10-27 14:13:24'),
(30, 6, 'uploads/workers/6/worker6_work2.avif', '2025-10-27 14:13:24'),
(31, 6, 'uploads/workers/6/worker6_work3.avif', '2025-10-27 14:13:24'),
(32, 6, 'uploads/workers/6/worker6_work4.avif', '2025-10-27 14:13:24'),
(33, 6, 'uploads/workers/6/worker6_work5.avif', '2025-10-27 14:13:24'),
(34, 6, 'uploads/workers/6/worker6_work6.avif', '2025-10-27 14:13:24'),
(35, 6, 'uploads/workers/6/worker6_work7.avif', '2025-10-27 14:13:24');

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
  MODIFY `temp_booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `application`
--
ALTER TABLE `application`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `application_resume`
--
ALTER TABLE `application_resume`
  MODIFY `resume_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `application_works`
--
ALTER TABLE `application_works`
  MODIFY `work_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `booking_modifications`
--
ALTER TABLE `booking_modifications`
  MODIFY `modification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `workers`
--
ALTER TABLE `workers`
  MODIFY `worker_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  MODIFY `work_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

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
