-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 21, 2025 at 03:55 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cedoc_fiveres`
--

-- --------------------------------------------------------

--
-- Table structure for table `applicants`
--

CREATE TABLE `applicants` (
  `id` int NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `program_course` varchar(100) NOT NULL,
  `school_university` varchar(100) NOT NULL,
  `other_school` varchar(100) DEFAULT NULL,
  `address` text NOT NULL,
  `contact_number` varchar(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `ojt_hours` int NOT NULL,
  `roles` varchar(50) NOT NULL,
  `resume_path` varchar(255) NOT NULL,
  `moa_path` varchar(255) DEFAULT NULL,
  `recom_path` varchar(255) DEFAULT NULL,
  `q1` text NOT NULL,
  `q2` text NOT NULL,
  `q3` text NOT NULL,
  `q4` text NOT NULL,
  `q5` text NOT NULL,
  `application_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Pending','Under Review','Accepted','Rejected') DEFAULT 'Pending',
  `notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `folder_name` varchar(255) NOT NULL,
  `temperature` varchar(50) DEFAULT NULL,
  `water_level` float DEFAULT NULL,
  `air_quality` float DEFAULT NULL,
  `date_uploaded` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id`, `file_name`, `file_type`, `folder_name`, `temperature`, `water_level`, `air_quality`, `date_uploaded`, `date_modified`) VALUES
(213, 'medical case.png', 'image/png', 'sample', NULL, NULL, NULL, '2025-04-21 01:55:05', '2025-04-21 01:55:05');

-- --------------------------------------------------------

--
-- Table structure for table `media_folders`
--

CREATE TABLE `media_folders` (
  `id` int NOT NULL,
  `folder_name` varchar(255) NOT NULL,
  `num_contents` int NOT NULL DEFAULT '0',
  `date_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `media_folders`
--

INSERT INTO `media_folders` (`id`, `folder_name`, `num_contents`, `date_modified`) VALUES
(79, 'sample', 1, '2025-04-21 01:55:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `employee_no` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `role` enum('Admin','User','Super Admin') NOT NULL DEFAULT 'User',
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `pin_code` varchar(6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `failed_attempts` int NOT NULL DEFAULT '0',
  `locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `employee_no`, `first_name`, `last_name`, `position`, `role`, `email`, `password`, `pin_code`, `created_at`, `updated_at`, `failed_attempts`, `locked_until`) VALUES
(8, 'MZ-5062', 'Arthur Nathan', 'Alcantara', 'Head', 'Admin', 'nathantwotoy@gmail.com', '123', '111222', '2025-04-02 07:50:28', '2025-04-20 07:19:51', 0, NULL),
(10, 'MZ-5063', 'brixter', 'luquing', 'Supervisor', 'Admin', 'biksterlooking@gmail.com', 'Brixter123', '123456', '2025-04-08 05:22:49', '2025-04-21 02:16:14', 0, NULL),
(16, 'SA001', 'John Michael', 'Montes', 'System Admin', 'Super Admin', 'montes.johnmichael@yahoo.com', '12345', '123456', '2025-04-17 07:46:01', '2025-04-21 00:59:52', 0, NULL),
(21, 'JRU-102', 'Kian', 'Visaya', 'Employee', 'User', 'Visaya@gmail.com', '123', '', '2025-04-20 11:58:29', '2025-04-20 11:58:29', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_runs`
--

CREATE TABLE `vehicle_runs` (
  `id` int NOT NULL,
  `vehicle_team` enum('Alpha','Bravo','Charlie','Delta') NOT NULL,
  `case_type` enum('Fire Case','Medical Case','Medical Standby','Trauma Case','Patient Transportation') NOT NULL,
  `transport_officer` varchar(255) DEFAULT NULL,
  `emergency_responders` text NOT NULL,
  `location` text NOT NULL,
  `dispatch_time` datetime NOT NULL,
  `back_to_base_time` datetime NOT NULL,
  `case_image` varchar(255) DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applicants`
--
ALTER TABLE `applicants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media_folders`
--
ALTER TABLE `media_folders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folder_name` (`folder_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_no` (`employee_no`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicle_runs`
--
ALTER TABLE `vehicle_runs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applicants`
--
ALTER TABLE `applicants`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=214;

--
-- AUTO_INCREMENT for table `media_folders`
--
ALTER TABLE `media_folders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `vehicle_runs`
--
ALTER TABLE `vehicle_runs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
