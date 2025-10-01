<?php
/**
 * Login Troubleshooting Script
 * This will help diagnose login issues
 */

require_once 'backend/config.php';

echo "<h2>Login System Diagnostics</h2>";

// Test 1: Check if database connection works
echo "<h3>1. Database Connection Test</h3>";
try {
    $stmt = $pdo->query("SELECT 1");
    echo "<p>✅ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit();
}

// Test 2: Check if users table exists and has correct structure
echo "<h3>2. Users Table Structure</h3>";
try {
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr><td>{$column['Field']}</td><td>{$column['Type']}</td><td>{$column['Default']}</td></tr>";
    }
    echo "</table>";
    
    // Check if role column includes 'customer'
    $roleColumn = array_filter($columns, function($col) { return $col['Field'] === 'role'; });
    if ($roleColumn) {
        $roleColumn = array_values($roleColumn)[0];
        if (strpos($roleColumn['Type'], 'customer') !== false) {
            echo "<p>✅ Role column includes 'customer'</p>";
        } else {
            echo "<p>❌ Role column missing 'customer': {$roleColumn['Type']}</p>";
            echo "<p><strong>Fix:</strong> Run <a href='backend/update-users-table.php'>update-users-table.php</a></p>";
        }
    }
} catch (Exception $e) {
    echo "<p>❌ Error checking users table: " . $e->getMessage() . "</p>";
}

// Test 3: Check if there are any users in the database
echo "<h3>3. Users in Database</h3>";
try {
    $stmt = $pdo->query("SELECT id, name, email, username, role, status FROM users LIMIT 5");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "<p>⚠️ No users found in database</p>";
        echo "<p><strong>Solution:</strong> Register a new user first</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Role</th><th>Status</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['name']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>{$user['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p>✅ Found " . count($users) . " users</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error checking users: " . $e->getMessage() . "</p>";
}

// Test 4: Test login functionality
echo "<h3>4. Login Function Test</h3>";
if (!empty($users)) {
    $testUser = $users[0];
    echo "<p>Testing with user: {$testUser['email']}</p>";
    
    // Check if login.php file exists
    if (file_exists('backend/login.php')) {
        echo "<p>✅ login.php file exists</p>";
    } else {
        echo "<p>❌ login.php file missing</p>";
    }
    
    // Check if config functions exist
    if (function_exists('isUserLoggedIn')) {
        echo "<p>✅ Login functions available</p>";
    } else {
        echo "<p>❌ Login functions missing</p>";
    }
} else {
    echo "<p>⚠️ Cannot test login - no users available</p>";
}

// Test 5: Check form submission path
echo "<h3>5. Form Submission Path Test</h3>";
$loginPath = 'backend/login.php';
if (file_exists($loginPath)) {
    echo "<p>✅ Login backend file exists at: $loginPath</p>";
} else {
    echo "<p>❌ Login backend file missing at: $loginPath</p>";
}

// Test 6: Create a test user if none exist
echo "<h3>6. Test User Creation</h3>";
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['test@login.com']);
    if (!$stmt->fetch()) {
        // Create test user
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, username, password, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, 'active', NOW(), NOW())
        ");
        $stmt->execute([
            'Test User',
            'test@login.com',
            'testuser',
            password_hash('password123', PASSWORD_DEFAULT)
        ]);
        echo "<p>✅ Test user created:</p>";
        echo "<p><strong>Email:</strong> test@login.com</p>";
        echo "<p><strong>Password:</strong> password123</p>";
    } else {
        echo "<p>ℹ️ Test user already exists:</p>";
        echo "<p><strong>Email:</strong> test@login.com</p>";
        echo "<p><strong>Password:</strong> password123</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error creating test user: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔧 Troubleshooting Steps:</h3>";
echo "<ol>";
echo "<li><strong>If role column missing 'customer':</strong> Run <a href='backend/update-users-table.php'>update-users-table.php</a></li>";
echo "<li><strong>If no users exist:</strong> <a href='register.html'>Register a new user</a></li>";
echo "<li><strong>Test login:</strong> <a href='login.html'>Try logging in</a> with test@login.com / password123</li>";
echo "<li><strong>Check browser console:</strong> Press F12 and look for JavaScript errors</li>";
echo "<li><strong>Check network tab:</strong> See if form submission reaches backend/login.php</li>";
echo "</ol>";

echo "<p><a href='login.html' class='btn btn-primary'>Test Login Now</a></p>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Diagnostics - The Stitch House</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px 12px; border: 1px solid #ddd; }
        th { background: #f5f5f5; }
        .btn { padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <!-- Content inserted above -->
</body>
</html>