<?php
/**
 * Fix Customer Gender Field
 * Ensure all customers have a gender value
 */

echo "<h2>Fix Customer Gender Field</h2>";

try {
    require_once 'backend/config.php';
    
    // Check current customer data
    $stmt = $pdo->query("SELECT id, fullname, gender FROM customer WHERE status = 'active'");
    $customers = $stmt->fetchAll();
    
    echo "<h3>Current Customer Data:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Gender</th><th>Status</th></tr>";
    
    $needsUpdate = 0;
    foreach ($customers as $customer) {
        $status = empty($customer['gender']) ? 'Missing' : 'OK';
        if (empty($customer['gender'])) {
            $needsUpdate++;
        }
        
        echo "<tr>";
        echo "<td>" . $customer['id'] . "</td>";
        echo "<td>" . htmlspecialchars($customer['fullname']) . "</td>";
        echo "<td>" . htmlspecialchars($customer['gender'] ?? 'NULL') . "</td>";
        echo "<td style='color: " . ($status === 'OK' ? 'green' : 'red') . ";'>" . $status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($needsUpdate > 0) {
        echo "<h3>Fixing Missing Gender Values:</h3>";
        
        // Update customers with missing gender to 'Male' as default
        $stmt = $pdo->prepare("UPDATE customer SET gender = 'Male' WHERE gender IS NULL OR gender = ''");
        $updated = $stmt->execute();
        
        if ($updated) {
            echo "<p style='color: green;'>✓ Updated $needsUpdate customers with default gender 'Male'</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to update customer gender values</p>";
        }
        
        // Verify the update
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM customer WHERE gender IS NULL OR gender = ''");
        $result = $stmt->fetch();
        
        if ($result['count'] == 0) {
            echo "<p style='color: green;'>✓ All customers now have gender values</p>";
        } else {
            echo "<p style='color: red;'>✗ " . $result['count'] . " customers still missing gender</p>";
        }
    } else {
        echo "<p style='color: green;'>✓ All customers already have gender values</p>";
    }
    
    echo "<h3>Database Schema Check:</h3>";
    
    // Check if gender column exists and its properties
    $stmt = $pdo->query("SHOW COLUMNS FROM customer LIKE 'gender'");
    $genderColumn = $stmt->fetch();
    
    if ($genderColumn) {
        echo "<p>✓ Gender column exists</p>";
        echo "<ul>";
        echo "<li><strong>Type:</strong> " . $genderColumn['Type'] . "</li>";
        echo "<li><strong>Null:</strong> " . $genderColumn['Null'] . "</li>";
        echo "<li><strong>Default:</strong> " . ($genderColumn['Default'] ?? 'NULL') . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>✗ Gender column does not exist</p>";
        echo "<p>Run this SQL to add the gender column:</p>";
        echo "<pre>ALTER TABLE customer ADD COLUMN gender ENUM('Male', 'Female', 'Other') NOT NULL DEFAULT 'Male';</pre>";
    }
    
    echo "<h3>Test Customer Measurements System:</h3>";
    echo "<p><a href='test_customer_measurements.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run System Test</a></p>";
    
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
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background: #f8f9fa; }
pre { 
    background: #f8f9fa; 
    padding: 10px; 
    border-radius: 5px; 
    overflow-x: auto;
}
</style>