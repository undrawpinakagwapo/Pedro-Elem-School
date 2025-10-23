-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2025 at 02:14 AM
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
-- Database: `db_elementary_school_pedro`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `audience_scope` varchar(20) NOT NULL DEFAULT 'all' COMMENT 'all | students | teachers',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `image` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_holidays`
--

CREATE TABLE `attendance_holidays` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `curriculum_id` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `curriculum`
--

CREATE TABLE `curriculum` (
  `id` int(11) NOT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `adviser_id` int(11) DEFAULT NULL,
  `school_year` varchar(255) DEFAULT NULL,
  `deleted` int(11) DEFAULT 0,
  `status` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `curriculum`
--

INSERT INTO `curriculum` (`id`, `grade_id`, `adviser_id`, `school_year`, `deleted`, `status`, `created_at`, `updated_at`, `name`) VALUES
(29, 20, 868, '2025-2026', 0, 1, '2025-10-09 13:55:02', '2025-10-09 13:55:02', 'MATATAG'),
(30, 21, 896, '2025-2026', 0, 1, '2025-10-09 15:36:24', '2025-10-09 15:36:24', 'MATATAG'),
(31, 22, 920, '2025-2026', 0, 1, '2025-10-10 01:51:46', '2025-10-10 01:51:46', 'MATATAG');

-- --------------------------------------------------------

--
-- Table structure for table `curriculum_child`
--

CREATE TABLE `curriculum_child` (
  `id` int(11) NOT NULL,
  `curriculum_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `adviser_id` int(11) DEFAULT NULL,
  `deleted` int(11) DEFAULT 0,
  `status` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `curriculum_child`
--

INSERT INTO `curriculum_child` (`id`, `curriculum_id`, `subject_id`, `adviser_id`, `deleted`, `status`, `created_at`, `updated_at`) VALUES
(1, 29, 20, 868, 0, 1, '2025-10-09 13:55:02', '2025-10-09 13:55:02'),
(2, 29, 21, 868, 0, 1, '2025-10-09 13:55:02', '2025-10-09 13:55:02'),
(3, 29, 22, 868, 0, 1, '2025-10-09 13:55:02', '2025-10-09 13:55:02'),
(4, 29, 24, 868, 0, 1, '2025-10-09 13:55:02', '2025-10-09 13:55:02'),
(5, 29, 31, 868, 0, 1, '2025-10-09 13:55:02', '2025-10-09 13:55:02'),
(6, 30, 22, 896, 0, 1, '2025-10-09 15:36:24', '2025-10-09 15:36:24'),
(7, 30, 23, 896, 0, 1, '2025-10-09 15:36:24', '2025-10-09 15:36:24'),
(8, 30, 24, 896, 0, 1, '2025-10-09 15:36:24', '2025-10-09 15:36:24'),
(9, 30, 26, 896, 0, 1, '2025-10-09 15:36:24', '2025-10-09 15:36:24'),
(10, 30, 27, 896, 0, 1, '2025-10-09 15:36:24', '2025-10-09 15:36:24'),
(11, 30, 28, 896, 0, 1, '2025-10-09 15:36:24', '2025-10-09 15:36:24'),
(12, 30, 29, 896, 0, 1, '2025-10-09 15:36:24', '2025-10-09 15:36:24'),
(13, 30, 30, 896, 0, 1, '2025-10-09 15:36:24', '2025-10-09 15:36:24'),
(14, 30, 31, 896, 0, 1, '2025-10-09 15:36:24', '2025-10-09 15:36:24'),
(15, 30, 33, 896, 0, 1, '2025-10-09 15:36:24', '2025-10-09 15:36:24'),
(16, 30, 34, 896, 0, 1, '2025-10-09 15:36:24', '2025-10-09 15:36:24'),
(17, 31, 22, 920, 0, 1, '2025-10-10 01:51:46', '2025-10-10 01:51:46'),
(18, 31, 23, 920, 0, 1, '2025-10-10 01:51:46', '2025-10-10 01:51:46'),
(19, 31, 24, 920, 0, 1, '2025-10-10 01:51:46', '2025-10-10 01:51:46'),
(20, 31, 26, 920, 0, 1, '2025-10-10 01:51:46', '2025-10-10 01:51:46'),
(21, 31, 27, 920, 0, 1, '2025-10-10 01:51:46', '2025-10-10 01:51:46'),
(22, 31, 28, 920, 0, 1, '2025-10-10 01:51:46', '2025-10-10 01:51:46'),
(23, 31, 29, 920, 0, 1, '2025-10-10 01:51:46', '2025-10-10 01:51:46'),
(24, 31, 30, 920, 0, 1, '2025-10-10 01:51:46', '2025-10-10 01:51:46'),
(25, 31, 31, 920, 0, 1, '2025-10-10 01:51:46', '2025-10-10 01:51:46'),
(26, 31, 33, 920, 0, 1, '2025-10-10 01:51:46', '2025-10-10 01:51:46'),
(27, 31, 34, 920, 0, 1, '2025-10-10 01:51:46', '2025-10-10 01:51:46');

-- --------------------------------------------------------

--
-- Table structure for table `grade_level`
--

