-- Create Database
CREATE DATABASE IF NOT EXISTS `stitch` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `stitch`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','manager','staff','customer') COLLATE utf8mb4_unicode_ci DEFAULT 'customer',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `customer`
-- --------------------------------------------------------

CREATE TABLE `customer` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fullname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `emergency_contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_code` varchar(50) COLLATE utf8mb4_unicode_ci UNIQUE DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `staff_types`
-- --------------------------------------------------------

CREATE TABLE `staff_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `base_salary` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `staff`
-- --------------------------------------------------------

CREATE TABLE `staff` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `staff_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fullname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salary` decimal(10,2) NOT NULL,
  `hire_date` date NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_id` varchar(50) COLLATE utf8mb4_unicode_ci UNIQUE DEFAULT NULL,
  `status` enum('active','inactive','terminated') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `exp_cat` (Expense Categories)
-- --------------------------------------------------------

CREATE TABLE `exp_cat` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `expanse`
-- --------------------------------------------------------

CREATE TABLE `expanse` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `exp_cat_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expense_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `receipt_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` enum('cash','bank','card','cheque') COLLATE utf8mb4_unicode_ci DEFAULT 'cash',
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `inc_cat` (Income Categories)
-- --------------------------------------------------------

CREATE TABLE `inc_cat` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `income`
-- --------------------------------------------------------

CREATE TABLE `income` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `inc_cat_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `income_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `receipt_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` enum('cash','bank','card','cheque') COLLATE utf8mb4_unicode_ci DEFAULT 'cash',
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `added_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `measurement_part`
-- --------------------------------------------------------

CREATE TABLE `measurement_part` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('Male','Female','Both') COLLATE utf8mb4_unicode_ci DEFAULT 'Both',
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'inches',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `measurement`
-- --------------------------------------------------------

