<?php
/**
 * Database Configuration for Customer Authentication
 * The Stitch House - Customer Portal Backend
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'stitch');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Database Connection
 */
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/**
 * Helper Functions
 */

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number (basic validation)
 */
function isValidPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10;
}

/**
 * Check if user is logged in
 */
function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if customer is logged in
 */
function isCustomerLoggedIn() {
    return isUserLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer';
}

/**
 * Check if admin/staff is logged in
 */
function isAdminLoggedIn() {
    return isUserLoggedIn() && isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'manager', 'staff']);
}

/**
 * Get current user info
 */
function getCurrentUser($pdo) {
    if (!isUserLoggedIn()) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, name, email, role, status FROM users WHERE id = ? AND status = 'active'");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return null;
    }
}

/**
 * Get current customer info
 */
function getCurrentCustomer($pdo) {
    if (!isCustomerLoggedIn()) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, fullname, email, phone, status FROM customer WHERE id = ? AND status = 'active'");
        $stmt->execute([$_SESSION['customer_id']]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return null;
    }
}

/**
 * Generate customer code
 */
function generateCustomerCode($pdo) {
    do {
        $code = 'CUST' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("SELECT id FROM customer WHERE customer_code = ?");
        $stmt->execute([$code]);
    } while ($stmt->fetch());
    
    return $code;
}

/**
 * Send JSON response
 */
function sendJsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

/**
 * Redirect with message
 */
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit();
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'text' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type'] ?? 'info'
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $message;
    }
    return null;
}

// Set timezone
date_default_timezone_set('America/New_York');

// Error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>