-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 27, 2025 at 04:18 AM
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
-- Table structure for table `admin_messages`
--

CREATE TABLE `admin_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `admin_reply` text DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `created_at` datetime DEFAULT current_timestamp(),
  `reply_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_messages`
--

INSERT INTO `admin_messages` (`id`, `user_id`, `subject`, `message`, `admin_reply`, `admin_id`, `status`, `created_at`, `reply_at`) VALUES
(1, 11, 'ผฟดำดฟหำดำดห', 'ดหำดหำด', 'กไฟไฟกไฟ', 11, 'replied', '2025-06-14 13:53:17', '2025-06-14 13:53:26'),
(2, 11, 'สอบถามเรื่องช่วยทำเลือกหัวหน้าห้องให้หน่อย', 'ขอไลน์หน่อยคะ พอจะส่งข้อมูลให้', '@cvc.ac.1 อันนี้ไลน์พี่ครับแอดมา\\r\\n', 11, 'replied', '2025-06-14 13:54:46', '2025-06-14 13:55:24'),
(3, 11, 'สอบถามเรื่องช่วยทำเลือกหัวหน้าห้องให้หน่อย', 'แชแชแชแชแชแชแชแชแชแชแชแชแชแชแชแชแชแชแชททททท', 'ว่าาาาาาาาาาาาาาาาาาาาา', 11, 'replied', '2025-06-14 16:13:32', '2025-06-14 16:14:29');

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
(2, 'วันนี้มีประชุมคณะสี ', 'มากันด้วยนะคั้บ', 11, '', '2025-06-13 02:48:54', '2025-06-13 02:48:54'),
(3, 'วันนี้มีประชุมคณะสีแดง', '9โมงเช้านัครับ', 11, '', '2025-06-16 01:28:45', '2025-06-16 01:28:45'),
(4, 'วันนี้สีฟ้าประชุม', '9โมงเช้าตรงโดมนะคั้บ  <3', 11, '', '2025-06-17 03:22:00', '2025-06-17 03:22:00');

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
(8, 9, 12, 1, '', 'uploads/candidates/684f8f92401da.jpg', 0, '2025-06-16 03:29:22'),
(9, 9, 8, 2, '', 'uploads/candidates/684f8fa155197.jpg', 0, '2025-06-16 03:29:37'),
(10, 9, 9, 3, '', 'uploads/candidates/684f8fab349ff.jpg', 0, '2025-06-16 03:29:47'),
(11, 10, 8, 1, '', 'uploads/candidates/684fb7292edf7.jpg', 0, '2025-06-16 06:18:17'),
(12, 10, 9, 2, '', 'uploads/candidates/684fb7427b095.jpg', 0, '2025-06-16 06:18:35'),
(13, 10, 12, 3, '', 'uploads/candidates/684fb74f9723b.jpg', 0, '2025-06-16 06:18:55');

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
(1, 'ระบบเลือกตั้งออนไลน์', 'ระบบจัดการการเลือกตั้งออนไลน์', 'admin@example.com', 10, 10, 5, 0, 1, 0, 30, '2025-06-02 01:31:12', '2025-06-13 08:49:44');

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
(7, 'teacher1', '$2y$10$8NTthdBhTvjLuwtpjVioxe9DhOB2cSj2tgka4RtckNeAVzz1XQhk2', 'อาจารย์ที่ปรึกษา 1', 'teacher1@example.com', 1, 'active', '2025-05-27 02:08:22', '2025-06-16 01:33:18'),
(8, 'student1', '$2y$10$uatihBEIKvFsxb86gCcJL.sfUYN0fMa64FkSTBXX2KskHQKld6Z1S', 'นักเรียน คนที่ 1', 'student1@example.com', 2, 'active', '2025-05-27 02:08:22', '2025-06-16 03:32:19'),
(9, 'student2', '$2y$10$LBAddZpwqRYSLom5/DSacOm76UwfyDkOORsC6sIGprImPPGBxUfAe', 'นักเรียน คนที่ 2', 'student2@example.com', 2, 'active', '2025-05-27 02:08:22', '2025-06-16 03:32:44'),
(11, 'admin', '$2y$10$6Ya3vSeDvdDYf2ivn8ViBe8yFcVo8ObZjLhyBkZB8Cb4VwauFhjha', 'System Administrator', 'admin@example.com', 1, 'active', '2025-05-30 02:21:13', '2025-05-30 02:21:13'),
(12, 'mzx4pz_.05', '$2y$10$XEhODODtoZSU2UqItIiHauHh.cqWtUv3KH3QuObc3S7k/XkQiuswm', '', 'mza641888@gmail.com', 2, 'active', '2025-05-31 01:33:27', '2025-06-16 03:31:41'),
(13, 'man', '$2y$10$ontwFxMu0fejXxSKpRn/2O87wOkRaF38PpgnurpsGjyEL4Tvs9bie', '', 'sagase66@gmail.com', 2, 'active', '2025-06-16 09:19:08', '2025-06-17 06:37:11'),
(14, 'mza641888@gmail.com', '$2y$10$SEdmlksIt1ghn0fNKUjvZOh6sRglB9mVQIRbBdyR.Q4afCvwTPB26', 'นายณัฐพล โลนันท์', 'manza4481@gmail.com', 2, 'active', '2025-06-17 07:47:18', '2025-06-17 07:47:18'),
(15, 'man111', '$2y$10$XdLBkmgl2YHqzc2mqsW.Y.dusAVT3oxBiulmMSqLVbSIt15oCiRrm', 'นายณัฐพล โลนันท์', 'manza41481@gmail.com', 2, 'active', '2025-06-17 08:05:36', '2025-06-17 08:05:36'),
(16, 'man111za', '$2y$10$tp.KprMGSluuqT4cUzXlnu7M80MnASbPSbAPSITBFop2ZoYG574f2', 'นายณัฐพล โลนันท์', 'manza414181@gmail.com', 2, 'active', '2025-06-17 08:20:19', '2025-06-17 08:20:19');

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

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `vote_id`, `user_id`, `candidate_id`, `vote_time`, `ip_address`) VALUES
(3, 9, 11, 10, '2025-06-16 03:30:11', NULL),
(4, 9, 7, 10, '2025-06-16 03:30:27', NULL),
(5, 9, 12, 9, '2025-06-16 03:32:08', NULL),
(6, 9, 8, 10, '2025-06-16 03:32:36', NULL),
(7, 9, 9, 10, '2025-06-16 03:33:10', NULL),
(8, 10, 11, 13, '2025-06-16 06:36:39', NULL),
(9, 10, 12, 13, '2025-06-16 09:07:24', NULL),
(10, 10, 13, 13, '2025-06-16 09:21:35', NULL);

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
(9, 'เลือกตั้งนายก', 'มาคั้บมา', '2025-06-16', '10:28:00', '11:59:00', 'completed', 0, 7, '2025-06-16 03:28:24', '2025-06-17 01:01:28'),
(10, 'เลือกตั้งนายก', '', '2025-06-16', '13:17:00', '19:17:00', 'completed', 0, 11, '2025-06-16 06:17:59', '2025-06-17 01:01:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_messages`
--
ALTER TABLE `admin_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`);

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
-- AUTO_INCREMENT for table `admin_messages`
--
ALTER TABLE `admin_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `candidate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `vote_logs`
--
ALTER TABLE `vote_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `voting`
--
ALTER TABLE `voting`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_messages`
--
ALTER TABLE `admin_messages`
  ADD CONSTRAINT `admin_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `admin_messages_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`);

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
