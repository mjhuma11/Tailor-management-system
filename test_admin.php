<?php
/**
 * Test Admin Dashboard Access
 * Simple test to verify admin functionality
 */

// Start session and simulate admin login
session_start();

// Simulate admin login for testing
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Admin';
$_SESSION['user_email'] = 'admin@stitch.com';
$_SESSION['user_role'] = 'admin';

echo "<h2>Admin Dashboard Test</h2>";

// Include admin config
try {
    require_once 'admin/includes/config.php';
    echo "<p>✓ Admin config loaded successfully</p>";
    
    // Test database connection
    if ($mysqli->ping()) {
        echo "<p>✓ Database connection is active</p>";
    } else {
        echo "<p>✗ Database connection failed</p>";
    }
    
    // Test admin functions
    $currentAdmin = getCurrentAdmin($mysqli);
    if ($currentAdmin) {
        echo "<p>✓ getCurrentAdmin() works - User: " . htmlspecialchars($currentAdmin['name']) . "</p>";
    } else {
        echo "<p>✗ getCurrentAdmin() failed</p>";
    }
    
    // Test dashboard stats
    $stats = getDashboardStats($mysqli);
    echo "<p>✓ Dashboard stats loaded:</p>";
    echo "<ul>";
    echo "<li>Total Customers: " . $stats['total_customers'] . "</li>";
    echo "<li>Total Orders: " . $stats['total_orders'] . "</li>";
    echo "<li>Pending Orders: " . $stats['pending_orders'] . "</li>";
    echo "<li>Last 30 Days Income: $" . number_format($stats['last_30_days_income'], 2) . "</li>";
    echo "</ul>";
    
    echo "<h3>Admin Dashboard Ready!</h3>";
    echo "<p><a href='admin/dashboard.php' class='btn btn-primary'>Go to Admin Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.btn { 
    display: inline-block; 
    padding: 10px 20px; 
    background: #007bff; 
    color: white; 
    text-decoration: none; 
    border-radius: 5px; 
}
.btn:hover { background: #0056b3; }
</style>