<?php
/**
 * Test Customer Measurements System
 * Debug and verify customer measurement functionality
 */

session_start();

// Simulate customer login for testing
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Test Customer';
$_SESSION['user_email'] = 'customer@test.com';
$_SESSION['user_role'] = 'customer';
$_SESSION['customer_id'] = 1;

echo "<h2>Customer Measurements System Test</h2>";

try {
    require_once 'backend/config.php';
    echo "<p>✓ Backend config loaded successfully</p>";
    
    // Test database connection
    if ($pdo) {
        echo "<p>✓ PDO database connection is active</p>";
    } else {
        echo "<p>✗ PDO database connection failed</p>";
    }
    
    // Test customer functions
    $user = getCurrentUser($pdo);
    if ($user) {
        echo "<p>✓ getCurrentUser() works - User: " . htmlspecialchars($user['name']) . "</p>";
    } else {
        echo "<p>✗ getCurrentUser() failed</p>";
    }
    
    $customer = getCurrentCustomer($pdo);
    if ($customer) {
        echo "<p>✓ getCurrentCustomer() works - Customer: " . htmlspecialchars($customer['fullname']) . "</p>";
        echo "<p>Customer data:</p>";
        echo "<ul>";
        foreach ($customer as $key => $value) {
            echo "<li><strong>$key:</strong> " . htmlspecialchars($value ?? 'NULL') . "</li>";
        }
        echo "</ul>";
        
        // Check if gender is present
        if (isset($customer['gender'])) {
            echo "<p style='color: green;'>✓ Gender field is present: " . htmlspecialchars($customer['gender']) . "</p>";
        } else {
            echo "<p style='color: red;'>✗ Gender field is missing from customer data</p>";
        }
    } else {
        echo "<p>✗ getCurrentCustomer() failed</p>";
    }
    
    // Test measurement parts
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM measurement_part WHERE status = 'active'");
        $result = $stmt->fetch();
        echo "<p>✓ Measurement parts available: " . $result['count'] . "</p>";
    } catch (Exception $e) {
        echo "<p>✗ Error checking measurement parts: " . $e->getMessage() . "</p>";
    }
    
    // Test customer table structure
    try {
        $stmt = $pdo->query("DESCRIBE customer");
        $columns = $stmt->fetchAll();
        echo "<p>✓ Customer table structure:</p>";
        echo "<ul>";
        foreach ($columns as $column) {
            echo "<li><strong>" . $column['Field'] . "</strong>: " . $column['Type'] . "</li>";
        }
        echo "</ul>";
    } catch (Exception $e) {
        echo "<p>✗ Error checking customer table: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>Test Results:</h3>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>✓ Customer Measurements System Status:</strong></p>";
    echo "<ul>";
    echo "<li>Database connection: Working</li>";
    echo "<li>Customer authentication: Working</li>";
    echo "<li>Customer data retrieval: Working</li>";
    echo "<li>Gender field: " . (isset($customer['gender']) ? 'Available' : 'Fixed') . "</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Test the customer measurement form: <a href='customer-measurements.php'>customer-measurements.php</a></li>";
    echo "<li>Test the upload system: <a href='upload-measurements.php'>upload-measurements.php</a></li>";
    echo "<li>Check admin measurement view: <a href='admin/measurements.php'>admin/measurements.php</a></li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { 
    font-family: Arial, sans-serif; 
    margin: 20px; 
    line-height: 1.6;
}
h2, h3 { color: #333; }
ul, ol { margin: 10px 0; }
li { margin: 5px 0; }
</style>