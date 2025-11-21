-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 11:57 AM
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
-- Database: `personal_finance_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `budget`
--

CREATE TABLE `budget` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `month` tinyint(4) NOT NULL,
  `year` smallint(6) NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget`
--

INSERT INTO `budget` (`id`, `user_id`, `month`, `year`, `amount`, `created_at`, `updated_at`) VALUES
(1, 5, 11, 2025, 3000000.00, '2025-11-19 17:38:09', '2025-11-19 17:45:10'),
(2, 5, 3, 2025, 3000000.00, '2025-11-20 07:56:46', '2025-11-20 19:36:35'),
(5, 4, 4, 2025, 0.00, '2025-11-20 18:36:16', '2025-11-20 18:36:16');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(10) NOT NULL CHECK (`type` in ('income','expense'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `user_id`, `name`, `type`) VALUES
(5, 3, 'Đi lại', 'expense'),
(7, 3, 'Lương', 'income'),
(8, 3, 'Ăn uống', 'expense'),
(9, 3, 'Nhậu , cf', 'expense'),
(14, 1, 'đi chợ', 'expense'),
(16, 1, 'lương', 'income'),
(17, 1, 'di chuyển', 'expense'),
(20, 4, 'an toi', 'income'),
(21, 4, 'an trua', 'expense'),
(22, 4, 'an chieu', 'expense'),
(24, 5, 'di choi', 'expense'),
(25, 5, 'ban xe', 'income'),
(26, 5, 'an toi', 'expense');

-- --------------------------------------------------------

--
-- Table structure for table `monthlybudget`
--

CREATE TABLE `monthlybudget` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `budget_amount` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('income','expense') DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `type`, `category_id`, `amount`, `transaction_date`, `description`, `created_at`) VALUES
(3, 3, NULL, 7, 1000000.00, '2025-11-14', '', '2025-11-14 08:33:34'),
(4, 3, NULL, 5, 50000.00, '2025-11-14', '', '2025-11-14 08:34:25'),
(5, 3, NULL, 9, 200000.00, '2025-11-15', '', '2025-11-14 08:36:21'),
(6, 1, NULL, 16, 1000000.00, '2025-11-26', '', '2025-11-14 11:43:00'),
(7, 1, NULL, 14, 123000.00, '2025-11-27', '', '2025-11-14 11:43:11'),
(8, 1, NULL, 17, 123214.00, '2025-11-21', '', '2025-11-14 11:43:22'),
(17, 4, NULL, 20, 200000.00, '2025-11-19', '', '2025-11-19 11:45:04'),
(18, 4, NULL, 22, 199000.00, '2025-12-11', '', '2025-11-19 11:45:53'),
(19, 4, NULL, 21, 199000.00, '2025-11-12', '', '2025-11-19 11:46:20'),
(37, 5, NULL, 25, 10000000.00, '2025-11-01', 'lanh luong', '2025-11-19 17:55:09'),
(39, 5, NULL, 24, 1100000.00, '2025-11-24', '', '2025-11-19 17:55:52'),
(42, 5, NULL, 24, 800000.00, '2025-11-20', '', '2025-11-19 17:57:55'),
(43, 5, NULL, 26, 1000000.00, '2025-11-20', '', '2025-11-19 17:59:17'),
(44, 5, NULL, 25, 5000000.00, '2025-11-05', '', '2025-11-20 06:52:26'),
(45, 5, NULL, 25, 100000.00, '2025-11-05', '', '2025-11-20 07:14:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `username`, `password_hash`, `created_at`) VALUES
(1, 'jamesmoccua159@gmail.com', '64131472', '$2y$10$yswltJrLlbAMBFLhC9WT..CZ1/ohAwS21uhVKHir8kVNDSYXi0KJC', '2025-11-14 06:38:36'),
(2, '123@gmail.com', '123', '$2y$10$wp/.7Y8c13v8B3V8ug/rB.GigYbqatezXCrUTQvNeIPIzpJ4.GkVC', '2025-11-14 08:03:29'),
(3, 'teobuong89@gmail.com', 'Tèo', '$2y$10$Qtbqn0PaaG7tJy9iGeP.VuTHY7S.KKGlC/CzWInnSXzWxTty6vhWe', '2025-11-14 08:30:35'),
(4, 'orionkid93@gmail.com', '64132410', '$2y$10$XTK56.Hm0QBkGCY7lLGxiuE4OtlerX3oP7VhAVVa5A0FyYJt4x/XG', '2025-11-19 10:45:10'),
(5, 'thuan.vln.64cntt@ntu.edu.vn', 'Thuan', '$2y$10$QTZN6jF4TLwNGN5DpNaiRuMCmSqVXG61gNN5dLQxVfynVciRsTq6O', '2025-11-19 14:04:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `budget`
--
ALTER TABLE `budget`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_month_year` (`user_id`,`month`,`year`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `monthlybudget`
--
ALTER TABLE `monthlybudget`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unq_budget` (`user_id`,`month`,`year`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `budget`
--
ALTER TABLE `budget`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `monthlybudget`
--
ALTER TABLE `monthlybudget`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alerts`
--
ALTER TABLE `alerts`
  ADD CONSTRAINT `alerts_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `budget`
--
ALTER TABLE `budget`
  ADD CONSTRAINT `budget_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
