<?php
session_start();

// Get flash message
$flashMessage = null;
if (isset($_SESSION['flash_message'])) {
    $flashMessage = [
        'text' => $_SESSION['flash_message'],
        'type' => $_SESSION['flash_type'] ?? 'info'
    ];
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - The Stitch House</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <span class="brand-text">The Stitch House</span>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>
        </div>
    </nav>

    <!-- Login Section -->
    <section class="login-section py-5">
        <div class="container">
            <!-- Flash Messages -->
            <?php if ($flashMessage): ?>
                <div class="row justify-content-center mb-3">
                    <div class="col-lg-5 col-md-7">
                        <div class="alert alert-<?= $flashMessage['type'] === 'error' ? 'danger' : $flashMessage['type'] ?> alert-dismissible fade show">
                            <?= htmlspecialchars($flashMessage['text']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7">
                    <div class="login-card">
                        <div class="text-center mb-4">
                            <h2 class="login-title">Welcome Back</h2>
                            <p class="text-muted">Sign in to your account</p>
                            <p class="mb-0">Don't have an account? 
                                <a href="register.php" class="text-decoration-none fw-bold text-primary">
                                    Sign Up
                                </a>
                            </p>
                        </div>

                        <form id="loginForm" action="backend/login.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                        <label class="form-check-label" for="remember">
                                            Remember me
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6 text-end">
                                    <a href="forgot-password.html" class="text-decoration-none">
                                        Forgot Password?
                                    </a>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Sign In
                                </button>
                            </div>

                            <div class="text-center">
                                <p class="mb-0">Don't have an account?
                                    <a href="register.php" class="text-decoration-none fw-bold text-primary">
                                        Sign Up
                                    </a>
                                </p>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="text-muted mb-3">Or sign in with:</p>
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-outline-danger">
                                    <i class="fab fa-google"></i> Google
                                </button>
                                <button class="btn btn-outline-primary">
                                    <i class="fab fa-facebook"></i> Facebook
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">&copy; 2024 The Stitch House. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="index.php#contact" class="text-decoration-none me-3">Contact</a>
                    <a href="index.php#about" class="text-decoration-none me-3">About</a>
                    <a href="index.php#services" class="text-decoration-none">Services</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Custom JS -->
    <script>
        $(document).ready(function () {
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
            
            // Toggle password visibility
            $('#togglePassword').on('click', function () {
                const passwordField = $('#password');
                const passwordFieldType = passwordField.attr('type');

                if (passwordFieldType === 'password') {
                    passwordField.attr('type', 'text');
                    $(this).html('<i class="fas fa-eye-slash"></i>');
                } else {
                    passwordField.attr('type', 'password');
                    $(this).html('<i class="fas fa-eye"></i>');
                }
            });

            // Form submission
            $('#loginForm').on('submit', function (e) {
                const email = $('#email').val();
                const password = $('#password').val();

                if (!email || !password) {
                    e.preventDefault();
                    alert('Please fill in all fields.');
                    return;
                }

                // Show loading state
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Signing In...').prop('disabled', true);

                // Form will submit normally to backend/login.php
            });
        });
    </script>

    <style>
        .login-section {
            min-height: 80vh;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
            align-items: center;
        }

        .login-card {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .login-title {
            color: #2c3e50;
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        .form-control:focus {
            border-color: #e74c3c;
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
        }

        .btn-primary {
            background: linear-gradient(45deg, #e74c3c, #f39c12);
            border: none;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #f39c12, #e74c3c);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }

        .btn-outline-danger,
        .btn-outline-primary {
            border-radius: 25px;
            padding: 8px 20px;
        }
    </style>
</body>

</html>