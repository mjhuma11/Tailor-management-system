<?php
/**
 * Update Users Table Structure
 * Ensures the users table has the correct role enum and default values
 */

require_once 'config.php';

try {
    echo "<h2>Updating Users Table Structure...</h2>";
    
    // Check current table structure
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    
    echo "<h3>Current Users Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Update role enum to include customer and set default
    echo "<h3>Updating Role Enum...</h3>";
    try {
        $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin','manager','staff','customer') DEFAULT 'customer'");
        echo "<p>✅ Role enum updated successfully!</p>";
        echo "<p>Available roles: admin, manager, staff, customer</p>";
        echo "<p>Default role: customer</p>";
    } catch (Exception $e) {
        echo "<p>❌ Error updating role enum: " . $e->getMessage() . "</p>";
    }
    
    // Add phone column if it doesn't exist
    echo "<h3>Adding Phone Column...</h3>";
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER email");
        echo "<p>✅ Phone column added successfully!</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ Phone column already exists.</p>";
        } else {
            echo "<p>❌ Error adding phone column: " . $e->getMessage() . "</p>";
        }
    }
    
    // Add login tracking columns
    echo "<h3>Adding Login Tracking Columns...</h3>";
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN login_attempts INT DEFAULT 0");
        echo "<p>✅ Login attempts column added.</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ Login attempts column already exists.</p>";
        }
    }
    
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN locked_until TIMESTAMP NULL");
        echo "<p>✅ Locked until column added.</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ Locked until column already exists.</p>";
        }
    }
    
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL");
        echo "<p>✅ Last login column added.</p>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ Last login column already exists.</p>";
        }
    }
    
    // Show updated table structure
    echo "<h3>Updated Users Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test inserting a customer user
    echo "<h3>Testing Customer Registration...</h3>";
    try {
        // Check if test user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute(['test@customer.com']);
        if ($stmt->fetch()) {
            echo "<p>ℹ️ Test customer already exists.</p>";
        } else {
            // Insert test customer
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, username, password, phone, status) 
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([
                'Test Customer',
                'test@customer.com',
                'test_customer',
                password_hash('password123', PASSWORD_DEFAULT),
                '1234567890'
            ]);
            
            // Check the inserted user's role
            $stmt = $pdo->prepare("SELECT role FROM users WHERE email = ?");
            $stmt->execute(['test@customer.com']);
            $user = $stmt->fetch();
            
            echo "<p>✅ Test customer created successfully!</p>";
            echo "<p>Default role assigned: <strong>{$user['role']}</strong></p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Error testing customer registration: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
    echo "<h3>🎉 Users Table Update Complete!</h3>";
    echo "<p><strong>Role Configuration:</strong></p>";
    echo "<ul>";
    echo "<li>✅ admin - System administrator</li>";
    echo "<li>✅ manager - Shop manager</li>";
    echo "<li>✅ staff - Shop staff/employee</li>";
    echo "<li>✅ customer - Customer (DEFAULT)</li>";
    echo "</ul>";
    
    echo "<p><a href='../register.html' class='btn btn-primary'>Test Registration</a> ";
    echo "<a href='../login.html' class='btn btn-secondary'>Test Login</a></p>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ Error: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Users Table - The Stitch House</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem 0;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin: 0 auto;
            max-width: 800px;
        }
        
        table {
            width: 100%;
            margin: 1rem 0;
        }
        
        th, td {
            padding: 8px 12px;
            text-align: left;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        .btn {
            margin: 5px;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Content will be inserted above -->
    </div>
</body>
</html>