-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2025 at 01:00 PM
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
-- Database: `stitch`
--

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fullname` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `city` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `emergency_contact` varchar(255) DEFAULT NULL,
  `customer_code` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`id`, `user_id`, `fullname`, `address`, `phone`, `city`, `email`, `comment`, `gender`, `date_of_birth`, `emergency_contact`, `customer_code`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'jhuma', 'dhaka', '(017) 452-19926', NULL, 'jh@gmail.com', NULL, 'Female', NULL, NULL, 'CUST20250003', 'active', '2025-10-04 06:10:47', '2025-10-04 06:10:47'),
(2, 3, 'test', 'dhaka', '(017) 452-19926', NULL, 'admin@gmail.com', NULL, 'Female', NULL, NULL, 'CUST20251481', 'active', '2025-10-04 06:23:00', '2025-10-04 06:23:00'),
(3, 4, 'nishat', 'DHAKA', '(017) 452-19926', NULL, 'nishat@gmail.com', NULL, 'Female', NULL, NULL, 'CUST20258804', 'active', '2025-10-06 03:38:08', '2025-10-06 03:38:08');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `documentable_id` bigint(20) UNSIGNED NOT NULL,
  `documentable_type` varchar(255) NOT NULL,
  `document_type` enum('image','pdf','doc','excel','other') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email`
--

CREATE TABLE `email` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `to_email` varchar(255) NOT NULL,
  `cc_email` text DEFAULT NULL,
  `bcc_email` text DEFAULT NULL,
  `subject` varchar(500) NOT NULL,
  `body` longtext NOT NULL,
  `attachment` varchar(500) DEFAULT NULL,
  `email_type` enum('order_confirmation','reminder','invoice','custom') DEFAULT 'custom',
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `sent_by` bigint(20) UNSIGNED DEFAULT NULL,
  `related_id` bigint(20) UNSIGNED DEFAULT NULL,
  `related_type` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expanse`
--

CREATE TABLE `expanse` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `exp_cat_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `expense_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `receipt_number` varchar(100) DEFAULT NULL,
  `vendor_name` varchar(255) DEFAULT NULL,
  `payment_method` enum('cash','bank','card','cheque') DEFAULT 'cash',
  `attachment` varchar(255) DEFAULT NULL,
  `added_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exp_cat`
--

CREATE TABLE `exp_cat` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exp_cat`
--

INSERT INTO `exp_cat` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Material Purchase', 'Purchase of fabrics, threads, buttons etc.', 'active', '2025-10-04 06:10:14', '2025-10-04 06:10:14'),
(2, 'Staff Salary', 'Monthly salary payments to staff', 'active', '2025-10-04 06:10:14', '2025-10-04 06:10:14'),
(3, 'Utilities', 'Electricity, water, internet bills', 'active', '2025-10-04 06:10:14', '2025-10-04 06:10:14'),
(4, 'Equipment', 'Sewing machines, tools, maintenance', 'active', '2025-10-04 06:10:14', '2025-10-04 06:10:14'),
(5, 'Marketing', 'Advertising and promotional expenses', 'active', '2025-10-04 06:10:14', '2025-10-04 06:10:14');

-- --------------------------------------------------------

--
-- Table structure for table `general_setting`
--

