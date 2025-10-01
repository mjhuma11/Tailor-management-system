<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Login - The Stitch House</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Debug Login Form</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        if ($_POST) {
                            echo "<div class='alert alert-info'>";
                            echo "<h5>Form Data Received:</h5>";
                            echo "<pre>" . print_r($_POST, true) . "</pre>";
                            echo "</div>";
                            
                            // Try to process login
                            require_once 'backend/config.php';
                            
                            $email = sanitize($_POST['email'] ?? '');
                            $password = $_POST['password'] ?? '';
                            
                            echo "<div class='alert alert-warning'>";
                            echo "<h5>Processing Login:</h5>";
                            echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
                            echo "<p><strong>Password Length:</strong> " . strlen($password) . "</p>";
                            
                            // Check if user exists
                            try {
                                $stmt = $pdo->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ? AND status = 'active'");
                                $stmt->execute([$email]);
                                $user = $stmt->fetch();
                                
                                if ($user) {
                                    echo "<p>✅ User found in database</p>";
                                    echo "<p><strong>User ID:</strong> {$user['id']}</p>";
                                    echo "<p><strong>Name:</strong> {$user['name']}</p>";
                                    echo "<p><strong>Role:</strong> {$user['role']}</p>";
                                    
                                    if (password_verify($password, $user['password'])) {
                                        echo "<p>✅ Password is correct</p>";
                                        echo "<div class='alert alert-success'>Login should work! Redirecting to actual login...</div>";
                                        echo "<script>setTimeout(() => { window.location.href = 'backend/login.php'; }, 2000);</script>";
                                    } else {
                                        echo "<p>❌ Password is incorrect</p>";
                                    }
                                } else {
                                    echo "<p>❌ User not found with email: " . htmlspecialchars($email) . "</p>";
                                    
                                    // Show available users
                                    $stmt = $pdo->query("SELECT email, role FROM users WHERE status = 'active' LIMIT 5");
                                    $users = $stmt->fetchAll();
                                    echo "<p><strong>Available users:</strong></p>";
                                    echo "<ul>";
                                    foreach ($users as $u) {
                                        echo "<li>{$u['email']} ({$u['role']})</li>";
                                    }
                                    echo "</ul>";
                                }
                            } catch (Exception $e) {
                                echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
                            }
                            echo "</div>";
                        }
                        ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       value="<?= htmlspecialchars($_POST['email'] ?? 'test@login.com') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required
                                       placeholder="Try: password123">
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Debug Login</button>
                            <a href="login.html" class="btn btn-secondary">Back to Normal Login</a>
                        </form>
                        
                        <hr>
                        <div class="mt-3">
                            <h5>Quick Actions:</h5>
                            <a href="test-login.php" class="btn btn-info btn-sm">Run Diagnostics</a>
                            <a href="backend/update-users-table.php" class="btn btn-warning btn-sm">Update Users Table</a>
                            <a href="register.html" class="btn btn-success btn-sm">Register New User</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>