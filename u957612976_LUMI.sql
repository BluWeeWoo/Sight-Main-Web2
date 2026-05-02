-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 01, 2026 at 07:11 AM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u957612976_LUMI`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_profile`
--

CREATE TABLE `admin_profile` (
  `admin_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role_level` enum('Admin','Moderator') DEFAULT NULL,
  `last_login` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_profile`
--

INSERT INTO `admin_profile` (`admin_id`, `user_id`, `role_level`, `last_login`) VALUES
(1, 5, 'Admin', '2026-04-07 01:39:26'),
(4, 11, 'Admin', '2026-04-06 21:34:35'),
(5, 12, 'Admin', '2026-04-06 21:39:37');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action_taken` varchar(255) DEFAULT NULL,
  `target_entity` varchar(255) DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `child_inventory`
--

CREATE TABLE `child_inventory` (
  `inventory_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `purchase_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `child_profile`
--

CREATE TABLE `child_profile` (
  `child_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `last_sync` datetime DEFAULT NULL,
  `login_code` varchar(50) DEFAULT NULL,
  `calibration_baseline` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Saves the TFLite facial mesh baseline for distance calculation' CHECK (json_valid(`calibration_baseline`)),
  `fcm_token` varchar(255) DEFAULT NULL COMMENT 'Firebase Cloud Messaging token for silent rule-update pushes',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `child_profile`
--

INSERT INTO `child_profile` (`child_id`, `user_id`, `birthdate`, `device_id`, `last_sync`, `login_code`, `calibration_baseline`, `fcm_token`, `updated_at`) VALUES
(1, 14, '2016-01-15', NULL, '2026-04-07 06:10:34', '001429', NULL, NULL, '2026-04-07 06:10:34'),
(2, 16, '2016-01-15', NULL, '2026-04-07 06:11:40', '322664', NULL, NULL, '2026-04-07 06:11:40'),
(3, 18, '2016-01-15', NULL, '2026-04-07 06:11:41', '528146', NULL, NULL, '2026-04-07 06:11:41'),
(4, 20, '2017-02-20', NULL, '2026-04-07 06:15:27', '996243', NULL, NULL, '2026-04-07 06:15:27');

-- --------------------------------------------------------

--
-- Table structure for table `clinician_patient_link`
--

CREATE TABLE `clinician_patient_link` (
  `link_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `child_id` int(11) DEFAULT NULL,
  `linkage_key` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `linkage_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clinician_patient_link`
--

INSERT INTO `clinician_patient_link` (`link_id`, `doctor_id`, `child_id`, `linkage_key`, `is_active`, `linkage_date`) VALUES
(1, 7, 1, '9f5626627d7c9e3e4a4b37a7a1bd6bfc', 1, '2026-04-06 22:10:34'),
(2, 7, 2, 'cbc9321c9e2223202df90f1732c41e9b', 1, '2026-04-06 22:11:41'),
(3, 7, 4, '2a98c9fcd89fddf8de38e1998c5cdf36', 1, '2026-04-06 22:15:27');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_profile`
--

CREATE TABLE `doctor_profile` (
  `doctor_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `specialty` varchar(255) DEFAULT NULL,
  `clinic` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `license_number` varchar(255) DEFAULT NULL,
  `is_validated` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor_profile`
--

INSERT INTO `doctor_profile` (`doctor_id`, `user_id`, `phone`, `specialty`, `clinic`, `location`, `license_number`, `is_validated`) VALUES
(7, 8, '091234567', 'Optometrist', 'Lipeye', 'Lipa', 'PRC-213456756454', 0),
(8, 21, '09110099192', 'Eyes', 'Sunset Clinic', 'Sunset bird', 'LUYS-09077LUY67', 0);

-- --------------------------------------------------------

--
-- Table structure for table `eye_health_metrics`
--

CREATE TABLE `eye_health_metrics` (
  `metric_id` int(11) NOT NULL,
  `child_id` int(11) DEFAULT NULL,
  `avg_blink_rate` float DEFAULT NULL,
  `avg_distance` float DEFAULT NULL,
  `strain_events` int(11) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `screen_time_minutes` int(11) DEFAULT 0 COMMENT 'How many minutes the screen was actually on during this logged block'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `eye_health_metrics`
--

INSERT INTO `eye_health_metrics` (`metric_id`, `child_id`, `avg_blink_rate`, `avg_distance`, `strain_events`, `timestamp`, `screen_time_minutes`) VALUES
(1, 1, 11, 53, 0, '2026-04-01 18:00:00', 180),
(2, 1, 10, 50, 2, '2026-04-02 18:00:00', 165),
(3, 1, 14, 47, 1, '2026-04-03 18:00:00', 150),
(4, 1, 13, 44, 0, '2026-04-04 18:00:00', 135),
(5, 1, 12, 41, 2, '2026-04-05 18:00:00', 120),
(6, 1, 11, 38, 1, '2026-04-06 18:00:00', 105),
(7, 1, 10, 35, 0, '2026-04-07 18:00:00', 90),
(8, 2, 13, 50, 0, '2026-04-01 18:00:00', 155),
(9, 2, 12, 48, 1, '2026-04-02 18:00:00', 145),
(10, 2, 11, 46, 0, '2026-04-03 18:00:00', 135),
(11, 2, 14, 44, 1, '2026-04-04 18:00:00', 125),
(12, 2, 13, 42, 0, '2026-04-05 18:00:00', 115),
(13, 2, 12, 40, 1, '2026-04-06 18:00:00', 105),
(14, 2, 11, 38, 0, '2026-04-07 18:00:00', 95),
(15, 3, 13, 50, 0, '2026-04-01 18:00:00', 155),
(16, 3, 12, 48, 1, '2026-04-02 18:00:00', 145),
(17, 3, 11, 46, 0, '2026-04-03 18:00:00', 135),
(18, 3, 14, 44, 1, '2026-04-04 18:00:00', 125),
(19, 3, 13, 42, 0, '2026-04-05 18:00:00', 115),
(20, 3, 12, 40, 1, '2026-04-06 18:00:00', 105),
(21, 3, 11, 38, 0, '2026-04-07 18:00:00', 95);

-- --------------------------------------------------------

--
-- Table structure for table `eye_health_score`
--

CREATE TABLE `eye_health_score` (
  `score_id` int(11) NOT NULL,
  `child_id` int(11) DEFAULT NULL,
  `daily_score` int(11) DEFAULT NULL,
  `grade` enum('Poor','Bad','Ok','Good','Very Good','Excellent') DEFAULT NULL,
  `recorded_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `eye_health_score`
--

INSERT INTO `eye_health_score` (`score_id`, `child_id`, `daily_score`, `grade`, `recorded_date`) VALUES
(1, 1, 70, 'Good', '2026-04-01 12:00:00'),
(2, 1, 73, 'Good', '2026-04-02 12:00:00'),
(3, 1, 76, 'Good', '2026-04-03 12:00:00'),
(4, 1, 79, 'Good', '2026-04-04 12:00:00'),
(5, 1, 82, 'Good', '2026-04-05 12:00:00'),
(6, 1, 85, 'Good', '2026-04-06 12:00:00'),
(7, 1, 88, 'Good', '2026-04-07 12:00:00'),
(8, 2, 72, 'Good', '2026-04-01 12:00:00'),
(9, 2, 74, 'Good', '2026-04-02 12:00:00'),
(10, 2, 76, 'Good', '2026-04-03 12:00:00'),
(11, 2, 78, 'Good', '2026-04-04 12:00:00'),
(12, 2, 80, 'Good', '2026-04-05 12:00:00'),
(13, 2, 82, 'Good', '2026-04-06 12:00:00'),
(14, 2, 84, 'Good', '2026-04-07 12:00:00'),
(15, 3, 72, 'Good', '2026-04-01 12:00:00'),
(16, 3, 74, 'Good', '2026-04-02 12:00:00'),
(17, 3, 76, 'Good', '2026-04-03 12:00:00'),
(18, 3, 78, 'Good', '2026-04-04 12:00:00'),
(19, 3, 80, 'Good', '2026-04-05 12:00:00'),
(20, 3, 82, 'Good', '2026-04-06 12:00:00'),
(21, 3, 84, 'Good', '2026-04-07 12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `guardian_child_link`
--

CREATE TABLE `guardian_child_link` (
  `guardian_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guardian_child_link`
--

INSERT INTO `guardian_child_link` (`guardian_id`, `child_id`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

-- --------------------------------------------------------

--
-- Table structure for table `guardian_profile`
--

CREATE TABLE `guardian_profile` (
  `guardian_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `contact_number` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guardian_profile`
--

INSERT INTO `guardian_profile` (`guardian_id`, `user_id`, `contact_number`) VALUES
(1, 13, '09123456789'),
(2, 15, '09123456789'),
(3, 17, '09123456789'),
(4, 19, '09123456789');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescription`
--

CREATE TABLE `prescription` (
  `recommendation_id` int(11) NOT NULL,
  `link_id` int(11) DEFAULT NULL,
  `advice_text` text DEFAULT NULL,
  `date_issued` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescription`
--

INSERT INTO `prescription` (`recommendation_id`, `link_id`, `advice_text`, `date_issued`) VALUES
(1, 1, 'Please follow the 20-20-20 rule and keep a safer viewing distance.', '2026-04-07 15:22:42'),
(2, 1, 'Recheck in 7 days and maintain proper viewing distance.', '2026-04-07 15:25:17'),
(3, 2, 'Try going outside', '2026-04-08 08:37:34');

-- --------------------------------------------------------

--
-- Table structure for table `session_limits`
--

CREATE TABLE `session_limits` (
  `limit_id` int(11) NOT NULL,
  `child_id` int(11) DEFAULT NULL,
  `daily_limit_minutes` int(11) DEFAULT NULL,
  `mode` enum('Strict','Relaxed') DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `harmful_distance_threshold` float DEFAULT 30 COMMENT 'Distance in cm that triggers the first warning',
  `critical_distance_threshold` float DEFAULT 10 COMMENT 'Distance in cm that triggers screen lock',
  `auto_enforce_breaks` tinyint(1) DEFAULT 1 COMMENT '1 for true, 0 for false',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Admin','Child','Guardian','Doctor') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `images` varchar(255) DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `must_change_password` tinyint(4) NOT NULL DEFAULT 0,
  `email_verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `email`, `last_name`, `first_name`, `password_hash`, `role`, `created_at`, `images`, `failed_login_attempts`, `locked_until`, `status`, `must_change_password`, `email_verified_at`) VALUES
(5, 'educavrabina29@gmail.com', 'Ligma', 'Chi', '$2y$12$rhQyvq9GFr9ftlJlXZsMnOCKqroFZkd3AD.enFCVqd6hFBDHhvqt6', 'Admin', '2026-04-07 09:39:26', NULL, 0, NULL, 'active', 0, '2026-04-07 09:39:26'),
(8, 'prinzfaina@gmail.com', 'Monje', 'Paul', '$2y$12$wyZ5OI3Vm2I0Dn4A1OVTu.SpdXdAG8IkJnnxWb0xWrJMnb5a7.IOu', 'Doctor', '2026-04-07 04:00:59', NULL, 0, NULL, 'active', 0, '2026-04-07 05:45:06'),
(11, 'shawnpogi@gmail.com', 'Garcia', 'Shawn', '$2y$12$Ywlxp8aTQf2/UGBGZDHIFuhfKRukerAhJfkhxMBxQt5EL9.FatcTy', 'Admin', '2026-04-07 05:34:35', NULL, 0, NULL, 'active', 0, '2026-04-07 05:34:35'),
(12, 'ajax_admin_69d49898ad8a7@example.com', 'Admin', 'Ajax', '$2y$12$gclo8bGBi9FOeskRFckVAegDI9TSVmY9JBDq90uglcOoM2Sbi0Agy', 'Admin', '2026-04-07 05:39:37', NULL, 0, NULL, 'active', 0, '2026-04-07 05:39:37'),
(13, 'guardian_manual_9b9115@example.com', 'Guardian9b9115', 'Flow', '$2y$12$wIE4GnGSfjEoeZkrfjxVv.YYxzSrpGpNB.M2mZx/ILffFUN0unuCK', 'Guardian', '2026-04-07 06:10:33', NULL, 0, NULL, 'active', 0, '2026-04-07 06:10:33'),
(14, 'child_manual_9b9115@example.com', 'Child9b9115', 'Flow', '$2y$12$gZMrDVaQsfi6P.nZ9keHmO6vUTGNKo52zeW.mgDwShLAD6i3DUyMC', 'Child', '2026-04-07 06:10:34', NULL, 0, NULL, 'active', 0, '2026-04-07 06:10:34'),
(15, 'guardian_flow_c81c56@example.com', 'Guardianc81c56', 'Flow', '$2y$12$lV87wvZaceyLeuAnVtpsf.whiIRFAg5W6FtUte3bsQagS1Algd/ii', 'Guardian', '2026-04-07 06:11:40', NULL, 0, NULL, 'active', 0, '2026-04-07 06:11:40'),
(16, 'child_flow_c81c56@example.com', 'Childc81c56', 'Flow', '$2y$12$zmZJSGWFpRwdvNhFgQXL5.SZ3aW8BajL4j9af7vcSk352Wpy9pNve', 'Child', '2026-04-07 06:11:40', NULL, 0, NULL, 'active', 0, '2026-04-07 06:11:40'),
(17, 'guardian_flow_d014e6@example.com', 'Guardiand014e6', 'Flow', '$2y$12$3bapMDDuFYCdhx1bLVew0OmywwzfWRzoZaiprwM/.gNI8Bnr8RTyC', 'Guardian', '2026-04-07 06:11:41', NULL, 0, NULL, 'active', 0, '2026-04-07 06:11:41'),
(18, 'child_flow_d014e6@example.com', 'Childd014e6', 'Flow', '$2y$12$vbYyOABS1emXSCEZ7v1kOeORnYPr.j.MIPff5qhXH7q7Ss5FkJu.O', 'Child', '2026-04-07 06:11:41', NULL, 0, NULL, 'active', 0, '2026-04-07 06:11:41'),
(19, 'guardian_pending_ee89f0@example.com', 'Guardianee89f0', 'Pending', '$2y$12$BGFXmfwyAQL/p1pE0q/6nOnXponLi.Zr9FI2MwkTAh7K8YteuNQxe', 'Guardian', '2026-04-07 06:15:27', NULL, 0, NULL, 'active', 0, '2026-04-07 06:15:27'),
(20, 'child_pending_ee89f0@example.com', 'Childee89f0', 'Pending', '$2y$12$N1Y61uNMYEUGLjUnD4XH8OX70nLfVe5ATabwuL8TlUN8oqtPw4CYa', 'Child', '2026-04-07 06:15:27', NULL, 0, NULL, 'active', 0, '2026-04-07 06:15:27'),
(21, 'prinzholden@gmail.com', 'holden', 'prinz', '$2y$12$fct3c0xf2MSxf091f2neBuw3kJ9KYrGwZqgsVAHU5B/gsT26r.Lky', 'Doctor', '2026-04-10 08:56:22', NULL, 0, NULL, 'active', 0, '2026-04-10 08:57:17');

-- --------------------------------------------------------

--
-- Table structure for table `virtual_pet`
--

CREATE TABLE `virtual_pet` (
  `pet_id` int(11) NOT NULL,
  `child_id` int(11) DEFAULT NULL,
  `pet_state` enum('Healthy','Good','Critical','Dead') DEFAULT NULL,
  `currency` int(11) DEFAULT 0,
  `xp_points` int(11) DEFAULT 0,
  `current_streak_days` int(11) DEFAULT 0,
  `last_streak_date` date DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_profile`
--
ALTER TABLE `admin_profile`
  ADD PRIMARY KEY (`admin_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `child_inventory`
--
ALTER TABLE `child_inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `child_profile`
--
ALTER TABLE `child_profile`
  ADD PRIMARY KEY (`child_id`),
  ADD UNIQUE KEY `login_code` (`login_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `clinician_patient_link`
--
ALTER TABLE `clinician_patient_link`
  ADD PRIMARY KEY (`link_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `doctor_profile`
--
ALTER TABLE `doctor_profile`
  ADD PRIMARY KEY (`doctor_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `eye_health_metrics`
--
ALTER TABLE `eye_health_metrics`
  ADD PRIMARY KEY (`metric_id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `eye_health_score`
--
ALTER TABLE `eye_health_score`
  ADD PRIMARY KEY (`score_id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `guardian_child_link`
--
ALTER TABLE `guardian_child_link`
  ADD PRIMARY KEY (`guardian_id`,`child_id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `guardian_profile`
--
ALTER TABLE `guardian_profile`
  ADD PRIMARY KEY (`guardian_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `prescription`
--
ALTER TABLE `prescription`
  ADD PRIMARY KEY (`recommendation_id`),
  ADD KEY `link_id` (`link_id`);

--
-- Indexes for table `session_limits`
--
ALTER TABLE `session_limits`
  ADD PRIMARY KEY (`limit_id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `virtual_pet`
--
ALTER TABLE `virtual_pet`
  ADD PRIMARY KEY (`pet_id`),
  ADD KEY `child_id` (`child_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `child_inventory`
--
ALTER TABLE `child_inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_profile`
--
ALTER TABLE `admin_profile`
  ADD CONSTRAINT `admin_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_profile` (`admin_id`);

--
-- Constraints for table `child_inventory`
--
ALTER TABLE `child_inventory`
  ADD CONSTRAINT `child_inventory_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `child_profile` (`child_id`) ON DELETE CASCADE;

--
-- Constraints for table `child_profile`
--
ALTER TABLE `child_profile`
  ADD CONSTRAINT `child_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `clinician_patient_link`
--
ALTER TABLE `clinician_patient_link`
  ADD CONSTRAINT `clinician_patient_link_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctor_profile` (`doctor_id`),
  ADD CONSTRAINT `clinician_patient_link_ibfk_2` FOREIGN KEY (`child_id`) REFERENCES `child_profile` (`child_id`);

--
-- Constraints for table `doctor_profile`
--
ALTER TABLE `doctor_profile`
  ADD CONSTRAINT `doctor_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `eye_health_metrics`
--
ALTER TABLE `eye_health_metrics`
  ADD CONSTRAINT `eye_health_metrics_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `child_profile` (`child_id`);

--
-- Constraints for table `eye_health_score`
--
ALTER TABLE `eye_health_score`
  ADD CONSTRAINT `eye_health_score_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `child_profile` (`child_id`);

--
-- Constraints for table `guardian_child_link`
--
ALTER TABLE `guardian_child_link`
  ADD CONSTRAINT `guardian_child_link_ibfk_1` FOREIGN KEY (`guardian_id`) REFERENCES `guardian_profile` (`guardian_id`),
  ADD CONSTRAINT `guardian_child_link_ibfk_2` FOREIGN KEY (`child_id`) REFERENCES `child_profile` (`child_id`);

--
-- Constraints for table `guardian_profile`
--
ALTER TABLE `guardian_profile`
  ADD CONSTRAINT `guardian_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `prescription`
--
ALTER TABLE `prescription`
  ADD CONSTRAINT `prescription_ibfk_1` FOREIGN KEY (`link_id`) REFERENCES `clinician_patient_link` (`link_id`);

--
-- Constraints for table `session_limits`
--
ALTER TABLE `session_limits`
  ADD CONSTRAINT `session_limits_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `child_profile` (`child_id`);

--
-- Constraints for table `virtual_pet`
--
ALTER TABLE `virtual_pet`
  ADD CONSTRAINT `virtual_pet_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `child_profile` (`child_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
