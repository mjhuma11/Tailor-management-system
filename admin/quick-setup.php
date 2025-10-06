<?php
/**
 * Simple Database Setup for Demo
 * Creates minimal tables needed for the admin panel
 */

try {
    // Connect to MySQL and create database
    $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "<h2>Quick Database Setup - The Stitch House</h2>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `stitch` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `stitch`");
    echo "<p>✅ Database 'stitch' ready.</p>";
    
    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL UNIQUE,
            `username` varchar(255) NOT NULL UNIQUE,
            `password` varchar(255) NOT NULL,
            `role` enum('admin','manager','staff') DEFAULT 'staff',
            `status` enum('active','inactive') DEFAULT 'active',
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✅ Users table created.</p>";
    
    // Create customer table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `customer` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `fullname` varchar(255) NOT NULL,
            `address` text NOT NULL,
            `phone` varchar(255) NOT NULL,
            `city` varchar(255) NOT NULL,
            `email` varchar(255) DEFAULT NULL,
            `comment` text DEFAULT NULL,
            `gender` enum('Male','Female','Other') NOT NULL,
            `date_of_birth` date DEFAULT NULL,
            `customer_code` varchar(50) DEFAULT NULL,
            `status` enum('active','inactive') DEFAULT 'active',
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✅ Customer table created.</p>";
    
    // Create order table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `order` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `customer_id` bigint(20) UNSIGNED NOT NULL,
            `order_number` varchar(100) NOT NULL UNIQUE,
            `title` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `cloth_type` varchar(255) NOT NULL,
            `received_date` date NOT NULL,
            `promised_date` date NOT NULL,
            `amount_charged` decimal(10,2) NOT NULL,
            `amount_paid` decimal(10,2) DEFAULT 0.00,
            `order_status` enum('received','in_progress','ready','delivered','cancelled') DEFAULT 'received',
            `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
            `deleted_at` timestamp NULL DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `customer_id` (`customer_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✅ Order table created.</p>";
    
    // Create income categories table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `inc_cat` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `status` enum('active','inactive') DEFAULT 'active',
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✅ Income categories table created.</p>";
    
    // Create income table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `income` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `inc_cat_id` bigint(20) UNSIGNED DEFAULT NULL,
            `title` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `income_date` date NOT NULL,
            `amount` decimal(10,2) NOT NULL,
            `receipt_number` varchar(100) DEFAULT NULL,
            `client_name` varchar(255) DEFAULT NULL,
            `payment_method` enum('cash','bank','card','cheque') DEFAULT 'cash',
            `deleted_at` timestamp NULL DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `inc_cat_id` (`inc_cat_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✅ Income table created.</p>";
    
    // Create expense categories table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `exp_cat` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `status` enum('active','inactive') DEFAULT 'active',
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✅ Expense categories table created.</p>";
    
    // Create expense table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `expanse` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `exp_cat_id` bigint(20) UNSIGNED DEFAULT NULL,
            `title` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `expense_date` date NOT NULL,
            `amount` decimal(10,2) NOT NULL,
            `receipt_number` varchar(100) DEFAULT NULL,
            `vendor_name` varchar(255) DEFAULT NULL,
            `payment_method` enum('cash','bank','card','cheque') DEFAULT 'cash',
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `exp_cat_id` (`exp_cat_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✅ Expense table created.</p>";
    
    // Insert default data
    
    // Check if admin user exists
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE email = 'admin@stitch.com'");
    if ($stmt->fetch()['count'] == 0) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name, email, username, password, role, status) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute(['Administrator', 'admin@stitch.com', 'admin', $adminPassword, 'admin', 'active']);
        echo "<p>✅ Admin user created (admin@stitch.com / admin123).</p>";
    } else {
        echo "<p>ℹ️ Admin user already exists.</p>";
    }
    
    // Insert sample income category
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM inc_cat WHERE name = 'Tailoring Services'");
    if ($stmt->fetch()['count'] == 0) {
        $pdo->prepare("INSERT INTO inc_cat (name, description, status) VALUES (?, ?, ?)")
            ->execute(['Tailoring Services', 'Income from stitching and alteration services', 'active']);
        echo "<p>✅ Sample income category created.</p>";
    }
    
    // Insert sample expense category
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM exp_cat WHERE name = 'Material Purchase'");
    if ($stmt->fetch()['count'] == 0) {
        $pdo->prepare("INSERT INTO exp_cat (name, description, status) VALUES (?, ?, ?)")
            ->execute(['Material Purchase', 'Purchase of fabrics, threads, buttons etc.', 'active']);
        echo "<p>✅ Sample expense category created.</p>";
    }
    
    // Insert sample customers
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM customer");
    if ($stmt->fetch()['count'] == 0) {
        $customers = [
            ['John Doe', '123 Main St', '+1-555-0123', 'New York', 'john@example.com', 'Regular customer', 'Male', '1985-03-15'],
            ['Sarah Johnson', '456 Oak Ave', '+1-555-0456', 'New York', 'sarah@example.com', 'Bridal customer', 'Female', '1990-07-22'],
            ['Michael Brown', '789 Pine Rd', '+1-555-0789', 'New York', 'michael@example.com', 'Business customer', 'Male', '1978-11-08']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO customer (fullname, address, phone, city, email, comment, gender, date_of_birth) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($customers as $customer) {
            $stmt->execute($customer);
        }
        echo "<p>✅ Sample customers created.</p>";
    }
    
    // Insert sample orders
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM `order`");
    if ($stmt->fetch()['count'] == 0) {
        $orders = [
            [1, 'ORD-001', 'Custom Business Suit', 'Navy blue business suit', 'Suit', '2024-08-27', '2024-09-03', 450.00, 150.00, 'received'],
            [2, 'ORD-002', 'Wedding Dress', 'Custom wedding dress', 'Dress', '2024-08-27', '2024-09-10', 850.00, 300.00, 'in_progress'],
            [3, 'ORD-003', 'Shirt Alteration', 'Sleeve shortening', 'Shirt', '2024-08-27', '2024-08-30', 35.00, 35.00, 'ready']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO `order` (customer_id, order_number, title, description, cloth_type, received_date, promised_date, amount_charged, amount_paid, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($orders as $order) {
            $stmt->execute($order);
        }
        echo "<p>✅ Sample orders created.</p>";
    }
    
    // Insert sample income
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM income");
    if ($stmt->fetch()['count'] == 0) {
        $income = [
            [1, 'Business Suit Payment', 'Partial payment', '2024-08-27', 150.00, 'RCP-001', 'John Doe', 'cash'],
            [1, 'Wedding Dress Deposit', 'Advance payment', '2024-08-27', 300.00, 'RCP-002', 'Sarah Johnson', 'card'],
            [1, 'Alteration Service', 'Full payment', '2024-08-27', 35.00, 'RCP-003', 'Michael Brown', 'cash']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO income (inc_cat_id, title, description, income_date, amount, receipt_number, client_name, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($income as $inc) {
            $stmt->execute($inc);
        }
        echo "<p>✅ Sample income records created.</p>";
    }
    
    echo "<hr>";
    echo "<h3>🎉 Setup Complete!</h3>";
    echo "<p><strong>Admin Login Credentials:</strong></p>";
    echo "<p>Email: <code>admin@stitch.com</code></p>";
    echo "<p>Password: <code>admin123</code></p>";
    echo "<br>";
    echo "<p><a href='../login.html' class='btn btn-primary btn-lg'>Go to Login</a></p>";
    echo "<p><a href='../index.php' class='btn btn-secondary'>View Website</a></p>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Setup - The Stitch House Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container { padding: 3rem 0; }
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
            color: #e83e8c;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body p-5">
                        <!-- Content inserted above -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>