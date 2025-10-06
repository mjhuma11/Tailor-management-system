<?php
/**
 * Admin Configuration File
 * The Stitch House - Admin Panel Configuration
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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

// Check if user is logged in
function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin/manager
function isAdminLoggedIn() {
    return isUserLoggedIn() && isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'manager']);
}

// Require authentication for admin pages
function requireAuth() {
    if (!isAdminLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        $_SESSION['flash_message'] = 'Please log in to access the admin panel.';
        $_SESSION['flash_type'] = 'error';
        header("Location: ../login.php");
        exit();
    }
}

// Get current admin user info
function getCurrentAdmin($mysqli) {
    if (!isUserLoggedIn()) {
        return null;
    }
    
    try {
        $stmt = $mysqli->prepare("SELECT id, name, email, role, status FROM users WHERE id = ? AND status = 'active'");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    } catch (Exception $e) {
        return null;
    }
}

// Get and clear flash messages
function getFlashMessages() {
    $messages = [];
    if (isset($_SESSION['flash_message'])) {
        $messages[$_SESSION['flash_type'] ?? 'info'] = $_SESSION['flash_message'];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    }
    return $messages;
}

// Show success message
function showSuccess($message) {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = 'success';
}

// Show error message
function showError($message) {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = 'error';
}

// Format currency
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

// Format date
function formatDate($date, $format = 'M d, Y') {
    if (empty($date) || $date === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    return date($format, strtotime($date));
}

// Get dashboard statistics
function getDashboardStats($mysqli) {
    $stats = [
        'total_customers' => 0,
        'total_orders' => 0,
        'pending_orders' => 0,
        'last_30_days_income' => 0
    ];
    
    try {
        // Total customers
        $result = $mysqli->query("SELECT COUNT(*) as count FROM customer WHERE status = 'active'");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['total_customers'] = $row['count'];
        }
        
        // Total orders
        $result = $mysqli->query("SELECT COUNT(*) as count FROM `order` WHERE deleted_at IS NULL");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['total_orders'] = $row['count'];
        }
        
        // Pending orders
        $result = $mysqli->query("SELECT COUNT(*) as count FROM `order` WHERE order_status IN ('received', 'in_progress') AND deleted_at IS NULL");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['pending_orders'] = $row['count'];
        }
        
        // Last 30 days income
        $result = $mysqli->query("SELECT COALESCE(SUM(amount), 0) as total FROM income WHERE income_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND deleted_at IS NULL");
        if ($result) {
            $row = $result->fetch_assoc();
            $stats['last_30_days_income'] = $row['total'];
        }
        
    } catch (Exception $e) {
        error_log("Error getting dashboard stats: " . $e->getMessage());
    }
    
    return $stats;
}

// Get chart data for dashboard
function getChartData($mysqli, $days = 30) {
    $data = [
        'labels' => [],
        'income' => [],
        'orders' => []
    ];
    
    try {
        // Get last 30 days data
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $data['labels'][] = date('M j', strtotime($date));
            
            // Income for this date
            $stmt = $mysqli->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM income WHERE DATE(income_date) = ? AND deleted_at IS NULL");
            $stmt->bind_param("s", $date);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $data['income'][] = floatval($row['total']);
            $stmt->close();
            
            // Orders for this date
            $stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM `order` WHERE DATE(created_at) = ? AND deleted_at IS NULL");
            $stmt->bind_param("s", $date);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $data['orders'][] = intval($row['count']);
            $stmt->close();
        }
        
    } catch (Exception $e) {
        error_log("Error getting chart data: " . $e->getMessage());
    }
    
    return $data;
}

// Set timezone
date_default_timezone_set('America/New_York');

// Error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>