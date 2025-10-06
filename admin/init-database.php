<?php
/**
 * Database Initialization Script
 * This script creates the database and imports the schema
 */

try {
    // Connect to MySQL server without specifying database
    $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "<h2>Database Initialization - The Stitch House</h2>";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `stitch` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✅ Database 'stitch' created or already exists.</p>";
    
    // Switch to the stitch database
    $pdo->exec("USE `stitch`");
    echo "<p>✅ Connected to 'stitch' database.</p>";
    
    // Read and execute the SQL file
    $sqlFile = '../DB/stitch(1).sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        
        // Remove the initial database creation commands since we already handled them
        $sql = preg_replace('/^CREATE DATABASE.*?;/m', '', $sql);
        $sql = preg_replace('/^USE.*?;/m', '', $sql);
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $successCount = 0;
        $totalStatements = count($statements);
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^(--|\/\*)/', $statement)) {
                try {
                    $pdo->exec($statement);
                    $successCount++;
                } catch (PDOException $e) {
                    // Ignore "table already exists" errors
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        echo "<p class='text-warning'>⚠️ Warning: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
            }
        }
        
        echo "<p>✅ Database schema imported successfully! ($successCount/$totalStatements statements executed)</p>";
    } else {
        echo "<p class='text-danger'>❌ SQL file not found: $sqlFile</p>";
    }
    
    echo "<hr>";
    echo "<h3>✅ Database initialization completed!</h3>";
    echo "<p><a href='setup.php' class='btn btn-primary'>Continue to Setup</a></p>";
    echo "<p><a href='../login.html' class='btn btn-secondary'>Go to Login</a></p>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<p>Please make sure:</p>";
    echo "<ul>";
    echo "<li>XAMPP is running</li>";
    echo "<li>MySQL service is started</li>";
    echo "<li>No password is set for root user (default XAMPP setup)</li>";
    echo "</ul>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Initialization - The Stitch House</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            padding-top: 3rem;
            padding-bottom: 3rem;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body p-5">
                        <!-- Content will be inserted above -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>