-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 29, 2025 at 11:37 AM
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
-- Database: `users_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'ali', 'aa@gam.com', '$2y$10$j9p3PZcuXEnVVBNNzkiL9.tcqnQ3LFLpHQtikw7swvuw5A3.xX1DG', 'user'),
(2, 'sead', 's@gmail.com', '$2y$10$Mb0i7.0sBrC.msiXzLisouLMH0Dpfl7kvbDA3UAXnq7YiB/gJGhVe', 'user'),
(4, 'SS', 'SS@gmail.com', '$2y$10$F.Jlt4O5FFKuPsVpsLzgzuwEDrZllFysOcW3XD8HD1nm6Et23g352', 'admin'),
(5, 'ali', 'f@gmail.com', '$2y$10$yEbqNvNUt31A63quYcUuy.mKZrpiK5/rWRBmIfZhD7OnNuAvF1F2u', 'user'),
(6, '22', '22@dd', '$2y$10$58bTivz.logciwgX4Gp6N.DObrs.vqcQZISIW2WZjDVR07lCeD5Si', 'user'),
(7, 'SS', 'fww3fww3@ga', '$2y$10$oGvBLBH69OCFN0NAg3RSLugxkvgp7Iq7CtFgFI5z7bJBz2nHbAF16', 'user'),
(8, 'dad', 'wdwdd@sd', '$2y$10$cpMQ3Qa9zY7cHVFHD6tQ1O9BzXQj95tQ71uaCfqeP5gbVzHCHk22K', 'user'),
(9, 'a', 'a@a', '$2y$10$OJnl8DSLBMw5RaozaFy4LeXfvm7talDJ1Ux0RrBhhQLrIJxqdg0p2', 'admin'),
(10, 'ali', 'd@d', '$2y$10$IRoEloA9w4/FlqE.0LRL2./6Sm/i/IetkHIJOsJ6Y/AuGKnccszZa', 'user'),
(11, 'wdd', 'dwd@ws', '$2y$10$VjF0OF.DrWeQv5x/ru5TFuqM3A8KIrjOlHJ8sbu1EIZQlO8ckx1V.', 'user'),
(12, 'dwd', '2dd@sd', '$2y$10$OIoyjtQBLK2dsEUprDWPGOXikh7mRNJ3elxpHzgCKYr74zCl05g/S', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
