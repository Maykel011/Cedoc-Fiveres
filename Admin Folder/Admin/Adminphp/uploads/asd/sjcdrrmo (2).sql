-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 26, 2025 at 01:43 AM
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
-- Database: `sjcdrrmo`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `user` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin') NOT NULL DEFAULT 'admin',
  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `user`, `email`, `password`, `role`, `date_created`) VALUES
(1, 'admin', 'admin@admin.com', '$2y$10$Au/Obwk2hgvotybmyLINkeQlHoUpJFnJ1i09s3C3MHRll7A4bsRDy', 'admin', '2025-03-06 02:52:32');

-- --------------------------------------------------------

--
-- Table structure for table `admin_pin`
--

CREATE TABLE `admin_pin` (
  `id` int NOT NULL,
  `pin_code` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin_pin`
--

INSERT INTO `admin_pin` (`id`, `pin_code`) VALUES
(1, '$2y$10$twkp959N9A4euAsDXOfpk.0EWDnm3WLe0ci.0QZeVEjLbtYT.ZQ02'),
(2, '$2y$10$GmRTk7I8VF6HJ7R8yCRePOgWvUVt3L8xm.mvZA1KnTpQFbi7nRxcm'),
(3, '$2y$10$xvFnKUoZVmHw2DJTkFveZe0pWKc/ro8ccmNaiRdQACSYrWHKa8oBC'),
(4, '$2y$10$q4yoYkiLM3NW5yQ.Sc69p.fni7zNXN5Z2841AfHUZnIVvp6NnlIIy'),
(5, '$2y$10$iiT9WbjpuJfJ6XLljGCUx.YZI.fdc9QZ1RuJw8hf4j.B7bH4rkNBW'),
(6, '$2y$10$YUdtL9rR7oxLejLSkuwSo.0ey7iSEMrbYzmgcq95FUM34IpPeSkD.'),
(7, '$2y$10$jSEW8Oy8Q3u4ZVr8MeGcYeCdz2rql0g2IC0Ie8N1uxVzpajIUCl4u'),
(8, '$2y$10$Ss3WkVKPj7V7rw/l1sPaUeLyJOqRVJjLD0CCRRNZYQj7quk8aTNiW'),
(9, '$2y$10$KjYwxjxFSglVuQnK/gMUseKqsdfLkuQM9O4l0mWwOAJQyuoCViNT.'),
(10, '$2y$10$nXsTdvzu5FHPaYpr8WAwk.jqpVAWoYGBlAjSp.qlRLDnEP4D4a2j2'),
(11, '$2y$10$hs/1GHBgtZae5RbbEWbC6.31PkrtFtpNK6t/2RilStg68jawUIwSC'),
(12, '$2y$10$/pjUOIh6s0bHALhpN8GiFuSl7By6AvZrGmco0bDKTVQdEOL2Ta5Mi'),
(13, '$2y$10$Z43zBD2ilv.M7ckRoOEeFOi/sJghXtAsiiDz7R1NJTmyGuDd0px9q'),
(14, '$2y$10$eBLbU8rUptrdNHA4CgtkTua8JcKuDDutu6dfPeJAbtR8NP3KLOCIC'),
(15, '$2y$10$dJWNEUyUIrhoO9.F7g5ZXur9vvXamztbcP0G14bHxvLZQxxqvyNda'),
(16, '$2y$10$PGnoqf2LNjatkevGy0SoReBeknBUBsZ/eeEHoFdE8i0NCfhcoFCL2'),
(17, '$2y$10$aL0XYRQhrfpDPtQ.7Gs9cuz2qcPJoumkMcei9kIGCD0K36MXrV1WW'),
(18, '$2y$10$cPIFQ74.iogTwCzV6cF41eVAlz7yfG.xNqb6u0ZF4dJFQBWuNslFe'),
(19, '$2y$10$VHcG4rYPqXfwqy0ERePIbuZ3owQI/PD1xTAn1JOUgl4vPN0FWF4.K'),
(20, '$2y$10$sPfXC2t.HKaoXHqJskru4eZcLi3Vgg7xgKTufeXUiwPCELJQ4hase');

-- --------------------------------------------------------

--
-- Table structure for table `folders`
--

CREATE TABLE `folders` (
  `id` int NOT NULL,
  `folder_name` varchar(255) NOT NULL,
  `date_modified` varchar(25) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `number_of_contents` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `folders`
--

INSERT INTO `folders` (`id`, `folder_name`, `date_modified`, `type`, `number_of_contents`) VALUES
(20, 'Heat Index', '2025-03-01 04:12:42 PM', 'Folder', 0),
(27, 'Testing', '2025-03-07 10:50:57 AM', 'Folder', 0),
(29, 'testting 1', '2025-03-19 09:01:24 PM', 'Folder', 0);

-- --------------------------------------------------------

--
-- Table structure for table `folder_content`
--

CREATE TABLE `folder_content` (
  `id` int NOT NULL,
  `folder_name` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `date_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `file_type` varchar(10) DEFAULT NULL,
  `temperature` varchar(50) DEFAULT 'N/A',
  `water_level` float DEFAULT NULL,
  `air_quality` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `folder_content`
--

INSERT INTO `folder_content` (`id`, `folder_name`, `file_name`, `date_modified`, `file_type`, `temperature`, `water_level`, `air_quality`) VALUES
(245, 'Testing', 'waterlevel.png', '2025-03-15 01:55:01', 'png', NULL, 10.52, NULL),
(247, 'Testing', 'Air quality.png', '2025-03-15 01:55:01', 'png', NULL, NULL, 12),
(248, 'Testing', 'heat index 35.jpg', '2025-03-15 01:55:01', 'jpg', '35', 0, NULL),
(251, 'Uncategorized', 'notification-bell.png', '2025-03-19 13:00:27', 'png', NULL, NULL, NULL),
(252, 'testting 1', 'notification-bell.png', '2025-03-25 06:55:15', 'png', '2', NULL, NULL),
(253, 'Testing', 'preview.png', '2025-03-20 08:11:45', 'png', NULL, 11.7, NULL),
(254, 'Uncategorized', 'preview.png', '2025-03-25 06:37:20', 'png', NULL, NULL, NULL),
(255, 'Uncategorized', 'preview.png', '2025-03-25 06:51:00', 'png', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `personnel`
--

CREATE TABLE `personnel` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `vehicle_runs`
--

INSERT INTO `vehicle_runs` (`id`, `vehicle_team`, `case_type`, `transport_officer`, `emergency_responders`, `location`, `dispatch_time`, `back_to_base_time`, `case_image`) VALUES
(1, 'Alpha', 'Medical Case', 'Danielito Bernardo', 'George Villanueva', 'San Juan Elementary School', '2025-03-11 12:18:00', '2025-03-11 13:20:00', 'uploads/Air quality.png'),
(2, 'Alpha', 'Medical Case', 'Michael Montes', 'David Odvina', 'FilOil', '2025-03-20 15:00:00', '2025-03-22 15:00:00', 'uploads/preview.png'),
(3, 'Charlie', 'Medical Standby', 'Brixter Luquing', 'Kian Visaya', 'san juan pinaglabanan', '2025-03-20 16:10:00', '2025-03-20 18:00:00', 'uploads/notification-bell.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user` (`user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_pin`
--
ALTER TABLE `admin_pin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `folders`
--
ALTER TABLE `folders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folder_name` (`folder_name`);

--
-- Indexes for table `folder_content`
--
ALTER TABLE `folder_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `personnel`
--
ALTER TABLE `personnel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
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
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_pin`
--
ALTER TABLE `admin_pin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `folders`
--
ALTER TABLE `folders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `folder_content`
--
ALTER TABLE `folder_content`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=256;

--
-- AUTO_INCREMENT for table `personnel`
--
ALTER TABLE `personnel`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicle_runs`
--
ALTER TABLE `vehicle_runs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
