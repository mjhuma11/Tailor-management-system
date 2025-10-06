<?php
require_once 'includes/config.php';
requireAuth();

$currentAdmin = getCurrentAdmin($mysqli);
$flashMessages = getFlashMessages();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$customer_filter = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;

// Build query
$where_conditions = ["1=1"];
$params = [];
$param_types = "";

if ($search) {
    $where_conditions[] = "(c.fullname LIKE ? OR c.phone LIKE ? OR c.customer_code LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "sss";
}

if ($customer_filter) {
    $where_conditions[] = "c.id = ?";
    $params[] = $customer_filter;
    $param_types .= "i";
}

$where_clause = implode(" AND ", $where_conditions);

try {
    // Get total count
    $count_query = "
        SELECT COUNT(DISTINCT c.id) as total 
        FROM customer c 
        INNER JOIN measurement m ON c.id = m.customer_id 
        WHERE $where_clause
    ";
    $stmt = $mysqli->prepare($count_query);
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $total_records = $result->fetch_assoc()['total'];
    $total_pages = ceil($total_records / $limit);
    $stmt->close();

    // Get customers with measurements
    $query = "
        SELECT c.*, 
               COUNT(m.id) as measurement_count,
               MAX(m.measurement_date) as last_measurement_date,
               u.name as last_taken_by
        FROM customer c 
        INNER JOIN measurement m ON c.id = m.customer_id 
        LEFT JOIN users u ON m.taken_by = u.id
        WHERE $where_clause 
        GROUP BY c.id
        ORDER BY MAX(m.updated_at) DESC 
        LIMIT $limit OFFSET $offset
    ";
    $stmt = $mysqli->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    $stmt->close();

} catch(Exception $e) {
    showError("Error fetching measurements: " . $e->getMessage());
    $customers = [];
    $total_pages = 0;
}

// Get all customers for filter dropdown
try {
    $result = $mysqli->query("SELECT id, fullname, customer_code FROM customer WHERE status = 'active' ORDER BY fullname");
    $allCustomers = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $allCustomers[] = $row;
        }
    }
} catch(Exception $e) {
    $allCustomers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Measurements - The Stitch House Admin</title>
    
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
        <main class="admin-main">
            <!-- Top Header -->
            <div class="admin-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">CUSTOMER MEASUREMENTS</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Measurements</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="header-actions">
                        <a href="measurement-parts.php" class="btn btn-outline-primary">
                            <i class="fas fa-cog"></i> Manage Parts
                        </a>
                        <a href="customers.php" class="btn btn-primary">
                            <i class="fas fa-users"></i> View Customers
                        </a>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if ($flashMessages): ?>
                <?php foreach ($flashMessages as $type => $message): ?>
                    <div class="alert alert-<?= $type == 'error' ? 'danger' : $type ?> alert-dismissible fade show mx-4 mt-3">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Search and Filter -->
            <div class="admin-content">
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="measurements.php" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search Customers</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= htmlspecialchars($search) ?>" 
                                       placeholder="Name, phone, or customer code">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="customer_id" class="form-label">Specific Customer</label>
                                <select class="form-select" id="customer_id" name="customer_id">
                                    <option value="">All Customers</option>
                                    <?php foreach ($allCustomers as $cust): ?>
                                        <option value="<?= $cust['id'] ?>" <?= $customer_filter == $cust['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cust['fullname']) ?> 
                                            <?php if ($cust['customer_code']): ?>
                                                (<?= htmlspecialchars($cust['customer_code']) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="measurements.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-refresh"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Measurements Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-ruler-combined"></i> Customer Measurements 
                            <span class="badge bg-primary ms-2"><?= $total_records ?> Customers</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($customers)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-ruler-combined fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No measurements found</h5>
                                <p class="text-muted">No customers have measurements recorded yet</p>
                                <a href="customers.php" class="btn btn-primary">
                                    <i class="fas fa-users"></i> View Customers
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Customer</th>
                                            <th>Contact</th>
                                            <th>Gender</th>
                                            <th>Measurements</th>
                                            <th>Last Updated</th>
                                            <th>Taken By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($customers as $customer): ?>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong><?= htmlspecialchars($customer['fullname']) ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            ID: <?= htmlspecialchars($customer['customer_code'] ?? $customer['id']) ?>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <i class="fas fa-phone text-muted"></i> <?= htmlspecialchars($customer['phone']) ?>
                                                        <?php if ($customer['email']): ?>
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="fas fa-envelope"></i> <?= htmlspecialchars($customer['email']) ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $gender_colors = [
                                                        'Male' => 'primary',
                                                        'Female' => 'danger',
                                                        'Other' => 'info'
                                                    ];
                                                    $color = $gender_colors[$customer['gender']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?= $color ?>">
                                                        <?= $customer['gender'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="measurement-count me-2">
                                                            <span class="badge bg-success fs-6"><?= $customer['measurement_count'] ?></span>
                                                        </div>
                                                        <small class="text-muted">measurements</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?= formatDate($customer['last_measurement_date']) ?>
                                                </td>
                                                <td>
                                                    <?= $customer['last_taken_by'] ? htmlspecialchars($customer['last_taken_by']) : 'N/A' ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="customer-measurements.php?customer_id=<?= $customer['id'] ?>" 
                                                           class="btn btn-sm btn-primary" title="View/Edit Measurements">
                                                            <i class="fas fa-ruler-combined"></i>
                                                        </a>
                                                        <a href="measurement-report.php?customer_id=<?= $customer['id'] ?>" 
                                                           class="btn btn-sm btn-outline-info" title="Print Report">
                                                            <i class="fas fa-print"></i>
                                                        </a>
                                                        <a href="add-order.php?customer_id=<?= $customer['id'] ?>" 
                                                           class="btn btn-sm btn-outline-success" title="Create Order">
                                                            <i class="fas fa-plus"></i>
                                                        </a>
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
                                    <nav aria-label="Measurements pagination">
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
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>