CREATE TABLE `grade_level` (
  `id` int(11) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `deleted` tinyint(4) DEFAULT 0,
  `added_by` int(11) DEFAULT NULL,
  `latest_edited_by` int(11) DEFAULT NULL,
  `added_date` datetime DEFAULT current_timestamp(),
  `latest_edited_date` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grade_level`
--

INSERT INTO `grade_level` (`id`, `code`, `name`, `status`, `deleted`, `added_by`, `latest_edited_by`, `added_date`, `latest_edited_date`) VALUES
(13, 'G1', '1', 1, 0, NULL, NULL, '2025-09-24 15:59:18', '2025-09-24 15:59:18'),
(14, 'G2', '2', 1, 0, NULL, NULL, '2025-09-24 21:14:28', '2025-09-24 21:14:28'),
(15, 'G3', '3', 1, 0, NULL, NULL, '2025-09-24 21:52:58', '2025-09-24 21:52:58'),
(16, 'G4', '4', 1, 0, NULL, NULL, '2025-09-24 22:26:27', '2025-09-24 22:26:27'),
(17, 'G5', '5', 1, 0, NULL, NULL, '2025-09-25 14:00:45', '2025-09-25 14:00:45'),
(18, 'G6', '6', 1, 0, NULL, NULL, '2025-09-25 14:31:35', '2025-09-25 14:31:35');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action`, `created_at`) VALUES
(1, NULL, 'User Select Transaction', '2025-10-09 13:50:07'),
(2, NULL, 'User Select Transaction', '2025-10-09 13:50:12'),
(3, NULL, 'User Select Transaction', '2025-10-09 13:50:13'),
(4, NULL, 'User Select Transaction', '2025-10-09 13:50:23'),
(5, NULL, 'User Select Transaction', '2025-10-09 13:50:24'),
(6, 1, 'User Login', '2025-10-09 13:50:38'),
(7, 1, 'User Insert Transaction', '2025-10-09 13:50:38'),
(8, 1, 'User Select Transaction', '2025-10-09 13:50:38'),
(9, 1, 'User Select Transaction', '2025-10-09 13:50:45'),
(10, 1, 'User Select Transaction', '2025-10-09 13:50:45'),
(11, 1, 'User Select Transaction', '2025-10-09 13:50:48'),
(12, 1, 'User Select Transaction', '2025-10-09 13:50:57'),
(13, 1, 'User __construct Transaction', '2025-10-09 13:51:03'),
(14, 1, 'User Select Transaction', '2025-10-09 13:51:03'),
(15, 1, 'User Insert Transaction', '2025-10-09 13:52:16'),
(16, 1, 'User Select Transaction', '2025-10-09 13:52:16'),
(17, NULL, 'User __construct Transaction', '2025-10-09 13:52:32'),
(18, NULL, 'User Select Transaction', '2025-10-09 13:52:33'),
(19, 868, 'User Login', '2025-10-09 13:52:38'),
(20, 868, 'User Insert Transaction', '2025-10-09 13:52:38'),
(21, 868, 'User Select Transaction', '2025-10-09 13:52:38'),
(22, 868, 'User Select Transaction', '2025-10-09 13:52:38'),
(23, 868, 'User Select Transaction', '2025-10-09 13:52:38'),
(24, 1, 'User Select Transaction', '2025-10-09 13:52:49'),
(25, 1, 'User Select Transaction', '2025-10-09 13:52:49'),
(26, 1, 'User Select Transaction', '2025-10-09 13:52:52'),
(27, 1, 'User Select Transaction', '2025-10-09 13:52:54'),
(28, 1, 'User Select Transaction', '2025-10-09 13:52:54'),
(29, 1, 'User Select Transaction', '2025-10-09 13:52:56'),
(30, 1, 'User __construct Transaction', '2025-10-09 13:53:02'),
(31, 1, 'User Select Transaction', '2025-10-09 13:53:18'),
(32, 1, 'User Update Transaction', '2025-10-09 13:53:22'),
(33, 1, 'User Select Transaction', '2025-10-09 13:53:22'),
(34, 1, 'User Select Transaction', '2025-10-09 13:53:27'),
(35, 1, 'User Select Transaction', '2025-10-09 13:53:30'),
(36, 1, 'User Select Transaction', '2025-10-09 13:53:33'),
(37, 1, 'User Select Transaction', '2025-10-09 13:53:33'),
(38, 1, 'User Select Transaction', '2025-10-09 13:53:36'),
(39, 1, 'User Select Transaction', '2025-10-09 13:53:36'),
(40, 1, 'User Select Transaction', '2025-10-09 13:53:38'),
(41, 1, 'User Select Transaction', '2025-10-09 13:53:38'),
(42, 1, 'User Select Transaction', '2025-10-09 13:53:46'),
(43, 1, 'User Select Transaction', '2025-10-09 13:53:49'),
(44, 1, 'User Insert Transaction', '2025-10-09 13:55:02'),
(45, 1, 'User Select Transaction', '2025-10-09 13:55:02'),
(46, 1, 'User Select Transaction', '2025-10-09 13:55:06'),
(47, 1, 'User Select Transaction', '2025-10-09 13:55:09'),
(48, 1, 'User Select Transaction', '2025-10-09 13:55:12'),
(49, 1, 'User Select Transaction', '2025-10-09 13:55:12'),
(50, 1, 'User Select Transaction', '2025-10-09 13:55:14'),
(51, 1, 'User Select Transaction', '2025-10-09 13:55:14'),
(52, 1, 'User Select Transaction', '2025-10-09 13:55:14'),
(53, 1, 'User Select Transaction', '2025-10-09 13:55:14'),
(54, 1, 'User Select Transaction', '2025-10-09 13:55:14'),
(55, 1, 'User Select Transaction', '2025-10-09 13:55:23'),
(56, 1, 'User Insert Transaction', '2025-10-09 13:55:32'),
(57, 1, 'User Select Transaction', '2025-10-09 13:55:32'),
(58, 868, 'User Select Transaction', '2025-10-09 13:55:46'),
(59, 868, 'User Select Transaction', '2025-10-09 13:55:46'),
(60, 868, 'User Select Transaction', '2025-10-09 13:55:46'),
(61, 868, 'User Select Transaction', '2025-10-09 13:55:46'),
(62, 868, 'User Select Transaction', '2025-10-09 13:55:50'),
(63, 868, 'User Select Transaction', '2025-10-09 13:55:50'),
(64, 868, 'User Select Transaction', '2025-10-09 13:55:53'),
(65, 868, 'User Select Transaction', '2025-10-09 13:55:54'),
(66, 868, 'User Select Transaction', '2025-10-09 13:56:30'),
(67, 868, 'User Select Transaction', '2025-10-09 13:56:30'),
(68, 868, 'User Select Transaction', '2025-10-09 13:56:34'),
(69, 868, 'User Select Transaction', '2025-10-09 13:56:35'),
(70, 868, 'User Insert Transaction', '2025-10-09 13:56:54'),
(71, 868, 'User Update Transaction', '2025-10-09 13:57:01'),
(72, 868, 'User Update Transaction', '2025-10-09 13:57:03'),
(73, 868, 'User Update Transaction', '2025-10-09 13:57:11'),
(74, 868, 'User Select Transaction', '2025-10-09 13:57:14'),
(75, 868, 'User Select Transaction', '2025-10-09 13:57:15'),
(76, 868, 'User Update Transaction', '2025-10-09 13:57:20'),
(77, 868, 'User Update Transaction', '2025-10-09 13:57:22'),
(78, 868, 'User Select Transaction', '2025-10-09 13:57:26'),
(79, 868, 'User Select Transaction', '2025-10-09 13:57:26'),
(80, 868, 'User Select Transaction', '2025-10-09 13:57:43'),
(81, 868, 'User Select Transaction', '2025-10-09 13:57:43'),
(82, 868, 'User Select Transaction', '2025-10-09 13:58:02'),
(83, 868, 'User Select Transaction', '2025-10-09 13:58:13'),
(84, 868, 'User Insert Transaction', '2025-10-09 13:58:33'),
(85, 868, 'User Update Transaction', '2025-10-09 13:58:36'),
(86, 868, 'User Update Transaction', '2025-10-09 13:58:37'),
(87, 868, 'User Update Transaction', '2025-10-09 13:58:38'),
(88, 868, 'User Update Transaction', '2025-10-09 13:58:38'),
(89, 868, 'User Update Transaction', '2025-10-09 13:58:38'),
(90, 868, 'User Update Transaction', '2025-10-09 13:58:38'),
(91, 868, 'User Update Transaction', '2025-10-09 13:58:38'),
(92, 868, 'User Update Transaction', '2025-10-09 13:58:38'),
(93, 868, 'User Update Transaction', '2025-10-09 13:58:39'),
(94, 868, 'User Update Transaction', '2025-10-09 13:58:39'),
(95, 868, 'User Update Transaction', '2025-10-09 13:58:39'),
(96, 868, 'User Update Transaction', '2025-10-09 13:58:39'),
(97, 868, 'User Update Transaction', '2025-10-09 13:58:39'),
(98, 868, 'User Update Transaction', '2025-10-09 13:58:39'),
(99, 868, 'User Update Transaction', '2025-10-09 13:58:39'),
(100, 868, 'User Update Transaction', '2025-10-09 13:58:40'),
(101, 868, 'User Update Transaction', '2025-10-09 13:58:40'),
(102, 868, 'User Update Transaction', '2025-10-09 13:58:40'),
(103, 868, 'User Update Transaction', '2025-10-09 13:58:40'),
(104, 868, 'User Update Transaction', '2025-10-09 13:58:40'),
(105, 868, 'User Update Transaction', '2025-10-09 13:58:40'),
(106, 868, 'User Update Transaction', '2025-10-09 13:58:40'),
(107, 868, 'User Insert Transaction', '2025-10-09 13:58:45'),
(108, 868, 'User Update Transaction', '2025-10-09 13:58:47'),
(109, 868, 'User Update Transaction', '2025-10-09 13:58:47'),
(110, 868, 'User Update Transaction', '2025-10-09 13:58:47'),
(111, 868, 'User Update Transaction', '2025-10-09 13:58:47'),
(112, 868, 'User Select Transaction', '2025-10-09 13:58:53'),
(113, 868, 'User Select Transaction', '2025-10-09 13:59:14'),
(114, 868, 'User Select Transaction', '2025-10-09 13:59:15'),
(115, 868, 'User Select Transaction', '2025-10-09 13:59:20'),
(116, 868, 'User Select Transaction', '2025-10-09 13:59:30'),
(117, 868, 'User Select Transaction', '2025-10-09 13:59:52'),
(118, 868, 'User Select Transaction', '2025-10-09 13:59:56'),
(119, NULL, 'User Select Transaction', '2025-10-09 14:09:24'),
(120, 868, 'User Login', '2025-10-09 14:09:29'),
(121, 868, 'User Insert Transaction', '2025-10-09 14:09:29'),
(122, 868, 'User Select Transaction', '2025-10-09 14:09:29'),
(123, 868, 'User Select Transaction', '2025-10-09 14:09:30'),
(124, 868, 'User Select Transaction', '2025-10-09 14:09:30'),
(125, 868, 'User Select Transaction', '2025-10-09 14:09:30'),
(126, 868, 'User Select Transaction', '2025-10-09 14:09:33'),
(127, 868, 'User Select Transaction', '2025-10-09 14:09:33'),
(128, 868, 'User Select Transaction', '2025-10-09 14:09:36'),
(129, 868, 'User Select Transaction', '2025-10-09 14:09:37'),
(130, 868, 'User Update Transaction', '2025-10-09 14:09:40'),
(131, 868, 'User Select Transaction', '2025-10-09 14:09:41'),
(132, 868, 'User Select Transaction', '2025-10-09 14:09:59'),
(133, 868, 'User Select Transaction', '2025-10-09 14:10:01'),
(134, 868, 'User Select Transaction', '2025-10-09 14:10:02'),
(135, 868, 'User Select Transaction', '2025-10-09 14:10:10'),
(136, 868, 'User Select Transaction', '2025-10-09 14:10:11'),
(137, 868, 'User Select Transaction', '2025-10-09 14:10:31'),
(138, 868, 'User Select Transaction', '2025-10-09 14:10:32'),
(139, 868, 'User Select Transaction', '2025-10-09 14:10:32'),
(140, 868, 'User Select Transaction', '2025-10-09 14:10:32'),
(141, 868, 'User Select Transaction', '2025-10-09 14:11:16'),
(142, 868, 'User Select Transaction', '2025-10-09 14:11:16'),
(143, 868, 'User Select Transaction', '2025-10-09 14:11:16'),
(144, 868, 'User Select Transaction', '2025-10-09 14:11:16'),
(145, 868, 'User Logout', '2025-10-09 14:11:22'),
(146, 868, 'User Insert Transaction', '2025-10-09 14:11:22'),
(147, NULL, 'User Select Transaction', '2025-10-09 14:11:22'),
(148, 1, 'User Login', '2025-10-09 14:11:30'),
(149, 1, 'User Insert Transaction', '2025-10-09 14:11:30'),
(150, 1, 'User Select Transaction', '2025-10-09 14:11:31'),
(151, 1, 'User Select Transaction', '2025-10-09 14:11:33'),
(152, 1, 'User __construct Transaction', '2025-10-09 14:11:35'),
(153, 1, 'User Select Transaction', '2025-10-09 14:11:35'),
(154, 1, 'User Select Transaction', '2025-10-09 14:12:09'),
(155, 1, 'User Select Transaction', '2025-10-09 14:12:09'),
(156, 1, 'User __construct Transaction', '2025-10-09 14:12:14'),
(157, 1, 'User Select Transaction', '2025-10-09 14:12:14'),
(158, 1, 'User Insert Transaction', '2025-10-09 14:12:32'),
(159, 1, 'User Select Transaction', '2025-10-09 14:12:32'),
(160, NULL, 'User Select Transaction', '2025-10-09 14:12:40'),
(161, 895, 'User Login', '2025-10-09 14:12:45'),
(162, 895, 'User Insert Transaction', '2025-10-09 14:12:45'),
(163, 895, 'User Select Transaction', '2025-10-09 14:12:46'),
(164, 895, 'User Select Transaction', '2025-10-09 14:13:07'),
(165, 895, 'User Select Transaction', '2025-10-09 14:13:13'),
(166, 895, 'User Select Transaction', '2025-10-09 14:13:17'),
(167, 895, 'User Logout', '2025-10-09 14:13:19'),
(168, 895, 'User Insert Transaction', '2025-10-09 14:13:19'),
(169, NULL, 'User Select Transaction', '2025-10-09 14:13:19'),
(170, 1, 'User Logout', '2025-10-09 14:13:55'),
(171, 1, 'User Insert Transaction', '2025-10-09 14:13:55'),
(172, NULL, 'User Select Transaction', '2025-10-09 14:13:55'),
(173, 869, 'User Login', '2025-10-09 14:14:09'),
(174, 869, 'User Insert Transaction', '2025-10-09 14:14:09'),
(175, 869, 'User Select Transaction', '2025-10-09 14:14:09'),
(176, 869, 'User Select Transaction', '2025-10-09 14:14:09'),
(177, 869, 'User Select Transaction', '2025-10-09 14:14:09'),
(178, 869, 'User Select Transaction', '2025-10-09 14:14:12'),
(179, 869, 'User Select Transaction', '2025-10-09 14:14:13'),
(180, 869, 'User Select Transaction', '2025-10-09 14:14:16'),
(181, 869, 'User Select Transaction', '2025-10-09 14:14:17'),
(182, NULL, 'User Select Transaction', '2025-10-09 15:33:53'),
(183, 1, 'User Login', '2025-10-09 15:34:26'),
(184, 1, 'User Insert Transaction', '2025-10-09 15:34:26'),
(185, 1, 'User Select Transaction', '2025-10-09 15:34:26'),
(186, 1, 'User Select Transaction', '2025-10-09 15:34:39'),
(187, 1, 'User __construct Transaction', '2025-10-09 15:34:41'),
(188, 1, 'User Select Transaction', '2025-10-09 15:34:50'),
(189, 1, 'User __construct Transaction', '2025-10-09 15:34:53'),
(190, 1, 'User Select Transaction', '2025-10-09 15:34:53'),
(191, 1, 'User Insert Transaction', '2025-10-09 15:35:11'),
(192, 1, 'User Select Transaction', '2025-10-09 15:35:11'),
(193, 1, 'User Select Transaction', '2025-10-09 15:35:15'),
(194, 1, 'User __construct Transaction', '2025-10-09 15:35:17'),
(195, 1, 'User Select Transaction', '2025-10-09 15:35:25'),
(196, 1, 'User Update Transaction', '2025-10-09 15:35:34'),
(197, 1, 'User Select Transaction', '2025-10-09 15:35:35'),
(198, 1, 'User Select Transaction', '2025-10-09 15:35:38'),
(199, 1, 'User Select Transaction', '2025-10-09 15:35:42'),
(200, 1, 'User Select Transaction', '2025-10-09 15:35:45'),
(201, 1, 'User Insert Transaction', '2025-10-09 15:36:24'),
(202, 1, 'User Select Transaction', '2025-10-09 15:36:24'),
(203, 1, 'User Select Transaction', '2025-10-09 15:36:27'),
(204, 1, 'User Select Transaction', '2025-10-09 15:36:29'),
(205, 1, 'User Select Transaction', '2025-10-09 15:36:33'),
(206, 1, 'User Select Transaction', '2025-10-09 15:36:33'),
(207, 1, 'User Select Transaction', '2025-10-09 15:36:36'),
(208, 1, 'User Select Transaction', '2025-10-09 15:36:36'),
(209, 1, 'User Select Transaction', '2025-10-09 15:36:36'),
(210, 1, 'User Select Transaction', '2025-10-09 15:36:36'),
(211, 1, 'User Select Transaction', '2025-10-09 15:36:36'),
(212, 1, 'User Select Transaction', '2025-10-09 15:36:44'),
(213, 1, 'User Insert Transaction', '2025-10-09 15:36:49'),
(214, 1, 'User Select Transaction', '2025-10-09 15:36:49'),
(215, NULL, 'User Select Transaction', '2025-10-09 15:36:58'),
(216, 896, 'User Login', '2025-10-09 15:37:02'),
(217, 896, 'User Insert Transaction', '2025-10-09 15:37:02'),
(218, 896, 'User Select Transaction', '2025-10-09 15:37:03'),
(219, 896, 'User Select Transaction', '2025-10-09 15:37:03'),
(220, 896, 'User Select Transaction', '2025-10-09 15:37:03'),
(221, 896, 'User Select Transaction', '2025-10-09 15:37:04'),
(222, 896, 'User Select Transaction', '2025-10-09 15:37:08'),
(223, 896, 'User Select Transaction', '2025-10-09 15:37:08'),
(224, 896, 'User Select Transaction', '2025-10-09 15:37:16'),
(225, 896, 'User Select Transaction', '2025-10-09 15:37:16'),
(226, 896, 'User Select Transaction', '2025-10-09 15:37:18'),
(227, 896, 'User Select Transaction', '2025-10-09 15:37:37'),
(228, 896, 'User Select Transaction', '2025-10-09 15:37:43'),
(229, 896, 'User Insert Transaction', '2025-10-09 15:37:59'),
(230, 896, 'User Update Transaction', '2025-10-09 15:38:01'),
(231, 896, 'User Update Transaction', '2025-10-09 15:38:02'),
(232, 896, 'User Update Transaction', '2025-10-09 15:38:02'),
(233, 896, 'User Update Transaction', '2025-10-09 15:38:02'),
(234, 896, 'User Select Transaction', '2025-10-09 15:38:12'),
(235, 896, 'User Update Transaction', '2025-10-09 15:38:32'),
(236, 896, 'User Update Transaction', '2025-10-09 15:38:33'),
(237, 896, 'User Update Transaction', '2025-10-09 15:38:33'),
(238, 896, 'User Update Transaction', '2025-10-09 15:38:33'),
(239, 896, 'User Update Transaction', '2025-10-09 15:38:33'),
(240, 896, 'User Select Transaction', '2025-10-09 15:38:34'),
(241, 896, 'User Select Transaction', '2025-10-09 15:38:34'),
(242, 896, 'User Insert Transaction', '2025-10-09 15:38:36'),
(243, 896, 'User Update Transaction', '2025-10-09 15:38:36'),
(244, 896, 'User Select Transaction', '2025-10-09 15:38:41'),
(245, 896, 'User Update Transaction', '2025-10-09 15:38:43'),
(246, 896, 'User Update Transaction', '2025-10-09 15:38:44'),
(247, 896, 'User Update Transaction', '2025-10-09 15:38:44'),
(248, 896, 'User Update Transaction', '2025-10-09 15:38:44'),
(249, 896, 'User Update Transaction', '2025-10-09 15:38:44'),
(250, 896, 'User Update Transaction', '2025-10-09 15:38:44'),
(251, 896, 'User Update Transaction', '2025-10-09 15:38:44'),
(252, 896, 'User Update Transaction', '2025-10-09 15:38:45'),
(253, 896, 'User Update Transaction', '2025-10-09 15:38:45'),
(254, 896, 'User Update Transaction', '2025-10-09 15:38:45'),
(255, 896, 'User Update Transaction', '2025-10-09 15:38:45'),
(256, 896, 'User Update Transaction', '2025-10-09 15:38:46'),
(257, 896, 'User Update Transaction', '2025-10-09 15:38:46'),
(258, 896, 'User Select Transaction', '2025-10-09 15:39:47'),
(259, 896, 'User Select Transaction', '2025-10-09 15:39:47'),
(260, 896, 'User Insert Transaction', '2025-10-09 15:40:07'),
(261, 896, 'User Update Transaction', '2025-10-09 15:40:07'),
(262, 896, 'User Update Transaction', '2025-10-09 15:40:08'),
(263, 896, 'User Update Transaction', '2025-10-09 15:40:08'),
(264, 896, 'User Update Transaction', '2025-10-09 15:40:08'),
(265, 896, 'User Update Transaction', '2025-10-09 15:40:08'),
(266, 896, 'User Update Transaction', '2025-10-09 15:40:08'),
(267, 896, 'User Update Transaction', '2025-10-09 15:40:09'),
(268, 896, 'User Update Transaction', '2025-10-09 15:40:09'),
(269, 896, 'User Update Transaction', '2025-10-09 15:40:10'),
(270, 896, 'User Update Transaction', '2025-10-09 15:40:10'),
(271, 896, 'User Select Transaction', '2025-10-09 15:40:22'),
(272, 896, 'User Select Transaction', '2025-10-09 15:41:54'),
(273, 896, 'User Select Transaction', '2025-10-09 15:41:54'),
(274, 896, 'User Select Transaction', '2025-10-09 15:42:01'),
(275, 896, 'User Select Transaction', '2025-10-09 15:42:01'),
(276, NULL, 'User Select Transaction', '2025-10-09 15:43:48'),
(277, 896, 'User Login', '2025-10-09 15:43:51'),
(278, 896, 'User Insert Transaction', '2025-10-09 15:43:51'),
(279, 896, 'User Select Transaction', '2025-10-09 15:43:52'),
(280, 896, 'User Select Transaction', '2025-10-09 15:43:53'),
(281, 896, 'User Select Transaction', '2025-10-09 15:43:53'),
(282, 896, 'User Select Transaction', '2025-10-09 15:43:53'),
(283, 896, 'User Select Transaction', '2025-10-09 15:43:55'),
(284, 896, 'User Select Transaction', '2025-10-09 15:43:55'),
(285, 896, 'User Update Transaction', '2025-10-09 15:44:06'),
(286, 896, 'User Select Transaction', '2025-10-09 15:44:07'),
(287, 896, 'User Select Transaction', '2025-10-09 15:44:15'),
(288, 896, 'User Select Transaction', '2025-10-09 15:44:22'),
(289, 896, 'User Insert Transaction', '2025-10-09 15:44:50'),
(290, 896, 'User Select Transaction', '2025-10-09 15:44:57'),
(291, 896, 'User Select Transaction', '2025-10-09 15:46:15'),
(292, 896, 'User Insert Transaction', '2025-10-09 15:46:20'),
(293, 896, 'User Select Transaction', '2025-10-09 15:46:20'),
(294, 896, 'User Select Transaction', '2025-10-09 15:46:26'),
(295, 896, 'User Select Transaction', '2025-10-09 15:48:18'),
(296, 896, 'User Select Transaction', '2025-10-09 15:48:55'),
(297, 896, 'User Select Transaction', '2025-10-09 15:48:59'),
(298, 896, 'User Logout', '2025-10-09 15:49:42'),
(299, 896, 'User Insert Transaction', '2025-10-09 15:49:42'),
(300, NULL, 'User Select Transaction', '2025-10-09 15:49:42'),
(301, NULL, 'User Select Transaction', '2025-10-09 23:24:58'),
(302, 896, 'User Login', '2025-10-09 23:25:02'),
(303, 896, 'User Insert Transaction', '2025-10-09 23:25:02'),
(304, 896, 'User Select Transaction', '2025-10-09 23:25:02'),
(305, 896, 'User Select Transaction', '2025-10-09 23:25:03'),
(306, 896, 'User Select Transaction', '2025-10-09 23:25:03'),
(307, 896, 'User Select Transaction', '2025-10-09 23:25:03'),
(308, 896, 'User Select Transaction', '2025-10-09 23:25:08'),
(309, 896, 'User Select Transaction', '2025-10-09 23:25:09'),
(310, 896, 'User Select Transaction', '2025-10-09 23:42:41'),
(311, 896, 'User Select Transaction', '2025-10-09 23:42:43'),
(312, 896, 'User Update Transaction', '2025-10-09 23:42:58'),
(313, 896, 'User Select Transaction', '2025-10-09 23:42:59'),
(314, 896, 'User Select Transaction', '2025-10-09 23:43:06'),
(315, 896, 'User Select Transaction', '2025-10-09 23:44:25'),
(316, 896, 'User Select Transaction', '2025-10-09 23:44:26'),
(317, 896, 'User Select Transaction', '2025-10-10 00:09:09'),
(318, 896, 'User Select Transaction', '2025-10-10 00:09:10'),
(319, 896, 'User Select Transaction', '2025-10-10 00:09:15'),
(320, 896, 'User Select Transaction', '2025-10-10 00:09:16'),
(321, 896, 'User Select Transaction', '2025-10-10 00:09:21'),
(322, 896, 'User Select Transaction', '2025-10-10 00:09:24'),
(323, 896, 'User Insert Transaction', '2025-10-10 00:09:30'),
(324, 896, 'User Select Transaction', '2025-10-10 00:09:30'),
(325, 896, 'User Select Transaction', '2025-10-10 00:09:35'),
(326, 896, 'User Insert Transaction', '2025-10-10 00:09:45'),
(327, 896, 'User Select Transaction', '2025-10-10 00:09:45'),
(328, 896, 'User Select Transaction', '2025-10-10 00:09:48'),
(329, 896, 'User Select Transaction', '2025-10-10 00:09:48'),
(330, 896, 'User Select Transaction', '2025-10-10 00:09:53'),
(331, 896, 'User Select Transaction', '2025-10-10 00:09:54'),
(332, 896, 'User Select Transaction', '2025-10-10 00:09:57'),
(333, 896, 'User Insert Transaction', '2025-10-10 00:10:03'),
(334, 896, 'User Select Transaction', '2025-10-10 00:10:04'),
(335, 896, 'User Select Transaction', '2025-10-10 00:10:09'),
(336, 896, 'User Select Transaction', '2025-10-10 00:10:09'),
(337, 896, 'User Select Transaction', '2025-10-10 00:10:12'),
(338, 896, 'User Select Transaction', '2025-10-10 00:10:16'),
(339, 896, 'User Select Transaction', '2025-10-10 00:11:01'),
(340, 896, 'User Select Transaction', '2025-10-10 00:11:02'),
(341, 896, 'User Select Transaction', '2025-10-10 00:11:07'),
(342, 896, 'User Select Transaction', '2025-10-10 00:16:44'),
(343, 896, 'User Select Transaction', '2025-10-10 00:16:44'),
(344, 896, 'User Select Transaction', '2025-10-10 00:16:48'),
(345, 896, 'User Select Transaction', '2025-10-10 00:16:48'),
(346, 896, 'User Select Transaction', '2025-10-10 00:16:52'),
(347, 896, 'User Select Transaction', '2025-10-10 00:16:55'),
(348, 896, 'User Update Transaction', '2025-10-10 00:17:07'),
(349, 896, 'User Select Transaction', '2025-10-10 00:17:07'),
(350, 896, 'User Select Transaction', '2025-10-10 00:17:10'),
(351, 896, 'User Select Transaction', '2025-10-10 00:17:10'),
(352, 896, 'User Select Transaction', '2025-10-10 00:17:12'),
(353, 896, 'User Select Transaction', '2025-10-10 00:28:04'),
(354, 896, 'User Select Transaction', '2025-10-10 00:28:05'),
(355, 896, 'User Select Transaction', '2025-10-10 00:28:07'),
(356, 896, 'User Select Transaction', '2025-10-10 00:28:08'),
(357, 896, 'User Select Transaction', '2025-10-10 00:28:09'),
(358, 896, 'User Select Transaction', '2025-10-10 00:28:09'),
(359, 896, 'User Select Transaction', '2025-10-10 00:28:10'),
(360, 896, 'User Select Transaction', '2025-10-10 00:28:10'),
(361, 896, 'User Select Transaction', '2025-10-10 00:28:51'),
(362, 896, 'User Select Transaction', '2025-10-10 00:28:52'),
(363, 896, 'User Select Transaction', '2025-10-10 00:28:53'),
(364, 896, 'User Select Transaction', '2025-10-10 00:29:08'),
(365, 896, 'User Select Transaction', '2025-10-10 00:29:09'),
(366, 896, 'User Select Transaction', '2025-10-10 00:29:10'),
(367, 896, 'User Select Transaction', '2025-10-10 00:30:34'),
(368, 896, 'User Select Transaction', '2025-10-10 00:30:35'),
(369, 896, 'User Select Transaction', '2025-10-10 00:30:36'),
(370, 896, 'User Select Transaction', '2025-10-10 00:30:41'),
(371, 896, 'User Select Transaction', '2025-10-10 00:30:41'),
(372, 896, 'User Select Transaction', '2025-10-10 00:30:42'),
(373, 896, 'User Select Transaction', '2025-10-10 00:30:43'),
(374, 896, 'User Select Transaction', '2025-10-10 00:30:48'),
(375, 896, 'User Select Transaction', '2025-10-10 00:30:48'),
(376, 896, 'User Select Transaction', '2025-10-10 00:31:48'),
(377, 896, 'User Select Transaction', '2025-10-10 00:31:48'),
(378, 896, 'User Select Transaction', '2025-10-10 00:31:51'),
(379, 896, 'User Select Transaction', '2025-10-10 00:31:53'),
(380, 896, 'User Select Transaction', '2025-10-10 00:31:54'),
(381, 896, 'User Select Transaction', '2025-10-10 00:31:55'),
(382, 896, 'User Select Transaction', '2025-10-10 00:31:55'),
(383, 896, 'User Select Transaction', '2025-10-10 00:38:25'),
(384, 896, 'User Select Transaction', '2025-10-10 00:38:25'),
(385, 896, 'User Select Transaction', '2025-10-10 00:38:27'),
(386, 896, 'User Select Transaction', '2025-10-10 00:38:27'),
(387, 896, 'User Select Transaction', '2025-10-10 00:38:28'),
(388, 896, 'User Select Transaction', '2025-10-10 00:38:29'),
(389, 896, 'User Select Transaction', '2025-10-10 00:38:29'),
(390, 896, 'User Select Transaction', '2025-10-10 00:54:37'),
(391, 896, 'User Select Transaction', '2025-10-10 00:54:38'),
(392, 896, 'User Select Transaction', '2025-10-10 00:54:40'),
(393, 896, 'User Select Transaction', '2025-10-10 00:55:59'),
(394, 896, 'User Select Transaction', '2025-10-10 01:04:01'),
(395, 896, 'User Update Transaction', '2025-10-10 01:04:29'),
(396, 896, 'User Select Transaction', '2025-10-10 01:04:30'),
(397, 896, 'User Select Transaction', '2025-10-10 01:04:37'),
(398, 896, 'User Select Transaction', '2025-10-10 01:04:37'),
(399, 896, 'User Select Transaction', '2025-10-10 01:04:45'),
(400, 896, 'User Select Transaction', '2025-10-10 01:04:45'),
(401, 896, 'User Select Transaction', '2025-10-10 01:04:48'),
(402, 896, 'User Select Transaction', '2025-10-10 01:19:25'),
(403, 896, 'User Select Transaction', '2025-10-10 01:19:25'),
(404, 896, 'User Select Transaction', '2025-10-10 01:19:29'),
(405, 896, 'User Select Transaction', '2025-10-10 01:19:29'),
(406, 896, 'User Select Transaction', '2025-10-10 01:19:36'),
(407, 896, 'User Select Transaction', '2025-10-10 01:19:51'),
(408, 896, 'User Select Transaction', '2025-10-10 01:19:52'),
(409, 896, 'User Select Transaction', '2025-10-10 01:19:54'),
(410, 896, 'User Update Transaction', '2025-10-10 01:20:10'),
(411, 896, 'User Select Transaction', '2025-10-10 01:20:10'),
(412, 896, 'User Select Transaction', '2025-10-10 01:20:16'),
(413, 896, 'User Select Transaction', '2025-10-10 01:20:17'),
(414, 896, 'User Select Transaction', '2025-10-10 01:20:22'),
(415, 896, 'User Select Transaction', '2025-10-10 01:50:14'),
(416, 896, 'User Select Transaction', '2025-10-10 01:50:15'),
(417, NULL, 'User Select Transaction', '2025-10-10 01:50:29'),
(418, 1, 'User Login', '2025-10-10 01:50:34'),
(419, 1, 'User Insert Transaction', '2025-10-10 01:50:34'),
(420, 1, 'User Select Transaction', '2025-10-10 01:50:35'),
(421, 1, 'User Select Transaction', '2025-10-10 01:50:47'),
(422, 1, 'User Select Transaction', '2025-10-10 01:50:48'),
(423, 1, 'User __construct Transaction', '2025-10-10 01:50:50'),
(424, 1, 'User Select Transaction', '2025-10-10 01:50:50'),
(425, 1, 'User __construct Transaction', '2025-10-10 01:50:53'),
(426, 1, 'User Select Transaction', '2025-10-10 01:50:54'),
(427, 1, 'User Insert Transaction', '2025-10-10 01:51:12'),
(428, 1, 'User Select Transaction', '2025-10-10 01:51:12'),
(429, 1, 'User Select Transaction', '2025-10-10 01:51:16'),
(430, 1, 'User Select Transaction', '2025-10-10 01:51:17'),
(431, 1, 'User Insert Transaction', '2025-10-10 01:51:46'),
(432, 1, 'User Select Transaction', '2025-10-10 01:51:46'),
(433, 1, 'User Select Transaction', '2025-10-10 01:51:54'),
(434, 1, 'User __construct Transaction', '2025-10-10 01:51:56'),
(435, 1, 'User Select Transaction', '2025-10-10 01:52:03'),
(436, 1, 'User Update Transaction', '2025-10-10 01:52:08'),
(437, 1, 'User Select Transaction', '2025-10-10 01:52:08'),
(438, 1, 'User Select Transaction', '2025-10-10 01:52:13'),
(439, 1, 'User Select Transaction', '2025-10-10 01:52:15'),
(440, 1, 'User Select Transaction', '2025-10-10 01:52:18'),
(441, 1, 'User Select Transaction', '2025-10-10 01:52:18'),
(442, 1, 'User Select Transaction', '2025-10-10 01:52:20'),
(443, 1, 'User Select Transaction', '2025-10-10 01:52:20'),
(444, 1, 'User Select Transaction', '2025-10-10 01:52:20'),
(445, 1, 'User Select Transaction', '2025-10-10 01:52:20'),
(446, 1, 'User Select Transaction', '2025-10-10 01:52:20'),
(447, 1, 'User Select Transaction', '2025-10-10 01:52:23'),
(448, 1, 'User Insert Transaction', '2025-10-10 01:52:29'),
(449, 1, 'User Select Transaction', '2025-10-10 01:52:29'),
(450, 896, 'User Logout', '2025-10-10 01:52:36'),
(451, 896, 'User Insert Transaction', '2025-10-10 01:52:36'),
(452, NULL, 'User Select Transaction', '2025-10-10 01:52:36'),
(453, 920, 'User Login', '2025-10-10 01:52:41'),
(454, 920, 'User Insert Transaction', '2025-10-10 01:52:41'),
(455, 920, 'User Select Transaction', '2025-10-10 01:52:41'),
(456, 920, 'User Select Transaction', '2025-10-10 01:52:41'),
(457, 920, 'User Select Transaction', '2025-10-10 01:52:42'),
(458, 920, 'User Select Transaction', '2025-10-10 01:52:42'),
(459, 920, 'User Select Transaction', '2025-10-10 01:52:44'),
(460, 920, 'User Select Transaction', '2025-10-10 01:52:44'),
(461, 920, 'User Select Transaction', '2025-10-10 01:52:51'),
(462, 920, 'User Insert Transaction', '2025-10-10 01:52:58'),
(463, 920, 'User Select Transaction', '2025-10-10 01:52:59'),
(464, 920, 'User Select Transaction', '2025-10-10 01:53:05'),
(465, 920, 'User Insert Transaction', '2025-10-10 01:53:11'),
(466, 920, 'User Select Transaction', '2025-10-10 01:53:11'),
(467, 920, 'User Select Transaction', '2025-10-10 01:53:19'),
(468, 920, 'User Insert Transaction', '2025-10-10 01:53:25'),
(469, 920, 'User Select Transaction', '2025-10-10 01:53:25'),
(470, 920, 'User Select Transaction', '2025-10-10 01:53:30'),
(471, 920, 'User Select Transaction', '2025-10-10 01:53:30'),
(472, 920, 'User Select Transaction', '2025-10-10 01:53:33'),
(473, 920, 'User Select Transaction', '2025-10-10 01:53:33'),
(474, 920, 'User Select Transaction', '2025-10-10 01:53:39'),
(475, 920, 'User Select Transaction', '2025-10-10 01:53:53'),
(476, 920, 'User Select Transaction', '2025-10-10 01:53:54'),
(477, 920, 'User Select Transaction', '2025-10-10 01:53:55'),
(478, 920, 'User Update Transaction', '2025-10-10 01:54:08'),
(479, 920, 'User Select Transaction', '2025-10-10 01:54:09'),
(480, 920, 'User Select Transaction', '2025-10-10 01:54:15'),
(481, 920, 'User Select Transaction', '2025-10-10 01:54:16'),
(482, 920, 'User Select Transaction', '2025-10-10 01:54:23');

