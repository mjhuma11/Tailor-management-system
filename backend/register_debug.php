<?php
/**
 * Debug Registration Handler
 * The Stitch House - Debug Version to Identify Issues
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'stitch');

echo "<h2>Registration Debug</h2>";

// Create MySQLi connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "<p>✓ Database connection successful</p>";

// Set charset to utf8mb4
$mysqli->set_charset("utf8mb4");

// Check if tables exist
$tables = ['users', 'customer'];
foreach ($tables as $table) {
    $result = $mysqli->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<p>✓ Table '$table' exists</p>";
    } else {
        echo "<p>✗ Table '$table' does not exist</p>";
    }
}

// Check table structure
echo "<h3>Users Table Structure:</h3>";
$result = $mysqli->query("DESCRIBE users");
if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>Error describing users table: " . $mysqli->error . "</p>";
}

echo "<h3>Customer Table Structure:</h3>";
$result = $mysqli->query("DESCRIBE customer");
if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>Error describing customer table: " . $mysqli->error . "</p>";
}

// Only process if POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Form Data Received:</h3>";
    
    // Get and sanitize form data
    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $terms = isset($_POST['terms']);
    $newsletter = isset($_POST['newsletter']);
    
    echo "<ul>";
    echo "<li>Username: '$username'</li>";
    echo "<li>Full Name: '$fullname'</li>";
    echo "<li>Email: '$email'</li>";
    echo "<li>Phone: '$phone'</li>";
    echo "<li>Address: '$address'</li>";
    echo "<li>Gender: '$gender'</li>";
    echo "<li>Password Length: " . strlen($password) . "</li>";
    echo "<li>Terms Accepted: " . ($terms ? 'Yes' : 'No') . "</li>";
    echo "<li>Newsletter: " . ($newsletter ? 'Yes' : 'No') . "</li>";
    echo "</ul>";
    
    // Validation
    $errors = [];
    
    if (empty($username)) $errors[] = 'Username is required.';
    if (empty($fullname)) $errors[] = 'Full name is required.';
    if (empty($email)) $errors[] = 'Email address is required.';
    if (empty($phone)) $errors[] = 'Phone number is required.';
    if (empty($address)) $errors[] = 'Address is required.';
    if (empty($gender)) $errors[] = 'Gender is required.';
    if (empty($password)) $errors[] = 'Password is required.';
    
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters long.';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';
    if (!$terms) $errors[] = 'You must accept the Terms & Conditions.';
    
    if (!empty($errors)) {
        echo "<h3>Validation Errors:</h3>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li style='color: red;'>$error</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>✓ All validation passed</p>";
        
        try {
            // Test user insertion
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            echo "<h3>Attempting Database Operations:</h3>";
            
            // Start transaction
            $mysqli->autocommit(FALSE);
            
            // Insert user
            $stmt = $mysqli->prepare("
                INSERT INTO users (
                    name, email, username, password, phone, role, status, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, 'customer', 'active', NOW(), NOW())
            ");
            
            if (!$stmt) {
                throw new Exception("Prepare failed for users table: " . $mysqli->error);
            }
            
            $stmt->bind_param("sssss", $fullname, $email, $username, $hashedPassword, $phone);
            
            if (!$stmt->execute()) {
                throw new Exception("Error inserting into users table: " . $stmt->error);
            }
            
            $userId = $mysqli->insert_id;
            echo "<p>✓ User inserted with ID: $userId</p>";
            $stmt->close();
            
            // Generate customer code
            $customerCode = 'CUST' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Insert customer
            $stmt = $mysqli->prepare("
                INSERT INTO customer (
                    user_id, fullname, email, phone, address, gender, 
                    customer_code, status, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
            ");
            
            if (!$stmt) {
                throw new Exception("Prepare failed for customer table: " . $mysqli->error);
            }
            
            $stmt->bind_param("issssss", $userId, $fullname, $email, $phone, $address, $gender, $customerCode);
            
            if (!$stmt->execute()) {
                throw new Exception("Error inserting into customer table: " . $stmt->error);
            }
            
            $customerId = $mysqli->insert_id;
            echo "<p>✓ Customer inserted with ID: $customerId</p>";
            $stmt->close();
            
            // Commit transaction
            $mysqli->commit();
            $mysqli->autocommit(TRUE);
            
            echo "<p style='color: green; font-weight: bold;'>✓ Registration completed successfully!</p>";
            echo "<p>User ID: $userId, Customer ID: $customerId, Customer Code: $customerCode</p>";
            
        } catch (Exception $e) {
            $mysqli->rollback();
            $mysqli->autocommit(TRUE);
            echo "<p style='color: red; font-weight: bold;'>✗ Registration failed: " . $e->getMessage() . "</p>";
        }
    }
} else {
    echo "<h3>Test Form:</h3>";
    echo '<form method="POST">
        <p>Username: <input type="text" name="username" value="testuser123" required></p>
        <p>Full Name: <input type="text" name="fullname" value="Test User" required></p>
        <p>Email: <input type="email" name="email" value="test@example.com" required></p>
        <p>Phone: <input type="tel" name="phone" value="1234567890" required></p>
        <p>Address: <textarea name="address" required>123 Test Street</textarea></p>
        <p>Gender: 
            <select name="gender" required>
                <option value="">Select</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </p>
        <p>Password: <input type="password" name="password" value="testpass123" required></p>
        <p>Confirm Password: <input type="password" name="confirmPassword" value="testpass123" required></p>
        <p><input type="checkbox" name="terms" checked required> Accept Terms</p>
        <p><input type="checkbox" name="newsletter"> Newsletter</p>
        <p><input type="submit" value="Test Registration"></p>
    </form>';
}

$mysqli->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>