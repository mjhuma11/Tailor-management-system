<?php
require_once 'includes/config.php';
requireAuth();

$currentAdmin = getCurrentAdmin($mysqli);
$flashMessages = getFlashMessages();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Build query
$where_conditions = ["o.deleted_at IS NULL"];
$params = [];

if ($search) {
    $where_conditions[] = "(o.order_number LIKE ? OR o.title LIKE ? OR c.fullname LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($status_filter) {
    $where_conditions[] = "o.order_status = ?";
    $params[] = $status_filter;
}

$where_clause = implode(" AND ", $where_conditions);

try {
    // Get total count
    $count_query = "
        SELECT COUNT(*) as total 
        FROM `order` o 
        LEFT JOIN customer c ON o.customer_id = c.id 
        WHERE $where_clause
    ";
    
    if (!empty($params)) {
        $stmt = $mysqli->prepare($count_query);
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $total_records = $result->fetch_assoc()['total'];
        $stmt->close();
    } else {
        $result = $mysqli->query($count_query);
        $total_records = $result->fetch_assoc()['total'];
    }
    
    $total_pages = ceil($total_records / $limit);

    // Get orders
    $query = "
        SELECT o.*, c.fullname as customer_name, c.phone as customer_phone,
               u.name as received_by_name
        FROM `order` o 
        LEFT JOIN customer c ON o.customer_id = c.id 
        LEFT JOIN users u ON o.received_by = u.id
        WHERE $where_clause 
        ORDER BY o.created_at DESC 
        LIMIT $limit OFFSET $offset
    ";
    
    $orders = [];
    if (!empty($params)) {
        $stmt = $mysqli->prepare($query);
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        $stmt->close();
    } else {
        $result = $mysqli->query($query);
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }

} catch(Exception $e) {
    showError("Error fetching orders: " . $e->getMessage());
    $orders = [];
    $total_pages = 0;
}

// Handle quick status updates
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    try {
        $order_id = (int)$_POST['order_id'];
        $new_status = sanitize($_POST['new_status']);
        
        $stmt = $mysqli->prepare("UPDATE `order` SET order_status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        $stmt->close();
        
        showSuccess("Order status updated successfully!");
        header("Location: orders.php?" . http_build_query($_GET));
        exit();
        
    } catch(Exception $e) {
        showError("Error updating order status: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - The Stitch House Admin</title>
    
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
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="admin-content">
            <!-- Top Bar -->
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="content-title">Orders Management</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Orders</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="header-actions">
                        <a href="add-order.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Order
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

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="orders.php" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search Orders</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Order number, title, or customer name">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status Filter</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="received" <?= $status_filter == 'received' ? 'selected' : '' ?>>Received</option>
                                <option value="in_progress" <?= $status_filter == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="ready" <?= $status_filter == 'ready' ? 'selected' : '' ?>>Ready</option>
                                <option value="delivered" <?= $status_filter == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="col-md-5 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="orders.php" class="btn btn-outline-secondary">
                                <i class="fas fa-refresh"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list-alt"></i> Orders List 
                        <span class="badge bg-primary ms-2"><?= $total_records ?> Total</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No orders found</h5>
                            <p class="text-muted">Start by adding your first order</p>
                            <a href="add-order.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Order
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Title</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Promised Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= formatDate($order['created_at']) ?></small>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= htmlspecialchars($order['customer_phone']) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($order['title']) ?>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($order['cloth_type']) ?></small>
                                            </td>
                                            <td>
                                                <strong><?= formatCurrency($order['amount_charged']) ?></strong>
                                                <?php if ($order['amount_paid'] > 0): ?>
                                                    <br>
                                                    <small class="text-success">Paid: <?= formatCurrency($order['amount_paid']) ?></small>
                                                <?php endif; ?>
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
                                            <td>
                                                <?php
                                                $priority_colors = [
                                                    'urgent' => 'danger',
                                                    'high' => 'warning',
                                                    'normal' => 'info',
                                                    'low' => 'secondary'
                                                ];
                                                $priority_color = $priority_colors[$order['priority']] ?? 'info';
                                                ?>
                                                <span class="badge bg-<?= $priority_color ?>">
                                                    <?= ucfirst($order['priority']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= formatDate($order['promised_date']) ?>
                                                <?php if (strtotime($order['promised_date']) < time() && $order['order_status'] != 'delivered'): ?>
                                                    <br><small class="text-danger">Overdue!</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                            data-bs-toggle="dropdown">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="edit-order.php?id=<?= $order['id'] ?>">
                                                                <i class="fas fa-edit"></i> Edit Order
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <button class="dropdown-item" onclick="updateStatus(<?= $order['id'] ?>, 'received')">
                                                                <i class="fas fa-inbox"></i> Mark as Received
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item" onclick="updateStatus(<?= $order['id'] ?>, 'in_progress')">
                                                                <i class="fas fa-cog"></i> Mark as In Progress
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item" onclick="updateStatus(<?= $order['id'] ?>, 'ready')">
                                                                <i class="fas fa-check"></i> Mark as Ready
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item" onclick="updateStatus(<?= $order['id'] ?>, 'delivered')">
                                                                <i class="fas fa-truck"></i> Mark as Delivered
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="card-footer">
                                <nav aria-label="Orders pagination">
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Status Update Form (Hidden) -->
    <form id="statusUpdateForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="order_id" id="statusOrderId">
        <input type="hidden" name="new_status" id="statusNewStatus">
    </form>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function updateStatus(orderId, newStatus) {
            if (confirm('Are you sure you want to update the order status?')) {
                document.getElementById('statusOrderId').value = orderId;
                document.getElementById('statusNewStatus').value = newStatus;
                document.getElementById('statusUpdateForm').submit();
            }
        }
    </script>
</body>
</html>