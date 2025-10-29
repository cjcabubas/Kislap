-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2025 at 12:58 PM
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
(1, 'admin', 'Administrator', 'System', NULL, '$2y$10$WaDhSPKA4EUkRN1kN1od/ONlIHMLNkAmgrPnL8QnG.JUiGShelHp6', '2025-10-22 12:04:45'),
(2, 'cjcabubas', 'Cabubas', 'Cj', '', '$2y$10$/f8Ppl3MDVIYnWucqasTIu/5i.YSCdf6vXbC9jkYLhumXJmE4NBZS', '2025-10-29 07:56:45');

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
  `final_price` decimal(10,2) DEFAULT NULL,
  `worker_proposed_date` date DEFAULT NULL,
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
(1, 'Navarro', 'Miguel', 'Santos', 'miguel.navarro@gmail.com', '09171234567', '$2y$10$WfQwbX19RhDSNmUxV0FIKeFkNUJs6iy9berhaoGS5vvqBGlpNO5eO', '12 Rizal St., Brgy. San Jose, Lucena City, Quezon', 'accepted', NULL, '2025-10-27 12:10:20', '2025-10-27 17:51:35'),
(2, 'Flores', 'Anna', NULL, 'anna.flores@yahoo.com', '09281234678', '$2y$10$yM7CO8CRnXz0dw3tvXp7xufRNsxvDw17DxbcjGUePXEDymaWvj2EC', '45 Mabini Ave., Brgy. Poblacion, Iloilo City, Iloilo', 'accepted', NULL, '2025-10-27 12:14:33', '2025-10-27 17:51:32'),
(3, 'Cruz', 'Diego', 'Ramos', 'diego.cruz@outlook.com', '09391234789', '$2y$10$OWgqbgTuqN.LqPTb7Y3zlu3PvSuEGeJ.zGCi5Ov4NyBUvauyb0z5.', '78 Magsaysay Rd., Brgy. Centro, Tagbilaran, Bohol', 'accepted', NULL, '2025-10-27 12:16:16', '2025-10-27 17:51:28'),
(4, 'Mercado', 'Beatrice', 'Gomez', 'bea.mercado@mail.com', '09181234890', '$2y$10$rsJIq5UikdexzEnCtEVfJOj2w0lH0czsaVk06Khaomm.djsKziDX.', '101 San Miguel St., Brgy. Burgos, Batangas City, Batangas', 'accepted', NULL, '2025-10-27 12:19:11', '2025-10-27 17:51:24'),
(5, 'Ilagan', 'Rafael', 'Torres', 'rafael.ilagan@protonmail.com', '09271234901', '$2y$10$G.87VjREljAzBMX5zVaV3u8GVbPv0pEQJOeeZ1FxP82Gnk0DP0E1W', '9 Laurel Lane, Brgy. San Isidro, Cagayan de Oro, Misamis Oriental', 'accepted', NULL, '2025-10-27 12:23:17', '2025-10-27 17:51:19'),
(6, 'Valdez', 'Camille', NULL, 'camille.valdez@gmail.com', '09371234560', '$2y$10$SWMs88YwEDYF3heLzEI0AOID21d0qcZnRs/2u4HgOhcc2bjtXSEsO', '222 Mango St., Brgy. Pasonanca, Zamboanga City, Zamboanga del Sur', 'accepted', NULL, '2025-10-27 12:25:19', '2025-10-27 17:51:13'),
(7, 'Pineda', 'Arnel', 'Bautista', 'arnel.pineda@yahoo.com', '09191234561', '$2y$10$wVYNjkkONA69HvN33Qpc/ePovUvcsU/hEXevW7RGd4vMVKEXDYBXy', '33 Acacia Dr., Brgy. San Roque, Naga City, Camarines Sur', 'accepted', NULL, '2025-10-27 12:55:16', '2025-10-27 14:13:24'),
(8, 'Aquino', 'Liza', 'dela Cruz', 'liza.aquino@icloud.com', '09291234562', '$2y$10$7ogiDcSrkZQ.gseekxlYFu7KGu3nQ1PJFzR48VVTJUZxOUAKxzTjS', '7 Coconut Rd., Brgy. San Antonio, Laoag City, Ilocos Norte', 'accepted', NULL, '2025-10-27 13:00:51', '2025-10-27 14:13:19'),
(9, 'Santos', 'Marco', 'Perez', 'marco.santos@ymail.com', '09391234563', '$2y$10$StezBCsrjo/f1dF4iiaXGO8Aam665PxmSXyeVtYVXSD2T24lBBSpq', '150 Bamboo St., Brgy. San Vicente, Puerto Princesa, Palawan', 'accepted', NULL, '2025-10-27 13:05:42', '2025-10-27 14:13:01'),
(10, 'Ortega', 'Nelia', 'Ramos', 'nelia.ortega@zoho.com', '09171234564', '$2y$10$H/J3coqb7KKdjZ4UwmDBQu5XNfkPgW0fuJUwLq4mRy0ZzLsTd8kY6', '56 Sampaguita Ln., Brgy. Divisoria, Tarlac City, Tarlac', 'accepted', NULL, '2025-10-27 14:05:25', '2025-10-27 14:12:57'),
(11, 'Reyes', 'Carlo', 'Alonzo', 'carlo.reyes@gmail.com', '09281234565', '$2y$10$7oefTFOYDubZ./S5lALSLueTYTt9jTocYMYL0sj/YgVt/b0kHdrIS', '88 Bago Blvd., Brgy. Banilad, Cebu City, Cebu', 'accepted', NULL, '2025-10-27 14:07:08', '2025-10-27 14:12:46'),
(12, 'Gonzaga', 'Melinda', 'Santos', 'melinda.gonzaga@mail.com', '09371234566', '$2y$10$F2hpK8VIR.jlGuzH/k8sbO7/PDwAyR.X2H6JtmqmVAj3Oj.qyzfHa', '14 Lapu-Lapu St., Brgy. Durano, Mandaue City, Cebu', 'accepted', NULL, '2025-10-27 14:11:00', '2025-10-27 14:12:53'),
(13, 'Herrera', 'Jason', 'Villanueva', 'j.herrera@outlook.com', '09191234567', '$2y$10$p.9qOxMe6pxhjQbi6k8aJOnlATnhs9oMKNgZs0aHtk.HJrZEMezk.', '300 Pine Rd., Brgy. Malvar, Angeles City, Pampanga', 'accepted', NULL, '2025-10-28 10:21:29', '2025-10-28 10:38:46'),
(14, 'Tan', 'Elaine', 'Yu', 'elaine.tan@gmail.com', '09291234568', '$2y$10$3YtzPYMiUS7asEXy6A.OK.cehDvTc3/RGOEe0BOYe91Xl/QqjMHIC', '19 Orchid Way, Brgy. Bagong Silang, Baguio City, Benguet', 'accepted', NULL, '2025-10-28 10:31:48', '2025-10-28 10:38:33'),
(15, 'De Guzman', 'Victor', 'Navarro', 'victor.dguzman@protonmail.com', '09391234569', '$2y$10$Iz8szcEQujNaHbO98mv3WukiI5Xxiq9kCKU1miKn7VtZSevomqfCO', '77 Sampaloc St., Brgy. Divisoria, Tarlac City, Tarlac', 'accepted', NULL, '2025-10-28 10:37:00', '2025-10-28 10:38:28'),
(16, 'Manalo', 'Rica', 'Santos', 'rica.manalo@yahoo.com', '09171234570', '$2y$10$K0euKLQpferIzA3REa27buMkszXiH9uxBxq87trzQCOF1g1RELwUW', '5 Palm Grove, Brgy. Poblacion, Roxas City, Capiz', 'accepted', NULL, '2025-10-28 10:42:27', '2025-10-28 10:49:52'),
(17, 'Fabillar', 'Jonah', 'Cruz', 'jonah.fabillar@gmail.com', '09281234571', '$2y$10$FYgbNr3.CCeodrcHctf6z.jwCF7DesRiJhAoy5kgMgfpFmYdk7TYC', '402 Pearl St., Brgy. Baybay, Legazpi City, Albay', 'accepted', NULL, '2025-10-28 10:44:40', '2025-10-28 10:49:49'),
(18, 'Catindig', 'Maribel', 'Aquino', 'maribel.catindig@aol.com', '09371234572', '$2y$10$YGcmmzn4pzHFi16ZETaTBeyQm5FVD34C.Rlz6UOFlsFhW0ylsaBgO', '21 Tulip Ln., Brgy. San Jose, Lucena City, Quezon', 'accepted', NULL, '2025-10-28 10:46:52', '2025-10-28 10:49:41'),
(19, 'Lozano', 'Edwin', 'Ramos', 'edwin.lozano@mail.com', '09191234573', '$2y$10$8nxwDLsbCKq.KT6WJrpSje4zg.eUFvUJf720QGMsntV..HZglDOBa', '9 Camia St., Brgy. San Miguel, Dumaguete City, Negros Oriental', 'accepted', NULL, '2025-10-28 10:49:22', '2025-10-28 10:49:38'),
(20, 'Salazar', 'Faye', 'Navarro', 'faye.salazar@outlook.com', '09291234574', '$2y$10$f/ooOupmflo4slCzns.YA.NTMKLukuuxBEzuOQoyB8HlFQvGonI/.', '9 Camia St., Brgy. San Miguel, Dumaguete City, Negros Oriental', 'accepted', NULL, '2025-10-28 10:54:53', '2025-10-28 10:55:12'),
(21, 'Pascual', 'Ruben', 'Morales', 'ruben.pascual@gmail.com', '09391234575', '$2y$10$HeAUVXpbC3BgA6iwrD0bIeZLO0Wawdr.fYRyjvjttcSGz8Ha8zMFe', '150 Maharlika Rd., Brgy. Poblacion, Butuan City, Agusan del Norte', 'pending', NULL, '2025-10-28 11:19:00', '2025-10-28 11:19:00'),
(23, 'Quiroz', 'Kelvin', 'Lacerna', 'kelvin.quiroz@gmail.com', '09281234577', '$2y$10$uaUtguYXixLSiM3yw3Ac.On3EegSpGqnS5kT1Ownma4f5CoBoRasi', '310 Baybayon St., Brgy. Poblacion, Legazpi City, Albay', 'pending', NULL, '2025-10-28 11:23:09', '2025-10-28 11:23:09'),
(24, 'Borja', 'Ma Teresa', 'Salonga', 'teresa.borja@zoho.com', '09371234578', '$2y$10$UfigzXtTPBOfqnsejSG/x.aakgXSoc4tNa7JIh19ZCYjsT2aNqxPO', '4 Sampaguita Rd., Brgy. Centro, Tuguegarao City, Cagayan', 'pending', NULL, '2025-10-28 11:27:35', '2025-10-28 11:27:35'),
(25, 'Lomboy', 'Arlene', 'Quinto', 'arlene.lomboy@icloud.com', '09191234579', '$2y$10$W11miF/ThJTH86LBkZwHOeVW00neeyVllxTmH7GnXrY3/nIuxTl1a', '88 Coconut Ln., Brgy. Baybay, Calbayog City, Samar', 'accepted', NULL, '2025-10-28 11:28:50', '2025-10-29 01:33:34'),
(26, 'Dela Cruz', 'Luis', 'Mendoza', 'luis.delacruz@gmail.com', '09281234580', '$2y$10$Gq2JDdYBZkA9TKH/JeGZOePfPc3xe4udK71E8OHBcW8qQudPSFBeO', '234 Palmera St., Brgy. Victoria, San Fernando, Pampanga', 'pending', NULL, '2025-10-28 11:29:43', '2025-10-29 06:09:20'),
(27, 'Torres', 'Hannah', 'Perez', 'hannah.torres@outlook.com', '09371234581', '$2y$10$VUzS6HoYyvZAAnl8ll.yAOVIlHHqvyEfcdZqnv1yQiad3vNLI82W6', '57 Mango Blvd., Brgy. Panghulo, Malolos City, Bulacan', 'pending', NULL, '2025-10-28 11:32:43', '2025-10-29 06:09:05'),
(28, 'Sison', 'Jerome', 'Reyes', 'jerome.sison@aol.com', '09171234582', '$2y$10$jd8KjIMlSZWLtMu7bDArfemy/xl4mmxTJZ/P.qlecEa9Nxl4Gg2hW', '400 Maharlika Rd., Brgy. San Juan, Laoag City, Ilocos Norte', 'pending', NULL, '2025-10-28 11:40:45', '2025-10-29 06:08:14'),
(29, 'Aguilar', 'Rafael', 'Salcedo', 'rafael.aguilar@gmail.com', '09291234583', '$2y$10$j4e3ANeharlTRpKezc.IW.B/DIAp4G7bPo5anGP7eOhFocN4MfxTq', '182 Laurel St., Brgy. San Isidro, Batangas City, Batangas', 'rejected', 'Unprofessional presentation\n\nAdditional Notes: quality not up to platform standards', '2025-10-28 11:42:31', '2025-10-29 08:09:54'),
(30, 'Delos Reyes', 'Cynthia', 'Aquino', 'cynthia.delosreyes@zoho.com', '09391234584', '$2y$10$4C6q7H5G2hfIeuEPZu/L6.Jhlg.hGH5PcqyHgwRzlzEkZ22GBFbgy', '118 Palm St., Brgy. Bagumbayan, Davao City, Davao del Sur', 'accepted', NULL, '2025-10-28 11:45:12', '2025-10-29 08:05:27'),
(31, 'Cruz', 'Marlon', 'Gomez', 'marlon.cruz@gmail.com', '09181234585', '$2y$10$qHOcaxQ4axoxD7H29OvgL.UpjiOqh9cDSKxjPOdoCtKhtxxztI5/C', '123 Juniper St., Brgy. Malanday, Valenzuela City, Metro Manila', 'accepted', NULL, '2025-10-28 11:59:12', '2025-10-28 12:01:03'),
(32, 'Alano', 'Kim', NULL, 'miko@gmail.com', '09123467891', '$2y$10$3OfUBloF4kKkonkLPS70ieHMXV8P4t0rbZ83GcyviA8L08k5yc9vG', '123 Malued, Mindanao', 'accepted', NULL, '2025-10-29 01:14:56', '2025-10-29 01:34:38');

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
(12, 12, 'uploads/application/resumes/resume_app_12_1761574260_7f09569f0ef3f0af.pdf', '2025-10-27 14:11:00'),
(13, 13, 'uploads/application/resumes/resume_app_13_1761646889_8d8e1efbd866b3cf.pdf', '2025-10-28 10:21:29'),
(14, 14, 'uploads/application/resumes/resume_app_14_1761647508_3e4c839a45a2f66c.pdf', '2025-10-28 10:31:48'),
(15, 15, 'uploads/application/resumes/resume_app_15_1761647820_7c48672681217fca.pdf', '2025-10-28 10:37:00'),
(16, 16, 'uploads/application/resumes/resume_app_16_1761648147_e3999c3da618993e.pdf', '2025-10-28 10:42:27'),
(17, 17, 'uploads/application/resumes/resume_app_17_1761648280_b4cd74497e5a8111.pdf', '2025-10-28 10:44:40'),
(18, 18, 'uploads/application/resumes/resume_app_18_1761648412_641523923c333fbc.pdf', '2025-10-28 10:46:52'),
(19, 19, 'uploads/application/resumes/resume_app_19_1761648562_c87725bfa5c2a27c.pdf', '2025-10-28 10:49:22'),
(20, 20, 'uploads/application/resumes/resume_app_20_1761648893_f31eeec81c684c2a.pdf', '2025-10-28 10:54:53'),
(21, 21, 'uploads/application/resumes/resume_app_21_1761650340_93390ede40bddead.pdf', '2025-10-28 11:19:00'),
(23, 23, 'uploads/application/resumes/resume_app_23_1761650589_04b1ecb0ec9addc0.pdf', '2025-10-28 11:23:09'),
(24, 24, 'uploads/application/resumes/resume_app_24_1761650855_a7284ba45e4ea599.pdf', '2025-10-28 11:27:35'),
(25, 25, 'uploads/application/resumes/resume_app_25_1761650930_10b4390dfbef49a8.pdf', '2025-10-28 11:28:50'),
(26, 26, 'uploads/application/resumes/resume_app_26_1761650983_11e3dbe922ed7b20.pdf', '2025-10-28 11:29:43'),
(27, 27, 'uploads/application/resumes/resume_app_27_1761651163_e74fc93d4c87a410.pdf', '2025-10-28 11:32:43'),
(28, 28, 'uploads/application/resumes/resume_app_28_1761651645_711d15754b483432.pdf', '2025-10-28 11:40:45'),
(29, 29, 'uploads/application/resumes/resume_app_29_1761651751_84800726922cc463.pdf', '2025-10-28 11:42:31'),
(30, 30, 'uploads/application/resumes/resume_app_30_1761651912_80bf0bf7d439d8d3.pdf', '2025-10-28 11:45:12'),
(31, 31, 'uploads/application/resumes/resume_app_31_1761652752_2add487c447ebfdd.pdf', '2025-10-28 11:59:12'),
(32, 32, 'uploads/application/resumes/resume_app_32_1761700496_7f80351e6af238a6.pdf', '2025-10-29 01:14:56');

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
(78, 12, 'uploads/application/works/work_4_app_12_1761574260_397b7f09589a14b1.avif', '2025-10-27 14:11:00'),
(79, 13, 'uploads/application/works/work_0_app_13_1761646889_002edbc68530ac24.avif', '2025-10-28 10:21:29'),
(80, 13, 'uploads/application/works/work_1_app_13_1761646889_4bc2a8ae4b8cf1b1.avif', '2025-10-28 10:21:29'),
(81, 13, 'uploads/application/works/work_2_app_13_1761646889_e91d2d58e1e3d212.avif', '2025-10-28 10:21:29'),
(82, 13, 'uploads/application/works/work_3_app_13_1761646889_a2a4a2da9efc6328.avif', '2025-10-28 10:21:29'),
(83, 13, 'uploads/application/works/work_4_app_13_1761646889_1093df28ad581dd8.avif', '2025-10-28 10:21:29'),
(84, 14, 'uploads/application/works/work_0_app_14_1761647508_2d983bac7b074743.avif', '2025-10-28 10:31:48'),
(85, 14, 'uploads/application/works/work_1_app_14_1761647508_34c9a3837d325fa9.avif', '2025-10-28 10:31:48'),
(86, 14, 'uploads/application/works/work_2_app_14_1761647508_284f664d55b9b558.avif', '2025-10-28 10:31:48'),
(87, 14, 'uploads/application/works/work_3_app_14_1761647508_a97246bca08251ad.avif', '2025-10-28 10:31:48'),
(88, 14, 'uploads/application/works/work_4_app_14_1761647508_541e7e7c40af63c4.avif', '2025-10-28 10:31:48'),
(89, 15, 'uploads/application/works/work_0_app_15_1761647820_354b671f9809146d.avif', '2025-10-28 10:37:00'),
(90, 15, 'uploads/application/works/work_1_app_15_1761647820_ba6438ba0000c496.avif', '2025-10-28 10:37:00'),
(91, 15, 'uploads/application/works/work_2_app_15_1761647820_03a39f8aa5e7fbcb.avif', '2025-10-28 10:37:00'),
(92, 15, 'uploads/application/works/work_3_app_15_1761647820_5e50d0a191284320.avif', '2025-10-28 10:37:00'),
(93, 15, 'uploads/application/works/work_4_app_15_1761647820_52da7c874689da03.avif', '2025-10-28 10:37:00'),
(94, 15, 'uploads/application/works/work_5_app_15_1761647820_d6609ae317ff39aa.avif', '2025-10-28 10:37:00'),
(95, 16, 'uploads/application/works/work_0_app_16_1761648147_12c27e95b6a0dbaa.avif', '2025-10-28 10:42:27'),
(96, 16, 'uploads/application/works/work_1_app_16_1761648147_526dd6bb5dd36ade.avif', '2025-10-28 10:42:27'),
(97, 16, 'uploads/application/works/work_2_app_16_1761648147_ebabe4ba00d405e7.avif', '2025-10-28 10:42:27'),
(98, 16, 'uploads/application/works/work_3_app_16_1761648147_26ae3e2cae979852.avif', '2025-10-28 10:42:27'),
(99, 16, 'uploads/application/works/work_4_app_16_1761648147_b5a06fa9b7059aa8.avif', '2025-10-28 10:42:27'),
(100, 17, 'uploads/application/works/work_0_app_17_1761648280_28eeded0d684fdb3.avif', '2025-10-28 10:44:40'),
(101, 17, 'uploads/application/works/work_1_app_17_1761648280_7de6d4dcd20bbd9a.avif', '2025-10-28 10:44:40'),
(102, 17, 'uploads/application/works/work_2_app_17_1761648280_50985c6d3ac5535b.avif', '2025-10-28 10:44:40'),
(103, 17, 'uploads/application/works/work_3_app_17_1761648280_177b87744ce25c73.avif', '2025-10-28 10:44:40'),
(104, 17, 'uploads/application/works/work_4_app_17_1761648280_640fd49fbdaa9383.avif', '2025-10-28 10:44:40'),
(105, 17, 'uploads/application/works/work_5_app_17_1761648280_0e432a9374cefc5d.avif', '2025-10-28 10:44:40'),
(106, 18, 'uploads/application/works/work_0_app_18_1761648412_9664b93c6f192be3.avif', '2025-10-28 10:46:52'),
(107, 18, 'uploads/application/works/work_1_app_18_1761648412_2d9f9e7f7fda7f37.avif', '2025-10-28 10:46:52'),
(108, 18, 'uploads/application/works/work_2_app_18_1761648412_3234aa5d14fdd2e0.avif', '2025-10-28 10:46:52'),
(109, 18, 'uploads/application/works/work_3_app_18_1761648412_aaa16c7eb5980494.avif', '2025-10-28 10:46:52'),
(110, 18, 'uploads/application/works/work_4_app_18_1761648412_297f37e9bbeb0aea.avif', '2025-10-28 10:46:52'),
(111, 18, 'uploads/application/works/work_5_app_18_1761648412_70866c4d60d82ada.avif', '2025-10-28 10:46:52'),
(112, 18, 'uploads/application/works/work_6_app_18_1761648412_5196c91d7fc9c2b1.avif', '2025-10-28 10:46:52'),
(113, 19, 'uploads/application/works/work_0_app_19_1761648562_3ede0ed094082fff.avif', '2025-10-28 10:49:22'),
(114, 19, 'uploads/application/works/work_1_app_19_1761648562_4a39d528da05b590.avif', '2025-10-28 10:49:22'),
(115, 19, 'uploads/application/works/work_2_app_19_1761648562_b857bd17f7bea7ad.avif', '2025-10-28 10:49:22'),
(116, 19, 'uploads/application/works/work_3_app_19_1761648562_7f69ae142b6ed661.avif', '2025-10-28 10:49:22'),
(117, 19, 'uploads/application/works/work_4_app_19_1761648562_dbf98111b8a5e19b.avif', '2025-10-28 10:49:22'),
(118, 20, 'uploads/application/works/work_0_app_20_1761648893_e0d4c4a7c48872e3.avif', '2025-10-28 10:54:53'),
(119, 20, 'uploads/application/works/work_1_app_20_1761648893_1853021200a1300c.avif', '2025-10-28 10:54:53'),
(120, 20, 'uploads/application/works/work_2_app_20_1761648893_76dc4f7ffa32c58f.avif', '2025-10-28 10:54:53'),
(121, 20, 'uploads/application/works/work_3_app_20_1761648893_255467d2f7fd9b79.avif', '2025-10-28 10:54:53'),
(122, 20, 'uploads/application/works/work_4_app_20_1761648893_ceaac2e29ef64a68.avif', '2025-10-28 10:54:53'),
(123, 20, 'uploads/application/works/work_5_app_20_1761648893_2cc45b34c6f48848.avif', '2025-10-28 10:54:53'),
(124, 21, 'uploads/application/works/work_0_app_21_1761650340_cd7a3966ab8c765c.avif', '2025-10-28 11:19:00'),
(125, 21, 'uploads/application/works/work_1_app_21_1761650340_449a626ab58844c0.avif', '2025-10-28 11:19:00'),
(126, 21, 'uploads/application/works/work_2_app_21_1761650340_9b38a4c35203f8ac.avif', '2025-10-28 11:19:00'),
(127, 21, 'uploads/application/works/work_3_app_21_1761650340_3dbf8e895ac400fd.avif', '2025-10-28 11:19:00'),
(128, 21, 'uploads/application/works/work_4_app_21_1761650340_4abb2d71a8cace02.avif', '2025-10-28 11:19:00'),
(129, 21, 'uploads/application/works/work_5_app_21_1761650340_56a57d60ec5a67d1.avif', '2025-10-28 11:19:00'),
(135, 23, 'uploads/application/works/work_0_app_23_1761650589_04485559534eb34a.avif', '2025-10-28 11:23:09'),
(136, 23, 'uploads/application/works/work_1_app_23_1761650589_7417582d2f954a1b.avif', '2025-10-28 11:23:09'),
(137, 23, 'uploads/application/works/work_2_app_23_1761650589_f8a000b12d7a5a7d.avif', '2025-10-28 11:23:09'),
(138, 23, 'uploads/application/works/work_3_app_23_1761650589_d5b5c9cdbf7e7514.avif', '2025-10-28 11:23:09'),
(139, 23, 'uploads/application/works/work_4_app_23_1761650589_e1ad8d68627605b5.avif', '2025-10-28 11:23:09'),
(140, 23, 'uploads/application/works/work_5_app_23_1761650589_6c328568cd3a9255.avif', '2025-10-28 11:23:09'),
(141, 23, 'uploads/application/works/work_6_app_23_1761650589_35b6f730869f9221.avif', '2025-10-28 11:23:09'),
(142, 24, 'uploads/application/works/work_0_app_24_1761650855_90012a5cdaf32c0c.avif', '2025-10-28 11:27:35'),
(143, 24, 'uploads/application/works/work_1_app_24_1761650855_9af680816fb479a1.avif', '2025-10-28 11:27:35'),
(144, 24, 'uploads/application/works/work_2_app_24_1761650855_5ce6554fcfffb188.avif', '2025-10-28 11:27:35'),
(145, 24, 'uploads/application/works/work_3_app_24_1761650855_9572bdf648e327bd.avif', '2025-10-28 11:27:35'),
(146, 24, 'uploads/application/works/work_4_app_24_1761650855_14f275968dd4e384.avif', '2025-10-28 11:27:35'),
(147, 24, 'uploads/application/works/work_5_app_24_1761650855_be69f6242cf4ff46.avif', '2025-10-28 11:27:35'),
(148, 25, 'uploads/application/works/work_0_app_25_1761650930_86aa6f22c94bf49a.avif', '2025-10-28 11:28:50'),
(149, 25, 'uploads/application/works/work_1_app_25_1761650930_698f422bfeff9f17.avif', '2025-10-28 11:28:50'),
(150, 25, 'uploads/application/works/work_2_app_25_1761650930_f6a3b42027cc330d.avif', '2025-10-28 11:28:50'),
(151, 25, 'uploads/application/works/work_3_app_25_1761650930_d2826ef5bb253f87.avif', '2025-10-28 11:28:50'),
(152, 25, 'uploads/application/works/work_4_app_25_1761650930_d0c21f9f4d142bf4.avif', '2025-10-28 11:28:50'),
(153, 25, 'uploads/application/works/work_5_app_25_1761650930_2f4d108dbb8ea2cf.avif', '2025-10-28 11:28:50'),
(154, 26, 'uploads/application/works/work_0_app_26_1761650983_0d46bc4aaf6b14ba.avif', '2025-10-28 11:29:43'),
(155, 26, 'uploads/application/works/work_1_app_26_1761650983_bf9780d3e79d981c.avif', '2025-10-28 11:29:43'),
(156, 26, 'uploads/application/works/work_2_app_26_1761650983_991bd24c13404a65.avif', '2025-10-28 11:29:43'),
(157, 26, 'uploads/application/works/work_3_app_26_1761650983_5b917a49a763a1ad.avif', '2025-10-28 11:29:43'),
(158, 26, 'uploads/application/works/work_4_app_26_1761650983_de659648bb2b596b.avif', '2025-10-28 11:29:43'),
(159, 26, 'uploads/application/works/work_5_app_26_1761650983_fb35e1b7dc84b7be.avif', '2025-10-28 11:29:43'),
(160, 26, 'uploads/application/works/work_6_app_26_1761650983_4a8585e478ff0a8e.avif', '2025-10-28 11:29:43'),
(161, 26, 'uploads/application/works/work_7_app_26_1761650983_2905ffdfbc11f13f.avif', '2025-10-28 11:29:43'),
(162, 27, 'uploads/application/works/work_0_app_27_1761651163_420851f7bd2a209a.avif', '2025-10-28 11:32:43'),
(163, 27, 'uploads/application/works/work_1_app_27_1761651163_eb0b9b0ee23bba09.avif', '2025-10-28 11:32:43'),
(164, 27, 'uploads/application/works/work_2_app_27_1761651163_b0803ddc2533ec16.avif', '2025-10-28 11:32:43'),
(165, 27, 'uploads/application/works/work_3_app_27_1761651163_bb1eca807fb1c9d5.avif', '2025-10-28 11:32:43'),
(166, 27, 'uploads/application/works/work_4_app_27_1761651163_6ffec14eccbe0b26.avif', '2025-10-28 11:32:43'),
(167, 28, 'uploads/application/works/work_0_app_28_1761651645_d68afc4bcb623e3a.avif', '2025-10-28 11:40:45'),
(168, 28, 'uploads/application/works/work_1_app_28_1761651645_1e74de385d5d0bd3.avif', '2025-10-28 11:40:45'),
(169, 28, 'uploads/application/works/work_2_app_28_1761651645_bc5ed4f7ec7a5bee.avif', '2025-10-28 11:40:45'),
(170, 28, 'uploads/application/works/work_3_app_28_1761651645_86aed3dfe490636a.avif', '2025-10-28 11:40:45'),
(171, 28, 'uploads/application/works/work_4_app_28_1761651645_2d942035f20ff269.avif', '2025-10-28 11:40:45'),
(172, 28, 'uploads/application/works/work_5_app_28_1761651645_09d296f08618ec89.avif', '2025-10-28 11:40:45'),
(173, 28, 'uploads/application/works/work_6_app_28_1761651645_7b4877205b5a47db.avif', '2025-10-28 11:40:45'),
(174, 28, 'uploads/application/works/work_7_app_28_1761651645_8a53c923b7d4b688.avif', '2025-10-28 11:40:45'),
(175, 29, 'uploads/application/works/work_0_app_29_1761651751_81ddad4d2180a4c9.avif', '2025-10-28 11:42:31'),
(176, 29, 'uploads/application/works/work_1_app_29_1761651751_fb3517ce4c15df02.avif', '2025-10-28 11:42:31'),
(177, 29, 'uploads/application/works/work_2_app_29_1761651751_ac089b85066abc8b.avif', '2025-10-28 11:42:31'),
(178, 29, 'uploads/application/works/work_3_app_29_1761651751_3eef4680fce63493.avif', '2025-10-28 11:42:31'),
(179, 29, 'uploads/application/works/work_4_app_29_1761651751_b45577fd7b0e9f09.avif', '2025-10-28 11:42:31'),
(180, 30, 'uploads/application/works/work_0_app_30_1761651912_c372e08afa845408.avif', '2025-10-28 11:45:12'),
(181, 30, 'uploads/application/works/work_1_app_30_1761651912_39bdd5f02fc03b69.avif', '2025-10-28 11:45:12'),
(182, 30, 'uploads/application/works/work_2_app_30_1761651912_5642db13da82f375.avif', '2025-10-28 11:45:12'),
(183, 30, 'uploads/application/works/work_3_app_30_1761651912_418cf145cfd8f6cb.avif', '2025-10-28 11:45:12'),
(184, 30, 'uploads/application/works/work_4_app_30_1761651912_c2946c2ac96d82da.avif', '2025-10-28 11:45:12'),
(185, 30, 'uploads/application/works/work_5_app_30_1761651912_ef278365f1b4e4eb.avif', '2025-10-28 11:45:12'),
(186, 31, 'uploads/application/works/work_0_app_31_1761652752_71d5097fb0393626.avif', '2025-10-28 11:59:12'),
(187, 31, 'uploads/application/works/work_1_app_31_1761652752_f05fd8232d159ba2.avif', '2025-10-28 11:59:12'),
(188, 31, 'uploads/application/works/work_2_app_31_1761652752_e36d37d1a103af0c.avif', '2025-10-28 11:59:12'),
(189, 31, 'uploads/application/works/work_3_app_31_1761652752_b796ae6aa01bf6b3.avif', '2025-10-28 11:59:12'),
(190, 31, 'uploads/application/works/work_4_app_31_1761652752_1267b4ddd5d0a994.avif', '2025-10-28 11:59:12'),
(191, 32, 'uploads/application/works/work_0_app_32_1761700496_097db009c11363d9.jpg', '2025-10-29 01:14:56'),
(192, 32, 'uploads/application/works/work_1_app_32_1761700496_cdad44fdfec92e8c.jpg', '2025-10-29 01:14:56'),
(193, 32, 'uploads/application/works/work_2_app_32_1761700496_94ca0719126d0e52.jpg', '2025-10-29 01:14:56'),
(194, 32, 'uploads/application/works/work_3_app_32_1761700496_4c20e1ea0f72600e.jpg', '2025-10-29 01:14:56');

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
(1, 3, 'aaa', 'aaa', 1.00, 1, 1, 1, 'active', '2025-10-27 15:14:19', '2025-10-27 15:14:19'),
(2, 1, 'Portrait ', 'dkmkfm', 1500.00, 2, 11, 3, 'active', '2025-10-27 15:57:30', '2025-10-27 15:57:30'),
(3, 12, 'Basic Portrait Session', 'Perfect for quick portraits or profile updates. Includes one outfit, one location, and light photo retouching.', 1500.00, 1, 15, 3, 'active', '2025-10-28 03:28:27', '2025-10-28 03:28:27'),
(4, 12, 'Standard Portrait Session', 'Great for personal shoots, couples, or small group portraits. Includes up to two outfits, one to two locations, and enhanced photo editing.', 2500.00, 2, 25, 5, 'active', '2025-10-28 03:28:27', '2025-10-28 03:28:27'),
(5, 12, 'Premium Portrait Experience', 'Full creative session with multiple outfits and locations. Includes advanced retouching, mood-based color grading, and all best shots edited.\r\n', 4000.00, 3, 40, 5, 'active', '2025-10-28 03:28:27', '2025-10-28 03:28:27'),
(6, 23, 'Basic Portrait Session', 'Short Portrait Session', 1500.00, 1, 15, 3, 'active', '2025-10-28 12:06:54', '2025-10-28 12:06:54'),
(7, 23, 'Standard Portrait Session', 'Quick but High Quality Session', 2500.00, 2, 25, 5, 'active', '2025-10-28 12:06:54', '2025-10-28 12:06:54'),
(8, 23, 'Premium Portrait Experience', '4k Resolution!', 4000.00, 3, 40, 5, 'active', '2025-10-28 12:06:54', '2025-10-28 12:06:54');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_otps`
--

CREATE TABLE `password_reset_otps` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `user_type` enum('user','worker') NOT NULL DEFAULT 'user',
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 'Cabubas', 'Christian', 'Caguiat', 'cjplaysgames83@gmail.com', '09193695376', '$2y$10$okQI9RoOghP5fc.HE7HK9.pzokVB6npq.CDWPogECPqyywH7EGUxu', '231, Tapuac District, Dagupan City, Pangasinan', '/Kislap/uploads/user/profile_photos/profile_1_1761731660_eb8cb9c3aad0b381.avif', '2025-10-26 15:59:38'),
(2, 'Ari', 'Lester', '', 'lesterari@outlook.com', '09991234343', '$2y$10$3TBUAIDI8tc3Qc24c.tk1e3hcrARFuX0yHMatKTfwuY2/UpmJMKTS', '445, Las Vegas Street, Binmaley, Pangasinan', NULL, '2025-10-27 09:51:31'),
(3, 'Balonzo', 'Mildred', 'Torio', 'balonzomildred@gmail.com', '09291113534', '$2y$10$iRrdRV2pRDgwx8hemgU4K.V5CRHmZrSxxMDypooHH9F7/p2nVWt02', '424 Fiesta Communities, Mexico, Pampanga', NULL, '2025-10-27 09:55:02'),
(4, 'Morales', 'Antonio', 'Villanueva', 'antonio.morales@live.com', '09371234567', '$2y$10$y4Nch5rbjrE2Ha3nkz0uVuSEmdCvg2BLTbAvDbowjVVtRaMjKxnbi', '7816 Batangas St., Barangay San Isidro, Batangas City, Batangas', NULL, '2025-10-27 10:17:48'),
(5, 'Vargas', 'Clara', 'Cruz', 'clara.vargas@rocketmail.com', '09241234567', '$2y$10$aIBp84z1G13B5lczGdJxEeiYb0166GNbK6pTzjwl2ghWgQAKgPs1a', '4532 Pine Ave., Barangay Langtang, Tarlac City, Tarlac', NULL, '2025-10-27 10:18:26'),
(6, 'Delos Santos', 'Roberto', 'Castillo', 'roberto.delossantos@icloud.com', '09152345678', '$2y$10$vWjJbv0G7YY0am1qILYD9eOAecWslLKD0JHWUidW7RM0U2kQpV5ke', '8901 Pineapple St., Barangay Gubat, Legazpi City, Albay', NULL, '2025-10-27 10:19:04'),
(7, 'Fernandez', 'Edgar', 'Reyes', 'edgar.fernandez@zoho.com', '09161123456', '$2y$10$eZ094N454UBMQbGqpc0/ceMopqdAsCR2.qHWr6sJXZ0QYo.LufsAC', '2347 Mangga St., Barangay Poblacion, Davao City, Davao del Sur', NULL, '2025-10-27 10:19:47'),
(8, 'Esteban', 'Julia', 'Solis', 'julia.esteban@gmail.com', '09251823456', '$2y$10$P7Fp8rJxLBpyRAorK75Xuulx73L0NFuuuVmP0y.0aEC8P1gw4.NL2', '5432 Sampaguita Rd., Barangay Kauswagan, Cagayan de Oro, Misamis Oriental', NULL, '2025-10-27 10:20:40'),
(9, 'Bautista', 'Raul', 'Perez', 'raul.bautista@hotmail.com', '09231123456', '$2y$10$055.5M8toGGANGNHkjYqiO21h5tT7ILJwrPpcl4jkdFebhYDjTrNi', '1134 Rosas Ave., Barangay Panacan, Davao City, Davao del Sur', NULL, '2025-10-27 10:21:29'),
(10, 'Alonzo', 'Hazel', 'Martinez', 'hazel.alonzo@aol.com', '09191456789', '$2y$10$4J0jTrEMtj5Z.pRunpT6cO0uPd5TbHlNn/om7i6SPPGAbCDgJpAmK', '8973 Zinnia St., Barangay Banilad, Cebu City, Cebu', NULL, '2025-10-27 10:22:03'),
(11, 'Cruz', 'Isabel', 'Del Rosario', 'isabel.cruz@gmail.com', '09361122334', '$2y$10$rZPRXnnHh8Ju0NrwzfQhge80sbZUgBf/QeD8wjv5ALjZNS7g9.XZW', '6785 Jasmine St., Barangay Talamban, Cebu City, Cebu', NULL, '2025-10-27 10:22:45'),
(12, 'Gonzales', 'Mikaella', NULL, 'mikaella@gmail.com', '09224569081', '$2y$10$MBYYVd8kguS8ChCVAMsyi.BrKA7IEvEv6m71YSFpkJzuKd30J9YQa', '231 Herrero Street, Dagupan, Pangasinan', NULL, '2025-10-29 08:02:55'),
(13, 'Martinez', 'Crystal James', 'Fernandez', 'crystal@gmail.com', '09442223434', '$2y$10$I2Z6Nr0.uw8UaCtSzxU9jeKweWHKEZCg8KP.gIF50OmCAqo8k.ziq', '231, Mayombo Dist., Dagupan, Pangasinan', NULL, '2025-10-29 09:29:05');

-- --------------------------------------------------------

--
-- Table structure for table `workers`
--

CREATE TABLE `workers` (
  `worker_id` int(11) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `status` enum('active','suspended','banned') NOT NULL DEFAULT 'active',
  `suspended_until` timestamp NULL DEFAULT NULL,
  `suspension_reason` text DEFAULT NULL,
  `suspended_by` int(11) DEFAULT NULL,
  `suspended_at` timestamp NULL DEFAULT NULL,
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

INSERT INTO `workers` (`worker_id`, `application_id`, `status`, `suspended_until`, `suspension_reason`, `suspended_by`, `suspended_at`, `lastName`, `firstName`, `middleName`, `email`, `phoneNumber`, `password`, `address`, `specialty`, `experience_years`, `bio`, `profile_photo`, `total_ratings`, `total_bookings`, `total_earnings`, `created_at`, `updated_at`, `average_rating`) VALUES
(1, 11, 'active', NULL, NULL, NULL, NULL, 'Reyes', 'Carlo', 'Alonzo', 'carlo.reyes@gmail.com', '09281234565', '$2y$10$7oefTFOYDubZ./S5lALSLueTYTt9jTocYMYL0sj/YgVt/b0kHdrIS', '88 Bago Blvd., Brgy. Banilad, Cebu City, Cebu', 'creative', 12, 'I am a creative/conceptual artist', 'uploads/workers/1/worker1_profile_1761586848_30e34ac39faf32c4.avif', 0, 0, 0.00, '2025-10-27 14:12:46', '2025-10-28 08:53:48', 0.00),
(2, 12, 'active', NULL, NULL, NULL, NULL, 'Gonzaga', 'Melinda', 'Santos', 'melinda.gonzaga@mail.com', '09371234566', '$2y$10$F2hpK8VIR.jlGuzH/k8sbO7/PDwAyR.X2H6JtmqmVAj3Oj.qyzfHa', '14 Lapu-Lapu St., Brgy. Durano, Mandaue City, Cebu', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-27 14:12:53', '2025-10-27 14:12:53', 0.00),
(3, 10, 'active', NULL, NULL, NULL, NULL, 'Ortega', 'Nelia', 'Ramos', 'nelia.ortega@zoho.com', '09171234564', '$2y$10$H/J3coqb7KKdjZ4UwmDBQu5XNfkPgW0fuJUwLq4mRy0ZzLsTd8kY6', '56 Sampaguita Ln., Brgy. Divisoria, Tarlac City, Tarlac', 'event', 0, 'AAA', NULL, 0, 0, 0.00, '2025-10-27 14:12:57', '2025-10-27 15:14:19', 0.00),
(4, 9, 'active', NULL, NULL, NULL, NULL, 'Santos', 'Marco', 'Perez', 'marco.santos@ymail.com', '09391234563', '$2y$10$StezBCsrjo/f1dF4iiaXGO8Aam665PxmSXyeVtYVXSD2T24lBBSpq', '150 Bamboo St., Brgy. San Vicente, Puerto Princesa, Palawan', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-27 14:13:01', '2025-10-27 14:13:01', 0.00),
(5, 8, 'active', NULL, NULL, NULL, NULL, 'Aquino', 'Liza', 'dela Cruz', 'liza.aquino@icloud.com', '09291234562', '$2y$10$7ogiDcSrkZQ.gseekxlYFu7KGu3nQ1PJFzR48VVTJUZxOUAKxzTjS', '7 Coconut Rd., Brgy. San Antonio, Laoag City, Ilocos Norte', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-27 14:13:19', '2025-10-27 14:13:19', 0.00),
(6, 7, 'active', NULL, NULL, NULL, NULL, 'Pineda', 'Arnel', 'Bautista', 'arnel.pineda@yahoo.com', '09191234561', '$2y$10$wVYNjkkONA69HvN33Qpc/ePovUvcsU/hEXevW7RGd4vMVKEXDYBXy', '33 Acacia Dr., Brgy. San Roque, Naga City, Camarines Sur', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-27 14:13:24', '2025-10-27 14:13:24', 0.00),
(7, 6, 'active', NULL, NULL, NULL, NULL, 'Valdez', 'Camille', '', 'camille.valdez@gmail.com', '09371234560', '$2y$10$SWMs88YwEDYF3heLzEI0AOID21d0qcZnRs/2u4HgOhcc2bjtXSEsO', '222 Mango St., Brgy. Pasonanca, Zamboanga City, Zamboanga del Sur', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-27 17:51:13', '2025-10-27 17:51:13', 0.00),
(8, 5, 'active', NULL, NULL, NULL, NULL, 'Ilagan', 'Rafael', 'Torres', 'rafael.ilagan@protonmail.com', '09271234901', '$2y$10$XscyCKLI3t9TSSwhrYKpmeDnJdS98LYPrx1E4S/UZSXS7hgKLtZ6u', '9 Laurel Lane, Brgy. San Isidro, Cagayan de Oro, Misamis Oriental', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-27 17:51:19', '2025-10-28 06:29:30', 0.00),
(9, 4, 'active', NULL, NULL, NULL, NULL, 'Mercado', 'Beatrice', 'Gomez', 'bea.mercado@mail.com', '09181234890', '$2y$10$rsJIq5UikdexzEnCtEVfJOj2w0lH0czsaVk06Khaomm.djsKziDX.', '101 San Miguel St., Brgy. Burgos, Batangas City, Batangas', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-27 17:51:24', '2025-10-27 17:51:24', 0.00),
(10, 3, 'active', NULL, NULL, NULL, NULL, 'Cruz', 'Diego', 'Ramos', 'diego.cruz@outlook.com', '09391234789', '$2y$10$OWgqbgTuqN.LqPTb7Y3zlu3PvSuEGeJ.zGCi5Ov4NyBUvauyb0z5.', '78 Magsaysay Rd., Brgy. Centro, Tagbilaran, Bohol', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-27 17:51:28', '2025-10-27 17:51:28', 0.00),
(11, 2, 'active', NULL, NULL, NULL, NULL, 'Flores', 'Anna', '', 'anna.flores@yahoo.com', '09281234678', '$2y$10$yM7CO8CRnXz0dw3tvXp7xufRNsxvDw17DxbcjGUePXEDymaWvj2EC', '45 Mabini Ave., Brgy. Poblacion, Iloilo City, Iloilo', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-27 17:51:32', '2025-10-27 17:51:32', 0.00),
(12, 1, 'active', NULL, NULL, NULL, NULL, 'Navarro', 'Miguel', 'Santos', 'miguel.navarro@gmail.com', '09171234567', '$2y$10$WfQwbX19RhDSNmUxV0FIKeFkNUJs6iy9berhaoGS5vvqBGlpNO5eO', '12 Rizal St., Brgy. San Jose, Lucena City, Quezon', 'portrait', 4, 'Hi, I’m Miguel, a photographer who captures genuine moments and emotions through my lens. I focus on creating clean, natural, and creative shots that tell real stories — whether it’s portraits, events, or everyday life', 'uploads/workers/12/worker12_profile_1761622494_f4c87e8d9f984259.avif', 0, 0, 0.00, '2025-10-27 17:51:35', '2025-10-29 11:20:16', 0.00),
(13, 15, 'active', NULL, NULL, NULL, NULL, 'De Guzman', 'Victor', 'Navarro', 'victor.dguzman@protonmail.com', '09391234569', '$2y$10$Iz8szcEQujNaHbO98mv3WukiI5Xxiq9kCKU1miKn7VtZSevomqfCO', '77 Sampaloc St., Brgy. Divisoria, Tarlac City, Tarlac', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-28 10:38:28', '2025-10-28 10:38:28', 0.00),
(14, 14, 'active', NULL, NULL, NULL, NULL, 'Tan', 'Elaine', 'Yu', 'elaine.tan@gmail.com', '09291234568', '$2y$10$3YtzPYMiUS7asEXy6A.OK.cehDvTc3/RGOEe0BOYe91Xl/QqjMHIC', '19 Orchid Way, Brgy. Bagong Silang, Baguio City, Benguet', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-28 10:38:33', '2025-10-28 10:38:33', 0.00),
(16, 13, 'active', NULL, NULL, NULL, NULL, 'Herrera', 'Jason', 'Villanueva', 'j.herrera@outlook.com', '09191234567', '$2y$10$p.9qOxMe6pxhjQbi6k8aJOnlATnhs9oMKNgZs0aHtk.HJrZEMezk.', '300 Pine Rd., Brgy. Malvar, Angeles City, Pampanga', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-28 10:38:46', '2025-10-28 10:38:46', 0.00),
(17, 19, 'active', NULL, NULL, NULL, NULL, 'Lozano', 'Edwin', 'Ramos', 'edwin.lozano@mail.com', '09191234573', '$2y$10$8nxwDLsbCKq.KT6WJrpSje4zg.eUFvUJf720QGMsntV..HZglDOBa', '9 Camia St., Brgy. San Miguel, Dumaguete City, Negros Oriental', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-28 10:49:38', '2025-10-28 10:49:38', 0.00),
(18, 18, 'active', NULL, NULL, NULL, NULL, 'Catindig', 'Maribel', 'Aquino', 'maribel.catindig@aol.com', '09371234572', '$2y$10$YGcmmzn4pzHFi16ZETaTBeyQm5FVD34C.Rlz6UOFlsFhW0ylsaBgO', '21 Tulip Ln., Brgy. San Jose, Lucena City, Quezon', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-28 10:49:41', '2025-10-28 10:49:41', 0.00),
(20, 17, 'active', NULL, NULL, NULL, NULL, 'Fabillar', 'Jonah', 'Cruz', 'jonah.fabillar@gmail.com', '09281234571', '$2y$10$FYgbNr3.CCeodrcHctf6z.jwCF7DesRiJhAoy5kgMgfpFmYdk7TYC', '402 Pearl St., Brgy. Baybay, Legazpi City, Albay', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-28 10:49:49', '2025-10-28 10:49:49', 0.00),
(21, 16, 'active', NULL, NULL, NULL, NULL, 'Manalo', 'Rica', 'Santos', 'rica.manalo@yahoo.com', '09171234570', '$2y$10$K0euKLQpferIzA3REa27buMkszXiH9uxBxq87trzQCOF1g1RELwUW', '5 Palm Grove, Brgy. Poblacion, Roxas City, Capiz', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-28 10:49:52', '2025-10-28 10:49:52', 0.00),
(22, 20, 'active', NULL, NULL, NULL, NULL, 'Salazar', 'Faye', 'Navarro', 'faye.salazar@outlook.com', '09291234574', '$2y$10$f/ooOupmflo4slCzns.YA.NTMKLukuuxBEzuOQoyB8HlFQvGonI/.', '9 Camia St., Brgy. San Miguel, Dumaguete City, Negros Oriental', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-28 10:55:12', '2025-10-28 16:30:07', 0.00),
(23, 31, 'active', NULL, NULL, NULL, NULL, 'Cruz', 'Marlon', 'Gomez', 'marlon.cruz@gmail.com', '09181234585', '$2y$10$qHOcaxQ4axoxD7H29OvgL.UpjiOqh9cDSKxjPOdoCtKhtxxztI5/C', '123 Juniper St., Brgy. Malanday, Valenzuela City, Metro Manila', 'portrait', 17, 'Hi, I’m Marlon – a portrait photographer passionate about capturing the real you. I believe in creating images that tell a story, whether it\'s in the studio or on location. My goal is to make you feel at ease so your true personality shines through.\r\n\r\nWith every shoot, I focus on the details that bring out your best self – the moments, expressions, and light that make each portrait unique. Whether it\'s a professional headshot or a personal portrait, I’m here to help you look and feel your best.\r\n\r\nLet’s connect and create something memorable.', 'uploads/workers/23/worker23_profile_1761735470_54ff2d47e13aca75.avif', 0, 0, 0.00, '2025-10-28 12:01:03', '2025-10-29 11:20:16', 0.00),
(24, 25, 'active', NULL, NULL, NULL, NULL, 'Lomboy', 'Arlene', 'Quinto', 'arlene.lomboy@icloud.com', '09191234579', '$2y$10$W11miF/ThJTH86LBkZwHOeVW00neeyVllxTmH7GnXrY3/nIuxTl1a', '88 Coconut Ln., Brgy. Baybay, Calbayog City, Samar', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-29 01:33:34', '2025-10-29 09:19:38', 0.00),
(25, 32, 'active', NULL, NULL, NULL, NULL, 'Alano', 'Kim', '', 'miko@gmail.com', '09123467891', '$2y$10$nUdt8Tt6CIXUzFMuNFO51uJqOaE15xRM3.GGTPXNgh9frekjxgwLe', '123 Malued, Mindanao', 'photobooth', 3, 'Professional Bitch', 'uploads/workers/25/worker25_profile_1761702028_3834d27f799bdb30.png', 0, 0, 0.00, '2025-10-29 01:34:38', '2025-10-29 09:18:37', 0.00),
(26, 30, 'active', NULL, NULL, NULL, NULL, 'Delos Reyes', 'Cynthia', 'Aquino', 'cynthia.delosreyes@zoho.com', '09391234584', '$2y$10$4C6q7H5G2hfIeuEPZu/L6.Jhlg.hGH5PcqyHgwRzlzEkZ22GBFbgy', '118 Palm St., Brgy. Bagumbayan, Davao City, Davao del Sur', '', 0, NULL, NULL, 0, 0, 0.00, '2025-10-29 08:05:27', '2025-10-29 09:19:47', 0.00);

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
(35, 6, 'uploads/workers/6/worker6_work7.avif', '2025-10-27 14:13:24'),
(36, 1, 'uploads/workers/1/worker1_work_1761586912_aa21962e239fbc9c.avif', '2025-10-27 17:41:52'),
(37, 1, 'uploads/workers/1/worker1_work_1761586923_cbccef7e11653728.avif', '2025-10-27 17:42:03'),
(38, 1, 'uploads/workers/1/worker1_work_1761586923_d4c73c39349122fc.avif', '2025-10-27 17:42:03'),
(39, 1, 'uploads/workers/1/worker1_work_1761586923_6fd9bd9f1d42e7ae.avif', '2025-10-27 17:42:03'),
(40, 7, 'uploads/workers/7/worker7_work1.avif', '2025-10-27 17:51:13'),
(41, 7, 'uploads/workers/7/worker7_work2.avif', '2025-10-27 17:51:13'),
(42, 7, 'uploads/workers/7/worker7_work3.avif', '2025-10-27 17:51:13'),
(43, 7, 'uploads/workers/7/worker7_work4.avif', '2025-10-27 17:51:13'),
(44, 7, 'uploads/workers/7/worker7_work5.avif', '2025-10-27 17:51:13'),
(45, 7, 'uploads/workers/7/worker7_work6.avif', '2025-10-27 17:51:13'),
(46, 7, 'uploads/workers/7/worker7_work7.avif', '2025-10-27 17:51:13'),
(47, 7, 'uploads/workers/7/worker7_work8.avif', '2025-10-27 17:51:13'),
(48, 8, 'uploads/workers/8/worker8_work1.avif', '2025-10-27 17:51:19'),
(49, 8, 'uploads/workers/8/worker8_work2.avif', '2025-10-27 17:51:19'),
(50, 8, 'uploads/workers/8/worker8_work3.avif', '2025-10-27 17:51:19'),
(51, 8, 'uploads/workers/8/worker8_work4.avif', '2025-10-27 17:51:19'),
(52, 8, 'uploads/workers/8/worker8_work5.avif', '2025-10-27 17:51:19'),
(53, 8, 'uploads/workers/8/worker8_work6.avif', '2025-10-27 17:51:19'),
(54, 9, 'uploads/workers/9/worker9_work1.avif', '2025-10-27 17:51:24'),
(55, 9, 'uploads/workers/9/worker9_work2.avif', '2025-10-27 17:51:24'),
(56, 9, 'uploads/workers/9/worker9_work3.avif', '2025-10-27 17:51:24'),
(57, 9, 'uploads/workers/9/worker9_work4.avif', '2025-10-27 17:51:24'),
(58, 9, 'uploads/workers/9/worker9_work5.avif', '2025-10-27 17:51:24'),
(59, 9, 'uploads/workers/9/worker9_work6.avif', '2025-10-27 17:51:24'),
(60, 10, 'uploads/workers/10/worker10_work1.avif', '2025-10-27 17:51:28'),
(61, 10, 'uploads/workers/10/worker10_work2.avif', '2025-10-27 17:51:28'),
(62, 10, 'uploads/workers/10/worker10_work3.avif', '2025-10-27 17:51:28'),
(63, 10, 'uploads/workers/10/worker10_work4.avif', '2025-10-27 17:51:28'),
(64, 10, 'uploads/workers/10/worker10_work5.avif', '2025-10-27 17:51:28'),
(65, 10, 'uploads/workers/10/worker10_work6.avif', '2025-10-27 17:51:28'),
(66, 10, 'uploads/workers/10/worker10_work7.avif', '2025-10-27 17:51:28'),
(67, 10, 'uploads/workers/10/worker10_work8.avif', '2025-10-27 17:51:28'),
(68, 11, 'uploads/workers/11/worker11_work1.avif', '2025-10-27 17:51:32'),
(69, 11, 'uploads/workers/11/worker11_work2.avif', '2025-10-27 17:51:32'),
(70, 11, 'uploads/workers/11/worker11_work3.avif', '2025-10-27 17:51:32'),
(71, 11, 'uploads/workers/11/worker11_work4.avif', '2025-10-27 17:51:32'),
(72, 11, 'uploads/workers/11/worker11_work5.avif', '2025-10-27 17:51:32'),
(73, 11, 'uploads/workers/11/worker11_work6.avif', '2025-10-27 17:51:32'),
(74, 11, 'uploads/workers/11/worker11_work7.avif', '2025-10-27 17:51:32'),
(76, 12, 'uploads/workers/12/worker12_work2.avif', '2025-10-27 17:51:35'),
(77, 12, 'uploads/workers/12/worker12_work3.avif', '2025-10-27 17:51:35'),
(78, 12, 'uploads/workers/12/worker12_work4.avif', '2025-10-27 17:51:35'),
(80, 12, 'uploads/workers/12/worker12_work6.avif', '2025-10-27 17:51:35'),
(82, 12, 'uploads/workers/12/worker12_work8.avif', '2025-10-27 17:51:35'),
(83, 12, 'uploads/workers/12/worker12_work_1761622181_08968b1a97e63b80.avif', '2025-10-28 03:29:41'),
(85, 12, 'uploads/workers/12/worker12_work_1761622443_b623ea7ed589bcd1.avif', '2025-10-28 03:34:03'),
(87, 12, 'uploads/workers/12/worker12_work_1761622810_9bc834c93434d109.avif', '2025-10-28 03:40:10'),
(88, 13, 'uploads/workers/13/worker13_work1.avif', '2025-10-28 10:38:28'),
(89, 13, 'uploads/workers/13/worker13_work2.avif', '2025-10-28 10:38:28'),
(90, 13, 'uploads/workers/13/worker13_work3.avif', '2025-10-28 10:38:28'),
(91, 13, 'uploads/workers/13/worker13_work4.avif', '2025-10-28 10:38:28'),
(92, 13, 'uploads/workers/13/worker13_work5.avif', '2025-10-28 10:38:28'),
(93, 13, 'uploads/workers/13/worker13_work6.avif', '2025-10-28 10:38:28'),
(94, 14, 'uploads/workers/14/worker14_work1.avif', '2025-10-28 10:38:33'),
(95, 14, 'uploads/workers/14/worker14_work2.avif', '2025-10-28 10:38:33'),
(96, 14, 'uploads/workers/14/worker14_work3.avif', '2025-10-28 10:38:33'),
(97, 14, 'uploads/workers/14/worker14_work4.avif', '2025-10-28 10:38:33'),
(98, 14, 'uploads/workers/14/worker14_work5.avif', '2025-10-28 10:38:33'),
(99, 16, 'uploads/workers/16/worker16_work1.avif', '2025-10-28 10:38:46'),
(100, 16, 'uploads/workers/16/worker16_work2.avif', '2025-10-28 10:38:46'),
(101, 16, 'uploads/workers/16/worker16_work3.avif', '2025-10-28 10:38:46'),
(102, 16, 'uploads/workers/16/worker16_work4.avif', '2025-10-28 10:38:46'),
(103, 16, 'uploads/workers/16/worker16_work5.avif', '2025-10-28 10:38:46'),
(104, 17, 'uploads/workers/17/worker17_work1.avif', '2025-10-28 10:49:38'),
(105, 17, 'uploads/workers/17/worker17_work2.avif', '2025-10-28 10:49:38'),
(106, 17, 'uploads/workers/17/worker17_work3.avif', '2025-10-28 10:49:38'),
(107, 17, 'uploads/workers/17/worker17_work4.avif', '2025-10-28 10:49:38'),
(108, 17, 'uploads/workers/17/worker17_work5.avif', '2025-10-28 10:49:38'),
(109, 18, 'uploads/workers/18/worker18_work1.avif', '2025-10-28 10:49:41'),
(110, 18, 'uploads/workers/18/worker18_work2.avif', '2025-10-28 10:49:41'),
(111, 18, 'uploads/workers/18/worker18_work3.avif', '2025-10-28 10:49:41'),
(112, 18, 'uploads/workers/18/worker18_work4.avif', '2025-10-28 10:49:41'),
(113, 18, 'uploads/workers/18/worker18_work5.avif', '2025-10-28 10:49:41'),
(114, 18, 'uploads/workers/18/worker18_work6.avif', '2025-10-28 10:49:41'),
(115, 18, 'uploads/workers/18/worker18_work7.avif', '2025-10-28 10:49:41'),
(116, 20, 'uploads/workers/20/worker20_work1.avif', '2025-10-28 10:49:49'),
(117, 20, 'uploads/workers/20/worker20_work2.avif', '2025-10-28 10:49:49'),
(118, 20, 'uploads/workers/20/worker20_work3.avif', '2025-10-28 10:49:49'),
(119, 20, 'uploads/workers/20/worker20_work4.avif', '2025-10-28 10:49:49'),
(120, 20, 'uploads/workers/20/worker20_work5.avif', '2025-10-28 10:49:49'),
(121, 20, 'uploads/workers/20/worker20_work6.avif', '2025-10-28 10:49:49'),
(122, 21, 'uploads/workers/21/worker21_work1.avif', '2025-10-28 10:49:52'),
(123, 21, 'uploads/workers/21/worker21_work2.avif', '2025-10-28 10:49:52'),
(124, 21, 'uploads/workers/21/worker21_work3.avif', '2025-10-28 10:49:52'),
(125, 21, 'uploads/workers/21/worker21_work4.avif', '2025-10-28 10:49:52'),
(126, 21, 'uploads/workers/21/worker21_work5.avif', '2025-10-28 10:49:52'),
(127, 22, 'uploads/workers/22/worker22_work1.avif', '2025-10-28 10:55:12'),
(128, 22, 'uploads/workers/22/worker22_work2.avif', '2025-10-28 10:55:12'),
(129, 22, 'uploads/workers/22/worker22_work3.avif', '2025-10-28 10:55:12'),
(130, 22, 'uploads/workers/22/worker22_work4.avif', '2025-10-28 10:55:12'),
(131, 22, 'uploads/workers/22/worker22_work5.avif', '2025-10-28 10:55:12'),
(132, 22, 'uploads/workers/22/worker22_work6.avif', '2025-10-28 10:55:12'),
(134, 23, 'uploads/workers/23/worker23_work2.avif', '2025-10-28 12:01:03'),
(135, 23, 'uploads/workers/23/worker23_work3.avif', '2025-10-28 12:01:03'),
(136, 23, 'uploads/workers/23/worker23_work4.avif', '2025-10-28 12:01:03'),
(138, 23, 'uploads/workers/23/worker23_work_1761653243_c49e7e1c206443d3.avif', '2025-10-28 12:07:23'),
(139, 23, 'uploads/workers/23/worker23_work_1761667371_0e77085acaa1de71.avif', '2025-10-28 16:02:51'),
(140, 24, 'uploads/workers/24/worker24_work1.avif', '2025-10-29 01:33:34'),
(141, 24, 'uploads/workers/24/worker24_work2.avif', '2025-10-29 01:33:34'),
(142, 24, 'uploads/workers/24/worker24_work3.avif', '2025-10-29 01:33:34'),
(143, 24, 'uploads/workers/24/worker24_work4.avif', '2025-10-29 01:33:34'),
(144, 24, 'uploads/workers/24/worker24_work5.avif', '2025-10-29 01:33:34'),
(145, 24, 'uploads/workers/24/worker24_work6.avif', '2025-10-29 01:33:34'),
(146, 25, 'uploads/workers/25/worker25_work1.jpg', '2025-10-29 01:34:38'),
(147, 25, 'uploads/workers/25/worker25_work2.jpg', '2025-10-29 01:34:38'),
(148, 25, 'uploads/workers/25/worker25_work3.jpg', '2025-10-29 01:34:38'),
(149, 25, 'uploads/workers/25/worker25_work4.jpg', '2025-10-29 01:34:38'),
(150, 26, 'uploads/workers/26/worker26_work1.avif', '2025-10-29 08:05:27'),
(151, 26, 'uploads/workers/26/worker26_work2.avif', '2025-10-29 08:05:27'),
(152, 26, 'uploads/workers/26/worker26_work3.avif', '2025-10-29 08:05:27'),
(153, 26, 'uploads/workers/26/worker26_work4.avif', '2025-10-29 08:05:27'),
(154, 26, 'uploads/workers/26/worker26_work5.avif', '2025-10-29 08:05:27'),
(155, 26, 'uploads/workers/26/worker26_work6.avif', '2025-10-29 08:05:27');

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
-- Indexes for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_otp` (`email`,`otp_code`),
  ADD KEY `idx_expires_at` (`expires_at`);

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
  ADD KEY `idx_specialty` (`specialty`),
  ADD KEY `fk_workers_suspended_by` (`suspended_by`),
  ADD KEY `idx_suspended_until` (`suspended_until`),
  ADD KEY `idx_status_suspended_until` (`status`,`suspended_until`);

