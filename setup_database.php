<?php
/**
 * Database Setup Script
 * The Stitch House - Create Database and Tables
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'stitch');

echo "<h2>Database Setup</h2>";

try {
    // Connect to MySQL server (without database)
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "<p>✓ Connected to MySQL server</p>";
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($mysqli->query($sql)) {
        echo "<p>✓ Database '" . DB_NAME . "' created or already exists</p>";
    } else {
        throw new Exception("Error creating database: " . $mysqli->error);
    }
    
    // Select the database
    $mysqli->select_db(DB_NAME);
    echo "<p>✓ Selected database '" . DB_NAME . "'</p>";
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS `users` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
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
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `users_email_unique` (`email`),
        UNIQUE KEY `users_username_unique` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($mysqli->query($sql)) {
        echo "<p>✓ Users table created or already exists</p>";
    } else {
        throw new Exception("Error creating users table: " . $mysqli->error);
    }
    
    // Create customer table
    $sql = "CREATE TABLE IF NOT EXISTS `customer` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
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
        `customer_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `customer_code_unique` (`customer_code`),
        KEY `customer_phone_index` (`phone`),
        KEY `customer_user_id_foreign` (`user_id`),
        CONSTRAINT `customer_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($mysqli->query($sql)) {
        echo "<p>✓ Customer table created or already exists</p>";
    } else {
        throw new Exception("Error creating customer table: " . $mysqli->error);
    }
    
    // Create additional tables that might be needed
    
    // Income categories table
    $sql = "CREATE TABLE IF NOT EXISTS `inc_cat` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($mysqli->query($sql)) {
        echo "<p>✓ Income categories table created or already exists</p>";
    } else {
        echo "<p>⚠ Error creating inc_cat table: " . $mysqli->error . "</p>";
    }
    
    // Income table
    $sql = "CREATE TABLE IF NOT EXISTS `income` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
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
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `income_inc_cat_id_foreign` (`inc_cat_id`),
        KEY `income_added_by_foreign` (`added_by`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($mysqli->query($sql)) {
        echo "<p>✓ Income table created or already exists</p>";
    } else {
        echo "<p>⚠ Error creating income table: " . $mysqli->error . "</p>";
    }
    
    // Orders table
    $sql = "CREATE TABLE IF NOT EXISTS `order` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `customer_id` bigint(20) UNSIGNED NOT NULL,
        `order_number` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
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
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `order_number_unique` (`order_number`),
        KEY `order_customer_id_foreign` (`customer_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($mysqli->query($sql)) {
        echo "<p>✓ Orders table created or already exists</p>";
    } else {
        echo "<p>⚠ Error creating order table: " . $mysqli->error . "</p>";
    }
    
    // Measurement parts table
    $sql = "CREATE TABLE IF NOT EXISTS `measurement_part` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `gender` enum('Male','Female','Both') COLLATE utf8mb4_unicode_ci DEFAULT 'Both',
        `unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'inches',
        `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `sort_order` int(11) DEFAULT 0,
        `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
        `deleted_at` timestamp NULL DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($mysqli->query($sql)) {
        echo "<p>✓ Measurement parts table created or already exists</p>";
    } else {
        echo "<p>⚠ Error creating measurement_part table: " . $mysqli->error . "</p>";
    }
    
    // Insert default admin user if not exists
    $adminEmail = 'admin@stitch.com';
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $adminEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("
            INSERT INTO users (name, email, username, password, role, status, created_at, updated_at) 
            VALUES ('Admin', ?, 'admin', ?, 'admin', 'active', NOW(), NOW())
        ");
        $stmt->bind_param("ss", $adminEmail, $adminPassword);
        
        if ($stmt->execute()) {
            echo "<p>✓ Default admin user created (admin@stitch.com / admin123)</p>";
        } else {
            echo "<p>⚠ Could not create admin user: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p>✓ Admin user already exists</p>";
    }
    
    // Insert sample income categories
    $categories = [
        ['Tailoring Services', 'Income from stitching new clothes'],
        ['Alteration Services', 'Income from cloth alterations'],
        ['Repair Services', 'Income from cloth repairs']
    ];
    
    foreach ($categories as $cat) {
        $stmt = $mysqli->prepare("INSERT IGNORE INTO inc_cat (name, description, status, created_at, updated_at) VALUES (?, ?, 'active', NOW(), NOW())");
        $stmt->bind_param("ss", $cat[0], $cat[1]);
        $stmt->execute();
    }
    echo "<p>✓ Sample income categories added</p>";
    
    // Insert sample measurement parts
    $parts = [
        ['Chest/Bust', 'Chest measurement for men, bust for women', 'Both', 'inches', 1],
        ['Waist', 'Waist measurement', 'Both', 'inches', 2],
        ['Hip', 'Hip measurement', 'Both', 'inches', 3],
        ['Shoulder', 'Shoulder width', 'Both', 'inches', 4],
        ['Sleeve Length', 'Arm length measurement', 'Both', 'inches', 5],
        ['Neck', 'Neck circumference', 'Both', 'inches', 6],
        ['Inseam', 'Inner leg measurement', 'Both', 'inches', 7],
        ['Outseam', 'Outer leg measurement', 'Both', 'inches', 8],
        ['Thigh', 'Thigh circumference', 'Both', 'inches', 9],
        ['Arm Length', 'Full arm length', 'Both', 'inches', 10],
        ['Wrist', 'Wrist circumference', 'Both', 'inches', 11],
        ['Ankle', 'Ankle circumference', 'Both', 'inches', 12]
    ];
    
    foreach ($parts as $part) {
        $stmt = $mysqli->prepare("INSERT IGNORE INTO measurement_part (name, description, gender, unit, sort_order, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())");
        $stmt->bind_param("ssssi", $part[0], $part[1], $part[2], $part[3], $part[4]);
        $stmt->execute();
    }
    echo "<p>✓ Sample measurement parts added (12 parts)</p>";
    
    echo "<h3>Database Setup Complete!</h3>";
    echo "<p>You can now test registration at: <a href='backend/register_debug.php'>register_debug.php</a></p>";
    echo "<p>Or use the normal registration at: <a href='register.php'>register.php</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

if (isset($mysqli)) {
    $mysqli->close();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
p { margin: 5px 0; }
</style>