CREATE TABLE `general_setting` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `company_address` text NOT NULL,
  `company_phone` varchar(255) NOT NULL,
  `company_email` varchar(255) NOT NULL,
  `company_website` varchar(255) DEFAULT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'USD',
  `currency_symbol` varchar(10) DEFAULT '$',
  `date_format` varchar(20) DEFAULT 'Y-m-d',
  `time_format` varchar(20) DEFAULT 'H:i:s',
  `timezone` varchar(50) DEFAULT 'UTC',
  `invoice_prefix` varchar(20) DEFAULT 'INV',
  `order_prefix` varchar(20) DEFAULT 'ORD',
  `email_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`email_settings`)),
  `sms_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sms_settings`)),
  `backup_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`backup_settings`)),
  `notification_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `general_setting`
--

INSERT INTO `general_setting` (`id`, `company_name`, `company_address`, `company_phone`, `company_email`, `company_website`, `company_logo`, `tax_rate`, `currency`, `currency_symbol`, `date_format`, `time_format`, `timezone`, `invoice_prefix`, `order_prefix`, `email_settings`, `sms_settings`, `backup_settings`, `notification_settings`, `created_at`, `updated_at`) VALUES
(1, 'Stitch Tailor Management', '123 Fashion Street, City', '+1234567890', 'info@stitch.com', NULL, NULL, 0.00, 'USD', '$', 'Y-m-d', 'H:i:s', 'UTC', 'INV', 'ORD', NULL, NULL, NULL, NULL, '2025-10-04 06:10:14', '2025-10-04 06:10:14');

-- --------------------------------------------------------

--
-- Table structure for table `income`
--

CREATE TABLE `income` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `inc_cat_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `income_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `receipt_number` varchar(100) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `payment_method` enum('cash','bank','card','cheque') DEFAULT 'cash',
  `attachment` varchar(255) DEFAULT NULL,
  `added_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inc_cat`
--

CREATE TABLE `inc_cat` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inc_cat`
--

INSERT INTO `inc_cat` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Tailoring Services', 'Income from stitching new clothes', 'active', '2025-10-04 06:10:14', '2025-10-04 06:10:14'),
(2, 'Alteration Services', 'Income from cloth alterations', 'active', '2025-10-04 06:10:14', '2025-10-04 06:10:14'),
(3, 'Repair Services', 'Income from cloth repairs', 'active', '2025-10-04 06:10:14', '2025-10-04 06:10:14'),
(4, 'Training Services', 'Income from tailoring training', 'active', '2025-10-04 06:10:14', '2025-10-04 06:10:14'),
(5, 'Other Services', 'Miscellaneous services income', 'active', '2025-10-04 06:10:14', '2025-10-04 06:10:14');

-- --------------------------------------------------------

--
-- Table structure for table `measurement`
--

