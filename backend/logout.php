<?php
/**
 * Logout Handler
 * The Stitch House - User Logout
 */

require_once 'config.php';

// Clear remember me token if exists
if (isset($_COOKIE['remember_token'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE token = ?");
        $stmt->execute([$_COOKIE['remember_token']]);
        
        // Clear the cookie
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    } catch (Exception $e) {
        error_log("Error clearing remember token: " . $e->getMessage());
    }
}

// Log the logout
if (isset($_SESSION['user_id'])) {
    error_log("User logout: User ID {$_SESSION['user_id']}, Email: " . ($_SESSION['user_email'] ?? 'unknown'));
}

// Clear all session data
session_destroy();

// Redirect to homepage with message
redirectWithMessage('../index.php', 'You have been logged out successfully.', 'success');
?>