-- --------------------------------------------------------

--
-- Table structure for table `registrar_student`
--

CREATE TABLE `registrar_student` (
  `id` int(11) NOT NULL,
  `curriculum_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `deleted` int(11) DEFAULT 0,
  `status` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `section_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrar_student`
--

INSERT INTO `registrar_student` (`id`, `curriculum_id`, `student_id`, `deleted`, `status`, `created_at`, `updated_at`, `section_id`) VALUES
(1, 29, 869, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(2, 29, 870, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(3, 29, 880, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(4, 29, 881, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(5, 29, 871, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(6, 29, 872, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(7, 29, 882, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(8, 29, 883, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(9, 29, 884, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(10, 29, 885, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(11, 29, 873, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(12, 29, 886, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(13, 29, 887, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(14, 29, 888, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(15, 29, 889, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(16, 29, 890, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(17, 29, 891, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(18, 29, 892, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(19, 29, 874, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(20, 29, 875, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(21, 29, 893, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(22, 29, 876, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(23, 29, 877, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(24, 29, 894, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(25, 29, 878, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(26, 29, 879, 0, 1, '2025-10-09 13:55:32', '2025-10-09 13:55:32', 20),
(27, 30, 914, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(28, 30, 915, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(29, 30, 916, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(30, 30, 897, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(31, 30, 917, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(32, 30, 898, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(33, 30, 899, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(34, 30, 918, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(35, 30, 900, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(36, 30, 901, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(37, 30, 902, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(38, 30, 903, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(39, 30, 904, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(40, 30, 905, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(41, 30, 919, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(42, 30, 906, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(43, 30, 907, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(44, 30, 908, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(45, 30, 909, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(46, 30, 910, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(47, 30, 911, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(48, 30, 912, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(49, 30, 913, 0, 1, '2025-10-09 15:36:49', '2025-10-09 15:36:49', 21),
(50, 31, 921, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(51, 31, 935, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(52, 31, 922, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(53, 31, 923, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(54, 31, 936, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(55, 31, 937, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(56, 31, 924, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(57, 31, 938, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(58, 31, 925, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(59, 31, 939, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(60, 31, 926, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(61, 31, 927, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(62, 31, 940, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(63, 31, 928, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(64, 31, 941, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(65, 31, 929, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(66, 31, 942, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(67, 31, 930, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(68, 31, 943, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(69, 31, 944, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(70, 31, 945, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(71, 31, 931, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(72, 31, 932, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(73, 31, 933, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(74, 31, 934, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(75, 31, 946, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(76, 31, 947, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22),
(77, 31, 948, 0, 1, '2025-10-10 01:52:29', '2025-10-10 01:52:29', 22);

-- --------------------------------------------------------

--
-- Table structure for table `registrar_student_orphans`
--

CREATE TABLE `registrar_student_orphans` (
  `id` int(11) NOT NULL,
  `curriculum_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `deleted` int(11) DEFAULT 0,
  `status` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `section_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` tinyint(3) UNSIGNED NOT NULL,
  `role_slug` varchar(32) NOT NULL,
  `role_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_slug`, `role_name`) VALUES
