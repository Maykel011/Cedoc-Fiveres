-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 14, 2025 at 02:55 AM
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
(3, 'Charlie', 'Medical Standby', 'Brixter Luquing', 'Kian Visaya', 'san juan pinaglabanan', '2025-03-20 16:10:00', '2025-03-20 18:00:00', 'uploads/Screenshot 2025-04-03 205923.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `vehicle_runs`
--
ALTER TABLE `vehicle_runs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `vehicle_runs`
--
ALTER TABLE `vehicle_runs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
