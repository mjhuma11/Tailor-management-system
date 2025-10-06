<?php
/**
 * Logout Handler
 * The Stitch House - User Logout
 */

// Start session
session_start();

// Database configuration for remember token cleanup
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'stitch');

// Create MySQLi connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$mysqli->connect_error) {
    $mysqli->set_charset("utf8mb4");
    
    // Clean up remember token if exists
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        
        try {
            $stmt = $mysqli->prepare("DELETE FROM remember_tokens WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error cleaning up remember token: " . $e->getMessage());
        }
        
        // Remove the cookie
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
    
    $mysqli->close();
}

// Log the logout
if (isset($_SESSION['user_id'])) {
    error_log("User logout: User ID {$_SESSION['user_id']}, Email: " . ($_SESSION['user_email'] ?? 'unknown'));
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Set logout message
session_start();
$_SESSION['flash_message'] = 'You have been successfully logged out.';
$_SESSION['flash_type'] = 'success';

// Redirect to homepage
header("Location: ../index.php");
exit();
?>