CREATE TABLE `measurement` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `measurement_part_id` bigint(20) UNSIGNED NOT NULL,
  `value` decimal(8,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `measurement_date` date NOT NULL,
  `taken_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `measurement`
--

INSERT INTO `measurement` (`id`, `customer_id`, `measurement_part_id`, `value`, `notes`, `measurement_date`, `taken_by`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 38.00, 'ufghghjh', '2025-10-06', 3, '2025-10-06 05:07:00', '2025-10-06 05:07:00'),
(2, 3, 2, 38.00, 'ufghghjh', '2025-10-06', 3, '2025-10-06 05:07:00', '2025-10-06 05:07:00'),
(3, 3, 3, 40.00, 'ufghghjh', '2025-10-06', 3, '2025-10-06 05:07:00', '2025-10-06 05:07:00'),
(4, 3, 4, 8.00, 'ufghghjh', '2025-10-06', 3, '2025-10-06 05:07:00', '2025-10-06 05:07:00'),
(5, 3, 5, 16.00, 'ufghghjh', '2025-10-06', 3, '2025-10-06 05:07:00', '2025-10-06 05:07:00'),
(6, 3, 6, 6.00, 'ufghghjh', '2025-10-06', 3, '2025-10-06 05:07:00', '2025-10-06 05:07:00'),
(7, 3, 7, 4.00, 'ufghghjh', '2025-10-06', 3, '2025-10-06 05:07:00', '2025-10-06 05:07:00'),
(8, 3, 8, 4.00, 'ufghghjh', '2025-10-06', 3, '2025-10-06 05:07:00', '2025-10-06 05:07:00');

-- --------------------------------------------------------

--
-- Table structure for table `measurements`
--

CREATE TABLE `measurements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `neck` decimal(5,2) DEFAULT NULL,
  `shoulder` decimal(5,2) DEFAULT NULL,
  `chest` decimal(5,2) DEFAULT NULL,
  `waist` decimal(5,2) DEFAULT NULL,
  `hip` decimal(5,2) DEFAULT NULL,
  `sleeve_length` decimal(5,2) DEFAULT NULL,
  `armhole` decimal(5,2) DEFAULT NULL,
  `upper_arm` decimal(5,2) DEFAULT NULL,
  `shirt_length` decimal(5,2) DEFAULT NULL,
  `pant_length` decimal(5,2) DEFAULT NULL,
  `inseam` decimal(5,2) DEFAULT NULL,
  `thigh` decimal(5,2) DEFAULT NULL,
  `calf` decimal(5,2) DEFAULT NULL,
  `bust` decimal(5,2) DEFAULT NULL,
  `blouse_length` decimal(5,2) DEFAULT NULL,
  `dress_length` decimal(5,2) DEFAULT NULL,
  `skirt_length` decimal(5,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `measurement_part`
--

CREATE TABLE `measurement_part` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `gender` enum('Male','Female','Both') DEFAULT 'Both',
  `unit` varchar(20) DEFAULT 'inches',
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `measurement_part`
--

INSERT INTO `measurement_part` (`id`, `name`, `description`, `gender`, `unit`, `image`, `sort_order`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Chest/Bust', 'Chest measurement for men, bust for women', 'Both', 'inches', NULL, 1, 'active', NULL, '2025-10-04 06:10:14', '2025-10-04 06:10:14'),
(2, 'Waist', 'Waist measurement', 'Both', 'inches', NULL, 2, 'active', NULL, '2025-10-04 06:10:14', '2025-10-04 06:10:14'),
(3, 'Hip', 'Hip measurement', 'Both', 'inches', NULL, 3, 'active', NULL, '2025-10-04 06:10:14', '2025-10-04 06:10:14'),
(4, 'Shoulder', 'Shoulder width', 'Both', 'inches', NULL, 4, 'active', NULL, '2025-10-04 06:10:14', '2025-10-04 06:10:14'),
(5, 'Sleeve Length', 'Arm length measurement', 'Both', 'inches', NULL, 5, 'active', NULL, '2025-10-04 06:10:14', '2025-10-04 06:10:14'),
(6, 'Neck', 'Neck circumference', 'Both', 'inches', NULL, 6, 'active', NULL, '2025-10-04 06:10:14', '2025-10-04 06:10:14'),
(7, 'Inseam', 'Inner leg measurement', 'Both', 'inches', NULL, 7, 'active', NULL, '2025-10-04 06:10:14', '2025-10-04 06:10:14'),
(8, 'Outseam', 'Outer leg measurement', 'Both', 'inches', NULL, 8, 'active', NULL, '2025-10-04 06:10:14', '2025-10-04 06:10:14');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `cloth_type` varchar(255) NOT NULL,
  `fabric_details` text DEFAULT NULL,
  `received_date` date NOT NULL,
  `promised_date` date NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `amount_charged` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `balance_amount` decimal(10,2) GENERATED ALWAYS AS (`amount_charged` - `amount_paid`) STORED,
  `payment_status` enum('pending','partial','paid') DEFAULT 'pending',
  `order_status` enum('received','in_progress','ready','delivered','cancelled') DEFAULT 'received',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `special_instructions` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`id`, `customer_id`, `order_number`, `title`, `description`, `cloth_type`, `fabric_details`, `received_date`, `promised_date`, `delivery_date`, `received_by`, `assigned_to`, `amount_charged`, `amount_paid`, `payment_status`, `order_status`, `priority`, `special_instructions`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'ORD20253928', 'Gown', 'tywerwrrrrrrrrrrrr', 'cotton', 'purpal', '2025-10-06', '2025-10-15', NULL, 3, NULL, 500.00, 0.00, 'pending', 'received', 'urgent', 'grttrhy', NULL, '2025-10-06 04:11:37', NULL),
(2, 1, 'ORD20250894', 'gown', 'cvbvnghngjnhgj', 'cotton', 'purpal', '2025-10-06', '2025-10-16', NULL, 3, NULL, 500.00, 0.00, 'pending', 'delivered', 'urgent', 'hbjkl.', NULL, '2025-10-06 05:08:11', '2025-10-06 05:09:11');

-- --------------------------------------------------------

--
-- Table structure for table `sms`
--

CREATE TABLE `sms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `to_number` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `sms_type` enum('order_confirmation','reminder','delivery_notice','custom') DEFAULT 'custom',
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `sent_by` bigint(20) UNSIGNED DEFAULT NULL,
  `related_id` bigint(20) UNSIGNED DEFAULT NULL,
  `related_type` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `staff_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fullname` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `salary` decimal(10,2) NOT NULL,
  `hire_date` date NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','terminated') DEFAULT 'active',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `staff_type_id`, `user_id`, `fullname`, `address`, `gender`, `phone`, `salary`, `hire_date`, `avatar`, `employee_id`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, 'rune', '60,feet', 'Female', '01745219926', 1500.01, '2025-10-01', NULL, 'EMP2025819', 'active', NULL, '2025-10-06 03:52:22', '2025-10-06 03:52:22'),
(2, 3, NULL, 'jhum', 'Dhaka', 'Female', '(017) 343-234678', 8000.00, '2025-10-03', NULL, 'EMP2025541', 'active', NULL, '2025-10-06 03:56:40', '2025-10-06 03:56:40'),
(3, 2, NULL, 'nishat', 'DHAKA', 'Female', '017343234678', 6000.00, '2025-10-01', NULL, 'EMP2025922', 'active', NULL, '2025-10-06 04:00:10', '2025-10-06 04:00:10');

-- --------------------------------------------------------

--
-- Table structure for table `staff_types`
--

CREATE TABLE `staff_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `base_salary` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_types`
--

INSERT INTO `staff_types` (`id`, `title`, `description`, `base_salary`, `created_at`, `updated_at`) VALUES
(1, 'Tailor', 'Main tailor responsible for stitching clothes', 2000.00, '2025-10-04 06:10:14', '2025-10-04 06:10:14'),
(2, 'Assistant Tailor', 'Assistant to main tailor', 6000.00, '2025-10-04 06:10:14', '2025-10-06 03:54:18'),
(3, 'Counter Staff', 'Handles customer orders and payments', 8000.00, '2025-10-04 06:10:14', '2025-10-06 03:54:37'),
(4, 'Security', 'Security personnel', 1000.00, '2025-10-04 06:10:14', '2025-10-04 06:10:14'),
(5, 'Manager', 'Shop manager', 15000.00, '2025-10-04 06:10:14', '2025-10-06 03:55:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','manager','staff','customer') DEFAULT 'customer',
  `status` enum('active','inactive') DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `username`, `email_verified_at`, `password`, `avatar`, `phone`, `role`, `status`, `remember_token`, `created_at`, `updated_at`, `login_attempts`, `locked_until`, `last_login`) VALUES
(1, 'Admin', 'admin@stitch.com', 'admin', NULL, '$2y$10$wf6yKKpnJiJ/TOXlp0vCU.xRaOmPKHgkxYJksRvHGzHu480Z9duam', NULL, NULL, 'admin', 'active', NULL, '2025-10-04 06:10:14', '2025-10-04 06:18:25', 2, NULL, NULL),
(2, 'jhuma', 'jh@gmail.com', 'jhum', NULL, '$2y$10$fbCr8nUhoQgLuC4TDrXww.fxTZov0y.1vssDzoFh9NiFSoaWz2bni', NULL, '(017) 452-19926', 'customer', 'active', NULL, '2025-10-04 06:10:47', '2025-10-06 05:26:20', 0, NULL, '2025-10-06 05:26:20'),
(3, 'test', 'admin@gmail.com', 'test', NULL, '$2y$10$XfSfVfUJJJbcP7JOU1.5euOY4WVeKnYPin5udgDXIBVObALQFSHmW', NULL, '(017) 452-19926', 'admin', 'active', NULL, '2025-10-04 06:23:00', '2025-10-06 05:37:06', 0, NULL, '2025-10-06 05:37:06'),
(4, 'nishat', 'nishat@gmail.com', 'nishat', NULL, '$2y$10$1Xg5VNvo9pxLQDuLgx49Oe6Fvzm23ac5tast12VGHN8MQ09Vqg/uy', NULL, '(017) 452-19926', 'customer', 'active', NULL, '2025-10-06 03:38:08', '2025-10-06 03:38:08', 0, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_code` (`customer_code`),
  ADD KEY `customer_phone_index` (`phone`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_documentable_index` (`documentable_id`,`documentable_type`),
  ADD KEY `documents_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `email`
--
ALTER TABLE `email`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_sent_by_foreign` (`sent_by`),
  ADD KEY `email_related_index` (`related_id`,`related_type`);

--
-- Indexes for table `expanse`
--
ALTER TABLE `expanse`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expanse_exp_cat_id_foreign` (`exp_cat_id`),
  ADD KEY `expanse_added_by_foreign` (`added_by`);

--
-- Indexes for table `exp_cat`
--
ALTER TABLE `exp_cat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `general_setting`
--
ALTER TABLE `general_setting`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `income`
--
ALTER TABLE `income`
  ADD PRIMARY KEY (`id`),
  ADD KEY `income_inc_cat_id_foreign` (`inc_cat_id`),
  ADD KEY `income_added_by_foreign` (`added_by`);

--
-- Indexes for table `inc_cat`
--
ALTER TABLE `inc_cat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `measurement`
--
ALTER TABLE `measurement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `measurement_customer_id_foreign` (`customer_id`),
  ADD KEY `measurement_measurement_part_id_foreign` (`measurement_part_id`),
  ADD KEY `measurement_taken_by_foreign` (`taken_by`);

--
-- Indexes for table `measurements`
--
ALTER TABLE `measurements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `measurement_part`
--
ALTER TABLE `measurement_part`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `order_customer_id_foreign` (`customer_id`),
  ADD KEY `order_received_by_foreign` (`received_by`),
  ADD KEY `order_assigned_to_foreign` (`assigned_to`);

--
-- Indexes for table `sms`
--
ALTER TABLE `sms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sms_sent_by_foreign` (`sent_by`),
  ADD KEY `sms_related_index` (`related_id`,`related_type`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `staff_staff_type_id_foreign` (`staff_type_id`),
  ADD KEY `staff_user_id_foreign` (`user_id`);

--
-- Indexes for table `staff_types`
--
ALTER TABLE `staff_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email`
--
ALTER TABLE `email`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expanse`
--
ALTER TABLE `expanse`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exp_cat`
--
ALTER TABLE `exp_cat`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `general_setting`
--
ALTER TABLE `general_setting`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `income`
--
ALTER TABLE `income`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inc_cat`
--
ALTER TABLE `inc_cat`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `measurement`
--
ALTER TABLE `measurement`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `measurements`
--
ALTER TABLE `measurements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `measurement_part`
--
ALTER TABLE `measurement_part`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sms`
--
ALTER TABLE `sms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `staff_types`
--
ALTER TABLE `staff_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `email`
--
ALTER TABLE `email`
  ADD CONSTRAINT `email_sent_by_foreign` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `expanse`
--
ALTER TABLE `expanse`
  ADD CONSTRAINT `expanse_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expanse_exp_cat_id_foreign` FOREIGN KEY (`exp_cat_id`) REFERENCES `exp_cat` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `income`
--
ALTER TABLE `income`
  ADD CONSTRAINT `income_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `income_inc_cat_id_foreign` FOREIGN KEY (`inc_cat_id`) REFERENCES `inc_cat` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `measurement`
--
ALTER TABLE `measurement`
  ADD CONSTRAINT `measurement_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `measurement_measurement_part_id_foreign` FOREIGN KEY (`measurement_part_id`) REFERENCES `measurement_part` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `measurement_taken_by_foreign` FOREIGN KEY (`taken_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `measurements`
--
ALTER TABLE `measurements`
  ADD CONSTRAINT `measurements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `staff` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sms`
--
ALTER TABLE `sms`
  ADD CONSTRAINT `sms_sent_by_foreign` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_staff_type_id_foreign` FOREIGN KEY (`staff_type_id`) REFERENCES `staff_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `staff_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
