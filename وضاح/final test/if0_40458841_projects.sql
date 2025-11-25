-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql206.byetcluster.com
-- Generation Time: Nov 25, 2025 at 05:44 PM
-- Server version: 11.4.7-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_40458841_projects`
--

-- --------------------------------------------------------

--
-- Table structure for table `annotations`
--

CREATE TABLE `annotations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `answer` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `score` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `annotations`
--

INSERT INTO `annotations` (`id`, `user_id`, `question_id`, `project_id`, `answer`, `created_at`, `score`) VALUES
(1, 1, 419, 1, 'د) كيف حالك؟', '2025-11-25 20:05:16', 0),
(2, 1, 248, 1, 'B) القي', '2025-11-25 20:05:31', 1),
(3, 1, 460, 1, 'ج) المعنى ياانت', '2025-11-25 20:05:41', 1),
(4, 1, 154, 1, 'A) عندما تتحقق المستحيلات.', '2025-11-25 20:05:56', 1),
(5, 1, 416, 1, 'ب) فرح وتقبل', '2025-11-25 20:06:16', 1),
(6, 1, 428, 1, 'ب) ما هاذا', '2025-11-25 20:06:26', 1),
(7, 1, 442, 1, 'د) تعبير عن الصبر من كثرة الإعادة', '2025-11-25 20:06:33', 1),
(8, 1, 222, 1, 'أ) شخص يسهر كثيرًا خارج البيت', '2025-11-25 20:06:45', 1),
(9, 1, 321, 1, 'أ) المشاكل تتزايد مع محاولة التقدم', '2025-11-25 20:06:53', 1),
(10, 1, 242, 1, 'B) حزن', '2025-11-25 20:07:01', 1),
(11, 1, 190, 2, 'أ) بمعنى ذهب أو راح', '2025-11-25 21:25:46', NULL),
(12, 1, 271, 2, 'أ) تعني أنني أقوم بخدمة الضيوف', '2025-11-25 21:25:55', NULL),
(13, 1, 7, 2, 'A) لقب لشخص ضعيف', '2025-11-25 21:26:00', NULL);

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
(1, 2, 'test', 10),
(2, 6, 'test2', 3);

-- --------------------------------------------------------

--
-- Table structure for table `cards_questions`
--

CREATE TABLE `cards_questions` (
  `id` int(11) NOT NULL,
  `card_id` int(11) NOT NULL,
  `type_Of_q` int(11) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cards_questions`
--

INSERT INTO `cards_questions` (`id`, `card_id`, `type_Of_q`, `question_id`) VALUES
(1, 1, NULL, 419),
(2, 1, NULL, 248),
(3, 1, NULL, 460),
(4, 1, NULL, 154),
(5, 1, NULL, 416),
(6, 1, NULL, 428),
(7, 1, NULL, 442),
(8, 1, NULL, 222),
(9, 1, NULL, 321),
(10, 1, NULL, 242),
(11, 2, NULL, 190),
(12, 2, NULL, 271),
(13, 2, NULL, 7);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `annotations`
--
ALTER TABLE `annotations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `cards`
--
ALTER TABLE `cards`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Indexes for table `cards_questions`
--
ALTER TABLE `cards_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `card_id` (`card_id`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `annotations`
--
ALTER TABLE `annotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `cards`
--
ALTER TABLE `cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cards_questions`
--
ALTER TABLE `cards_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
