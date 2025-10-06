<?php
/**
 * Login Handler with MySQLi
 * The Stitch House - Login Backend with Role-based Redirection
 */

// Start session
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'stitch');

// Create MySQLi connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Set charset to utf8mb4
$mysqli->set_charset("utf8mb4");

/**
 * Helper Functions
 */

// Sanitize input data
function sanitize($data) {
    global $mysqli;
    return $mysqli->real_escape_string(htmlspecialchars(strip_tags(trim($data))));
}

// Validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Redirect with message
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('../login.php', 'Invalid request method.', 'error');
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
    redirectWithMessage('../login.php', $errorMessage, 'error');
}

try {
    // Add login_attempts and locked_until columns to users table if they don't exist
    try {
        $mysqli->query("ALTER TABLE users ADD COLUMN login_attempts INT DEFAULT 0");
        $mysqli->query("ALTER TABLE users ADD COLUMN locked_until TIMESTAMP NULL");
        $mysqli->query("ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL");
    } catch (Exception $e) {
        // Columns might already exist, ignore error
    }

    // Find user by email
    $stmt = $mysqli->prepare("
        SELECT id, name, email, password, role, status, login_attempts, locked_until 
        FROM users 
        WHERE email = ? AND status = 'active'
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        redirectWithMessage('../login.php', 'Invalid email or password.', 'error');
    }

    // Check if account is locked
    if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
        $lockTime = date('H:i', strtotime($user['locked_until']));
        redirectWithMessage('../login.php', "Account is temporarily locked due to multiple failed login attempts. Try again after $lockTime.", 'error');
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

        $stmt = $mysqli->prepare("
            UPDATE users 
            SET login_attempts = ?, locked_until = ?, updated_at = NOW() 
            WHERE email = ?
        ");
        $stmt->bind_param("iss", $attempts, $lockUntil, $email);
        $stmt->execute();
        $stmt->close();

        if ($lockUntil) {
            redirectWithMessage('../login.php', 'Too many failed login attempts. Account locked for 30 minutes.', 'error');
        } else {
            $remaining = 5 - $attempts;
            redirectWithMessage('../login.php', "Invalid email or password. $remaining attempts remaining.", 'error');
        }
    }

    // Get customer details if user is a customer
    $customer = null;
    if ($user['role'] === 'customer') {
        $stmt = $mysqli->prepare("
            SELECT c.id, c.fullname, c.email, c.phone, c.address, c.gender, c.customer_code, c.status
            FROM customer c
            WHERE c.user_id = ? AND c.status = 'active'
        ");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();
        $stmt->close();

        if (!$customer) {
            redirectWithMessage('../login.php', 'Customer account not found or inactive.', 'error');
        }
    }

    // Successful login - reset login attempts and update last login
    $stmt = $mysqli->prepare("
        UPDATE users 
        SET login_attempts = 0, locked_until = NULL, last_login = NOW(), updated_at = NOW() 
        WHERE email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();

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

    // Set remember me cookie if requested
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expires = time() + (30 * 24 * 60 * 60); // 30 days

        // Store token in database (create remember_tokens table if needed)
        try {
            $mysqli->query("
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

            $stmt = $mysqli->prepare("
                INSERT INTO remember_tokens (user_id, token, expires_at) 
                VALUES (?, ?, FROM_UNIXTIME(?))
            ");
            $stmt->bind_param("isi", $user['id'], $token, $expires);
            $stmt->execute();
            $stmt->close();

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

        $redirectUrl = $_SESSION['redirect_after_login'] ?? '../index.php';
        unset($_SESSION['redirect_after_login']);

        redirectWithMessage($redirectUrl, "Welcome back, {$user['name']}!", 'success');
    }

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    redirectWithMessage('../login.php', 'Login failed. Please try again later.', 'error');
}

// Close connection
$mysqli->close();
?>