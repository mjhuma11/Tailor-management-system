<?php

/**
 * Customer Login Handler
 * The Stitch House - Customer Login Backend
 */

require_once 'config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('../login.html', 'Invalid request method.', 'error');
}

// Get and sanitize form data
$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validation
$errors = [];

if (empty($email)) $errors[] = 'Email address is required.';
if (empty($password)) $errors[] = 'Password is required.';

// Email format validation
if (!empty($email) && !isValidEmail($email)) {
    $errors[] = 'Please enter a valid email address.';
}

// If there are validation errors, redirect back
if (!empty($errors)) {
    $errorMessage = implode(' ', $errors);
    redirectWithMessage('../login.html', $errorMessage, 'error');
}

try {
    // Add login_attempts and locked_until columns to users table if they don't exist
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN login_attempts INT DEFAULT 0");
        $pdo->exec("ALTER TABLE users ADD COLUMN locked_until TIMESTAMP NULL");
        $pdo->exec("ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL");
    } catch (Exception $e) {
        // Columns might already exist, ignore error
    }

    // Find user by email
    $stmt = $pdo->prepare("
        SELECT id, name, email, password, role, status, login_attempts, locked_until 
        FROM users 
        WHERE email = ? AND status = 'active'
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        redirectWithMessage('../login.html', 'Invalid email or password.', 'error');
    }

    // Check if account is locked
    if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
        $lockTime = date('H:i', strtotime($user['locked_until']));
        redirectWithMessage('../login.html', "Account is temporarily locked due to multiple failed login attempts. Try again after $lockTime.", 'error');
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        // Increment login attempts
        $attempts = $user['login_attempts'] + 1;
        $lockUntil = null;

        // Lock account after 5 failed attempts for 30 minutes
        if ($attempts >= 5) {
            $lockUntil = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        }

        $stmt = $pdo->prepare("
            UPDATE users 
            SET login_attempts = ?, locked_until = ?, updated_at = NOW() 
            WHERE email = ?
        ");
        $stmt->execute([$attempts, $lockUntil, $email]);

        if ($lockUntil) {
            redirectWithMessage('../login.html', 'Too many failed login attempts. Account locked for 30 minutes.', 'error');
        } else {
            $remaining = 5 - $attempts;
            redirectWithMessage('../login.html', "Invalid email or password. $remaining attempts remaining.", 'error');
        }
    }

    // Get customer details if user is a customer
    $customer = null;
    if ($user['role'] === 'customer') {
        $stmt = $pdo->prepare("
            SELECT c.id, c.fullname, c.email, c.phone, c.address, c.gender, c.customer_code, c.status
            FROM customer c
            WHERE c.email = ? AND c.status = 'active'
        ");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        if (!$customer) {
            redirectWithMessage('../login.html', 'Customer account not found or inactive.', 'error');
        }
    }

    // Successful login - reset login attempts and update last login
    $stmt = $pdo->prepare("
        UPDATE users 
        SET login_attempts = 0, locked_until = NULL, last_login = NOW(), updated_at = NOW() 
        WHERE email = ?
    ");
    $stmt->execute([$email]);

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    // Set customer-specific session variables if user is a customer
    if ($user['role'] === 'customer' && $customer) {
        $_SESSION['customer_id'] = $customer['id'];
        $_SESSION['customer_code'] = $customer['customer_code'];
        $_SESSION['customer_phone'] = $customer['phone'];
    }

    // Set remember me cookie if requested (optional)
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expires = time() + (30 * 24 * 60 * 60); // 30 days

        // Store token in database (create remember_tokens table if needed)
        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS remember_tokens (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    token VARCHAR(64) NOT NULL,
                    expires_at TIMESTAMP NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_token (token),
                    INDEX idx_user_id (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $stmt = $pdo->prepare("
                INSERT INTO remember_tokens (user_id, token, expires_at) 
                VALUES (?, ?, FROM_UNIXTIME(?))
            ");
            $stmt->execute([$user['id'], $token, $expires]);

            // Set cookie
            setcookie('remember_token', $token, $expires, '/', '', false, true);
        } catch (Exception $e) {
            error_log("Remember me token creation failed: " . $e->getMessage());
        }
    }

    // Log successful login and redirect based on role
    if (in_array($user['role'], ['admin', 'manager'])) {
        // Admin and Manager users go to admin dashboard
        error_log("Admin/Manager login successful: User ID {$user['id']}, Role: {$user['role']}, Email: {$user['email']}, Name: {$user['name']}");

        $redirectUrl = $_SESSION['redirect_after_login'] ?? '../admin/dashboard.php';
        unset($_SESSION['redirect_after_login']);

        redirectWithMessage($redirectUrl, "Welcome back, {$user['name']}!", 'success');
    } else {
        // Customer and Staff users go to homepage
        if ($user['role'] === 'customer' && $customer) {
            error_log("Customer login successful: User ID {$user['id']}, Customer ID {$customer['id']}, Email: {$user['email']}, Name: {$user['name']}");
        } else {
            error_log("Staff login successful: User ID {$user['id']}, Role: {$user['role']}, Email: {$user['email']}, Name: {$user['name']}");
        }

        $redirectUrl = $_SESSION['redirect_after_login'] ?? '../index.html';
        unset($_SESSION['redirect_after_login']);

        redirectWithMessage($redirectUrl, "Welcome back, {$user['name']}!", 'success');
    }
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    redirectWithMessage('../login.html', 'Login failed. Please try again later.', 'error');
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    redirectWithMessage('../login.html', 'An unexpected error occurred. Please try again.', 'error');
}
