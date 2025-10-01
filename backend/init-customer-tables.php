<?php

/**
 * Initialize Customer Authentication Tables
 * Run this once to set up customer authentication system
 */

require_once 'config.php';

try {
    echo "<h2>Initializing Customer Authentication System...</h2>";

    // Create customer_auth table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS customer_auth (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            last_login TIMESTAMP NULL,
            login_attempts INT DEFAULT 0,
            locked_until TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customer(id) ON DELETE CASCADE,
            INDEX idx_email (email),
            INDEX idx_customer_id (customer_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✅ Customer authentication table created.</p>";

    // Create remember_tokens table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS remember_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customer(id) ON DELETE CASCADE,
            INDEX idx_token (token),
            INDEX idx_customer_id (customer_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✅ Remember tokens table created.</p>";

    // Create newsletter_subscribers table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT NULL,
            email VARCHAR(255) NOT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customer(id) ON DELETE SET NULL,
            UNIQUE KEY unique_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "<p>✅ Newsletter subscribers table created.</p>";

    // Add customer_code to customer table if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE customer ADD COLUMN customer_code VARCHAR(50) UNIQUE DEFAULT NULL");
        echo "<p>✅ Customer code column added to customer table.</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ Customer code column already exists.</p>";
        } else {
            echo "<p>⚠️ Warning: " . $e->getMessage() . "</p>";
        }
    }

    // Add user_id to customer table to link with users table
    try {
        $pdo->exec("ALTER TABLE customer ADD COLUMN user_id INT NULL");
        $pdo->exec("ALTER TABLE customer ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
        echo "<p>✅ User ID column added to customer table.</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "<p>ℹ️ User ID column already exists.</p>";
        } else {
            echo "<p>⚠️ Warning: " . $e->getMessage() . "</p>";
        }
    }

    // Update users table role enum to include customer and set default
    try {
        $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin','manager','staff','customer') DEFAULT 'customer'");
        echo "<p>✅ Users table role updated to include 'customer' with default 'customer'.</p>";
    } catch (Exception $e) {
        echo "<p>⚠️ Warning updating role enum: " . $e->getMessage() . "</p>";
    }

    // Add login tracking columns to users table
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN login_attempts INT DEFAULT 0");
        $pdo->exec("ALTER TABLE users ADD COLUMN locked_until TIMESTAMP NULL");
        $pdo->exec("ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL");
        echo "<p>✅ Login tracking columns added to users table.</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ Login tracking columns already exist.</p>";
        } else {
            echo "<p>⚠️ Warning: " . $e->getMessage() . "</p>";
        }
    }

    // Add phone column to users table if it doesn't exist (for customer registration)
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL");
        echo "<p>✅ Phone column added to users table.</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ Phone column already exists.</p>";
        } else {
            echo "<p>⚠️ Warning: " . $e->getMessage() . "</p>";
        }
    }

    echo "<hr>";
    echo "<h3>🎉 Customer authentication system initialized successfully!</h3>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li>Register new customers via register.html</li>";
    echo "<li>Login existing customers via login.html</li>";
    echo "<li>Access customer dashboard after login</li>";
    echo "</ul>";
    echo "<p><a href='../register.html' class='btn btn-primary'>Test Registration</a> ";
    echo "<a href='../login.html' class='btn btn-secondary'>Test Login</a></p>";
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ Error: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer System Setup - The Stitch House</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            padding-top: 3rem;
            padding-bottom: 3rem;
        }

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

        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-1px);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body p-5">
                        <!-- Content will be inserted above -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>