--
-- Indexes for table `worker_availability`
--
ALTER TABLE `worker_availability`
  ADD PRIMARY KEY (`availability_id`),
  ADD UNIQUE KEY `unique_worker_date` (`worker_id`,`date`),
  ADD KEY `idx_worker_date` (`worker_id`,`date`),
  ADD KEY `idx_date` (`date`);

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
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ai_temp_bookings`
--
ALTER TABLE `ai_temp_bookings`
  MODIFY `temp_booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `application`
--
ALTER TABLE `application`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `application_resume`
--
ALTER TABLE `application_resume`
  MODIFY `resume_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `application_works`
--
ALTER TABLE `application_works`
  MODIFY `work_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=195;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `workers`
--
ALTER TABLE `workers`
  MODIFY `worker_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `worker_availability`
--
ALTER TABLE `worker_availability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `worker_works`
--
ALTER TABLE `worker_works`
  MODIFY `work_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

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
  ADD CONSTRAINT `fk_workers_suspended_by` FOREIGN KEY (`suspended_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `workers_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `application` (`application_id`) ON DELETE SET NULL;

--
-- Constraints for table `worker_availability`
--
ALTER TABLE `worker_availability`
  ADD CONSTRAINT `worker_availability_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE;

--
-- Constraints for table `worker_works`
--
ALTER TABLE `worker_works`
  ADD CONSTRAINT `worker_works_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `workers` (`worker_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
