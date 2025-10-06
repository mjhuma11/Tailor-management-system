<?php
require_once 'includes/config.php';
requireAuth();

$currentAdmin = getCurrentAdmin($mysqli);
$flashMessages = getFlashMessages();

// Get order ID
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$order_id) {
    header("Location: orders.php");
    exit();
}

// Get order details
try {
    $stmt = $mysqli->prepare("
        SELECT o.*, c.fullname as customer_name 
        FROM `order` o 
        LEFT JOIN customer c ON o.customer_id = c.id 
        WHERE o.id = ? AND o.deleted_at IS NULL
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    
    if (!$order) {
        showError("Order not found!");
        header("Location: orders.php");
        exit();
    }
} catch(Exception $e) {
    showError("Error fetching order: " . $e->getMessage());
    header("Location: orders.php");
    exit();
}

// Get customers for dropdown
try {
    $result = $mysqli->query("SELECT id, fullname, phone FROM customer WHERE status = 'active' ORDER BY fullname");
    $customers = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }
} catch(Exception $e) {
    $customers = [];
}

// Handle form submission
if ($_POST) {
    try {
        $customer_id = sanitize($_POST['customer_id']);
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $cloth_type = sanitize($_POST['cloth_type']);
        $fabric_details = sanitize($_POST['fabric_details']);
        $received_date = sanitize($_POST['received_date']);
        $promised_date = sanitize($_POST['promised_date']);
        $delivery_date = !empty($_POST['delivery_date']) ? sanitize($_POST['delivery_date']) : null;
        $amount_charged = sanitize($_POST['amount_charged']);
        $amount_paid = sanitize($_POST['amount_paid']);
        $order_status = sanitize($_POST['order_status']);
        $payment_status = sanitize($_POST['payment_status']);
        $priority = sanitize($_POST['priority']);
        $special_instructions = sanitize($_POST['special_instructions']);
        
        $stmt = $mysqli->prepare("
            UPDATE `order` SET 
                customer_id = ?, title = ?, description = ?, cloth_type = ?, 
                fabric_details = ?, received_date = ?, promised_date = ?, delivery_date = ?,
                amount_charged = ?, amount_paid = ?, order_status = ?, payment_status = ?,
                priority = ?, special_instructions = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            "isssssssddsssi",
            $customer_id, $title, $description, $cloth_type, $fabric_details,
            $received_date, $promised_date, $delivery_date, $amount_charged, 
            $amount_paid, $order_status, $payment_status, $priority, 
            $special_instructions, $order_id
        );
        $stmt->execute();
        $stmt->close();
        
        showSuccess("Order updated successfully!");
        header("Location: orders.php");
        exit();
        
    } catch(Exception $e) {
        showError("Error updating order: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order - The Stitch House Admin</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Admin CSS -->
    <link href="assets/admin.css" rel="stylesheet">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <nav class="admin-sidebar">
            <div class="sidebar-header">
                <h4 class="sidebar-title">The Stitch House</h4>
            </div>
            
            <div class="sidebar-menu">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="add-order.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Add Order</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link active" href="orders.php">
                            <i class="fas fa-list-alt"></i>
                            <span>View/Edit Orders</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="customers.php">
                            <i class="fas fa-users"></i>
                            <span>Customers</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="sidebar-footer">
                <div class="admin-user-info">
                    <div class="admin-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="admin-details">
                        <span class="admin-name"><?= htmlspecialchars($currentAdmin['name']) ?></span>
                        <small class="admin-role"><?= ucfirst($currentAdmin['role']) ?></small>
                    </div>
                    <a href="logout.php" class="logout-btn" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="admin-content">
            <!-- Top Bar -->
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="content-title">Edit Order #<?= htmlspecialchars($order['order_number']) ?></h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="orders.php">Orders</a></li>
                                <li class="breadcrumb-item active">Edit Order</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="header-actions">
                        <a href="orders.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if ($flashMessages): ?>
                <?php foreach ($flashMessages as $type => $message): ?>
                    <div class="alert alert-<?= $type == 'error' ? 'danger' : $type ?> alert-dismissible fade show">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Edit Order Form -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-edit"></i> Order Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="customer_id" class="form-label">Customer *</label>
                                        <select class="form-select" id="customer_id" name="customer_id" required>
                                            <option value="">Select Customer</option>
                                            <?php foreach ($customers as $customer): ?>
                                                <option value="<?= $customer['id'] ?>" 
                                                        <?= $customer['id'] == $order['customer_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($customer['fullname']) ?> - <?= htmlspecialchars($customer['phone']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="title" class="form-label">Order Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" required 
                                               value="<?= htmlspecialchars($order['title']) ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($order['description']) ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="cloth_type" class="form-label">Cloth Type *</label>
                                        <input type="text" class="form-control" id="cloth_type" name="cloth_type" required
                                               value="<?= htmlspecialchars($order['cloth_type']) ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="fabric_details" class="form-label">Fabric Details</label>
                                        <input type="text" class="form-control" id="fabric_details" name="fabric_details"
                                               value="<?= htmlspecialchars($order['fabric_details']) ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="received_date" class="form-label">Received Date *</label>
                                        <input type="date" class="form-control" id="received_date" name="received_date" 
                                               value="<?= $order['received_date'] ?>" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="promised_date" class="form-label">Promised Date *</label>
                                        <input type="date" class="form-control" id="promised_date" name="promised_date" 
                                               value="<?= $order['promised_date'] ?>" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="delivery_date" class="form-label">Delivery Date</label>
                                        <input type="date" class="form-control" id="delivery_date" name="delivery_date"
                                               value="<?= $order['delivery_date'] ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="amount_charged" class="form-label">Amount Charged *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="amount_charged" name="amount_charged" 
                                                   step="0.01" min="0" value="<?= $order['amount_charged'] ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="amount_paid" class="form-label">Amount Paid</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="amount_paid" name="amount_paid" 
                                                   step="0.01" min="0" value="<?= $order['amount_paid'] ?>">
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="payment_status" class="form-label">Payment Status</label>
                                        <select class="form-select" id="payment_status" name="payment_status">
                                            <option value="pending" <?= $order['payment_status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="partial" <?= $order['payment_status'] == 'partial' ? 'selected' : '' ?>>Partial</option>
                                            <option value="paid" <?= $order['payment_status'] == 'paid' ? 'selected' : '' ?>>Paid</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="order_status" class="form-label">Order Status</label>
                                        <select class="form-select" id="order_status" name="order_status">
                                            <option value="received" <?= $order['order_status'] == 'received' ? 'selected' : '' ?>>Received</option>
                                            <option value="in_progress" <?= $order['order_status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                            <option value="ready" <?= $order['order_status'] == 'ready' ? 'selected' : '' ?>>Ready</option>
                                            <option value="delivered" <?= $order['order_status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                            <option value="cancelled" <?= $order['order_status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="priority" class="form-label">Priority</label>
                                        <select class="form-select" id="priority" name="priority">
                                            <option value="low" <?= $order['priority'] == 'low' ? 'selected' : '' ?>>Low</option>
                                            <option value="normal" <?= $order['priority'] == 'normal' ? 'selected' : '' ?>>Normal</option>
                                            <option value="high" <?= $order['priority'] == 'high' ? 'selected' : '' ?>>High</option>
                                            <option value="urgent" <?= $order['priority'] == 'urgent' ? 'selected' : '' ?>>Urgent</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="special_instructions" class="form-label">Special Instructions</label>
                                    <textarea class="form-control" id="special_instructions" name="special_instructions" rows="3"><?= htmlspecialchars($order['special_instructions']) ?></textarea>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="orders.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Order
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Order Summary -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i> Order Summary
                            </h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Order Number:</strong></td>
                                    <td><?= htmlspecialchars($order['order_number']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Customer:</strong></td>
                                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td><?= formatDate($order['created_at']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Balance:</strong></td>
                                    <td>
                                        <?php 
                                        $balance = $order['amount_charged'] - $order['amount_paid'];
                                        echo formatCurrency($balance);
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-bolt"></i> Quick Actions
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-info btn-sm" onclick="setStatus('in_progress')">
                                    <i class="fas fa-play"></i> Start Working
                                </button>
                                <button class="btn btn-outline-success btn-sm" onclick="setStatus('ready')">
                                    <i class="fas fa-check"></i> Mark Ready
                                </button>
                                <button class="btn btn-outline-primary btn-sm" onclick="setStatus('delivered')">
                                    <i class="fas fa-truck"></i> Mark Delivered
                                </button>
                                <hr>
                                <button class="btn btn-outline-danger btn-sm" onclick="setStatus('cancelled')">
                                    <i class="fas fa-times"></i> Cancel Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function setStatus(status) {
            document.getElementById('order_status').value = status;
            
            // Auto-set delivery date when marking as delivered
            if (status === 'delivered' && !document.getElementById('delivery_date').value) {
                document.getElementById('delivery_date').value = new Date().toISOString().split('T')[0];
            }
        }

        // Auto-calculate payment status based on amounts
        document.getElementById('amount_paid').addEventListener('input', function() {
            const charged = parseFloat(document.getElementById('amount_charged').value) || 0;
            const paid = parseFloat(this.value) || 0;
            const paymentStatus = document.getElementById('payment_status');
            
            if (paid === 0) {
                paymentStatus.value = 'pending';
            } else if (paid >= charged) {
                paymentStatus.value = 'paid';
            } else {
                paymentStatus.value = 'partial';
            }
        });
    </script>
</body>
</html>