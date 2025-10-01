<?php

/**
 * Contact Form Handler
 * The Stitch House - Contact Form Processing
 */

require_once 'config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('../index.php#contact', 'Invalid request method.', 'error');
}

// Get and sanitize form data
$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$service = sanitize($_POST['service'] ?? '');
$message = sanitize($_POST['message'] ?? '');

// Validation
$errors = [];

if (empty($name)) $errors[] = 'Name is required.';
if (empty($email)) $errors[] = 'Email is required.';
if (empty($message)) $errors[] = 'Message is required.';
if (empty($service)) $errors[] = 'Please select a service.';

// Email validation
if (!empty($email) && !isValidEmail($email)) {
    $errors[] = 'Please enter a valid email address.';
}

// If there are validation errors, redirect back
if (!empty($errors)) {
    $errorMessage = implode(' ', $errors);
    redirectWithMessage('../index.php#contact', $errorMessage, 'error');
}

try {
    // Create contact_messages table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            service VARCHAR(100),
            message TEXT NOT NULL,
            status ENUM('new', 'read', 'replied') DEFAULT 'new',
            user_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Get current user ID if logged in
    $userId = isUserLoggedIn() ? $_SESSION['user_id'] : null;

    // Insert contact message
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages (name, email, phone, service, message, user_id, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([$name, $email, $phone, $service, $message, $userId]);

    // Log the contact form submission
    error_log("Contact form submitted: Name: $name, Email: $email, Service: $service");

    // Redirect with success message
    redirectWithMessage('../index.php#contact', 'Thank you for your message! We will contact you soon.', 'success');
} catch (PDOException $e) {
    error_log("Contact form error: " . $e->getMessage());
    redirectWithMessage('../index.php#contact', 'Failed to send message. Please try again later.', 'error');
} catch (Exception $e) {
    error_log("Contact form error: " . $e->getMessage());
    redirectWithMessage('../index.php#contact', 'An unexpected error occurred. Please try again.', 'error');
}