CREATE TABLE `measurement` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `measurement_part_id` bigint(20) UNSIGNED NOT NULL,
  `value` decimal(8,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `measurement_date` date NOT NULL,
  `taken_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `order`
-- --------------------------------------------------------

CREATE TABLE `order` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(100) COLLATE utf8mb4_unicode_ci UNIQUE NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cloth_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fabric_details` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `received_date` date NOT NULL,
  `promised_date` date NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `amount_charged` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `balance_amount` decimal(10,2) GENERATED ALWAYS AS (`amount_charged` - `amount_paid`) STORED,
  `payment_status` enum('pending','partial','paid') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `order_status` enum('received','in_progress','ready','delivered','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'received',
  `priority` enum('low','normal','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'normal',
  `special_instructions` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `documents`
-- --------------------------------------------------------

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `documentable_id` bigint(20) UNSIGNED NOT NULL,
  `documentable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_type` enum('image','pdf','doc','excel','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `is_public` boolean DEFAULT false,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `email`
-- --------------------------------------------------------

CREATE TABLE `email` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `to_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cc_email` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bcc_email` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachment` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_type` enum('order_confirmation','reminder','invoice','custom') COLLATE utf8mb4_unicode_ci DEFAULT 'custom',
  `status` enum('pending','sent','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sent_by` bigint(20) UNSIGNED DEFAULT NULL,
  `related_id` bigint(20) UNSIGNED DEFAULT NULL,
  `related_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `sms`
-- --------------------------------------------------------

CREATE TABLE `sms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `to_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sms_type` enum('order_confirmation','reminder','delivery_notice','custom') COLLATE utf8mb4_unicode_ci DEFAULT 'custom',
  `status` enum('pending','sent','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sent_by` bigint(20) UNSIGNED DEFAULT NULL,
  `related_id` bigint(20) UNSIGNED DEFAULT NULL,
  `related_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `general_setting`
-- --------------------------------------------------------

CREATE TABLE `general_setting` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'USD',
  `currency_symbol` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '$',
  `date_format` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'Y-m-d',
  `time_format` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'H:i:s',
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'UTC',
  `invoice_prefix` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'INV',
  `order_prefix` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'ORD',
  `email_settings` json DEFAULT NULL,
  `sms_settings` json DEFAULT NULL,
  `backup_settings` json DEFAULT NULL,
  `notification_settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- INDEXES AND CONSTRAINTS
-- --------------------------------------------------------

-- Primary Keys
ALTER TABLE `users` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `users_email_unique` (`email`), ADD UNIQUE KEY `users_username_unique` (`username`);
ALTER TABLE `customer` ADD PRIMARY KEY (`id`), ADD KEY `customer_phone_index` (`phone`);
ALTER TABLE `staff_types` ADD PRIMARY KEY (`id`);
ALTER TABLE `staff` ADD PRIMARY KEY (`id`), ADD KEY `staff_staff_type_id_foreign` (`staff_type_id`), ADD KEY `staff_user_id_foreign` (`user_id`);
ALTER TABLE `exp_cat` ADD PRIMARY KEY (`id`);
ALTER TABLE `expanse` ADD PRIMARY KEY (`id`), ADD KEY `expanse_exp_cat_id_foreign` (`exp_cat_id`), ADD KEY `expanse_added_by_foreign` (`added_by`);
ALTER TABLE `inc_cat` ADD PRIMARY KEY (`id`);
ALTER TABLE `income` ADD PRIMARY KEY (`id`), ADD KEY `income_inc_cat_id_foreign` (`inc_cat_id`), ADD KEY `income_added_by_foreign` (`added_by`);
ALTER TABLE `measurement_part` ADD PRIMARY KEY (`id`);
ALTER TABLE `measurement` ADD PRIMARY KEY (`id`), ADD KEY `measurement_customer_id_foreign` (`customer_id`), ADD KEY `measurement_measurement_part_id_foreign` (`measurement_part_id`), ADD KEY `measurement_taken_by_foreign` (`taken_by`);
ALTER TABLE `order` ADD PRIMARY KEY (`id`), ADD KEY `order_customer_id_foreign` (`customer_id`), ADD KEY `order_received_by_foreign` (`received_by`), ADD KEY `order_assigned_to_foreign` (`assigned_to`);
ALTER TABLE `documents` ADD PRIMARY KEY (`id`), ADD KEY `documents_documentable_index` (`documentable_id`, `documentable_type`), ADD KEY `documents_uploaded_by_foreign` (`uploaded_by`);
ALTER TABLE `email` ADD PRIMARY KEY (`id`), ADD KEY `email_sent_by_foreign` (`sent_by`), ADD KEY `email_related_index` (`related_id`, `related_type`);
ALTER TABLE `sms` ADD PRIMARY KEY (`id`), ADD KEY `sms_sent_by_foreign` (`sent_by`), ADD KEY `sms_related_index` (`related_id`, `related_type`);
ALTER TABLE `general_setting` ADD PRIMARY KEY (`id`);

-- Auto Increment
ALTER TABLE `users` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `customer` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `staff_types` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `staff` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `exp_cat` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `expanse` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `inc_cat` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `income` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `measurement_part` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `measurement` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `order` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `documents` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `email` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `sms` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `general_setting` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

-- Foreign Key Constraints
ALTER TABLE `staff` 
  ADD CONSTRAINT `staff_staff_type_id_foreign` FOREIGN KEY (`staff_type_id`) REFERENCES `staff_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `staff_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `expanse` 
  ADD CONSTRAINT `expanse_exp_cat_id_foreign` FOREIGN KEY (`exp_cat_id`) REFERENCES `exp_cat` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expanse_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `income` 
  ADD CONSTRAINT `income_inc_cat_id_foreign` FOREIGN KEY (`inc_cat_id`) REFERENCES `inc_cat` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `income_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `measurement` 
  ADD CONSTRAINT `measurement_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `measurement_measurement_part_id_foreign` FOREIGN KEY (`measurement_part_id`) REFERENCES `measurement_part` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `measurement_taken_by_foreign` FOREIGN KEY (`taken_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `order` 
  ADD CONSTRAINT `order_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `staff` (`id`) ON DELETE SET NULL;

ALTER TABLE `documents` 
  ADD CONSTRAINT `documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `email` 
  ADD CONSTRAINT `email_sent_by_foreign` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `sms` 
  ADD CONSTRAINT `sms_sent_by_foreign` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- --------------------------------------------------------
-- SAMPLE DATA
-- --------------------------------------------------------

-- Insert sample data for staff types
INSERT INTO `staff_types` (`title`, `description`, `base_salary`, `created_at`, `updated_at`) VALUES
('Tailor', 'Main tailor responsible for stitching clothes', 2000.00, NOW(), NOW()),
('Assistant Tailor', 'Assistant to main tailor', 1500.00, NOW(), NOW()),
('Counter Staff', 'Handles customer orders and payments', 1200.00, NOW(), NOW()),
('Security', 'Security personnel', 1000.00, NOW(), NOW()),
('Manager', 'Shop manager', 3000.00, NOW(), NOW());

-- Insert sample data for expense categories
INSERT INTO `exp_cat` (`name`, `description`, `status`, `created_at`, `updated_at`) VALUES
('Material Purchase', 'Purchase of fabrics, threads, buttons etc.', 'active', NOW(), NOW()),
('Staff Salary', 'Monthly salary payments to staff', 'active', NOW(), NOW()),
('Utilities', 'Electricity, water, internet bills', 'active', NOW(), NOW()),
('Equipment', 'Sewing machines, tools, maintenance', 'active', NOW(), NOW()),
('Marketing', 'Advertising and promotional expenses', 'active', NOW(), NOW());

-- Insert sample data for income categories
INSERT INTO `inc_cat` (`name`, `description`, `status`, `created_at`, `updated_at`) VALUES
('Tailoring Services', 'Income from stitching new clothes', 'active', NOW(), NOW()),
('Alteration Services', 'Income from cloth alterations', 'active', NOW(), NOW()),
('Repair Services', 'Income from cloth repairs', 'active', NOW(), NOW()),
('Training Services', 'Income from tailoring training', 'active', NOW(), NOW()),
('Other Services', 'Miscellaneous services income', 'active', NOW(), NOW());

-- Insert sample measurement parts
INSERT INTO `measurement_part` (`name`, `description`, `gender`, `unit`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
('Chest/Bust', 'Chest measurement for men, bust for women', 'Both', 'inches', 1, 'active', NOW(), NOW()),
('Waist', 'Waist measurement', 'Both', 'inches', 2, 'active', NOW(), NOW()),
('Hip', 'Hip measurement', 'Both', 'inches', 3, 'active', NOW(), NOW()),
('Shoulder', 'Shoulder width', 'Both', 'inches', 4, 'active', NOW(), NOW()),
('Sleeve Length', 'Arm length measurement', 'Both', 'inches', 5, 'active', NOW(), NOW()),
('Neck', 'Neck circumference', 'Both', 'inches', 6, 'active', NOW(), NOW()),
('Inseam', 'Inner leg measurement', 'Both', 'inches', 7, 'active', NOW(), NOW()),
('Outseam', 'Outer leg measurement', 'Both', 'inches', 8, 'active', NOW(), NOW());

-- Insert default admin user
INSERT INTO `users` (`name`, `email`, `username`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
('Admin', 'admin@stitch.com', 'admin', '$2y$10$wf6yKKpnJiJ/TOXlp0vCU.xRaOmPKHgkxYJksRvHGzHu480Z9duam', 'admin', 'active', NOW(), NOW());

-- Insert general settings
INSERT INTO `general_setting` (`company_name`, `company_address`, `company_phone`, `company_email`, `currency`, `currency_symbol`, `invoice_prefix`, `order_prefix`, `created_at`, `updated_at`) VALUES
('Stitch Tailor Management', '123 Fashion Street, City', '+1234567890', 'info@stitch.com', 'USD', '$', 'INV', 'ORD', NOW(), NOW());

COMMIT;