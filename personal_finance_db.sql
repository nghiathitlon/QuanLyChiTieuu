-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 03:55 AM
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
  `due_date` date NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `is_done` tinyint(1) NOT NULL DEFAULT 0,
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
(1, 5, 11, 2025, 7000000.00, '2025-11-19 17:38:09', '2025-11-25 09:31:58'),
(2, 5, 3, 2025, 3000000.00, '2025-11-20 07:56:46', '2025-11-20 19:36:35'),
(5, 4, 4, 2025, 0.00, '2025-11-20 18:36:16', '2025-11-20 18:36:16'),
(7, 5, 2, 2023, 5000000.00, '2025-11-23 07:42:49', '2025-11-23 07:42:49'),
(8, 5, 12, 2025, 7500000.00, '2025-12-01 03:38:53', '2025-12-01 03:38:53');

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
(29, 5, 'mua do ve sinh du phong', 'expense'),
(33, 5, 'bán đồ chơi', 'expense'),
(35, 5, 'bán bánh mì', 'income'),
(36, 5, 'bán bánh mì sandwich', 'expense');

-- --------------------------------------------------------

--
-- Table structure for table `financial_goals`
--

CREATE TABLE `financial_goals` (
  `goal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `goal_name` varchar(255) NOT NULL,
  `target_amount` double NOT NULL,
  `current_amount` double NOT NULL DEFAULT 0,
  `deadline` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_goals`
--

INSERT INTO `financial_goals` (`goal_id`, `user_id`, `goal_name`, `target_amount`, `current_amount`, `deadline`, `notes`, `created_at`) VALUES
(1, 5, 'để dành 1 tỉ', 1000000000, 1020000000, '2040-06-05', '', '2025-12-02 02:37:22');

-- --------------------------------------------------------

--
-- Table structure for table `goals`
--

CREATE TABLE `goals` (
  `goal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `goal_name` varchar(255) NOT NULL,
  `target_amount` decimal(15,2) NOT NULL,
  `saved_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `deadline` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `goals`
--

INSERT INTO `goals` (`goal_id`, `user_id`, `goal_name`, `target_amount`, `saved_amount`, `deadline`, `notes`, `status`, `created_at`) VALUES
(1, 5, 'để dành 1 tỉ', 1000000000.00, 0.00, '2040-05-05', 'mua xe o to', 'pending', '2025-12-02 02:50:22');

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
-- Table structure for table `reminders`
--

CREATE TABLE `reminders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `remind_date` date NOT NULL,
  `is_done` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `remind_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `reminders`
--

INSERT INTO `reminders` (`id`, `user_id`, `title`, `description`, `remind_date`, `is_done`, `created_at`, `remind_time`) VALUES
(1, 5, 'đóng tiền điện tháng 11', '2.000.000 VNĐ', '2025-11-24', 1, '2025-11-22 16:33:55', NULL),
(2, 5, 'Đóng tiền nhà tháng 11', '700.000 VNĐ', '2025-11-22', 0, '2025-11-22 16:34:48', NULL),
(3, 5, 'Đóng tiền quỹ lớp', '90.000 VNĐ', '2025-11-25', 1, '2025-11-22 16:41:46', NULL),
(4, 5, 'Đóng tiền hụi cho anh 3 chợ gà', '790.000 VNĐ', '2025-11-27', 0, '2025-11-22 16:42:19', NULL),
(6, 5, 'Đóng tiền ủng hộ mùa lũ 2025', '6.666.000VNĐ', '2025-11-24', 0, '2025-11-22 16:43:52', NULL),
(7, 5, 'đóng học phí cho con trai', '5.00.000', '2025-11-22', 1, '2025-11-22 19:49:31', NULL),
(9, 5, 'học thêm', '200.000 VNĐ', '2025-11-23', 1, '2025-11-22 19:54:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `savings`
--

CREATE TABLE `savings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `amount` bigint(20) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `savings`
--

INSERT INTO `savings` (`id`, `user_id`, `name`, `amount`, `created_at`) VALUES
(9, 5, 'Tiết kiệm tháng 11/2025', 790000, '2025-11-29 16:09:57'),
(10, 5, 'Tiết kiệm tháng 11/2025', 110000, '2025-11-29 16:17:03'),
(12, 5, 'Tiết kiệm tháng 11/2025', 100000, '2025-11-29 16:38:33'),
(13, 5, 'Tiết kiệm tháng 12/2025', 1000000, '2025-12-01 10:38:28');

-- --------------------------------------------------------

--
-- Table structure for table `savings_logs`
--

CREATE TABLE `savings_logs` (
  `log_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `date_added` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
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
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `type`, `category_id`, `note`, `amount`, `transaction_date`, `description`, `created_at`) VALUES
(3, 3, NULL, 7, NULL, 1000000.00, '2025-11-14', '', '2025-11-14 08:33:34'),
(4, 3, NULL, 5, NULL, 50000.00, '2025-11-14', '', '2025-11-14 08:34:25'),
(5, 3, NULL, 9, NULL, 200000.00, '2025-11-15', '', '2025-11-14 08:36:21'),
(6, 1, NULL, 16, NULL, 1000000.00, '2025-11-26', '', '2025-11-14 11:43:00'),
(7, 1, NULL, 14, NULL, 123000.00, '2025-11-27', '', '2025-11-14 11:43:11'),
(8, 1, NULL, 17, NULL, 123214.00, '2025-11-21', '', '2025-11-14 11:43:22'),
(17, 4, NULL, 20, NULL, 200000.00, '2025-11-19', '', '2025-11-19 11:45:04'),
(18, 4, NULL, 22, NULL, 199000.00, '2025-12-11', '', '2025-11-19 11:45:53'),
(19, 4, NULL, 21, NULL, 199000.00, '2025-11-12', '', '2025-11-19 11:46:20'),
(37, 5, NULL, 25, NULL, 10000000.00, '2025-11-01', '0', '2025-11-19 17:55:09'),
(39, 5, NULL, 24, NULL, 800000.00, '2025-11-24', '222', '2025-11-19 17:55:52'),
(42, 5, NULL, 24, NULL, 5000000.00, '2025-11-22', '0', '2025-11-19 17:57:55'),
(44, 5, NULL, 25, NULL, 3000000.00, '2025-11-05', '0', '2025-11-20 06:52:26'),
(45, 5, NULL, 25, NULL, 1000000.00, '2025-11-05', '0', '2025-11-20 07:14:47'),
(47, 5, NULL, 29, NULL, 300000.00, '2025-11-25', '0', '2025-11-22 19:44:45'),
(50, 5, NULL, 25, NULL, 1000000.00, '2025-11-25', 'ban 10 o banh mi ', '2025-11-29 10:21:07'),
(52, 5, NULL, 25, NULL, 10000000.00, '2025-12-02', 'ban chiec sh150i', '2025-12-01 03:36:23'),
(53, 5, NULL, 24, NULL, 2500000.00, '2025-12-01', 'di choi voi em ghe 1m7', '2025-12-01 03:38:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `avatar` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expire` datetime DEFAULT NULL,
  `reset_expire` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `username`, `password_hash`, `created_at`, `avatar`, `reset_token`, `token_expire`, `reset_expire`) VALUES
(1, 'jamesmoccua159@gmail.com', '', '64131472', '$2y$10$yswltJrLlbAMBFLhC9WT..CZ1/ohAwS21uhVKHir8kVNDSYXi0KJC', '2025-11-14 06:38:36', NULL, NULL, NULL, NULL),
(2, '123@gmail.com', '', '123', '$2y$10$wp/.7Y8c13v8B3V8ug/rB.GigYbqatezXCrUTQvNeIPIzpJ4.GkVC', '2025-11-14 08:03:29', NULL, NULL, NULL, NULL),
(3, 'teobuong89@gmail.com', '', 'Tèo', '$2y$10$Qtbqn0PaaG7tJy9iGeP.VuTHY7S.KKGlC/CzWInnSXzWxTty6vhWe', '2025-11-14 08:30:35', NULL, NULL, NULL, NULL),
(4, 'orionkid93@gmail.com', '', '64132410', '$2y$10$XTK56.Hm0QBkGCY7lLGxiuE4OtlerX3oP7VhAVVa5A0FyYJt4x/XG', '2025-11-19 10:45:10', '1764555114_1763995766_meme-meo.webp', NULL, NULL, NULL),
(5, 'thuan.vln.64cntt@ntu.edu.vn', '$2y$10$iKoq6CvtwfuqiVhhuD3Zieq6GfxxACgJ.PKDEQfs0K.IWcMgCLXLG', 'Thuann ne', '123', '2025-11-19 14:04:09', '1764560124_OIP.webp', NULL, NULL, '2025-11-29 09:51:21'),
(6, 'tho.nh.64cntt@ntu.edu.vn', '', 'huutho', '$2y$10$bHU43Yv2q.IcUhDxCJxVduSwAoOpi4Lytz7uEhOA32mmCz.9nFwA6', '2025-11-30 10:25:27', NULL, 'bff78ebf337b460c632c37cb7a78998a99ef11ce923445111e0499ebf536fda6', '2025-12-01 04:12:19', NULL),
(7, 'tiembanhcanh@gmail.com', '', 'Nguyễn Hữu Thọ', '$2y$10$Fa1vPeOomYz9DkjEEUOYIuiF85exezs/v/Mc8lHRIRHUWMwo7EjbG', '2025-11-30 12:05:45', NULL, '8f5804d3bc9b3a8408e4dd99faea3d7425d7285e7e4d3ccd2ec36d3dcdb26411', '2025-11-30 13:42:01', NULL),
(11, 'aido@gmail.com', '$2y$10$hpQnSePKCORwBJsYY.QyCuHloSv7PaTkspficC.mNEM0B.ewJGal2', 'ai do', '', '2025-12-02 02:14:10', NULL, NULL, NULL, NULL),
(13, 'orionkid144@gmail.com', '$2y$10$7qih3XcVzGNeI3e99dIyxey.wJM05aYkPqB0en1715QP0Am0lBNyK', 'toi la ai', '', '2025-12-02 02:17:39', NULL, NULL, NULL, NULL);

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
-- Indexes for table `financial_goals`
--
ALTER TABLE `financial_goals`
  ADD PRIMARY KEY (`goal_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `goals`
--
ALTER TABLE `goals`
  ADD PRIMARY KEY (`goal_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `monthlybudget`
--
ALTER TABLE `monthlybudget`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unq_budget` (`user_id`,`month`,`year`);

--
-- Indexes for table `reminders`
--
ALTER TABLE `reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `savings`
--
ALTER TABLE `savings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `savings_logs`
--
ALTER TABLE `savings_logs`
  ADD PRIMARY KEY (`log_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `financial_goals`
--
ALTER TABLE `financial_goals`
  MODIFY `goal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `goals`
--
ALTER TABLE `goals`
  MODIFY `goal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `monthlybudget`
--
ALTER TABLE `monthlybudget`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reminders`
--
ALTER TABLE `reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `savings`
--
ALTER TABLE `savings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `savings_logs`
--
ALTER TABLE `savings_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
-- Constraints for table `financial_goals`
--
ALTER TABLE `financial_goals`
  ADD CONSTRAINT `financial_goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `goals`
--
ALTER TABLE `goals`
  ADD CONSTRAINT `goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reminders`
--
ALTER TABLE `reminders`
  ADD CONSTRAINT `reminders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

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
