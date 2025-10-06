<?php

/**
 * Customer Registration Handler with MySQLi
 * The Stitch House - Customer Registration Backend
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
function sanitize($data)
{
    global $mysqli;
    return $mysqli->real_escape_string(htmlspecialchars(strip_tags(trim($data))));
}

// Validate email format
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate phone number
function isValidPhone($phone)
{
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10;
}

// Generate customer code
function generateCustomerCode($mysqli)
{
    do {
        $code = 'CUST' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $mysqli->prepare("SELECT id FROM customer WHERE customer_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } while ($result->num_rows > 0);

    return $code;
}

// Redirect with message
function redirectWithMessage($url, $message, $type = 'success')
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('../register.php', 'Invalid request method.', 'error');
}

// Get and sanitize form data
$username = sanitize($_POST['username'] ?? '');
$fullname = sanitize($_POST['fullname'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$address = sanitize($_POST['address'] ?? '');
$gender = sanitize($_POST['gender'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$terms = isset($_POST['terms']);
$newsletter = isset($_POST['newsletter']);

// Validation
$errors = [];

// Required fields validation
if (empty($username)) $errors[] = 'Username is required.';
if (empty($fullname)) $errors[] = 'Full name is required.';
if (empty($email)) $errors[] = 'Email address is required.';
if (empty($phone)) $errors[] = 'Phone number is required.';
if (empty($address)) $errors[] = 'Address is required.';
if (empty($gender)) $errors[] = 'Gender is required.';
if (empty($password)) $errors[] = 'Password is required.';

// Username validation
if (!empty($username)) {
    if (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters long.';
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores.';
    }
}

// Email validation
if (!empty($email) && !isValidEmail($email)) {
    $errors[] = 'Please enter a valid email address.';
}

// Phone validation
if (!empty($phone) && !isValidPhone($phone)) {
    $errors[] = 'Please enter a valid phone number.';
}

// Password validation
if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters long.';
}

if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match.';
}

// Gender validation
$validGenders = ['Male', 'Female', 'Other', 'male', 'female', 'other'];
if (!empty($gender) && !in_array($gender, $validGenders)) {
    $errors[] = 'Please select a valid gender.';
}

// Normalize gender to proper case
if (!empty($gender)) {
    $gender = ucfirst(strtolower($gender));
}

// Terms validation
if (!$terms) {
    $errors[] = 'You must accept the Terms & Conditions.';
}

// If there are validation errors, redirect back with errors
if (!empty($errors)) {
    $errorMessage = implode(' ', $errors);
    redirectWithMessage('../register.php', $errorMessage, 'error');
}

try {
    // Check if username already exists in users table
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        redirectWithMessage('../register.php', 'This username is already taken. Please choose a different username.', 'error');
    }
    $stmt->close();

    // Check if email already exists in users table
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        redirectWithMessage('../register.php', 'An account with this email address already exists.', 'error');
    }
    $stmt->close();

    // Check if phone already exists in customer table
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    $phonePattern = "%$cleanPhone%";
    $stmt = $mysqli->prepare("SELECT id FROM customer WHERE phone LIKE ?");
    $stmt->bind_param("s", $phonePattern);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        redirectWithMessage('../register.php', 'An account with this phone number already exists.', 'error');
    }
    $stmt->close();

    // Generate customer code
    $customerCode = generateCustomerCode($mysqli);

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Start transaction
    $mysqli->autocommit(FALSE);

    // Insert user in users table first
    $stmt = $mysqli->prepare("
        INSERT INTO users (
            name, email, username, password, phone, role, status, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, 'customer', 'active', NOW(), NOW())
    ");
    $stmt->bind_param("sssss", $fullname, $email, $username, $hashedPassword, $phone);

    if (!$stmt->execute()) {
        throw new Exception("Error creating user account: " . $stmt->error);
    }

    $userId = $mysqli->insert_id;
    $stmt->close();

    // Insert customer record
    $stmt = $mysqli->prepare("
        INSERT INTO customer (
            user_id, fullname, email, phone, address, gender, 
            customer_code, status, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
    ");
    $stmt->bind_param("issssss", $userId, $fullname, $email, $phone, $address, $gender, $customerCode);

    if (!$stmt->execute()) {
        throw new Exception("Error creating customer record: " . $stmt->error);
    }

    $customerId = $mysqli->insert_id;
    $stmt->close();

    // If newsletter subscription is checked, add to newsletter list
    if ($newsletter) {
        try {
            // Create newsletter table if it doesn't exist
            $mysqli->query("
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

            $stmt = $mysqli->prepare("
                INSERT INTO newsletter_subscribers (customer_id, email, status, subscribed_at) 
                VALUES (?, ?, 'active', NOW())
                ON DUPLICATE KEY UPDATE 
                status = 'active', 
                customer_id = VALUES(customer_id)
            ");
            $stmt->bind_param("is", $customerId, $email);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            // Newsletter subscription failed, but don't stop registration
            error_log("Newsletter subscription failed: " . $e->getMessage());
        }
    }

    // Commit transaction
    $mysqli->commit();
    $mysqli->autocommit(TRUE);

    // Log the registration
    error_log("New customer registered: User ID $userId, Customer ID $customerId, Email: $email, Username: $username");

    // Redirect to login page after successful registration
    redirectWithMessage('../login.php', 'Registration successful! Please log in with your new account.', 'success');
} catch (Exception $e) {
    // Rollback transaction on error
    $mysqli->rollback();
    $mysqli->autocommit(TRUE);

    // Log detailed error information
    error_log("Registration error: " . $e->getMessage());
    error_log("Registration error details - Username: $username, Email: $email, Phone: $phone");
    
    // Show more specific error message in development
    $errorMessage = 'Registration failed. ';
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        if (strpos($e->getMessage(), 'email') !== false) {
            $errorMessage .= 'Email address already exists.';
        } elseif (strpos($e->getMessage(), 'username') !== false) {
            $errorMessage .= 'Username already exists.';
        } else {
            $errorMessage .= 'Duplicate information found.';
        }
    } else {
        $errorMessage .= 'Please check your information and try again.';
    }
    
    redirectWithMessage('../register.php', $errorMessage, 'error');
}

// Close connection
$mysqli->close();
