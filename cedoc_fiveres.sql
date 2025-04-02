-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 02, 2025 at 12:49 AM
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
(200, 'hto.png', 'image/png', 'Sample', NULL, NULL, 14, '2025-03-28 12:48:19', '2025-03-29 03:41:34'),
(201, 'news.mp4', 'video/mp4', 'Sample', NULL, NULL, NULL, '2025-03-28 12:49:53', '2025-03-28 12:49:53'),
(202, 'BSIT_MCO_Montes_John Michael.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Sample', NULL, NULL, NULL, '2025-03-28 12:50:10', '2025-03-28 12:50:10'),
(203, 'OJT-OT-Work-Request March 8.pdf', 'application/pdf', 'Sample', NULL, NULL, NULL, '2025-03-28 13:14:10', '2025-03-28 13:14:10');

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
(40, 'Sample', 4, '2025-03-28 13:14:10');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT for table `media_folders`
--
ALTER TABLE `media_folders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