(1, 'admin', 'Admin'),
(2, 'teacher', 'Teacher'),
(3, 'principal', 'Principal'),
(4, 'students', 'Students'),
(5, 'student', 'Student');

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

CREATE TABLE `section` (
  `id` int(11) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `adviser_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `deleted` tinyint(4) DEFAULT 0,
  `added_by` int(11) DEFAULT NULL,
  `latest_edited_by` int(11) DEFAULT NULL,
  `added_date` datetime DEFAULT current_timestamp(),
  `latest_edited_date` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section`
--

INSERT INTO `section` (`id`, `code`, `name`, `adviser_id`, `grade_id`, `status`, `deleted`, `added_by`, `latest_edited_by`, `added_date`, `latest_edited_date`) VALUES
(20, 'SEC 1', 'HONESTY', NULL, 13, 1, 0, NULL, NULL, '2025-09-24 15:59:47', '2025-09-24 15:59:47'),
(21, 'SEC 2', 'VIBRANT', NULL, 13, 1, 0, NULL, NULL, '2025-09-24 16:00:03', '2025-09-24 16:00:03'),
(22, 'SEC 1', 'HOSPITABLE', NULL, 14, 1, 0, NULL, NULL, '2025-09-24 21:19:48', '2025-09-24 21:19:48'),
(23, 'SEC 1', 'LOYALTY', NULL, 15, 1, 0, NULL, NULL, '2025-09-24 21:56:20', '2025-09-24 21:56:20'),
(24, 'SEC 1', 'SINCERITY', NULL, 16, 1, 0, NULL, NULL, '2025-09-24 22:27:16', '2025-09-24 22:27:16'),
(25, 'SEC 1', 'FAIRNESS', NULL, 17, 1, 0, NULL, NULL, '2025-09-25 14:01:47', '2025-09-25 14:01:47'),
(26, 'SEC 1', 'HOPE', NULL, 18, 1, 0, NULL, NULL, '2025-09-25 14:32:18', '2025-09-25 14:32:18'),
(27, 'SEC 2', 'PURITY', NULL, 15, 1, 0, NULL, NULL, '2025-09-27 13:09:14', '2025-09-27 13:09:14');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `LRN` int(11) DEFAULT NULL,
  `studentName` varchar(512) DEFAULT NULL,
  `Gender` varchar(512) DEFAULT NULL,
  `Birthdae` varchar(512) DEFAULT NULL,
  `Mother Tongue` varchar(512) DEFAULT NULL,
  `RELIGION` varchar(512) DEFAULT NULL,
  `purok_street_address` varchar(512) DEFAULT NULL,
  `Barangay` varchar(512) DEFAULT NULL,
  `city_munici` varchar(512) DEFAULT NULL,
  `Province` varchar(512) DEFAULT NULL,
  `father_name` varchar(512) DEFAULT NULL,
  `mother_name` varchar(512) DEFAULT NULL,
  `Guardian` varchar(512) DEFAULT NULL,
  `Relationship` varchar(512) DEFAULT NULL,
  `contact_no_gurdian_parent` varchar(512) DEFAULT NULL,
  `learning_modality` varchar(512) DEFAULT NULL,
  `REMARKS` varchar(512) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `deleted` tinyint(4) DEFAULT 0,
  `added_by` int(11) DEFAULT NULL,
  `latest_edited_by` int(11) DEFAULT NULL,
  `added_date` datetime DEFAULT current_timestamp(),
  `latest_edited_date` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_attendance`
--

CREATE TABLE `student_attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `curriculum_id` int(11) DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  `latest_edited_by` int(11) DEFAULT NULL,
  `added_date` datetime DEFAULT current_timestamp(),
  `latest_edited_date` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `am_status` enum('P','A') NOT NULL DEFAULT 'A',
  `pm_status` enum('P','A') NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_attendance`
--

INSERT INTO `student_attendance` (`id`, `student_id`, `curriculum_id`, `attendance_date`, `section_id`, `remarks`, `added_by`, `latest_edited_by`, `added_date`, `latest_edited_date`, `am_status`, `pm_status`) VALUES
(1, 914, 30, '2025-10-09', 21, 'Absent AM', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'A', 'P'),
(2, 915, 30, '2025-10-09', 21, 'Absent', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'A', 'A'),
(3, 916, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(4, 897, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(5, 917, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(6, 898, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(7, 899, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(8, 918, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(9, 900, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(10, 901, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(11, 902, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(12, 903, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(13, 904, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(14, 905, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(15, 919, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(16, 906, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(17, 907, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(18, 908, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(19, 909, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(20, 910, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(21, 911, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(22, 912, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P'),
(23, 913, 30, '2025-10-09', 21, '', NULL, NULL, '2025-10-09 15:40:07', '2025-10-09 23:42:58', 'P', 'P');

-- --------------------------------------------------------

--
-- Table structure for table `student_attendance_backup_20250907`
--

CREATE TABLE `student_attendance_backup_20250907` (
  `id` int(11) NOT NULL DEFAULT 0,
  `student_id` int(11) NOT NULL,
  `curriculum_id` int(11) DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `att_date` date NOT NULL,
  `status` enum('present','absent','tardy','excused') NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  `latest_edited_by` int(11) DEFAULT NULL,
  `added_date` datetime DEFAULT current_timestamp(),
  `latest_edited_date` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `am_status` enum('P','A','L','E') DEFAULT NULL,
  `am_remarks` varchar(255) DEFAULT NULL,
  `pm_status` enum('P','A','L','E') DEFAULT NULL,
  `pm_remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_remedial_classes`
--

CREATE TABLE `student_remedial_classes` (
  `id` int(11) NOT NULL,
  `ssg_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `conducted_from` date DEFAULT NULL,
  `conducted_to` date DEFAULT NULL,
  `final_rating` decimal(5,2) DEFAULT NULL,
  `remedial_mark` decimal(5,2) DEFAULT NULL,
  `recomputed_final` decimal(5,2) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `added_by` int(11) DEFAULT NULL,
  `latest_edited_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_remedial_classes`
--

INSERT INTO `student_remedial_classes` (`id`, `ssg_id`, `subject_id`, `conducted_from`, `conducted_to`, `final_rating`, `remedial_mark`, `recomputed_final`, `remarks`, `deleted`, `added_by`, `latest_edited_by`, `created_at`, `updated_at`) VALUES
(1, 2614, 33, NULL, NULL, 74.00, NULL, NULL, '', 0, 896, 896, '2025-10-10 01:04:29', '2025-10-10 01:20:10'),
(2, 2660, 31, NULL, NULL, 74.75, NULL, NULL, '', 0, 896, 896, '2025-10-10 01:04:29', '2025-10-10 01:20:10'),
(3, 2734, 33, NULL, NULL, 74.00, 80.00, 77.00, 'PASSED', 0, 920, 920, '2025-10-10 01:54:08', '2025-10-10 01:54:08'),
(4, 2762, 24, NULL, NULL, 74.00, 80.00, 77.00, 'PASSED', 0, 920, 920, '2025-10-10 01:54:08', '2025-10-10 01:54:08');

-- --------------------------------------------------------

--
-- Table structure for table `student_subject_core_values`
--

CREATE TABLE `student_subject_core_values` (
  `id` int(11) NOT NULL,
  `ssg_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `curriculum_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `md_q1` enum('AO','SO','RO','NO') DEFAULT NULL,
  `md_q2` enum('AO','SO','RO','NO') DEFAULT NULL,
  `md_q3` enum('AO','SO','RO','NO') DEFAULT NULL,
  `md_q4` enum('AO','SO','RO','NO') DEFAULT NULL,
  `mt_q1` enum('AO','SO','RO','NO') DEFAULT NULL,
  `mt_q2` enum('AO','SO','RO','NO') DEFAULT NULL,
  `mt_q3` enum('AO','SO','RO','NO') DEFAULT NULL,
  `mt_q4` enum('AO','SO','RO','NO') DEFAULT NULL,
  `mk_q1` enum('AO','SO','RO','NO') DEFAULT NULL,
  `mk_q2` enum('AO','SO','RO','NO') DEFAULT NULL,
  `mk_q3` enum('AO','SO','RO','NO') DEFAULT NULL,
  `mk_q4` enum('AO','SO','RO','NO') DEFAULT NULL,
  `mb_q1` enum('AO','SO','RO','NO') DEFAULT NULL,
  `mb_q2` enum('AO','SO','RO','NO') DEFAULT NULL,
  `mb_q3` enum('AO','SO','RO','NO') DEFAULT NULL,
  `mb_q4` enum('AO','SO','RO','NO') DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `deleted` tinyint(4) DEFAULT 0,
  `added_by` int(11) DEFAULT NULL,
  `latest_edited_by` int(11) DEFAULT NULL,
  `added_date` datetime DEFAULT current_timestamp(),
  `latest_edited_date` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_subject_core_values`
--

INSERT INTO `student_subject_core_values` (`id`, `ssg_id`, `student_id`, `subject_id`, `curriculum_id`, `section_id`, `md_q1`, `md_q2`, `md_q3`, `md_q4`, `mt_q1`, `mt_q2`, `mt_q3`, `mt_q4`, `mk_q1`, `mk_q2`, `mk_q3`, `mk_q4`, `mb_q1`, `mb_q2`, `mb_q3`, `mb_q4`, `status`, `deleted`, `added_by`, `latest_edited_by`, `added_date`, `latest_edited_date`) VALUES
(1, 2542, 869, 20, 29, 20, 'AO', NULL, NULL, NULL, 'SO', NULL, NULL, NULL, 'SO', NULL, NULL, NULL, 'SO', NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:33', '2025-10-09 13:58:33'),
(2, 2614, 914, 33, 30, 21, 'AO', 'SO', NULL, NULL, 'AO', 'AO', NULL, NULL, 'AO', 'SO', NULL, NULL, 'AO', 'SO', NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:44:50', '2025-10-09 15:44:50');

-- --------------------------------------------------------

--
-- Table structure for table `student_subject_core_values_rows`
--

CREATE TABLE `student_subject_core_values_rows` (
  `id` int(11) NOT NULL,
  `ssg_id` int(11) NOT NULL,
  `core_name` enum('maka_diyos','makatao','maka_kalikasan','maka_bansa') NOT NULL,
  `behavior_index` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `q1` enum('AO','SO','RO','NO') DEFAULT NULL,
  `q2` enum('AO','SO','RO','NO') DEFAULT NULL,
  `q3` enum('AO','SO','RO','NO') DEFAULT NULL,
  `q4` enum('AO','SO','RO','NO') DEFAULT NULL,
  `deleted` tinyint(4) DEFAULT 0,
  `added_by` int(11) DEFAULT NULL,
  `latest_edited_by` int(11) DEFAULT NULL,
  `added_date` datetime DEFAULT current_timestamp(),
  `latest_edited_date` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_subject_core_values_rows`
--

INSERT INTO `student_subject_core_values_rows` (`id`, `ssg_id`, `core_name`, `behavior_index`, `q1`, `q2`, `q3`, `q4`, `deleted`, `added_by`, `latest_edited_by`, `added_date`, `latest_edited_date`) VALUES
(1, 2542, 'maka_diyos', 1, 'AO', NULL, NULL, NULL, 0, 868, 868, '2025-10-09 13:58:33', '2025-10-09 13:58:33'),
(2, 2542, 'maka_diyos', 2, 'RO', NULL, NULL, NULL, 0, 868, 868, '2025-10-09 13:58:33', '2025-10-09 13:58:33'),
(3, 2542, 'makatao', 1, 'SO', NULL, NULL, NULL, 0, 868, 868, '2025-10-09 13:58:33', '2025-10-09 13:58:33'),
(4, 2542, 'makatao', 2, 'NO', NULL, NULL, NULL, 0, 868, 868, '2025-10-09 13:58:33', '2025-10-09 13:58:33'),
(5, 2542, 'maka_kalikasan', 1, 'SO', NULL, NULL, NULL, 0, 868, 868, '2025-10-09 13:58:33', '2025-10-09 13:58:33'),
(6, 2542, 'maka_bansa', 1, 'SO', NULL, NULL, NULL, 0, 868, 868, '2025-10-09 13:58:33', '2025-10-09 13:58:33'),
(7, 2542, 'maka_bansa', 2, 'AO', NULL, NULL, NULL, 0, 868, 868, '2025-10-09 13:58:33', '2025-10-09 13:58:33'),
(8, 2614, 'maka_diyos', 1, 'AO', 'SO', NULL, NULL, 0, 896, 896, '2025-10-09 15:44:50', '2025-10-09 15:44:50'),
(9, 2614, 'maka_diyos', 2, 'AO', 'SO', NULL, NULL, 0, 896, 896, '2025-10-09 15:44:50', '2025-10-09 15:44:50'),
(10, 2614, 'makatao', 1, 'AO', 'AO', NULL, NULL, 0, 896, 896, '2025-10-09 15:44:50', '2025-10-09 15:44:50'),
(11, 2614, 'makatao', 2, 'AO', 'SO', NULL, NULL, 0, 896, 896, '2025-10-09 15:44:50', '2025-10-09 15:44:50'),
(12, 2614, 'maka_kalikasan', 1, 'AO', 'SO', NULL, NULL, 0, 896, 896, '2025-10-09 15:44:50', '2025-10-09 15:44:50'),
(13, 2614, 'maka_bansa', 1, 'AO', 'SO', NULL, NULL, 0, 896, 896, '2025-10-09 15:44:50', '2025-10-09 15:44:50'),
(14, 2614, 'maka_bansa', 2, 'AO', 'SO', NULL, NULL, 0, 896, 896, '2025-10-09 15:44:50', '2025-10-09 15:44:50');

-- --------------------------------------------------------

--
-- Table structure for table `student_subject_grades`
--

CREATE TABLE `student_subject_grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `curriculum_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `school_year` varchar(32) DEFAULT NULL,
  `q1` decimal(5,2) DEFAULT NULL,
  `q2` decimal(5,2) DEFAULT NULL,
  `q3` decimal(5,2) DEFAULT NULL,
  `q4` decimal(5,2) DEFAULT NULL,
  `final_average` decimal(5,2) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `deleted` tinyint(4) DEFAULT 0,
  `added_by` int(11) DEFAULT NULL,
  `latest_edited_by` int(11) DEFAULT NULL,
  `added_date` datetime DEFAULT current_timestamp(),
  `latest_edited_date` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_subject_grades`
--

INSERT INTO `student_subject_grades` (`id`, `student_id`, `subject_id`, `curriculum_id`, `section_id`, `school_year`, `q1`, `q2`, `q3`, `q4`, `final_average`, `status`, `deleted`, `added_by`, `latest_edited_by`, `added_date`, `latest_edited_date`) VALUES
(2516, 869, 31, 29, 20, '2025-2026', 74.00, 74.00, 74.00, 74.00, 74.00, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2517, 870, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2518, 880, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2519, 881, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2520, 871, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2521, 872, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2522, 882, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2523, 883, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2524, 884, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2525, 885, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2526, 873, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2527, 886, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2528, 887, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2529, 888, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2530, 889, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2531, 890, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2532, 891, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2533, 892, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2534, 874, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2535, 875, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2536, 893, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2537, 876, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2538, 877, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2539, 894, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2540, 878, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2541, 879, 31, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:56:54', '2025-10-09 13:56:54'),
(2542, 869, 20, 29, 20, '2025-2026', 74.00, 74.00, 74.00, 74.00, 74.00, 1, 0, 868, 868, '2025-10-09 13:58:33', '2025-10-09 13:58:45'),
(2543, 870, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2544, 880, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2545, 881, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2546, 871, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2547, 872, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2548, 882, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2549, 883, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2550, 884, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2551, 885, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2552, 873, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2553, 886, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2554, 887, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2555, 888, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2556, 889, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2557, 890, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2558, 891, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2559, 892, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2560, 874, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2561, 875, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2562, 893, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2563, 876, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2564, 877, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2565, 894, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2566, 878, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2567, 879, 20, 29, 20, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 868, 868, '2025-10-09 13:58:45', '2025-10-09 13:58:45'),
(2568, 914, 34, 30, 21, '2025-2026', 80.00, 80.00, 80.00, 80.00, 80.00, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2569, 915, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2570, 916, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2571, 897, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2572, 917, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2573, 898, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2574, 899, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2575, 918, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2576, 900, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2577, 901, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2578, 902, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2579, 903, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2580, 904, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2581, 905, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2582, 919, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2583, 906, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2584, 907, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2585, 908, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2586, 909, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2587, 910, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2588, 911, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2589, 912, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2590, 913, 34, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:37:59', '2025-10-09 15:37:59'),
(2591, 914, 28, 30, 21, '2025-2026', 80.00, 80.00, 80.00, 80.00, 80.00, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:44:06'),
(2592, 915, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2593, 916, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2594, 897, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2595, 917, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2596, 898, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2597, 899, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2598, 918, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2599, 900, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2600, 901, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2601, 902, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2602, 903, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2603, 904, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2604, 905, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2605, 919, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2606, 906, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2607, 907, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2608, 908, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2609, 909, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2610, 910, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2611, 911, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2612, 912, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2613, 913, 28, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:38:36', '2025-10-09 15:38:36'),
(2614, 914, 33, 30, 21, '2025-2026', 74.00, 74.00, 74.00, 74.00, 74.00, 1, 0, 896, 896, '2025-10-09 15:44:50', '2025-10-10 00:09:30'),
(2615, 914, 24, 30, 21, '2025-2026', 80.00, 80.00, 80.00, 80.00, 80.00, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2616, 915, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2617, 916, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2618, 897, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2619, 917, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2620, 898, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2621, 899, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2622, 918, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2623, 900, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2624, 901, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2625, 902, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2626, 903, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2627, 904, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2628, 905, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2629, 919, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2630, 906, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2631, 907, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2632, 908, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2633, 909, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2634, 910, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2635, 911, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2636, 912, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2637, 913, 24, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-09 15:46:20', '2025-10-09 15:46:20'),
(2638, 915, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2639, 916, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2640, 897, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2641, 917, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2642, 898, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2643, 899, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2644, 918, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2645, 900, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2646, 901, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2647, 902, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2648, 903, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2649, 904, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2650, 905, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2651, 919, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2652, 906, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2653, 907, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2654, 908, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2655, 909, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2656, 910, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2657, 911, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2658, 912, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2659, 913, 33, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:30', '2025-10-10 00:09:30'),
(2660, 914, 31, 30, 21, '2025-2026', 74.00, 75.00, 75.00, 75.00, 74.75, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2661, 915, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2662, 916, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2663, 897, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2664, 917, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2665, 898, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2666, 899, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2667, 918, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2668, 900, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2669, 901, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2670, 902, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2671, 903, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2672, 904, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2673, 905, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2674, 919, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2675, 906, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2676, 907, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2677, 908, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2678, 909, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2679, 910, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2680, 911, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2681, 912, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2682, 913, 31, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:09:45', '2025-10-10 00:09:45'),
(2683, 914, 22, 30, 21, '2025-2026', 80.00, 80.00, 80.00, 80.00, 80.00, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:17:07'),
(2684, 915, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2685, 916, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2686, 897, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2687, 917, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2688, 898, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2689, 899, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2690, 918, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2691, 900, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2692, 901, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2693, 902, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2694, 903, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2695, 904, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2696, 905, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2697, 919, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2698, 906, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2699, 907, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2700, 908, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2701, 909, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2702, 910, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2703, 911, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2704, 912, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2705, 913, 22, 30, 21, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 896, 896, '2025-10-10 00:10:03', '2025-10-10 00:10:03'),
(2706, 921, 34, 31, 22, '2025-2026', 80.00, 80.00, 80.00, 80.00, 80.00, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2707, 935, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2708, 922, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2709, 923, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2710, 936, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2711, 937, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2712, 924, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2713, 938, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2714, 925, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2715, 939, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2716, 926, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2717, 927, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2718, 940, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2719, 928, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2720, 941, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2721, 929, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2722, 942, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2723, 930, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2724, 943, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2725, 944, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2726, 945, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2727, 931, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2728, 932, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2729, 933, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2730, 934, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2731, 946, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2732, 947, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2733, 948, 34, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:52:58', '2025-10-10 01:52:58'),
(2734, 921, 33, 31, 22, '2025-2026', 74.00, 74.00, 74.00, 74.00, 74.00, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2735, 935, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2736, 922, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2737, 923, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2738, 936, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2739, 937, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2740, 924, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2741, 938, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2742, 925, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2743, 939, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2744, 926, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2745, 927, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2746, 940, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2747, 928, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2748, 941, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2749, 929, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2750, 942, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2751, 930, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2752, 943, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2753, 944, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2754, 945, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2755, 931, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2756, 932, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2757, 933, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2758, 934, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2759, 946, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2760, 947, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2761, 948, 33, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:11', '2025-10-10 01:53:11'),
(2762, 921, 24, 31, 22, '2025-2026', 74.00, 74.00, 74.00, 74.00, 74.00, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2763, 935, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2764, 922, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2765, 923, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2766, 936, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2767, 937, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2768, 924, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2769, 938, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2770, 925, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2771, 939, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2772, 926, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2773, 927, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2774, 940, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2775, 928, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2776, 941, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2777, 929, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2778, 942, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2779, 930, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2780, 943, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2781, 944, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2782, 945, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2783, 931, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2784, 932, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2785, 933, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2786, 934, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2787, 946, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2788, 947, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25'),
(2789, 948, 24, 31, 22, '2025-2026', NULL, NULL, NULL, NULL, NULL, 1, 0, 920, 920, '2025-10-10 01:53:25', '2025-10-10 01:53:25');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `deleted` tinyint(4) DEFAULT 0,
  `added_by` int(11) DEFAULT NULL,
  `latest_edited_by` int(11) DEFAULT NULL,
  `added_date` datetime DEFAULT current_timestamp(),
  `latest_edited_date` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `code`, `name`, `status`, `deleted`, `added_by`, `latest_edited_by`, `added_date`, `latest_edited_date`) VALUES
(20, 'LANG', 'Language', 1, 0, NULL, NULL, '2025-09-24 16:08:04', '2025-09-24 16:08:04'),
(21, 'R&D', 'Reading & Literacy', 1, 0, NULL, NULL, '2025-09-24 16:08:28', '2025-09-24 16:08:28'),
(22, 'MATH', 'Mathematics', 1, 0, NULL, NULL, '2025-09-24 16:08:42', '2025-09-24 16:08:42'),
(23, 'SCI', 'Science', 1, 0, NULL, NULL, '2025-09-24 16:08:53', '2025-09-24 16:08:53'),
(24, 'MAKA / ARA', 'Makabansa/ Araling Panlipunan', 1, 0, NULL, NULL, '2025-09-24 16:10:10', '2025-09-24 16:10:10'),
(25, 'EPP / TLE', 'EPP / TLE', 1, 0, NULL, NULL, '2025-09-24 16:10:31', '2025-09-24 16:10:31'),
(26, 'MAPEH', 'MAPEH', 1, 0, NULL, NULL, '2025-09-24 16:10:44', '2025-09-24 16:10:44'),
(27, 'MUS', 'Music', 1, 0, NULL, NULL, '2025-09-24 16:10:56', '2025-09-24 16:10:56'),
(28, 'AR', 'Arts', 1, 0, NULL, NULL, '2025-09-24 16:11:14', '2025-09-24 16:11:14'),
(29, 'PHY', 'Physical Education', 1, 0, NULL, NULL, '2025-09-24 16:11:30', '2025-09-24 16:11:30'),
(30, 'HEA', 'Health', 1, 0, NULL, NULL, '2025-09-24 16:11:41', '2025-09-24 16:11:41'),
(31, 'GMRC', 'GMRC / ESP', 1, 0, NULL, NULL, '2025-09-24 16:12:02', '2025-09-24 16:12:02'),
(32, 'MT', 'Mother Tongue', 1, 0, NULL, NULL, '2025-09-24 20:24:46', '2025-09-25 14:34:36'),
(33, 'FIL', 'Filipino', 1, 0, NULL, NULL, '2025-09-24 21:20:12', '2025-09-24 21:20:12'),
(34, 'ENG', 'English', 1, 0, NULL, NULL, '2025-09-24 21:20:24', '2025-09-24 21:20:24');

-- --------------------------------------------------------

--
-- Table structure for table `system_info`
--

CREATE TABLE `system_info` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `descriptions` varchar(255) DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `city` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `zip_code` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_info`
--

INSERT INTO `system_info` (`id`, `title`, `descriptions`, `version`, `created_at`, `city`, `province`, `zip_code`) VALUES
(1, 'KAMPON NI JAMBERT', 'PROJECT NA MAKABOANG', '1', '2025-08-31 21:23:00', 'CAGAYAN DE ORO', 'MISAMIS ORIENTAL', '9018');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `password` varchar(45) DEFAULT NULL,
  `status` int(11) DEFAULT 1 COMMENT '0= not active, 1 = actrive',
  `verify` tinyint(4) DEFAULT 1 COMMENT '0 = not verify , 1 = verfit by admin, 2 = reject',
  `user_type` varchar(45) DEFAULT '2' COMMENT '1 = admin,   2 = user, 3 courier, 4 = shops',
  `role_id` tinyint(3) UNSIGNED DEFAULT 2,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `driver_status` int(11) DEFAULT NULL COMMENT '0 = available, 1 = not available',
  `token` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `deleted` int(11) DEFAULT 0,
  `account_first_name` varchar(255) DEFAULT NULL,
  `account_last_name` varchar(45) DEFAULT NULL,
  `account_display_name` varchar(45) DEFAULT NULL,
  `billing_address` varchar(45) DEFAULT NULL,
  `contact_no` varchar(45) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `emp_id` varchar(255) DEFAULT NULL,
  `account_middle_name` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `dateof_birth` date DEFAULT NULL,
  `nationality` varchar(255) DEFAULT NULL,
  `mother_tongue` varchar(100) DEFAULT NULL,
  `religion` varchar(255) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `LRN` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `house_street_sitio_purok` varchar(255) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `municipality_city` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `guardian` varchar(255) DEFAULT NULL,
  `relationship` varchar(255) DEFAULT NULL,
  `contact_no_of_parent` varchar(255) DEFAULT NULL,
  `learning_modality` varchar(255) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `batch` varchar(50) DEFAULT NULL,
  `set_group` varchar(50) DEFAULT NULL,
  `grade_level` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `status`, `verify`, `user_type`, `role_id`, `created_at`, `updated_at`, `driver_status`, `token`, `code`, `deleted`, `account_first_name`, `account_last_name`, `account_display_name`, `billing_address`, `contact_no`, `image`, `qr_code`, `emp_id`, `account_middle_name`, `gender`, `dateof_birth`, `nationality`, `mother_tongue`, `religion`, `department_id`, `LRN`, `address`, `house_street_sitio_purok`, `barangay`, `municipality_city`, `province`, `father_name`, `mother_name`, `guardian`, `relationship`, `contact_no_of_parent`, `learning_modality`, `remarks`, `batch`, `set_group`, `grade_level`) VALUES
(1, 'admin', 'admin@gmail.com', 'admin123', 1, 1, '1', 1, '2024-10-06 20:10:10', '2025-10-10 01:50:34', NULL, 'dea000eaae6c94e6c8cc73953d4ebec9', '758783', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(181, 'admin@gmail.com', '', 'admin123', 1, 1, '1', 1, '2025-04-26 20:46:10', '2025-10-07 23:25:44', NULL, '62c8c1fee77d28c8e431202b6429c6ce', '472848', 0, '', '', NULL, NULL, '', 'src/images/products/uploads/68c9a27b40b94_5.jpg', NULL, '', '', '1', '0000-00-00', '1', NULL, '1', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(868, 'villadores', 'erwin@gmail.com', 'password', 1, 1, '2', 2, '2025-10-09 13:52:16', '2025-10-09 14:09:29', NULL, 'b0ec71369089445f3ecce232c0193504', NULL, 0, 'Erwin', 'Villadores', NULL, NULL, '0976213125', NULL, NULL, NULL, 'S', '1', NULL, '1', NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(869, '127677240001', NULL, 'ABELLIDO', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 14:14:09', NULL, '5b9bb75a64c61406df3d4b6d6eb6f3bc', NULL, 0, 'BLAISE HARDEE', 'ABELLIDO', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240001.png', NULL, 'BALAT', 'Male', '2018-11-17', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240001', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'ABELLIDO, RANDY ACERO', 'BALAT,CHONA,SINAHON,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(870, '127677240002', NULL, 'ABELLIDO', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, 'cf411bc5f2fdc274b1c3c94c67ea5d32', NULL, 0, 'LURIEL', 'ABELLIDO', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240002.png', NULL, 'HAMLIG', 'Male', '2019-08-09', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240002', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'ABELLIDO, REDUARDO ACERO', 'HAMLIG,LUCIL,ECHANO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(871, '127677240004', NULL, 'ALQUISALAS', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, '7fc6bcf86b2cd6decec43d9bb79f7207', NULL, 0, 'JEDRICK', 'ALQUISALAS', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240004.png', NULL, 'LUMANTAS', 'Male', '2019-06-01', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240004', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'ALQUISALAS, PATRICK DAVE LUMAHANG', 'LUMANTAS,MILDRED,JARAMILLO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(872, '127677240003', NULL, 'ALVAREZ', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, 'e7762751415c4022ebb3f1175cc95831', NULL, 0, 'ALEXANDER', 'ALVAREZ', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240003.png', NULL, 'GABULE', 'Male', '2019-04-15', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240003', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'ALVAREZ, ISAGANI ALAM', 'GABULE,LOTCEL,AMILAO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(873, '127677240026', NULL, 'CERIALES', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, '1b207656ce82aea0a2069502af9fa115', NULL, 0, 'PETE EMMANUELLE', 'CERIALES', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240026.png', NULL, 'SAGUING', 'Male', '2018-11-02', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240026', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'CERIALES, RYAN LOFRANCO', 'SAGUING,MANILYN,FUENTES,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(874, '126642240080', NULL, 'MERIN', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, 'e91e19a9a41a3c81875147ad74a86b3a', NULL, 0, 'CARL DREDD MACKAROV', 'MERIN', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/126642240080.png', NULL, 'LAJERA', 'Male', '2019-04-24', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '126642240080', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'MERIN, CHARLIE IMAN', 'LAJERA,JAYCEEBELLE,GAMALO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(875, '127677240011', NULL, 'MUGOT', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, '986813dc9f73954ec1e62ba32ab15d37', NULL, 0, 'AXIL DANN', 'MUGOT', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240011.png', NULL, 'HEREDIANO', 'Male', '2019-01-23', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240011', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'MUGOT, JORDAN TABIOS', 'HEREDIANO,ANNALIZA,SUMAYLO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(876, '127677240033', NULL, 'RACINES', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, 'dbf7dff831536cbb614beeb4451eca12', NULL, 0, 'KIEFER', 'RACINES', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240033.png', NULL, 'JARAMILLO', 'Male', '2019-05-24', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240033', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'RACINES, RONALD BEHIGA', 'JARAMILLO,IRENE,LUNTAYAO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(877, '127677240052', NULL, 'SUIDAD', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, 'd177751f92042a358d23608ff7674d8e', NULL, 0, 'EZEQUILLE', 'SUIDAD', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240052.png', NULL, 'CALIBO', 'Male', '2019-04-09', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240052', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'SUIDAD, ROMY PASCO', 'CALIBO,MARY GRACE,SEGUDAN,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(878, '127677240036', NULL, 'TINOY', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, '97b3744b440ef4f2955c01db170590fe', NULL, 0, 'JEFF ZYRON', 'TINOY', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240036.png', NULL, 'SINADJAN', 'Male', '2019-08-04', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240036', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'TINOY, JIMBOY ALIM', 'SINADJAN,MAE RETCHELL,POVADORA,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(879, '127677240038', NULL, 'VANZUELA', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, 'd8466cb7f95a36bdeec5a0da372af7a0', NULL, 0, 'SIMPLY ZION', 'VANZUELA', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240038.png', NULL, 'ROXAS', 'Male', '2018-11-27', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240038', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'VANZUELA, ESTEBEN ABUCAYON', 'ROXAS,DINALYN,,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(880, '127677240013', NULL, 'ABRIOL', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, 'e9c0e3b08ad0dda157ec409e9f1e5a60', NULL, 0, 'KATELYN', 'ABRIOL', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240013.png', NULL, 'SARDALLA', 'Female', '2019-05-20', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240013', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'ABRIOL, JEMMER NAJOS', 'SARDALLA,MARY JANE,-,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(881, '127677240039', NULL, 'AGAN', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, '11ffe06e12b666517aed60e3c5cb9638', NULL, 0, 'CHRISPINE ERETHYL', 'AGAN', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240039.png', NULL, 'AMILAO', 'Female', '2019-09-01', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240039', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'AGAN, CHRIS POLD LEONARDO', 'AMILAO,MAXINE,RAÑIN,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(882, '127677240015', NULL, 'BACLAYO', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, 'c6095ebf78b9aac6e23f26998b10a02e', NULL, 0, 'AZIALAINE', 'BACLAYO', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240015.png', NULL, 'LUGTU', 'Female', '2019-03-09', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240015', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'BACLAYO, RALPH JERSON NOVAL', 'LUGTU,MARY ANGELOU,BERNARDO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(883, '127677240017', NULL, 'BALDON', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, '0d46143f1a054f848ef26b05054af3e8', NULL, 0, 'ALEYAH SELYN', 'BALDON', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240017.png', NULL, 'VALDE', 'Female', '2019-09-03', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240017', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'BALDON, SEGUNDO DAYATA JR', 'VALDE,JONALYN,-,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(884, '127677240041', NULL, 'BALDON', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, 'b6a1a0edcb6b3d8392aa22326ac23192', NULL, 0, 'IRA', 'BALDON', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240041.png', NULL, 'GOLOCINO', 'Female', '2018-11-05', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240041', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'BALDON, REY DAYATA', 'GOLOCINO,NELY,OMISOL,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(885, '127677240042', NULL, 'CAO', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, 'eee27a192d838e09c590edb5baf131a3', NULL, 0, 'BRITNEY', 'CAO', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240042.png', NULL, 'TUN-ANAN', 'Female', '2019-08-20', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240042', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'CAO, JOSEPH NAVARRETE', 'TUN-ANAN,CRISTINE,GAID,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(886, '127677240044', NULL, 'DEL ROSARIO', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, '97820c08f63599ed88ffa2d6c66f26a5', NULL, 0, 'GWYNETH', 'DEL ROSARIO', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240044.png', NULL, 'CELLACAY', 'Female', '2019-05-14', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240044', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'DEL ROSARIO, ROLANDO MAHINAY', 'CELLACAY,GINA,BERINDES,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(887, '127677240018', NULL, 'GABULE', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, 'e15ecbef8002cdede88c9594a2b5b520', NULL, 0, 'KASSIE', 'GABULE', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240018.png', NULL, 'DAANG', 'Female', '2019-01-19', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240018', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'GABULE, GENESIS PORTRIAS', 'DAANG,ALLECX,AMOGUIS,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(888, '127677240019', NULL, 'GANNABAN', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, 'bd6ebac1e2eb7538c04c0ffd3633857a', NULL, 0, 'MARIAH QUINN', 'GANNABAN', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240019.png', NULL, 'MEDALLA', 'Female', '2019-08-03', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240019', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'GANNABAN, MARK YAMIT', 'MEDALLA,ALLIYA,P,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(889, '127677240045', NULL, 'GUNGOB', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, '277b10e822a7e699c0848c87ceaf41ef', NULL, 0, 'DECY GRACE', 'GUNGOB', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240045.png', NULL, 'MONTEPULCA', 'Female', '2018-12-04', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240045', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'GUNGOB, EDUARD LARGO', 'MONTEPULCA,JUDY ANN,ORENDAIN,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(890, '127677240046', NULL, 'INOC', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, '67e19a0eac184d04b3546843a74cc0fb', NULL, 0, 'AQUIA ZHAVIA', 'INOC', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240046.png', NULL, 'SABANDIJA', 'Female', '2019-07-15', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240046', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'INOC, AUGYMAR CAMUS', 'SABANDIJA,ANGELIKA NIÑA,SAMSON,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(891, '127677240021', NULL, 'LUMAJANG', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, '5498ae8314a31dd24a4d872f329f51a7', NULL, 0, 'YRHEL', 'LUMAJANG', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240021.png', NULL, 'AMPILAN', 'Female', '2019-09-06', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240021', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'LUMAJANG, JULIUS SALVADOR', 'AMPILAN,CHERRY MAE,BARLUSCA,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(892, '127677240047', NULL, 'MAASIN', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, '26dfa1b1a005c6c167ad14bb4805a873', NULL, 0, 'REONNA ESABELLE', 'MAASIN', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240047.png', NULL, 'MACANIP', 'Female', '2019-01-10', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240047', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'MAASIN, DINNES ESTRADA', 'MACANIP,MYLEN,LINRES,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(893, '127677240048', NULL, 'QUIBOL', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, '19818937dad51c42555335bf4fc1059c', NULL, 0, 'GREYLOU', 'QUIBOL', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240048.png', NULL, 'ALIEM', 'Female', '2019-03-28', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240048', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'QUIBOL, GREGORIO BACLAYON', 'ALIEM,MARICEL,VALLE,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(894, '127677240022', NULL, 'TABIOS', 1, 1, '5', 2, '2025-10-09 13:53:22', '2025-10-09 13:53:22', NULL, '0ae507acdd0eb7ebafbb2f772f59df9e', NULL, 0, 'SHAWNTEL CHEN', 'TABIOS', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240022.png', NULL, 'BACLEON', 'Female', '2019-04-03', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240022', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'TABIOS, SHARIM PALASAN', 'BACLEON,CHEENIE,SAYAGNAO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 HONESTY', 'Grade 1'),
(895, 'principal@gmail.com', 'principal@gmail.com', 'password', 1, 1, '3', 3, '2025-10-09 14:12:32', '2025-10-09 14:12:45', NULL, '121772fbfef8a7b209c2d27b739847c0', NULL, 0, 'Principal', 'Pidot', NULL, NULL, NULL, NULL, NULL, '12345', 'P', '1', NULL, '1', NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(896, 'baanan@gmail.com', 'baanan@gmail.com', 'password', 1, 1, '2', 2, '2025-10-09 15:35:11', '2025-10-09 23:25:01', NULL, '6eb6d7dfb152334a87b45e5334a57c49', NULL, 0, 'Paul', 'Baanan', NULL, NULL, NULL, NULL, NULL, '1231', 'Banana', '1', NULL, '1', NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(897, '127677240024', NULL, 'BACULIO', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, 'dd7b55271157c34fc12aad274e644d73', NULL, 0, 'HAROLD', 'BACULIO', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240024.png', NULL, 'JUMARITO', 'Male', '2019-02-08', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240024', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'BACULIO, HERSON ECHAVEZ', 'JUMARITO,JULIE,MAGPULONG,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(898, '127677240005', NULL, 'BESINGA', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, 'd5fcae50b54de8e493a4a951d747158f', NULL, 0, 'MAVY', 'BESINGA', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240005.png', NULL, 'BAHIAN', 'Male', '2019-08-11', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240005', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'BESINGA, ANGELITO ESCOL', 'BAHIAN,APPLE,LABIS,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(899, '127677240023', NULL, 'CABUSAS', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, '6bcc9691e34a89c79b97bb1426def309', NULL, 0, 'ZIAN KUERT', 'CABUSAS', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240023.png', NULL, 'JABLA', 'Male', '2019-07-13', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240023', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'CABUSAS, NELMAR VALDERAMA', 'JABLA,AIZA,TABIOS,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(900, '127677240006', NULL, 'DANGGOY', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, '205e5754c7088cdae25678c60aad0347', NULL, 0, 'JHON KERBY', 'DANGGOY', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240006.png', NULL, 'ENCABO', 'Male', '2019-04-22', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240006', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'DANGGOY, GERALD LABIS', 'ENCABO,KRISNALIE,CANONIGO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(901, '127677240027', NULL, 'DEROTAS', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, '2326226d3033a6e69ae54a1adf0b5537', NULL, 0, 'KIAN ALEC', 'DEROTAS', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240027.png', NULL, '-', 'Male', '2018-11-06', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240027', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', NULL, 'DEROTAS,RYAN MARIE,-,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(902, '127677240007', NULL, 'FABRICANTE', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, '15c5073a28b86f8f95a8f89e6d881182', NULL, 0, 'LUIJIE', 'FABRICANTE', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240007.png', NULL, 'UBAUB', 'Male', '2019-01-16', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240007', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'FABRICANTE, ERIC RIVERA', 'UBAUB,IVY,YANA,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(903, '127677240008', NULL, 'GABULE', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, 'a8780cbdd62145556fc49411286f2ced', NULL, 0, 'REY', 'GABULE', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240008.png', NULL, 'NOB', 'Male', '2019-02-16', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240008', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'GABULE, RUSTY AMILAO', 'NOB,VIOLETA,CABIGQUEZ,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(904, '127677240049', NULL, 'GAID', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, '51bb0effd47963cccc74055215475005', NULL, 0, 'LUIS EZEKYLE', 'GAID', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240049.png', NULL, 'GATANG', 'Male', '2019-04-05', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240049', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'GAID, MARK SALINAS', 'GATANG,ANGELICA,DOCINOS,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(905, '127677240028', NULL, 'IMUS', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, 'a144ec9f4b89c6a9b3b8d4590b72d572', NULL, 0, 'MARK JAYZAM', 'IMUS', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240028.png', NULL, 'CUGAY', 'Male', '2019-08-15', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240028', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'IMUS, JAY ARH SANCHEZ', 'CUGAY,MARYN,DIANGO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(906, '127677240009', NULL, 'LAMBERTE', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, '06bc382d3d4d5098d2b66e2acf98c4fa', NULL, 0, 'NOAH', 'LAMBERTE', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240009.png', NULL, 'JANOPLO', 'Male', '2019-06-24', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240009', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'LAMBERTE, JONWEL COLANSE', 'JANOPLO,CHARLENE,RAMOS,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(907, '127677240010', NULL, 'MACUGAY', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, 'ddca60977f437130dbd86612179dae42', NULL, 0, 'ROY ZACHARIE', 'MACUGAY', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240010.png', NULL, '-', 'Male', '2019-06-12', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240010', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', NULL, 'MACUGAY,ALLYSA JOY,YADAO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(908, '127677240029', NULL, 'MURILLO', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, '05bb19b23d325328edd1e23caf5e7261', NULL, 0, 'KIRT ANDRIE', 'MURILLO', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240029.png', NULL, 'BUSIÑOS', 'Male', '2019-09-12', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240029', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'MURILLO, PATRICK JAPUS', 'BUSIÑOS,BABY RHEA,BAHIAN,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(909, '127677240032', NULL, 'QUIBOL', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, 'ce78359d50c64fe9cb05cf149bfd2250', NULL, 0, 'KURT ALLYN DAVE', 'QUIBOL', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240032.png', NULL, '-', 'Male', '2018-12-30', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240032', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', NULL, 'QUIBOL,NIRYL PHOBELL,BACULIO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(910, '127677240034', NULL, 'SAGUING', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, '3b37e9f9930c859b27a6eb2198735831', NULL, 0, 'MHYCO ANGELO', 'SAGUING', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240034.png', NULL, '-', 'Male', '2018-12-27', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240034', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', NULL, 'SAGUING,MIKA ANGELA,LAGARIT,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(911, '127677240012', NULL, 'TAGALUGUIN', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, 'c8a2abb36289eb7295be5def2c8378b1', NULL, 0, 'ZAYN KELLIAN', 'TAGALUGUIN', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240012.png', NULL, 'ESTRADA', 'Male', '2019-04-14', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240012', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'TAGALUGUIN, ANDY MALUPAY', 'ESTRADA,IRIS,CABANAS,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(912, '126642230164', NULL, 'TAÑECA', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, '257ba8c83e576da63e3a40636db2acc3', NULL, 0, 'LAURENZ GREY', 'TAÑECA', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/126642230164.png', NULL, '-', 'Male', '2018-08-07', NULL, 'Cebuano', 'Christianity', NULL, '126642230164', NULL, NULL, 'AGUSAN CANYON', 'MANOLO FORTICH', 'BUKIDNON', NULL, 'TAÑECA,GLORY LANIE MAE,LUHORAN,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(913, '127677240037', NULL, 'UBARCO', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, '31165d357888fd37a2e0f61b22edc543', NULL, 0, 'ETHAN', 'UBARCO', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240037.png', NULL, 'PATOLILIC', 'Male', '2019-01-28', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240037', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'UBARCO, FREDERICO JARAMILLO', 'PATOLILIC,ROWENA,LOMONGO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(914, '127677240053', NULL, 'ABUEVA', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, '418667a86f123e99ec417bb2bcd58388', NULL, 0, 'EUGENN ANTHONEE', 'ABUEVA', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240053.png', NULL, 'UBA', 'Female', '2018-02-28', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240053', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'ABUEVA, RAYMUND VILLALON', 'UBA,RIA,LLEJES,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(915, '127677240040', NULL, 'ANGUS', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, '7365b7425763cb876e55a338fc93b795', NULL, 0, 'CLAUBELLE MARGARETH', 'ANGUS', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240040.png', NULL, 'BACULIO', 'Female', '2019-01-31', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240040', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'ANGUS, MARK LLOYD MAHILAC', 'BACULIO,RENEBELLE,LUNDAY,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(916, '127677240014', NULL, 'APAG', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, 'eac040fbdaa789eae729b853bdd7f160', NULL, 0, 'CHASLY JANE', 'APAG', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240014.png', NULL, 'JABAGAT', 'Female', '2019-01-03', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240014', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'APAG, DOMINGO EBAL JR', 'JABAGAT,JINKY,TAYTAY,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(917, '127677240016', NULL, 'BAHIAN', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, '402ce2be33aa1d266a534e49530bca76', NULL, 0, 'HEAVEN MAY', 'BAHIAN', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240016.png', NULL, 'COLUBIO', 'Female', '2019-05-14', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240016', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'BAHIAN, JEFFREY SALINAS', 'COLUBIO,MELISSA,BLANCO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(918, '127677240043', NULL, 'CARALDE', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, '756eea453ea92563f2a49c3e118acc9e', NULL, 0, 'KITTHINE', 'CARALDE', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240043.png', NULL, 'RAUTO', 'Female', '2019-01-21', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240043', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'CARALDE, JEPSIE SANCHEZ', 'RAUTO,KRISTINA,PELENIO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(919, '127677240020', NULL, 'LABIS', 1, 1, '5', 2, '2025-10-09 15:35:34', '2025-10-09 15:35:34', NULL, 'c8b67d591f33a117ad24acfda23e252e', NULL, 0, 'AMARAH EUNICE', 'LABIS', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677240020.png', NULL, 'SUNDO', 'Female', '2019-03-23', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677240020', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'LABIS, RENE ESTRADA', 'SUNDO,HELEN,ABELLIDO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 1 VIBRANT', 'Grade 1'),
(920, 'tagaan@gmail.com', 'tagaan@gmail.com', 'password', 1, 1, '2', 2, '2025-10-10 01:51:12', '2025-10-10 01:52:41', NULL, '122235095e64e0165bcf96bbe9bfddc0', NULL, 0, 'Rudgel', 'Tagaan', NULL, NULL, NULL, NULL, NULL, NULL, 'Bayot', '1', NULL, '1', NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(921, '127677230014', NULL, 'ABA', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:07', NULL, '94a4b07ff94169c3d8c25121c34fa7e5', NULL, 0, 'JACOB', 'ABA', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230014.png', NULL, 'IMUS', 'Male', '2018-10-12', NULL, 'Cebuano / Sinugbuanong Binisay', 'Islam', NULL, '127677230014', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'ABA, ELVIN NAVALES', 'IMUS,JANETH,BRONOLA,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(922, '127994230116', NULL, 'ABELLIDO', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:07', NULL, '638a5c5dc369a6b448fe8f81eac80e25', NULL, 0, 'KHENT JOHNREY', 'ABELLIDO', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127994230116.png', NULL, 'LACRE', 'Male', '2017-04-06', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127994230116', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'ABELLIDO, JOHNREY NERI', 'LACRE,RELLY ANN,IMBAT,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(923, '127677230030', NULL, 'AGAD', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:07', NULL, '6f31bdd0c13a2800f989ab1d39f51c5e', NULL, 0, 'RALF ECKO', 'AGAD', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230030.png', NULL, 'PLAZA', 'Male', '2018-05-20', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230030', NULL, NULL, 'POBLACION', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'AGAD, FRANCIS ERIC SENERPIDA', 'PLAZA,QUEENLYN,BACO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(924, '127677230015', NULL, 'BAHIAN', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:07', NULL, '0c87ee87d039ad9aeb9d0f2395d37f65', NULL, 0, 'REYMARK ISAACH', 'BAHIAN', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230015.png', NULL, '-', 'Male', '2018-01-21', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230015', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', NULL, 'BAHIAN,STEPHANIE MAE,KILEM,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(925, '127677230031', NULL, 'BRAULIO', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:07', NULL, '2825343d15d3b1653f094295220607e2', NULL, 0, 'MARK JM', 'BRAULIO', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230031.png', NULL, 'CAYUBIT', 'Male', '2018-03-23', NULL, 'Cebuano/Kana/Sinugboanong Bini', 'Christianity', NULL, '127677230031', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'BRAULII, JADE ESTRAÑERO', 'CAYUBIT,APPLE MAE,BACULIO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(926, '127677230016', NULL, 'DIANGO', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:07', NULL, 'a99fda4783a7447eb48728be3c1766f7', NULL, 0, 'KENT RENE', 'DIANGO', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230016.png', NULL, 'CADUTDUT', 'Male', '2018-04-12', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230016', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'DIANGO, RENE SUNDO', 'CADUTDUT,JENELYN,ARDIENTE,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(927, '127677230017', NULL, 'DUHAYLUNGSOD', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:07', NULL, 'de595d6f5a21fd9c2a2d0202a22c8963', NULL, 0, 'EUGENE', 'DUHAYLUNGSOD', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230017.png', NULL, 'GABULE', 'Male', '2018-02-16', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230017', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'DUHAYLUNGSOD, RENE AGAN', 'GABULE,SIOBE,GEMINA,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(928, '127677230018', NULL, 'GAID', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:07', NULL, 'fa06e77547da2079822343e025b8b025', NULL, 0, 'LUKE EZEKIEL', 'GAID', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230018.png', NULL, 'GATANG', 'Male', '2018-05-02', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230018', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'GAID, MARK SALINAS', 'GATANG,ANGELA,DOCINOS,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(929, '127677230043', NULL, 'KAAMIÑO', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:07', NULL, '8bb169f83229963436c610310ebea3a2', NULL, 0, 'FLYN', 'KAAMIÑO', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230043.png', NULL, 'DEDIOS', 'Male', '2018-01-04', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230043', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'KAAMIÑO, JUNE RAY NUÑEZ', 'DEDIOS,CATHERINE,TABIOS,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(930, '127677230044', NULL, 'MAGANTE', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:07', NULL, '434589571e61085d8d01d2a629394124', NULL, 0, 'JACOBB', 'MAGANTE', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230044.png', NULL, 'BAGAS', 'Male', '2018-01-25', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230044', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'MAGANTE, ANGELO RAPOY', 'BAGAS,JENNIE,AGBO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(931, '127677230046', NULL, 'PABINGWIT', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:07', NULL, '0291c4d0d4043b0e204fd092520f20d6', NULL, 0, 'MARVIN', 'PABINGWIT', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230046.png', NULL, 'PAJARON', 'Male', '2018-10-29', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230046', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'PABINGWIT, ALVIN DUCLAN', 'PAJARON,MARRY JOY,YANGCO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(932, '127677230019', NULL, 'PAGLINAWAN', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:07', NULL, 'cb4e6db3cff6f03dffcdd3257a15d92e', NULL, 0, 'LEANDER RUJJ', 'PAGLINAWAN', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230019.png', NULL, 'ALAVANZA', 'Male', '2018-07-30', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230019', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'PAGLINAWAN, JURRY ABUHAN', 'ALAVANZA,LEZYL,RATUNIL,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(933, '127677230052', NULL, 'PALLORINA', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:07', NULL, 'abfbf17814f2a864ebd5f9ea048e577e', NULL, 0, 'JOEBETH', 'PALLORINA', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230052.png', NULL, 'JR TAGANOS', 'Male', '2017-11-10', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230052', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'PALLORINA, JOEBETH NOVILLAS', 'TAGANOS,SHERLEY,PAIRAT,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(934, '127677230006', NULL, 'SALVADOR', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:07', NULL, 'e5e28e2e44595ef04398a56fb5be0b93', NULL, 0, 'JOHN GREYSON', 'SALVADOR', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230006.png', NULL, 'CAGO', 'Male', '2018-07-01', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230006', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'SALVADOR, JOHN ANTHONY CLARIN', 'CAGO,MANILYN,MALINAWON,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(935, '127677230035', NULL, 'ABABON', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:07', NULL, '3a9adb1c609f47d6feb7e64ac5bb76ac', NULL, 0, 'YHENNA SHEEN', 'ABABON', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230035.png', NULL, 'LABIS', 'Female', '2018-04-07', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230035', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'ABABON, MARK ANTHONY DIJAN', 'LABIS,PATRICIA SHANNEL,ESTRADA,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(936, '127677230036', NULL, 'AMILIG', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:07', NULL, '6ee95af15d382aae9534281d93beab28', NULL, 0, 'SHARINA', 'AMILIG', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230036.png', NULL, 'ADAYA', 'Female', '2018-06-05', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230036', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'AMILIG, RODEL DE LA CRUZ', 'ADAYA,MARY GRACE,OMAS-AS,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(937, '127677230047', NULL, 'ARIO', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:07', NULL, 'd179301d34060569930b52fcd172655a', NULL, 0, 'ISABELLA SOPHIA FARRAH', 'ARIO', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230047.png', NULL, 'CANONO', 'Female', '2017-12-11', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230047', NULL, NULL, NULL, 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'ARIO, MECBAR SEDDONG', 'CANONO,ANGELICA,BOÑOL,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(938, '127677230022', NULL, 'BARROZO', 1, 1, '5', 2, '2025-10-10 01:52:07', '2025-10-10 01:52:08', NULL, 'b49943489de4af7ed222e75d59cc8751', NULL, 0, 'ZNIKEA', 'BARROZO', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230022.png', NULL, '-', 'Female', '2018-10-25', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230022', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', NULL, 'BARROZO,ZYRA,IMUS,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(939, '127677230055', NULL, 'CAO', 1, 1, '5', 2, '2025-10-10 01:52:08', '2025-10-10 01:52:08', NULL, '4453b78b4626695f872dcfbad09abdfc', NULL, 0, 'NATHALIE', 'CAO', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230055.png', NULL, 'TUN-ANAN', 'Female', '2018-01-28', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230055', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', NULL, 'TUN-ANAN,CRISTINE,-,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(940, '127669230016', NULL, 'ESTRADA', 1, 1, '5', 2, '2025-10-10 01:52:08', '2025-10-10 01:52:08', NULL, 'a549b45dfd5068f546b8a62437805fd9', NULL, 0, 'PRINCESS ALZHIA', 'ESTRADA', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127669230016.png', NULL, 'DIANGO', 'Female', '2018-06-10', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127669230016', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'ESTRADA, RALPH OSEP', 'DIANGO,SHIELA MAE,PAHIS,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(941, '106766220036', NULL, 'IMUS', 1, 1, '5', 2, '2025-10-10 01:52:08', '2025-10-10 01:52:08', NULL, '22c34b20f99ac96ad49f3cab9436e0bd', NULL, 0, 'ALTHEA KATE', 'IMUS', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/106766220036.png', NULL, 'SAYGAN', 'Female', '2017-08-13', NULL, 'Tagalog', 'Christianity', NULL, '106766220036', NULL, NULL, 'SAN RAFAEL', 'CITY OF TARLAC (Capital)', 'TARLAC', 'IMUS, RONALD ABA', 'SAYGAN,JAYLYN,TONGOL,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(942, '127677230024', NULL, 'MAGALLANES', 1, 1, '5', 2, '2025-10-10 01:52:08', '2025-10-10 01:52:08', NULL, 'c39fa6c9fb79ebd6278eec3f26029761', NULL, 0, 'ALTHEA MARIE', 'MAGALLANES', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230024.png', NULL, 'DIANGO', 'Female', '2018-02-19', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230024', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'MAGALLANES, RONALD EBCAS', 'DIANGO,ANA MARIE,SUNDO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(943, '127676230120', NULL, 'MANTUA', 1, 1, '5', 2, '2025-10-10 01:52:08', '2025-10-10 01:52:08', NULL, '1d95fab121422bafaf208eb7ffcb175f', NULL, 0, 'KYLINE JOY', 'MANTUA', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127676230120.png', NULL, '-', 'Female', '2018-04-08', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127676230120', NULL, NULL, 'MOLUGAN', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'HINOG, RAYFIL MACARAIG', 'MANTUA,JOY,ESTREMOS,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(944, '127677230040', NULL, 'MARA', 1, 1, '5', 2, '2025-10-10 01:52:08', '2025-10-10 01:52:08', NULL, 'e3c5b77488c079f280cdc4342efa2b67', NULL, 0, 'ANDREA ROSE', 'MARA', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230040.png', NULL, 'LABALAN', 'Female', '2018-10-22', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230040', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'MARA, ANDRE BONN DIGANG', 'LABALAN,ROSE MARIE,-,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(945, '127677230025', NULL, 'NATINO', 1, 1, '5', 2, '2025-10-10 01:52:08', '2025-10-10 01:52:08', NULL, '94114075b5e8e7c9023063c9cac0e146', NULL, 0, 'ARCHELYN', 'NATINO', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230025.png', NULL, 'ADOBAR', 'Female', '2018-05-24', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230025', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'NATINO, GARRY PERCALES', 'ADOBAR,RUBIELYN,PAZ,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(946, '127677230050', NULL, 'SUANER', 1, 1, '5', 2, '2025-10-10 01:52:08', '2025-10-10 01:52:08', NULL, '6e518ffd0e0f7d52ab0e9444dee2da62', NULL, 0, 'DIVINE', 'SUANER', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230050.png', NULL, 'ACLA', 'Female', '2018-06-28', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230050', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'SUANER, ALVIN TAPALES', 'ACLA,EMILY,OBEDENCIO,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(947, '501880230007', NULL, 'TABIOS', 1, 1, '5', 2, '2025-10-10 01:52:08', '2025-10-10 01:52:08', NULL, '06250b51e958d572a67602b8b12de8ec', NULL, 0, 'VIANCEY', 'TABIOS', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/501880230007.png', NULL, 'MACAHIS', 'Female', '2018-08-19', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '501880230007', NULL, NULL, 'SAPANG AMA', 'SAPANG DALAGA', 'MISAMIS OCCIDENTAL', 'TABIOS, ALDEI NAVARRETTE', 'MACAHIS,WELVELYN,TARNATE,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2'),
(948, '127677230056', NULL, 'TAGANOS', 1, 1, '5', 2, '2025-10-10 01:52:08', '2025-10-10 01:52:08', NULL, 'fcacf035e1447e6ce8ea5d06d03c7f39', NULL, 0, 'LORIE XYRIEL', 'TAGANOS', NULL, NULL, NULL, '/src/images/logos/OIP-removebg-preview.png', '/uploads/qrcodes/127677230056.png', NULL, 'SABACAHAN', 'Female', '2018-09-14', NULL, 'Cebuano / Sinugbuanong Binisay', 'Christianity', NULL, '127677230056', NULL, NULL, 'BOLOBOLO', 'CITY OF EL SALVADOR', 'MISAMIS ORIENTAL', 'TAGANOS, LARRY PAIRAT', 'SABACAHAN,LEONISA,HONALOC,', NULL, NULL, NULL, 'Face to Face', NULL, '2025 - 2026', 'Grade 2 HOSPITABLE', 'Grade 2');

-- --------------------------------------------------------

--
-- Table structure for table `view_page`
--

CREATE TABLE `view_page` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `datetime` datetime DEFAULT current_timestamp(),
  `token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_curriculum_code_and_id`
-- (See below for the actual view)
--
CREATE TABLE `v_curriculum_code_and_id` (
`code` text
,`id` int(11)
);

-- --------------------------------------------------------

--
-- Structure for view `v_curriculum_code_and_id`
--
DROP TABLE IF EXISTS `v_curriculum_code_and_id`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_curriculum_code_and_id`  AS SELECT concat(replace(trim(`c`.`school_year`),' ',''),`gl`.`name`,`s`.`name`) AS `code`, `c`.`id` AS `id` FROM (((`curriculum` `c` join `section` `s` on(`s`.`id` = `c`.`grade_id`)) join `grade_level` `gl` on(`gl`.`id` = `s`.`grade_id`)) join `users` `u` on(`u`.`user_id` = `c`.`adviser_id`)) WHERE `c`.`deleted` = 0 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `audience_scope` (`audience_scope`),
  ADD KEY `status` (`status`),
  ADD KEY `deleted` (`deleted`);

--
-- Indexes for table `attendance_holidays`
--
ALTER TABLE `attendance_holidays`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_scdy` (`section_id`,`curriculum_id`,`date`);

--
-- Indexes for table `curriculum`
--
ALTER TABLE `curriculum`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_curriculum_adviser_id` (`adviser_id`),
  ADD KEY `fk_curriculum_section_id` (`grade_id`);

--
-- Indexes for table `curriculum_child`
--
ALTER TABLE `curriculum_child`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_curriculum_child_curriculum_id` (`curriculum_id`),
  ADD KEY `fk_curriculum_child_subject_id` (`subject_id`),
  ADD KEY `fk_curriculum_child_adviser_id` (`adviser_id`);

--
-- Indexes for table `grade_level`
--
ALTER TABLE `grade_level`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `registrar_student`
--
ALTER TABLE `registrar_student`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_registrar_student_curriculum_id` (`curriculum_id`),
  ADD KEY `fk_registrar_student_section_id` (`section_id`),
  ADD KEY `fk_registrar_student_user_id` (`student_id`);

--
-- Indexes for table `registrar_student_orphans`
--
ALTER TABLE `registrar_student_orphans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fk_registrar_student_curriculum_id` (`curriculum_id`),
  ADD KEY `fk_registrar_student_section_id` (`section_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_slug` (`role_slug`);

--
-- Indexes for table `section`
--
ALTER TABLE `section`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_section_grade_id` (`grade_id`),
  ADD KEY `fk_section_adviser_id` (`adviser_id`),
  ADD KEY `fk_section_added_by` (`added_by`),
  ADD KEY `fk_section_edited_by` (`latest_edited_by`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `LRN` (`LRN`),
  ADD KEY `fk_students_added_by` (`added_by`),
  ADD KEY `fk_students_edited_by` (`latest_edited_by`);

--
-- Indexes for table `student_attendance`
--
ALTER TABLE `student_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_attendance_once_per_day` (`student_id`,`curriculum_id`,`attendance_date`),
  ADD KEY `fk_sa_curriculum` (`curriculum_id`),
  ADD KEY `fk_sa_added_by` (`added_by`),
  ADD KEY `fk_sa_edited_by` (`latest_edited_by`),
  ADD KEY `idx_attendance_lookup` (`section_id`,`curriculum_id`,`attendance_date`),
  ADD KEY `idx_sa_student_date_curriculum` (`student_id`,`curriculum_id`,`attendance_date`);

--
-- Indexes for table `student_remedial_classes`
--
ALTER TABLE `student_remedial_classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_remedial_ssg` (`ssg_id`),
  ADD KEY `fk_remedial_subject` (`subject_id`),
  ADD KEY `fk_remedial_added_by` (`added_by`),
  ADD KEY `fk_remedial_edited_by` (`latest_edited_by`);

--
-- Indexes for table `student_subject_core_values`
--
ALTER TABLE `student_subject_core_values`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sscv_ssg` (`ssg_id`),
  ADD KEY `idx_sscv_student_subject_curriculum` (`student_id`,`subject_id`,`curriculum_id`),
  ADD KEY `fk_sscv_added_by` (`added_by`),
  ADD KEY `fk_sscv_edited_by` (`latest_edited_by`);

--
-- Indexes for table `student_subject_core_values_rows`
--
ALTER TABLE `student_subject_core_values_rows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ssg_core_beh` (`ssg_id`,`core_name`,`behavior_index`),
  ADD KEY `idx_ssg_core` (`ssg_id`,`core_name`);

--
-- Indexes for table `student_subject_grades`
--
ALTER TABLE `student_subject_grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ssg_student_subject_curriculum` (`student_id`,`subject_id`,`curriculum_id`),
  ADD UNIQUE KEY `uq_ssg` (`student_id`,`subject_id`,`curriculum_id`),
  ADD KEY `fk_ssg_subject` (`subject_id`),
  ADD KEY `fk_ssg_curriculum` (`curriculum_id`),
  ADD KEY `fk_ssg_section` (`section_id`),
  ADD KEY `fk_ssg_added_by` (`added_by`),
  ADD KEY `fk_ssg_edited_by` (`latest_edited_by`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_subjects_added_by` (`added_by`),
  ADD KEY `fk_subjects_edited_by` (`latest_edited_by`);

--
-- Indexes for table `system_info`
--
ALTER TABLE `system_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `fk_users_role_id` (`role_id`),
  ADD KEY `idx_users_batch_set_group` (`batch`,`set_group`);

--
-- Indexes for table `view_page`
--
ALTER TABLE `view_page`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vp_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance_holidays`
--
ALTER TABLE `attendance_holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `curriculum`
--
ALTER TABLE `curriculum`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `curriculum_child`
--
ALTER TABLE `curriculum_child`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `grade_level`
--
ALTER TABLE `grade_level`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=483;

--
-- AUTO_INCREMENT for table `registrar_student`
--
ALTER TABLE `registrar_student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `registrar_student_orphans`
--
ALTER TABLE `registrar_student_orphans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `section`
--
ALTER TABLE `section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_attendance`
--
ALTER TABLE `student_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `student_remedial_classes`
--
ALTER TABLE `student_remedial_classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student_subject_core_values`
--
ALTER TABLE `student_subject_core_values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student_subject_core_values_rows`
--
ALTER TABLE `student_subject_core_values_rows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `student_subject_grades`
--
ALTER TABLE `student_subject_grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2790;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `system_info`
--
ALTER TABLE `system_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=949;

--
-- AUTO_INCREMENT for table `view_page`
--
ALTER TABLE `view_page`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `curriculum`
--
ALTER TABLE `curriculum`
  ADD CONSTRAINT `fk_curriculum_adviser_id` FOREIGN KEY (`adviser_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_curriculum_section_id` FOREIGN KEY (`grade_id`) REFERENCES `section` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `curriculum_child`
--
ALTER TABLE `curriculum_child`
  ADD CONSTRAINT `fk_curriculum_child_adviser_id` FOREIGN KEY (`adviser_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_curriculum_child_curriculum_id` FOREIGN KEY (`curriculum_id`) REFERENCES `curriculum` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_curriculum_child_subject_id` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `registrar_student`
--
ALTER TABLE `registrar_student`
  ADD CONSTRAINT `fk_registrar_student_curriculum_id` FOREIGN KEY (`curriculum_id`) REFERENCES `curriculum` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_registrar_student_section_id` FOREIGN KEY (`section_id`) REFERENCES `section` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_registrar_student_user_id` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `section`
--
ALTER TABLE `section`
  ADD CONSTRAINT `fk_section_added_by` FOREIGN KEY (`added_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_section_adviser_id` FOREIGN KEY (`adviser_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_section_edited_by` FOREIGN KEY (`latest_edited_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_section_grade_id` FOREIGN KEY (`grade_id`) REFERENCES `grade_level` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_added_by` FOREIGN KEY (`added_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_students_edited_by` FOREIGN KEY (`latest_edited_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `student_attendance`
--
ALTER TABLE `student_attendance`
  ADD CONSTRAINT `fk_sa_added_by` FOREIGN KEY (`added_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sa_edited_by` FOREIGN KEY (`latest_edited_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sa_section` FOREIGN KEY (`section_id`) REFERENCES `section` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `student_remedial_classes`
--
ALTER TABLE `student_remedial_classes`
  ADD CONSTRAINT `fk_remedial_added_by` FOREIGN KEY (`added_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_remedial_edited_by` FOREIGN KEY (`latest_edited_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_remedial_ssg` FOREIGN KEY (`ssg_id`) REFERENCES `student_subject_grades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_remedial_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_subject_core_values`
--
ALTER TABLE `student_subject_core_values`
  ADD CONSTRAINT `fk_sscv_added_by` FOREIGN KEY (`added_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sscv_edited_by` FOREIGN KEY (`latest_edited_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sscv_ssg` FOREIGN KEY (`ssg_id`) REFERENCES `student_subject_grades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_subject_core_values_rows`
--
ALTER TABLE `student_subject_core_values_rows`
  ADD CONSTRAINT `fk_sscv_rows_ssg` FOREIGN KEY (`ssg_id`) REFERENCES `student_subject_grades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_subject_grades`
--
ALTER TABLE `student_subject_grades`
  ADD CONSTRAINT `fk_ssg_added_by` FOREIGN KEY (`added_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ssg_curriculum` FOREIGN KEY (`curriculum_id`) REFERENCES `curriculum` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ssg_edited_by` FOREIGN KEY (`latest_edited_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ssg_section` FOREIGN KEY (`section_id`) REFERENCES `section` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ssg_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ssg_user` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `fk_subjects_added_by` FOREIGN KEY (`added_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_subjects_edited_by` FOREIGN KEY (`latest_edited_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON UPDATE CASCADE;

--
-- Constraints for table `view_page`
--
ALTER TABLE `view_page`
  ADD CONSTRAINT `fk_view_page_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
