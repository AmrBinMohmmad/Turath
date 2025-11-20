-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 20, 2025 at 07:08 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `projects`
--

-- --------------------------------------------------------

--
-- Table structure for table `cards`
--

CREATE TABLE `cards` (
  `id` int(11) NOT NULL,
  `number_of_users` int(11) DEFAULT NULL,
  `card_name` varchar(255) DEFAULT NULL,
  `number_of_question` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cards`
--

INSERT INTO `cards` (`id`, `number_of_users`, `card_name`, `number_of_question`) VALUES
(1, 5, 'project one', 10),
(2, 30, 'project one2', 55),
(3, 5, 'project one44', 44),
(4, 6, 'project one266', 6),
(5, 6, 'project one266', 6),
(6, 6, 'project one266', 6),
(7, 6, 'project one266', 6),
(8, 6, 'project one266', 6),
(9, 6, 'project one266', 6),
(10, 6, 'project one266', 6),
(11, 6, 'project one266', 6),
(12, 6, 'project one266', 6),
(13, 6, 'project one266', 6),
(14, 22, 'project one2dw', 5),
(15, 22, 'project one2dw', 5),
(16, 22, 'project one2dw', 5),
(17, 22, 'project one2dw', 5),
(18, 22, 'project one2dw', 5),
(19, 22, 'project one2dw', 5),
(20, 22, 'project one2dw', 5),
(21, 22, 'project one2dw', 5),
(22, 22, 'project one2dw', 5),
(23, 22, 'project one2dw', 5),
(24, 22, 'project one2dw', 5),
(25, 22, 'project one2dw', 5),
(26, 22, 'project one2dw', 5),
(27, 22, 'project one2dw', 5),
(28, 22, 'project one2dw', 5),
(29, 22, 'project one2dw', 5),
(30, 22, 'project one2dw', 5),
(31, 22, 'project one2dw', 5),
(32, 22, 'project one2dw', 5),
(33, 22, 'project one2dw', 5),
(34, 22, 'project one2dw', 5),
(35, 22, 'project one2dw', 5),
(36, 22, 'project one2dw', 5),
(37, 22, 'project one2dw', 5),
(38, 22, 'project one2dw', 5),
(39, 22, 'project one2dw', 5),
(40, 22, 'project one2dw', 5),
(41, 22, 'project one2dw', 5),
(42, 22, 'project one2dw', 5),
(43, 22, 'project one2dw', 5),
(44, 22, 'project one2dw', 5),
(45, 22, 'project one2dw', 5),
(46, 22, 'project one2dw', 5),
(47, 22, 'project one2dw', 5),
(48, 22, 'project one2dw', 5),
(49, 4, 'project one44www', 244),
(50, 4, 'project one44www', 244),
(51, 4, 'project one44www', 244),
(52, 4, 'project one44www', 244),
(53, 4, 'project one44www', 244),
(54, 4, 'project one44www', 244),
(55, 4, 'project one44www', 244),
(56, 4, 'project one44www', 244),
(57, 4, 'project one44www', 244),
(58, 4, 'project one44www', 244),
(59, 4, 'project one44www', 244),
(60, 4, 'project one44www', 244),
(61, 4, 'project one44www', 244),
(62, 4, 'project one44www', 244),
(63, 4, 'project one44www', 244),
(64, 4, 'project oneggg', 5),
(65, 4, 'project oneggg', 5),
(66, 4, 'ddwa', 5),
(67, 5, 'project one2dw', 10);

-- --------------------------------------------------------

--
-- Table structure for table `cards_questions`
--

CREATE TABLE `cards_questions` (
  `card_id` int(11) NOT NULL,
  `type_Of_q` int(11) DEFAULT NULL,
  `number_of_q` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cards_questions`
--

INSERT INTO `cards_questions` (`card_id`, `type_Of_q`, `number_of_q`) VALUES
(3, NULL, NULL),
(7, NULL, NULL),
(7, NULL, NULL),
(7, NULL, NULL),
(7, NULL, NULL),
(7, NULL, NULL),
(7, NULL, NULL),
(21, NULL, NULL),
(21, NULL, NULL),
(21, NULL, NULL),
(21, NULL, NULL),
(21, NULL, NULL),
(22, NULL, NULL),
(22, NULL, NULL),
(22, NULL, NULL),
(22, NULL, NULL),
(22, NULL, NULL),
(23, NULL, NULL),
(23, NULL, NULL),
(23, NULL, NULL),
(23, NULL, NULL),
(23, NULL, NULL),
(24, NULL, NULL),
(24, NULL, NULL),
(24, NULL, NULL),
(24, NULL, NULL),
(24, NULL, NULL),
(25, NULL, NULL),
(25, NULL, NULL),
(25, NULL, NULL),
(25, NULL, NULL),
(25, NULL, NULL),
(26, NULL, NULL),
(26, NULL, NULL),
(26, NULL, NULL),
(26, NULL, NULL),
(26, NULL, NULL),
(27, NULL, NULL),
(27, NULL, NULL),
(27, NULL, NULL),
(27, NULL, NULL),
(27, NULL, NULL),
(64, 1, 1222),
(64, 1, 1602),
(65, 1, 2001),
(65, 1, 1802),
(66, 1, 491),
(67, 1, 1164),
(67, 1, 575),
(67, 1, 909),
(67, 1, 2078),
(67, 1, 699),
(67, 1, 423),
(67, 1, 1442);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cards`
--
ALTER TABLE `cards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cards_questions`
--
ALTER TABLE `cards_questions`
  ADD KEY `card_id` (`card_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cards`
--
ALTER TABLE `cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cards_questions`
--
ALTER TABLE `cards_questions`
  ADD CONSTRAINT `cards_questions_ibfk_1` FOREIGN KEY (`card_id`) REFERENCES `cards` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
