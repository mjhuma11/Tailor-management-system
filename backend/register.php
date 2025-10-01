<?php
/**
 * Customer Registration Handler
 * The Stitch House - Customer Registration Backend
 */

require_once 'config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('../register.html', 'Invalid request method.', 'error');
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

// Terms validation
if (!$terms) {
    $errors[] = 'You must accept the Terms & Conditions.';
}

// Gender validation
if (!in_array($gender, ['Male', 'Female', 'Other'])) {
    $errors[] = 'Please select a valid gender.';
}

// If there are validation errors, redirect back with errors
if (!empty($errors)) {
    $errorMessage = implode(' ', $errors);
    redirectWithMessage('../register.html', $errorMessage, 'error');
}

try {
    // Check if username already exists in users table
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        redirectWithMessage('../register.html', 'This username is already taken. Please choose a different username.', 'error');
    }
    
    // Check if email already exists in users table
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        redirectWithMessage('../register.html', 'An account with this email address already exists.', 'error');
    }
    
    // Check if phone already exists in customer table
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    $stmt = $pdo->prepare("SELECT id FROM customer WHERE phone LIKE ?");
    $stmt->execute(["%$cleanPhone%"]);
    if ($stmt->fetch()) {
        redirectWithMessage('../register.html', 'An account with this phone number already exists.', 'error');
    }
    
    // Generate customer code
    $customerCode = generateCustomerCode($pdo);
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new customer in customer table
    $stmt = $pdo->prepare("
        INSERT INTO customer (
            fullname, email, phone, address, gender, 
            customer_code, status, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
    ");
    
    $stmt->execute([
        $fullname,
        $email,
        $phone,
        $address,
        $gender,
        $customerCode
    ]);
    
    $customerId = $pdo->lastInsertId();
    
    // Insert user in users table with customer role (default)
    $stmt = $pdo->prepare("
        INSERT INTO users (
            name, email, username, password, phone, status, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())
    ");
    
    // Generate unique username from email
    $username = explode('@', $email)[0] . '_' . $customerId;
    
    $stmt->execute([
        $fullname,
        $email,
        $username,
        $hashedPassword,
        $phone
    ]);
    
    $userId = $pdo->lastInsertId();
    
    // Link customer to user account
    $stmt = $pdo->prepare("UPDATE customer SET user_id = ? WHERE id = ?");
    $stmt->execute([$userId, $customerId]);
    
    // If newsletter subscription is checked, add to newsletter list (optional)
    if ($newsletter) {
        try {
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
            
            $stmt = $pdo->prepare("
                INSERT INTO newsletter_subscribers (customer_id, email, status, subscribed_at) 
                VALUES (?, ?, 'active', NOW())
                ON DUPLICATE KEY UPDATE 
                status = 'active', 
                customer_id = VALUES(customer_id)
            ");
            $stmt->execute([$customerId, $email]);
        } catch (Exception $e) {
            // Newsletter subscription failed, but don't stop registration
            error_log("Newsletter subscription failed: " . $e->getMessage());
        }
    }
    
    // Log the registration
    error_log("New customer registered: User ID $userId, Customer ID $customerId, Email: $email, Name: $fullname");
    
    // Redirect to login page after successful registration
    redirectWithMessage('../login.html', 'Registration successful! Please log in with your new account.', 'success');
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    redirectWithMessage('../register.html', 'Registration failed. Please try again later.', 'error');
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    redirectWithMessage('../register.html', 'An unexpected error occurred. Please try again.', 'error');
}
?>