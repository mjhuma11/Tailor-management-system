<?php
/**
 * Customer Dashboard
 * The Stitch House - Customer Portal
 */

require_once 'backend/config.php';

// Check if customer is logged in
if (!isCustomerLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    redirectWithMessage('login.html', 'Please log in to access your dashboard.', 'error');
}

$user = getCurrentUser($pdo);
$customer = getCurrentCustomer($pdo);

if (!$user || !$customer) {
    redirectWithMessage('login.html', 'Session expired. Please log in again.', 'error');
}

// Get customer's orders
try {
    $stmt = $pdo->prepare("
        SELECT o.*, 
               DATE_FORMAT(o.received_date, '%M %d, %Y') as formatted_received_date,
               DATE_FORMAT(o.promised_date, '%M %d, %Y') as formatted_promised_date,
               DATE_FORMAT(o.delivery_date, '%M %d, %Y') as formatted_delivery_date
        FROM `order` o 
        WHERE o.customer_id = ? AND o.deleted_at IS NULL 
        ORDER BY o.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$customer['id']]);
    $orders = $stmt->fetchAll();
} catch (Exception $e) {
    $orders = [];
}

// Get flash message
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - The Stitch House</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
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
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($user['name']) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="customer-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a class="dropdown-item" href="customer-measurements.php"><i class="fas fa-ruler-combined"></i> My Measurements</a></li>
                        <li><a class="dropdown-item" href="upload-measurements.php"><i class="fas fa-upload"></i> Upload Measurements</a></li>
                        <li><a class="dropdown-item" href="my-orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                        <li><a class="dropdown-item" href="my-profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="backend/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home"></i> Home
                </a>
            </div>
        </div>
    </nav>

    <!-- Dashboard Section -->
    <section class="py-5">
        <div class="container">
            <!-- Flash Message -->
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?= $flashMessage['type'] === 'error' ? 'danger' : $flashMessage['type'] ?> alert-dismissible fade show">
                    <?= htmlspecialchars($flashMessage['text']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Welcome Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h2 class="card-title mb-2">Welcome back, <?= htmlspecialchars($user['name']) ?>!</h2>
                                    <p class="card-text mb-0">
                                        <i class="fas fa-id-card"></i> Customer ID: <?= htmlspecialchars($customer['customer_code'] ?? $customer['id']) ?>
                                        <span class="ms-3"><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></span>
                                        <span class="ms-3"><i class="fas fa-phone"></i> <?= htmlspecialchars($customer['phone']) ?></span>
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <a href="index.php#contact" class="btn btn-light">
                                        <i class="fas fa-plus"></i> New Order
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-shopping-bag fa-2x text-primary mb-2"></i>
                            <h5 class="card-title"><?= count($orders) ?></h5>
                            <p class="card-text text-muted">Total Orders</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                            <h5 class="card-title">
                                <?= count(array_filter($orders, function($o) { return in_array($o['order_status'], ['received', 'in_progress']); })) ?>
                            </h5>
                            <p class="card-text text-muted">In Progress</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <h5 class="card-title">
                                <?= count(array_filter($orders, function($o) { return $o['order_status'] === 'ready'; })) ?>
                            </h5>
                            <p class="card-text text-muted">Ready for Pickup</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-truck fa-2x text-info mb-2"></i>
                            <h5 class="card-title">
                                <?= count(array_filter($orders, function($o) { return $o['order_status'] === 'delivered'; })) ?>
                            </h5>
                            <p class="card-text text-muted">Delivered</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Measurement Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-gradient" style="background: linear-gradient(45deg, #28a745, #20c997);">
                        <div class="card-body text-white">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="card-title mb-2">
                                        <i class="fas fa-ruler-combined"></i> Measurement Center
                                    </h5>
                                    <p class="card-text mb-0">
                                        Submit your measurements for the perfect fit. Choose from manual entry or file upload.
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <a href="customer-measurements.php" class="btn btn-light me-2">
                                        <i class="fas fa-keyboard"></i> Enter Measurements
                                    </a>
                                    <a href="upload-measurements.php" class="btn btn-outline-light">
                                        <i class="fas fa-upload"></i> Upload File
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-history"></i> Recent Orders
                            </h5>
                            <a href="my-orders.php" class="btn btn-outline-primary btn-sm">
                                View All Orders
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($orders)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No orders yet</h5>
                                    <p class="text-muted">Start by placing your first order with us!</p>
                                    <a href="index.php#contact" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Place Order
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Order #</th>
                                                <th>Item</th>
                                                <th>Status</th>
                                                <th>Promised Date</th>
                                                <th>Amount</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($orders as $order): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($order['title']) ?>
                                                        <br>
                                                        <small class="text-muted"><?= htmlspecialchars($order['cloth_type']) ?></small>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $status_colors = [
                                                            'received' => 'warning',
                                                            'in_progress' => 'info',
                                                            'ready' => 'success',
                                                            'delivered' => 'primary',
                                                            'cancelled' => 'danger'
                                                        ];
                                                        $color = $status_colors[$order['order_status']] ?? 'secondary';
                                                        ?>
                                                        <span class="badge bg-<?= $color ?>">
                                                            <?= ucfirst(str_replace('_', ' ', $order['order_status'])) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= $order['formatted_promised_date'] ?></td>
                                                    <td>
                                                        <strong>$<?= number_format($order['amount_charged'], 2) ?></strong>
                                                        <?php if ($order['amount_paid'] > 0): ?>
                                                            <br>
                                                            <small class="text-success">Paid: $<?= number_format($order['amount_paid'], 2) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="order-details.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
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
    
    <style>
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }
        
        .bg-primary {
            background: linear-gradient(45deg, #e74c3c, #f39c12) !important;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #e74c3c, #f39c12);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(45deg, #f39c12, #e74c3c);
            transform: translateY(-1px);
        }
    </style>
</body>
</html>