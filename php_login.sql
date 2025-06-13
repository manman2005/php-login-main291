-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2025 at 04:54 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `php_login`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `status` enum('draft','published','archived') DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `created_by`, `status`, `created_at`, `updated_at`) VALUES
(1, 'วันนี้มีเลือกตั้งประธานสีนะคั้บ', 'รอกันเลยตอนเช้า', 11, 'published', '2025-05-30 07:14:00', '2025-05-30 07:14:00'),
(2, 'วันนี้มีประชุมคณะสี ', 'มากันด้วยนะคั้บ', 11, '', '2025-06-13 02:48:54', '2025-06-13 02:48:54');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `candidate_id` int(11) NOT NULL,
  `vote_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `candidate_number` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `vote_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`candidate_id`, `vote_id`, `user_id`, `candidate_number`, `description`, `image_path`, `vote_count`, `created_at`) VALUES
(1, 1, 8, 1, '', 'uploads/candidates/683a61d63904f.JPG', 0, '2025-05-30 03:58:33');

-- --------------------------------------------------------

--
-- Table structure for table `elections`
--

CREATE TABLE `elections` (
  `id` int(11) NOT NULL,
  `election_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('upcoming','active','completed','cancelled') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `elections`
--

INSERT INTO `elections` (`id`, `election_name`, `description`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'เลือกตั้งประธานนักเรียน 2567', 'การเลือกตั้งประธานนักเรียนประจำปีการศึกษา 2567', '2024-05-01 08:00:00', '2024-05-01 16:00:00', 'upcoming', '2025-05-27 02:08:22', '2025-05-27 02:08:22'),
(2, 'เลือกหัวหน้าห้อง ม.6/1', 'การเลือกตั้งหัวหน้าห้องเรียน ม.6/1', '2024-04-01 09:00:00', '2024-04-01 12:00:00', 'active', '2025-05-27 02:08:22', '2025-05-27 02:08:22'),
(3, 'เลือกประธานชมรมคอมพิวเตอร์', 'การเลือกตั้งประธานชมรมคอมพิวเตอร์', '2024-03-15 13:00:00', '2024-03-15 15:00:00', 'completed', '2025-05-27 02:08:22', '2025-05-27 02:08:22');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `site_name` varchar(255) NOT NULL,
  `site_description` text DEFAULT NULL,
  `admin_email` varchar(255) NOT NULL,
  `items_per_page` int(11) NOT NULL DEFAULT 10,
  `max_candidates` int(11) NOT NULL DEFAULT 10,
  `voting_duration` int(11) NOT NULL DEFAULT 60,
  `allow_multiple_votes` tinyint(1) NOT NULL DEFAULT 0,
  `require_verification` tinyint(1) NOT NULL DEFAULT 1,
  `enable_email_notifications` tinyint(1) NOT NULL DEFAULT 0,
  `notification_before_end` int(11) NOT NULL DEFAULT 30,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `site_name`, `site_description`, `admin_email`, `items_per_page`, `max_candidates`, `voting_duration`, `allow_multiple_votes`, `require_verification`, `enable_email_notifications`, `notification_before_end`, `created_at`, `updated_at`) VALUES
(1, 'ระบบเลือกตั้งออนไลน์', 'ระบบจัดการการเลือกตั้งออนไลน์', 'admin@example.com', 10, 10, 60, 0, 1, 0, 30, '2025-06-02 01:31:12', '2025-06-02 01:31:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 2,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `role_id`, `status`, `created_at`, `updated_at`) VALUES
(7, 'teacher1', '$2y$10$3HwLMqk.Ry0tH1jXF4YzX.QEqWWw0QZO8kGz.OVvM1N4EqQrKA2Hy', 'อาจารย์ที่ปรึกษา 1', 'teacher1@example.com', 1, 'active', '2025-05-27 02:08:22', '2025-05-27 02:08:22'),
(8, 'student1', '$2y$10$/5LLCxWmRgDtmD6ZcFMmFerXZqlVAW.cvYSY5B0eg7r4Y8.a8babC', 'นักเรียน คนที่ 1', 'student1@example.com', 2, 'active', '2025-05-27 02:08:22', '2025-05-30 07:02:35'),
(9, 'student2', '$2y$10$3HwLMqk.Ry0tH1jXF4YzX.QEqWWw0QZO8kGz.OVvM1N4EqQrKA2Hy', 'นักเรียน คนที่ 2', 'student2@example.com', 2, 'active', '2025-05-27 02:08:22', '2025-05-27 02:08:22'),
(10, 'student3', '$2y$10$3HwLMqk.Ry0tH1jXF4YzX.QEqWWw0QZO8kGz.OVvM1N4EqQrKA2Hy', 'นักเรียน คนที่ 3', 'student3@example.com', 2, 'active', '2025-05-27 02:08:22', '2025-05-27 02:08:22'),
(11, 'admin', '$2y$10$6Ya3vSeDvdDYf2ivn8ViBe8yFcVo8ObZjLhyBkZB8Cb4VwauFhjha', 'System Administrator', 'admin@example.com', 1, 'active', '2025-05-30 02:21:13', '2025-05-30 02:21:13'),
(12, 'mzx4pz_.05', '$2y$10$szrWB100SeSr6bhV/dYvK.H5SVkmOMhyXB14wUIu9hhTaJrWqArEO', '', 'mza641888@gmail.com', 2, 'active', '2025-05-31 01:33:27', '2025-06-13 02:43:01');

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`log_id`, `user_id`, `action`, `ip_address`, `user_agent`, `created_at`) VALUES
(2, 11, 'edit_user', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-30 07:02:35');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`role_id`, `role_name`, `created_at`) VALUES
(1, 'admin', '2025-05-27 02:08:22'),
(2, 'user', '2025-05-27 02:08:22');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `vote_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `vote_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vote_logs`
--

CREATE TABLE `vote_logs` (
  `log_id` int(11) NOT NULL,
  `vote_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `voting`
--

CREATE TABLE `voting` (
  `vote_id` int(11) NOT NULL,
  `vote_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('draft','active','completed','cancelled') DEFAULT 'draft',
  `total_votes` int(11) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voting`
--

INSERT INTO `voting` (`vote_id`, `vote_name`, `description`, `date`, `start_time`, `end_time`, `status`, `total_votes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'อาชีวศึกษา', '123', '2025-05-30', '09:23:00', '09:40:00', 'draft', 0, 11, '2025-05-30 02:23:34', '2025-05-30 02:23:34'),
(2, 'นร้', '้อ่ิาืสท', '2025-05-30', '15:21:00', '15:30:00', 'draft', 0, 11, '2025-05-30 08:05:34', '2025-05-30 08:05:34'),
(3, 'อาชีวศึกษา', 'ไฟำหกพดะัเี้ร่', '2025-05-31', '09:00:00', '10:00:00', 'draft', 0, 11, '2025-05-31 01:59:19', '2025-05-31 01:59:19'),
(4, 'อาชีวศึกษา', 'หฟกดเััะพะำพ', '2025-05-31', '14:06:00', '16:06:00', 'draft', 0, 11, '2025-05-31 07:06:08', '2025-05-31 07:06:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`candidate_id`),
  ADD UNIQUE KEY `unique_candidate_number` (`vote_id`,`candidate_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `elections`
--
ALTER TABLE `elections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vote` (`vote_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- Indexes for table `vote_logs`
--
ALTER TABLE `vote_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `vote_id` (`vote_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `voting`
--
ALTER TABLE `voting`
  ADD PRIMARY KEY (`vote_id`),
  ADD KEY `created_by` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `candidate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `elections`
--
ALTER TABLE `elections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vote_logs`
--
ALTER TABLE `vote_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `voting`
--
ALTER TABLE `voting`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`vote_id`) REFERENCES `voting` (`vote_id`),
  ADD CONSTRAINT `candidates_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `user_roles` (`role_id`);

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_ibfk_1` FOREIGN KEY (`vote_id`) REFERENCES `voting` (`vote_id`),
  ADD CONSTRAINT `votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `votes_ibfk_3` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`candidate_id`);

--
-- Constraints for table `vote_logs`
--
ALTER TABLE `vote_logs`
  ADD CONSTRAINT `vote_logs_ibfk_1` FOREIGN KEY (`vote_id`) REFERENCES `voting` (`vote_id`),
  ADD CONSTRAINT `vote_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `voting`
--
ALTER TABLE `voting`
  ADD CONSTRAINT `voting_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
