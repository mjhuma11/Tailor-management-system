<?php
require_once 'includes/config.php';
requireAuth();

$currentAdmin = getCurrentAdmin($mysqli);
$dashboardStats = getDashboardStats($mysqli);
$chartData = getChartData($mysqli, 30);
$flashMessages = getFlashMessages();

// Handle Quick Forms Submissions
if ($_POST) {
    try {
        // Quick Order Form
        if (isset($_POST['customer_id']) && isset($_POST['title'])) {
            $customer_id = sanitize($_POST['customer_id']);
            $title = sanitize($_POST['title']);
            $cloth_type = sanitize($_POST['cloth_type']);
            $amount_charged = sanitize($_POST['amount_charged']);
            $received_date = sanitize($_POST['received_date']);
            $promised_date = sanitize($_POST['promised_date']);
            $priority = sanitize($_POST['priority'] ?? 'normal');
            
            // Generate order number
            $order_number = 'ORD' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            $stmt = $mysqli->prepare("
                INSERT INTO `order` (
                    customer_id, order_number, title, cloth_type, 
                    received_date, promised_date, received_by, 
                    amount_charged, priority, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->bind_param("isssssids", $customer_id, $order_number, $title, $cloth_type,
                $received_date, $promised_date, $_SESSION['user_id'], $amount_charged, $priority);
            $stmt->execute();
            $stmt->close();
            
            showSuccess("Order #$order_number created successfully!");
            header("Location: dashboard.php");
            exit();
        }
        
        // Quick Customer Form
        if (isset($_POST['fullname']) && isset($_POST['phone'])) {
            $fullname = sanitize($_POST['fullname']);
            $phone = sanitize($_POST['phone']);
            $gender = sanitize($_POST['gender']);
            $email = sanitize($_POST['email'] ?? '');
            $address = sanitize($_POST['address'] ?? '');
            
            $stmt = $mysqli->prepare("
                INSERT INTO customer (
                    fullname, phone, gender, email, address, 
                    status, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())
            ");
            
            $stmt->bind_param("sssss", $fullname, $phone, $gender, $email, $address);
            $stmt->execute();
            $stmt->close();
            
            showSuccess("Customer '$fullname' added successfully!");
            header("Location: dashboard.php");
            exit();
        }
        
        // Quick Income Form
        if (isset($_POST['amount']) && isset($_POST['entry_date'])) {
            $amount = sanitize($_POST['amount']);
            $description = sanitize($_POST['description']);
            $entry_date = sanitize($_POST['entry_date']);
            
            // Get default income category or create one
            $result = $mysqli->query("SELECT id FROM inc_cat WHERE status = 'active' LIMIT 1");
            $category = $result ? $result->fetch_assoc() : null;
            $category_id = $category ? $category['id'] : null;
            
            $stmt = $mysqli->prepare("
                INSERT INTO income (
                    amount, description, income_date, inc_cat_id,
                    added_by, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->bind_param("dssii", $amount, $description, $entry_date, $category_id, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
            
            showSuccess("Income of " . formatCurrency($amount) . " added successfully!");
            header("Location: dashboard.php");
            exit();
        }
        
    } catch(Exception $e) {
        showError("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - The Stitch House Admin</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom Admin CSS -->
    <link href="assets/admin.css" rel="stylesheet">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Top Header -->
            <div class="admin-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-outline-secondary d-lg-none me-3 sidebar-toggle" type="button">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="page-title">DASHBOARD</h1>
                    </div>
                    <div class="header-actions">
                        <a href="../index.php" class="btn btn-outline-secondary" target="_blank">
                            <i class="fas fa-external-link-alt"></i> View Website
                        </a>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (!empty($flashMessages)): ?>
                <?php foreach ($flashMessages as $type => $message): ?>
                    <div class="alert alert-<?php echo $type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show mx-4 mt-3">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Dashboard Content -->
            <div class="admin-content">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-number"><?php echo number_format($dashboardStats['total_customers']); ?></div>
                                    <div class="stat-label">Total Customers!</div>
                                </div>
                            </div>
                            <div class="stat-card-footer">
                                <a href="customers.php" class="stat-link">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card stat-card-success">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-number"><?php echo number_format($dashboardStats['total_orders']); ?></div>
                                    <div class="stat-label">Total Orders!</div>
                                </div>
                            </div>
                            <div class="stat-card-footer">
                                <a href="orders.php" class="stat-link">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card stat-card-warning">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-number"><?php echo formatCurrency($dashboardStats['last_30_days_income']); ?></div>
                                    <div class="stat-label">Last 30 Days Income!</div>
                                </div>
                            </div>
                            <div class="stat-card-footer">
                                <a href="income.php" class="stat-link">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card stat-card-danger">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-number"><?php echo number_format($dashboardStats['pending_orders']); ?></div>
                                    <div class="stat-label">Pending Orders!</div>
                                </div>
                            </div>
                            <div class="stat-card-footer">
                                <a href="orders.php?status=received" class="stat-link">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="activity-card">
                            <div class="activity-header">
                                <h5 class="activity-title">Recent Orders</h5>
                                <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="activity-body">
                                <?php
                                try {
                                    $result = $mysqli->query("
                                        SELECT o.*, c.fullname as customer_name 
                                        FROM `order` o 
                                        LEFT JOIN customer c ON o.customer_id = c.id 
                                        WHERE o.deleted_at IS NULL 
                                        ORDER BY o.created_at DESC 
                                        LIMIT 5
                                    ");
                                    $recentOrders = [];
                                    if ($result) {
                                        while ($row = $result->fetch_assoc()) {
                                            $recentOrders[] = $row;
                                        }
                                    }
                                    
                                    if (empty($recentOrders)): ?>
                                        <p class="text-muted">No recent orders found.</p>
                                    <?php else: ?>
                                        <ul class="activity-list">
                                            <?php foreach ($recentOrders as $order): 
                                                // Determine status badge color
                                                $statusClass = 'info';
                                                switch($order['order_status']) {
                                                    case 'received': $statusClass = 'warning'; break;
                                                    case 'delivered': $statusClass = 'success'; break;
                                                    case 'ready': $statusClass = 'primary'; break;
                                                    case 'in_progress': $statusClass = 'info'; break;
                                                    case 'cancelled': $statusClass = 'danger'; break;
                                                }
                                            ?>
                                                <li class="activity-item">
                                                    <div class="activity-icon">
                                                        <i class="fas fa-shopping-cart"></i>
                                                    </div>
                                                    <div class="activity-info">
                                                        <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                                        <small class="text-muted d-block">
                                                            <?php echo htmlspecialchars($order['customer_name'] ?? 'Unknown Customer'); ?> - 
                                                            <?php echo formatCurrency($order['amount_charged']); ?>
                                                        </small>
                                                        <small class="text-muted">
                                                            <?php echo formatDate($order['created_at'], 'M d, Y g:i A'); ?>
                                                        </small>
                                                    </div>
                                                    <div class="activity-status">
                                                        <span class="badge bg-<?php echo $statusClass; ?>">
                                                            <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                                                        </span>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif;
                                } catch (Exception $e) {
                                    echo "<p class='text-danger'>Error loading recent orders: " . htmlspecialchars($e->getMessage()) . "</p>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="activity-card">
                            <div class="activity-header">
                                <h5 class="activity-title">Recent Customers</h5>
                                <a href="customers.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="activity-body">
                                <?php
                                try {
                                    $result = $mysqli->query("
                                        SELECT * FROM customer 
                                        WHERE status = 'active' 
                                        ORDER BY created_at DESC 
                                        LIMIT 5
                                    ");
                                    $recentCustomers = [];
                                    if ($result) {
                                        while ($row = $result->fetch_assoc()) {
                                            $recentCustomers[] = $row;
                                        }
                                    }
                                    
                                    if (empty($recentCustomers)): ?>
                                        <p class="text-muted">No recent customers found.</p>
                                    <?php else: ?>
                                        <ul class="activity-list">
                                            <?php foreach ($recentCustomers as $customer): ?>
                                                <li class="activity-item">
                                                    <div class="activity-icon">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div class="activity-info">
                                                        <strong><?php echo htmlspecialchars($customer['fullname']); ?></strong>
                                                        <small class="text-muted d-block">
                                                            <?php echo htmlspecialchars($customer['email'] ?? 'No email'); ?>
                                                        </small>
                                                        <small class="text-muted">
                                                            <?php echo formatDate($customer['created_at'], 'M d, Y'); ?>
                                                        </small>
                                                    </div>
                                                    <div class="activity-status">
                                                        <span class="badge bg-success">Active</span>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif;
                                } catch (Exception $e) {
                                    echo "<p class='text-danger'>Error loading recent customers: " . htmlspecialchars($e->getMessage()) . "</p>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        $(document).ready(function() {
            // Mobile sidebar toggle functionality
            $('.sidebar-toggle').on('click', function() {
                $('.admin-sidebar').toggleClass('show');
                
                // Add overlay for mobile
                if ($('.admin-sidebar').hasClass('show')) {
                    $('body').append('<div class="sidebar-overlay show"></div>');
                } else {
                    $('.sidebar-overlay').remove();
                }
            });
            
            // Close sidebar when clicking overlay
            $(document).on('click', '.sidebar-overlay', function() {
                $('.admin-sidebar').removeClass('show');
                $(this).remove();
            });
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        });
    </script>
</body>
</html>