-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2024 at 07:21 PM
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
-- Database: `divisionexpo`
--

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `logo_path`) VALUES
(1, 'Kalashnikov Concern', 'logo/67164ddda3e88.png'),
(2, 'Wakanda', 'logo/6717a22f3e51e.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date_time` datetime NOT NULL,
  `country` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `max_visitors` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('open','closed','cancelled') NOT NULL DEFAULT 'open',
  `event_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `date_time`, `country`, `location`, `max_visitors`, `created_at`, `status`, `event_image`) VALUES
(3, 'Russia Firearms Expo', 'Russian Firearms Expo in Moscow. Starts at 10th of November 2024 10:00 AM Feat. Kalashnikov Concern Ltd', '2024-11-10 10:00:00', 'Russian Federation', 'Moscow', 90, '2024-10-21 12:55:26', 'open', NULL),
(4, 'Wakanda x Russia', 'Test', '2024-10-31 20:01:00', 'Wakanda', 'Wakanda Palace', 20, '2024-10-22 13:02:27', 'open', 'event_images/6717a263d3e51.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `event_companies`
--

CREATE TABLE `event_companies` (
  `event_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_companies`
--

INSERT INTO `event_companies` (`event_id`, `company_id`) VALUES
(3, 1),
(4, 1),
(4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`id`, `user_id`, `event_id`, `registration_date`) VALUES
(5, 1, 3, '2024-10-22 11:30:25'),
(7, 4, 4, '2024-10-22 13:04:08'),
(8, 4, 3, '2024-10-22 13:04:13'),
(9, 7, 4, '2024-10-24 03:43:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `profile_completed` tinyint(1) DEFAULT 0,
  `failed_login_attempts` int(11) DEFAULT 0,
  `lockout_time` datetime DEFAULT NULL,
  `recovery_question` varchar(255) NOT NULL,
  `recovery_answer` varchar(255) NOT NULL,
  `recovery_question_2` varchar(255) DEFAULT NULL,
  `recovery_answer_2` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `is_admin`, `first_name`, `last_name`, `phone_number`, `country`, `profile_completed`, `failed_login_attempts`, `lockout_time`, `recovery_question`, `recovery_answer`, `recovery_question_2`, `recovery_answer_2`) VALUES
(1, 'alief987@protonmail.com', '$2y$10$4p3opOO9l4GFdg5SvHnrDevX/qkChnFfiKtBbF2WVzJGRIVEDqeKi', 0, 'Alif', 'Nurfaiz', '089614193149', 'Indonesia', 1, 0, NULL, '', '', NULL, NULL),
(2, 'alief987@division.expo.com', '$2y$10$35sOiSd68V3wybBQIsQ82uhHY6IsBAJmwocQG2TECu5GVF5.zDvW2', 1, NULL, NULL, NULL, NULL, 0, 0, NULL, '', '', NULL, NULL),
(4, 'testemail@test123.com', '$2y$10$qUZYE7KfDgTmjqN4Lh7VZuBCY0uKie8KVSC544z2y8/SBgb5KPEkm', 0, 'The ', 'Tester', '(432) 589-3467', 'United Testing', 1, 0, NULL, '', '', NULL, NULL),
(5, 'maxell.nathanael@division.expo.com', '$2y$10$2llC1tkyGwb7RiEF5m536OQbpOP4LrhdWoNi7YsUQR1tqJpNa8fYi', 1, NULL, NULL, NULL, NULL, 0, 0, NULL, '', '', NULL, NULL),
(7, 'john.thor@umn.ac.id', '$2y$10$8gLQKA75dNoNLdjn5yzeLeL6StQKWZmDLKz.czy65eJYbmRG6cnbG', 0, 'john', 'thor', '123123123', 'Wakanda', 1, 0, NULL, '', '', NULL, NULL),
(27, '123@123.com', '$2y$10$4MSKtgSb0Uc3RMrIohIsde6Ptg4TUCT0Iur8KRNq9pMFuVRLhHa7.', 0, '123', '123', '123', '123', 1, 0, NULL, 'Siapa Nama Orang tua?', '$2y$10$VYsHk/RsdoGZH3p5WPUaT.7xaoHnY.o0IbifLSU/s9pQWa1AmHYHq', 'Apa nama kota tempat Anda dilahirkan?', '$2y$10$tj5j2vVszf7POy1chhJGSeCtO/SE2kSRrheIJg/qfAiLfu8m5VR/i');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_companies`
--
ALTER TABLE `event_companies`
  ADD PRIMARY KEY (`event_id`,`company_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `event_companies`
--
ALTER TABLE `event_companies`
  ADD CONSTRAINT `event_companies_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`),
  ADD CONSTRAINT `event_companies_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
