-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 10, 2025 at 03:31 AM
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
) ;

--
-- Dumping data for table `applicants`
--

INSERT INTO `applicants` (`id`, `full_name`, `program_course`, `school_university`, `other_school`, `address`, `contact_number`, `email`, `ojt_hours`, `roles`, `resume_path`, `moa_path`, `recom_path`, `q1`, `q2`, `q3`, `q4`, `q5`, `application_date`, `status`, `notes`) VALUES
(1, 'Brixter Luquing', 'BSIT', 'JRU', NULL, 'Mandaluyong City', '09384534477', 'biksterlooking@gmail.com', 486, 'Full Stack', 'uploads/1743755005_Term_End_Self-assessment_and_Evaluation.docx', 'uploads/1743755005_SONG-CHORDS_ARRANGEMENT-for-March-30-2025__1_.pdf', 'uploads/1743755005_WAR_Montes_March3_March7.docx.pdf', '1232134 r1ff1tg', 'fg2edwf', 'sdfsdfgsdf', 'sdfs', 'zxczxcvWEFWE', '2025-04-04 08:23:25', 'Pending', NULL);

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
);

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id`, `file_name`, `file_type`, `folder_name`, `temperature`, `water_level`, `air_quality`, `date_uploaded`, `date_modified`) VALUES
(206, 'WAR_Montes_March3_March7.docx.pdf', 'application/pdf', 'sample', NULL, NULL, NULL, '2025-04-03 04:05:39', '2025-04-03 04:05:39'),
(207, 'news (1).mp4', 'video/mp4', 'sample', NULL, NULL, NULL, '2025-04-03 07:17:42', '2025-04-03 07:17:42'),
(208, 'BSIT_MCO_Montes_John Michael.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'sample', NULL, NULL, NULL, '2025-04-03 07:18:42', '2025-04-03 07:18:42'),
(209, 'TESE_MONTES JOHN MICHAEL.jpg', 'image/jpeg', 'sample', NULL, NULL, NULL, '2025-04-03 07:37:48', '2025-04-03 07:37:48');

-- --------------------------------------------------------

--
-- Table structure for table `media_folders`
--

CREATE TABLE `media_folders` (
  `id` int NOT NULL,
  `folder_name` varchar(255) NOT NULL,
  `num_contents` int NOT NULL DEFAULT '0',
  `date_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

--
-- Dumping data for table `media_folders`
--

INSERT INTO `media_folders` (`id`, `folder_name`, `num_contents`, `date_modified`) VALUES
(63, 'sample', 4, '2025-04-03 07:37:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `employee_no` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `position` enum('Head','Supervisor','Employee') NOT NULL,
  `role` enum('Admin','User','Super admin') NOT NULL DEFAULT 'User',
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `pin_code` varchar(6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `failed_attempts` int NOT NULL DEFAULT '0',
  `locked_until` datetime DEFAULT NULL
<<<<<<< HEAD
) ;
=======
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
>>>>>>> ee2dfdeff4ac59a62955ecff7a6f996bf544064e

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `employee_no`, `first_name`, `last_name`, `position`, `role`, `email`, `password`, `pin_code`, `created_at`, `updated_at`, `failed_attempts`, `locked_until`) VALUES
(8, 'MZ-5062', 'Arthur Nathan', 'Alcantara', 'Supervisor', 'Admin', 'nathantwotoy@gmail.com', '$2y$10$gyLSpDU.f8lgLefC7L6t.e36.jMaGxeSmIO6M4XRYkE2a6WknrHdi', '111222', '2025-04-02 07:50:28', '2025-04-08 06:24:09', 5, '2025-04-08 06:44:09'),
(10, 'MZ-5063', 'brixter', 'luquing', 'Supervisor', 'Admin', 'biksterlooking@gmail.com', '$2y$10$DVDKTjp/aHI636sp.xwI2O6QqkxJVbCD/zCIYc3GYYnbAVF8sGVfC', '123456', '2025-04-08 05:22:49', '2025-04-08 06:02:13', 4, '2025-04-08 06:32:13'),
(11, 'MZ-5064', 'brixter1', 'luquing1', 'Supervisor', 'Admin', 'biksterlooking1@gmail.com', '$2y$10$HJMO81X8vH5L0ZxzaMUJ2ePeHULB.l77dMPd0PzDoq/71qJo79.WG', '123456', '2025-04-08 06:01:16', '2025-04-10 03:31:01', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_runs`
--

CREATE TABLE `vehicle_runs` (
  `id` int NOT NULL,
  `vehicle_team` varchar(255) NOT NULL,
  `case_type` varchar(255) NOT NULL,
  `transport_officer` varchar(255) NOT NULL,
  `emergency_responders` varchar(255) NOT NULL,
  `location` text NOT NULL,
  `dispatch_time` datetime NOT NULL,
  `back_to_base_time` datetime NOT NULL,
  `case_image` varchar(255) NOT NULL
);

--
-- Dumping data for table `vehicle_runs`
--

INSERT INTO `vehicle_runs` (`id`, `vehicle_team`, `case_type`, `transport_officer`, `emergency_responders`, `location`, `dispatch_time`, `back_to_base_time`, `case_image`) VALUES
(1, 'Alpha', 'Medical Case', 'Danielito Bernardo', 'George Villanueva', 'San Juan Elementary School', '2025-03-11 12:18:00', '2025-03-11 13:20:00', 'uploads/Air quality.png'),
(2, 'Alpha', 'Medical Case', 'Michael Montes', 'David Odvina', 'FilOil', '2025-03-20 15:00:00', '2025-03-22 15:00:00', 'uploads/preview.png'),
(3, 'Charlie', 'Medical Standby', 'Brixter Luquing', 'Kian Visaya', 'san juan pinaglabanan', '2025-03-20 16:10:00', '2025-03-20 18:00:00', 'uploads/Screenshot 2025-04-03 205923.png');

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=210;

--
-- AUTO_INCREMENT for table `media_folders`
--
ALTER TABLE `media_folders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `vehicle_runs`
--
ALTER TABLE `vehicle_runs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
